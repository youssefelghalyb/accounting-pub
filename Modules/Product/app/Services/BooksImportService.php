<?php

namespace Modules\Product\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Single shared Excel importer used by both the web upload (BooksImport) and the
 * `product:import-books` CLI command — previously two independent, hand-duplicated copies of
 * the same logic. Writes Contractor/contractor_books instead of Author/author_book_contracts,
 * per docs/contractor-migration-plan.md §6.
 *
 * Header set: Book ID, Product ID, Book Name, SKU, Price, Status, Description, ISBN, Pages,
 * Cover Type, Published Date, Language, Is Translated, Translated From, Translated To,
 * Translator, اشراف, تقديم, Category, Sub Category, Used in Sales, Author 1, المتعاقد,
 * الجنسية, الكمية.
 *
 * Safe to re-run: skips rows whose product_id already exists (books/products are never
 * updated by re-running this importer — only stock ["الكمية"] is reconciled on every run).
 */
class BooksImportService
{
    private const REQUIRED_HEADERS = [
        'Book ID', 'Product ID', 'Book Name', 'SKU', 'Price', 'Status',
        'ISBN', 'Pages', 'Cover Type', 'Published Date', 'Language',
        'Is Translated', 'Translated From', 'Translated To', 'Translator',
        'Category', 'Sub Category', 'Author 1',
    ];

    /** in-memory name → id cache, populated as we insert or load */
    private array $categoryCache = [];
    private array $contractorCache = [];

    private array $stats = [
        'total_rows' => 0,
        'categories_created' => 0,
        'products_inserted' => 0,
        'products_skipped' => 0,
        'books_inserted' => 0,
        'books_skipped' => 0,
        'contractors_created' => 0,
        'contractor_books_upserted' => 0,
        'stock_reconciled' => 0,
        'errors' => [],
    ];

    public function importFromPath(string $path, int $defaultSubWarehouseId = 1): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();

        $this->loadExistingCategories();
        $this->loadExistingContractors();

        $rows = $this->parseSheet($sheet);
        $this->stats['total_rows'] = count($rows);

        foreach ($rows as $index => $row) {
            try {
                DB::transaction(fn () => $this->processRow($row, $defaultSubWarehouseId));
            } catch (\Throwable $e) {
                $this->stats['errors'][] = [
                    'row' => $index + 2,
                    'book_id' => $row['book_id'],
                    'name' => $row['book_name'],
                    'message' => $e->getMessage(),
                ];
                Log::error('BooksImportService row error', [
                    'row' => $index + 2,
                    'book_id' => $row['book_id'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->stats;
    }

    // ─── Row processing ───────────────────────────────────────────────────────

    private function processRow(array $row, int $defaultSubWarehouseId): void
    {
        if (DB::table('products')->where('id', $row['product_id'])->exists()) {
            $this->stats['products_skipped']++;
            $this->stats['books_skipped']++;
            $this->reconcileStock($row, $defaultSubWarehouseId);

            return;
        }

        $categoryId = $this->resolveCategory($row['category']);
        $subCategoryId = $this->resolveCategory($row['sub_category'], $categoryId);

        DB::table('products')->insert([
            'id' => $row['product_id'],
            'name' => $row['book_name'],
            'type' => 'book',
            'sku' => $row['sku'],
            'description' => $row['description'],
            'base_price' => $row['price'],
            'status' => $row['status'],
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->stats['products_inserted']++;

        DB::table('books')->insert([
            'id' => $row['book_id'],
            'product_id' => $row['product_id'],
            'category_id' => $categoryId,
            'sub_category_id' => $subCategoryId,
            'isbn' => $row['isbn'],
            'num_of_pages' => $row['pages'],
            'cover_type' => $row['cover_type'],
            'published_at' => $row['published_at'],
            'language' => $row['language'],
            'is_translated' => $row['is_translated'],
            'translated_from' => $row['translated_from'],
            'translated_to' => $row['translated_to'],
            'translator_name' => $row['translator_name'],
            'authors' => $row['authors_text'],
            'supervisor' => $row['supervisor'],
            'introduction_by' => $row['introduction_by'],
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->stats['books_inserted']++;

        $this->syncContractor($row);

        if ($row['used_in_sales']) {
            $this->reconcileStock($row, $defaultSubWarehouseId);
        }
    }

    /**
     * المتعاقد: find-or-create Contractor by exact name (never duplicate), create/update the
     * contractor_books row for this book. Percentage/basis/dates aren't in the sheet, so a new
     * row gets zeroed defaults (matching the old importer's contract-creation defaults) and an
     * already-existing row is left as-is. الجنسية sets/updates the contractor's nationality.
     */
    private function syncContractor(array $row): void
    {
        if (! $row['contractor_name']) {
            return;
        }

        $contractorId = $this->resolveContractor($row['contractor_name'], $row['nationality']);

        $exists = DB::table('contractor_books')->where('book_id', $row['book_id'])->exists();

        if (! $exists) {
            DB::table('contractor_books')->insert([
                'book_id' => $row['book_id'],
                'contractor_id' => $contractorId,
                'profit_percentage' => 0,
                'percentage_basis' => 'sale_price',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->stats['contractor_books_upserted']++;
        }
    }

    /**
     * الكمية: reconciles sub_warehouse_products to this EXACT quantity (an adjustment, not
     * always-additive like the old importer's hardcoded "always insert 100 for new products") —
     * this importer is the master-Excel reconciliation pass, run after the one-time DB migration,
     * so it should make stock match the sheet rather than blindly add on top of it.
     */
    private function reconcileStock(array $row, int $subWarehouseId): void
    {
        if ($row['quantity'] === null) {
            return;
        }

        $stock = DB::table('sub_warehouse_products')
            ->where('sub_warehouse_id', $subWarehouseId)
            ->where('product_id', $row['product_id'])
            ->first();

        $targetQty = $row['quantity'];

        if (! $stock) {
            DB::table('sub_warehouse_products')->insert([
                'sub_warehouse_id' => $subWarehouseId,
                'product_id' => $row['product_id'],
                'quantity' => $targetQty,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $delta = $targetQty;
        } else {
            $delta = $targetQty - $stock->quantity;

            if ($delta === 0) {
                return;
            }

            DB::table('sub_warehouse_products')
                ->where('id', $stock->id)
                ->update(['quantity' => $targetQty, 'updated_at' => now()]);
        }

        DB::table('stock_movements')->insert([
            'product_id' => $row['product_id'],
            'from_sub_warehouse_id' => $delta < 0 ? $subWarehouseId : null,
            'to_sub_warehouse_id' => $delta > 0 ? $subWarehouseId : null,
            'quantity' => abs($delta),
            'movement_type' => $delta > 0 ? 'inbound' : 'outbound',
            'reason' => 'excel_reconciliation',
            'reference_id' => null,
            'notes' => 'Stock reconciled from master Excel import',
            'user_id' => 1,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->stats['stock_reconciled']++;
    }

    // ─── Sheet parsing ────────────────────────────────────────────────────────

    private function parseSheet(Worksheet $sheet): array
    {
        $maxRow = $sheet->getHighestDataRow();

        $headers = [];
        foreach ($sheet->getRowIterator(1, 1) as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $h = trim((string) $cell->getValue());
                if ($h !== '') {
                    $headers[$h] = $cell->getColumn();
                }
            }
        }

        foreach (self::REQUIRED_HEADERS as $col) {
            if (! isset($headers[$col])) {
                throw new \RuntimeException("Missing required column in Excel: {$col}");
            }
        }

        $get = fn (string $col, int $r) => isset($headers[$col]) ? $this->cellValue($sheet, $headers[$col] . $r) : null;

        $rows = [];

        for ($r = 2; $r <= $maxRow; $r++) {
            $bookId = (int) $get('Book ID', $r);
            $productId = (int) $get('Product ID', $r);

            if (! $bookId || ! $productId) {
                continue;
            }

            $isTranslated = strtolower($get('Is Translated', $r) ?? '') === 'yes';
            $rawAuthors = $get('Author 1', $r) ?? '';
            $authorsText = trim(str_replace(["\n", "\r"], '', $rawAuthors)) ?: null;

            $quantityRaw = $get('الكمية', $r);

            $rows[] = [
                'book_id' => $bookId,
                'product_id' => $productId,
                'book_name' => $get('Book Name', $r),
                'sku' => $get('SKU', $r),
                'price' => (float) ($get('Price', $r) ?? 0),
                'status' => strtolower($get('Status', $r) ?? 'active') === 'active' ? 'active' : 'inactive',
                'description' => $get('Description', $r),
                'isbn' => $get('ISBN', $r),
                'pages' => is_numeric($get('Pages', $r)) ? (int) $get('Pages', $r) : null,
                'cover_type' => strtolower($get('Cover Type', $r) ?? 'soft') === 'hard' ? 'hard' : 'soft',
                'published_at' => $this->parseDate($get('Published Date', $r)),
                'language' => $get('Language', $r),
                'is_translated' => $isTranslated ? 1 : 0,
                'translated_from' => $isTranslated ? $get('Translated From', $r) : null,
                'translated_to' => $isTranslated ? $get('Translated To', $r) : null,
                'translator_name' => $get('Translator', $r),
                'supervisor' => $get('اشراف', $r),
                'introduction_by' => $get('تقديم', $r),
                'category' => $get('Category', $r),
                'sub_category' => $get('Sub Category', $r),
                'used_in_sales' => strtolower($get('Used in Sales', $r) ?? 'yes') !== 'no',
                'authors_text' => $authorsText,
                'contractor_name' => $get('المتعاقد', $r),
                'nationality' => $get('الجنسية', $r),
                'quantity' => is_numeric($quantityRaw) ? (int) $quantityRaw : null,
            ];
        }

        return $rows;
    }

    // ─── Resolvers ────────────────────────────────────────────────────────────

    private function resolveCategory(?string $name, ?int $parentId = null): ?int
    {
        if (! $name || trim($name) === '') {
            return null;
        }

        $name = trim($name);
        $key = $parentId ? "{$parentId}::{$name}" : $name;

        if (isset($this->categoryCache[$key])) {
            return $this->categoryCache[$key];
        }

        $existing = DB::table('book_categories')
            ->where('name', $name)
            ->where('parent_id', $parentId)
            ->value('id');

        if ($existing) {
            return $this->categoryCache[$key] = (int) $existing;
        }

        $id = DB::table('book_categories')->insertGetId([
            'name' => $name,
            'parent_id' => $parentId,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->stats['categories_created']++;

        return $this->categoryCache[$key] = $id;
    }

    private function resolveContractor(string $name, ?string $nationality): int
    {
        $name = trim($name);

        if (isset($this->contractorCache[$name])) {
            $contractorId = $this->contractorCache[$name];

            if ($nationality) {
                DB::table('contractors')->where('id', $contractorId)->whereNull('nationality')
                    ->update(['nationality' => $nationality, 'updated_at' => now()]);
            }

            return $contractorId;
        }

        $existing = DB::table('contractors')->where('name', $name)->first();

        if ($existing) {
            if ($nationality && ! $existing->nationality) {
                DB::table('contractors')->where('id', $existing->id)
                    ->update(['nationality' => $nationality, 'updated_at' => now()]);
            }

            return $this->contractorCache[$name] = $existing->id;
        }

        $id = DB::table('contractors')->insertGetId([
            'name' => $name,
            'nationality' => $nationality,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->stats['contractors_created']++;

        return $this->contractorCache[$name] = $id;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function loadExistingCategories(): void
    {
        DB::table('book_categories')->get(['id', 'name', 'parent_id'])->each(function ($row) {
            $key = $row->parent_id ? "{$row->parent_id}::{$row->name}" : $row->name;
            $this->categoryCache[$key] = $row->id;
        });
    }

    private function loadExistingContractors(): void
    {
        DB::table('contractors')->get(['id', 'name'])->each(function ($row) {
            $this->contractorCache[$row->name] = $row->id;
        });
    }

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
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }
        try {
            return (new \DateTime($value))->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    public function getStats(): array
    {
        return $this->stats;
    }
}
