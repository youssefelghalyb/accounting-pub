<?php

namespace Modules\Product\Imports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * BooksImport
 *
 * Imports from the Excel sheet (final__2_.xlsx format) into:
 *   - book_categories  (create if not exists, by name)
 *   - products
 *   - books
 *   - authors          (create if not exists, by exact name)
 *   - author_book_contracts
 *   - contract_authors
 *   - sub_warehouse_products  (qty = 100 per product, sub_warehouse_id = 1)
 *   - stock_movements         (inbound record per product)
 *
 * Safe to re-run: skips rows whose book_id or product_id already exist.
 *
 * Usage from a controller:
 *   $result = (new BooksImport())->import($request->file('excel'));
 *   return response()->json($result);
 *
 * Or via Artisan / job:
 *   (new BooksImport())->importFromPath(storage_path('books.xlsx'));
 */
class BooksImport
{
    private const SUB_WAREHOUSE_ID = 1;
    private const DEFAULT_STOCK    = 100;

    /** in-memory name → id cache, populated as we insert or load */
    private array $categoryCache = [];
    private array $authorCache   = [];

    private array $stats = [
        'categories_created' => 0,
        'products_inserted'  => 0,
        'products_skipped'   => 0,
        'books_inserted'     => 0,
        'books_skipped'      => 0,
        'authors_created'    => 0,
        'contracts_inserted' => 0,
        'stock_inserted'     => 0,
        'errors'             => [],
    ];

    // ─────────────────────────────────────────────────────────────────────────

    public function import($uploadedFile): array
    {
        $path = $uploadedFile->getRealPath();
        return $this->importFromPath($path);
    }

    public function importFromPath(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet();

        $this->loadExistingCategories();
        $this->loadExistingAuthors();

        $rows = $this->parseSheet($sheet);

        DB::transaction(function () use ($rows) {
            foreach ($rows as $index => $row) {
                try {
                    $this->processRow($row);
                } catch (\Throwable $e) {
                    $this->stats['errors'][] = [
                        'row'     => $index + 2,
                        'book_id' => $row['book_id'],
                        'message' => $e->getMessage(),
                    ];
                    Log::error('BooksImport row error', [
                        'row'     => $index + 2,
                        'book_id' => $row['book_id'],
                        'error'   => $e->getMessage(),
                    ]);
                }
            }
        });

        return $this->stats;
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function parseSheet(Worksheet $sheet): array
    {
        $maxRow = $sheet->getHighestDataRow();

        // Map header label → column letter
        $headers = [];
        foreach ($sheet->getRowIterator(1, 1) as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $h = trim((string) $cell->getValue());
                if ($h !== '') {
                    $headers[$h] = $cell->getColumn();
                }
            }
        }

        $required = [
            'Book ID', 'Product ID', 'Book Name', 'SKU', 'Price', 'Status',
            'ISBN', 'Pages', 'Cover Type', 'Published Date', 'Language',
            'Is Translated', 'Translated From', 'Translated To', 'Translator',
            'Category', 'Sub Category', 'Author 1',
        ];

        foreach ($required as $col) {
            if (! isset($headers[$col])) {
                throw new \RuntimeException("Missing required column in Excel: {$col}");
            }
        }

        $get = fn(string $col, int $row) => $this->cellValue($sheet, $headers[$col] . $row);

        $rows = [];

        for ($r = 2; $r <= $maxRow; $r++) {
            $bookId    = (int) $get('Book ID', $r);
            $productId = (int) $get('Product ID', $r);

            if (! $bookId || ! $productId) {
                continue;
            }

            $isTranslated = strtolower($get('Is Translated', $r) ?? '') === 'yes';

            $rawAuthors = $get('Author 1', $r) ?? '';
            $authors    = array_values(array_filter(
                array_map(
                    fn($a) => trim(str_replace(["\n", "\r"], '', $a)),
                    explode(',', $rawAuthors)
                ),
                fn($a) => $a !== ''
            ));

            $rows[] = [
                'book_id'         => $bookId,
                'product_id'      => $productId,
                'book_name'       => $get('Book Name', $r),
                'sku'             => $get('SKU', $r),
                'price'           => (float) ($get('Price', $r) ?? 0),
                'status'          => strtolower($get('Status', $r) ?? 'active') === 'active' ? 'active' : 'inactive',
                'isbn'            => $get('ISBN', $r),
                'pages'           => is_numeric($get('Pages', $r)) ? (int) $get('Pages', $r) : null,
                'cover_type'      => strtolower($get('Cover Type', $r) ?? 'soft') === 'hard' ? 'hard' : 'soft',
                'published_at'    => $this->parseDate($get('Published Date', $r)),
                'language'        => $get('Language', $r),
                'is_translated'   => $isTranslated ? 1 : 0,
                'translated_from' => $isTranslated ? $get('Translated From', $r) : null,
                'translated_to'   => $isTranslated ? $get('Translated To', $r)   : null,
                'translator_name' => $get('Translator', $r),
                'category'        => $get('Category', $r),
                'sub_category'    => $get('Sub Category', $r),
                'authors'         => $authors,
            ];
        }

        return $rows;
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function processRow(array $row): void
    {
        // ── Skip if product already exists ────────────────────────────────────
        if (DB::table('products')->where('id', $row['product_id'])->exists()) {
            $this->stats['products_skipped']++;
            $this->stats['books_skipped']++;
            return;
        }

        // ── Categories ────────────────────────────────────────────────────────
        $categoryId    = $this->resolveCategory($row['category']);
        $subCategoryId = $this->resolveCategory($row['sub_category'], $categoryId);

        // ── Product ───────────────────────────────────────────────────────────
        DB::table('products')->insert([
            'id'         => $row['product_id'],
            'name'       => $row['book_name'],
            'type'       => 'book',
            'sku'        => $row['sku'],
            'description'=> null,
            'base_price' => $row['price'],
            'status'     => $row['status'],
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->stats['products_inserted']++;

        // ── Book ──────────────────────────────────────────────────────────────
        DB::table('books')->insert([
            'id'              => $row['book_id'],
            'product_id'      => $row['product_id'],
            'category_id'     => $categoryId,
            'sub_category_id' => $subCategoryId,
            'isbn'            => $row['isbn'],
            'num_of_pages'    => $row['pages'],
            'cover_type'      => $row['cover_type'],
            'published_at'    => $row['published_at'],
            'language'        => $row['language'],
            'is_translated'   => $row['is_translated'],
            'translated_from' => $row['translated_from'],
            'translated_to'   => $row['translated_to'],
            'translator_name' => $row['translator_name'],
            'created_by'      => 1,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
        $this->stats['books_inserted']++;

        // ── Authors + Contract ────────────────────────────────────────────────
        if (! empty($row['authors'])) {
            $contractId = DB::table('author_book_contracts')->insertGetId([
                'book_name'                  => $row['book_name'],
                'contract_date'              => now()->toDateString(),
                'contract_price'             => 0,
                'percentage_from_book_profit'=> 0,
                'created_by'                 => 1,
                'created_at'                 => now(),
                'updated_at'                 => now(),
                'book_id'                    => $row['book_id'],
            ]);
            $this->stats['contracts_inserted']++;

            foreach ($row['authors'] as $index => $authorName) {
                $authorId = $this->resolveAuthor($authorName);

                DB::table('contract_authors')->insert([
                    'contract_id'      => $contractId,
                    'author_id'        => $authorId,
                    'is_representative'=> $index === 0 ? 1 : 0,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }

        // ── Stock ─────────────────────────────────────────────────────────────
        // Check if a stock row already exists for this product/sub-warehouse
        $existingStock = DB::table('sub_warehouse_products')
            ->where('sub_warehouse_id', self::SUB_WAREHOUSE_ID)
            ->where('product_id', $row['product_id'])
            ->exists();

        if (! $existingStock) {
            DB::table('sub_warehouse_products')->insert([
                'sub_warehouse_id' => self::SUB_WAREHOUSE_ID,
                'product_id'       => $row['product_id'],
                'quantity'         => self::DEFAULT_STOCK,
                'created_by'       => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            DB::table('stock_movements')->insert([
                'product_id'          => $row['product_id'],
                'from_sub_warehouse_id' => null,
                'to_sub_warehouse_id' => self::SUB_WAREHOUSE_ID,
                'quantity'            => self::DEFAULT_STOCK,
                'movement_type'       => 'inbound',
                'reason'              => 'initial_import',
                'reference_id'        => null,
                'notes'               => 'Imported from Excel',
                'user_id'             => 1,
                'created_by'          => 1,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            $this->stats['stock_inserted']++;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function resolveCategory(?string $name, ?int $parentId = null): ?int
    {
        if (! $name || trim($name) === '') {
            return null;
        }

        $name = trim($name);
        $key  = $parentId ? "{$parentId}::{$name}" : $name;

        if (isset($this->categoryCache[$key])) {
            return $this->categoryCache[$key];
        }

        $existing = DB::table('book_categories')
            ->where('name', $name)
            ->where('parent_id', $parentId)
            ->value('id');

        if ($existing) {
            $this->categoryCache[$key] = (int) $existing;
            return $this->categoryCache[$key];
        }

        // Create it
        $id = DB::table('book_categories')->insertGetId([
            'name'       => $name,
            'parent_id'  => $parentId,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->categoryCache[$key] = $id;
        $this->stats['categories_created']++;

        return $id;
    }

    private function resolveAuthor(string $name): int
    {
        $name = trim($name);

        if (isset($this->authorCache[$name])) {
            return $this->authorCache[$name];
        }

        $existing = DB::table('authors')
            ->where('full_name', $name)
            ->value('id');

        if ($existing) {
            $this->authorCache[$name] = (int) $existing;
            return $this->authorCache[$name];
        }

        $id = DB::table('authors')->insertGetId([
            'full_name'  => $name,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->authorCache[$name] = $id;
        $this->stats['authors_created']++;

        return $id;
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function loadExistingCategories(): void
    {
        DB::table('book_categories')->get(['id', 'name', 'parent_id'])->each(function ($row) {
            $key = $row->parent_id ? "{$row->parent_id}::{$row->name}" : $row->name;
            $this->categoryCache[$key] = $row->id;
        });
    }

    private function loadExistingAuthors(): void
    {
        DB::table('authors')->get(['id', 'full_name'])->each(function ($row) {
            $this->authorCache[$row->full_name] = $row->id;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function cellValue(Worksheet $sheet, string $coord): ?string
    {
        $val = $sheet->getCell($coord)->getCalculatedValue();
        if ($val === null || $val === '') {
            return null;
        }
        $str = trim((string) $val);
        return $str === '' ? null : $str;
    }

    private function parseDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        // PhpSpreadsheet may return a numeric serial or a formatted string
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)
                ->format('Y-m-d');
        }
        try {
            return (new \DateTime($value))->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }
}