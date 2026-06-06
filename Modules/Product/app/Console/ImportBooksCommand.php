<?php

namespace Modules\Product\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * php artisan product:import-books /path/to/final.xlsx
 * php artisan product:import-books /path/to/final.xlsx --dry-run
 * php artisan product:import-books /path/to/final.xlsx --stock=50
 * php artisan product:import-books /path/to/final.xlsx --sub-warehouse=2
 * php artisan product:import-books /path/to/final.xlsx --skip-stock
 */
class ImportBooksCommand extends Command
{
    protected $signature = 'product:import-books
        {file              : Absolute or relative path to the .xlsx file}
        {--dry-run         : Validate and preview without writing to DB}
        {--skip-stock      : Skip sub_warehouse_products and stock_movements}
        {--stock=100       : Stock quantity per product}
        {--sub-warehouse=1 : Sub-warehouse ID for stock}';

    protected $description = 'Import products, books, categories, authors, contracts and stock from Excel';

    private array $categoryCache = [];
    private array $authorCache   = [];

    private array $stats = [
        'total_rows'         => 0,
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

    // -------------------------------------------------------------------------

    public function handle(): int
    {
        $filePath     = $this->argument('file');
        $dryRun       = (bool) $this->option('dry-run');
        $skipStock    = (bool) $this->option('skip-stock');
        $stockQty     = (int)  $this->option('stock');
        $subWarehouse = (int)  $this->option('sub-warehouse');

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return self::FAILURE;
        }

        $this->printHeader($dryRun, $filePath, $stockQty, $subWarehouse, $skipStock);

        // Load Excel
        $this->line('  📂 Loading Excel file...');
        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();

        // Parse
        $this->line('  🔍 Parsing rows...');
        try {
            $rows = $this->parseSheet($sheet);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->stats['total_rows'] = count($rows);
        $this->info("  ✔  Found <fg=yellow>{$this->stats['total_rows']}</> rows");
        $this->line('');

        // Pre-load caches
        $this->line('  📦 Loading existing categories and authors from DB...');
        $this->loadExistingCategories();
        $this->loadExistingAuthors();
        $this->info("  ✔  Categories in DB : " . count($this->categoryCache));
        $this->info("  ✔  Authors in DB    : " . count($this->authorCache));
        $this->line('');

        if ($dryRun) {
            $this->warn('  ⚠  DRY RUN — nothing will be written to the database.');
            $this->line('');
        }

        // Progress bar
        $this->line('  ⚙️  Processing...');
        $this->line('');

        $bar = $this->output->createProgressBar($this->stats['total_rows']);
        $bar->setFormat('  %current%/%max% [%bar%] %percent:3s%%  %message%');
        $bar->setMessage('');
        $bar->start();

        foreach ($rows as $index => $row) {
            $bar->setMessage($this->truncate($row['book_name'], 40));

            try {
                if ($dryRun) {
                    $this->dryRunRow($row);
                } else {
                    DB::transaction(fn () => $this->processRow($row, $skipStock, $stockQty, $subWarehouse));
                }
            } catch (\Throwable $e) {
                $this->stats['errors'][] = [
                    'row'     => $index + 2,
                    'book_id' => $row['book_id'],
                    'name'    => $row['book_name'],
                    'message' => $e->getMessage(),
                ];
                Log::error('product:import-books error', [
                    'row'     => $index + 2,
                    'book_id' => $row['book_id'],
                    'error'   => $e->getMessage(),
                ]);
            }

            $bar->advance();
        }

        $bar->setMessage('done ✔');
        $bar->finish();

        $this->line('');
        $this->line('');
        $this->printSummary($dryRun);

        return empty($this->stats['errors']) ? self::SUCCESS : self::FAILURE;
    }

    // =========================================================================
    // Row processors
    // =========================================================================

    private function processRow(array $row, bool $skipStock, int $stockQty, int $subWarehouse): void
    {
        if (DB::table('products')->where('id', $row['product_id'])->exists()) {
            $this->stats['products_skipped']++;
            $this->stats['books_skipped']++;
            return;
        }

        $categoryId    = $this->resolveCategory($row['category']);
        $subCategoryId = $this->resolveCategory($row['sub_category'], $categoryId);

        DB::table('products')->insert([
            'id'          => $row['product_id'],
            'name'        => $row['book_name'],
            'type'        => 'book',
            'sku'         => $row['sku'],
            'description' => null,
            'base_price'  => $row['price'],
            'status'      => $row['status'],
            'created_by'  => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        $this->stats['products_inserted']++;

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

        if (! empty($row['authors'])) {
            $contractId = DB::table('author_book_contracts')->insertGetId([
                'book_name'                   => $row['book_name'],
                'contract_date'               => now()->toDateString(),
                'contract_price'              => 0,
                'percentage_from_book_profit' => 0,
                'created_by'                  => 1,
                'created_at'                  => now(),
                'updated_at'                  => now(),
                'book_id'                     => $row['book_id'],
            ]);
            $this->stats['contracts_inserted']++;

            foreach ($row['authors'] as $i => $authorName) {
                DB::table('contract_authors')->insert([
                    'contract_id'       => $contractId,
                    'author_id'         => $this->resolveAuthor($authorName),
                    'is_representative' => $i === 0 ? 1 : 0,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        }

        if (! $skipStock) {
            $stockExists = DB::table('sub_warehouse_products')
                ->where('sub_warehouse_id', $subWarehouse)
                ->where('product_id', $row['product_id'])
                ->exists();

            if (! $stockExists) {
                DB::table('sub_warehouse_products')->insert([
                    'sub_warehouse_id' => $subWarehouse,
                    'product_id'       => $row['product_id'],
                    'quantity'         => $stockQty,
                    'created_by'       => 1,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                DB::table('stock_movements')->insert([
                    'product_id'            => $row['product_id'],
                    'from_sub_warehouse_id' => null,
                    'to_sub_warehouse_id'   => $subWarehouse,
                    'quantity'              => $stockQty,
                    'movement_type'         => 'inbound',
                    'reason'                => 'initial_import',
                    'reference_id'          => null,
                    'notes'                 => 'Imported via product:import-books',
                    'user_id'               => 1,
                    'created_by'            => 1,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);

                $this->stats['stock_inserted']++;
            }
        }
    }

    private function dryRunRow(array $row): void
    {
        if (DB::table('products')->where('id', $row['product_id'])->exists()) {
            $this->stats['products_skipped']++;
            $this->stats['books_skipped']++;
            return;
        }

        $this->stats['products_inserted']++;
        $this->stats['books_inserted']++;

        foreach ($row['authors'] as $name) {
            if (! isset($this->authorCache[$name])) {
                $this->authorCache[$name] = -1;
                $this->stats['authors_created']++;
            }
        }

        if (! empty($row['authors'])) {
            $this->stats['contracts_inserted']++;
        }

        $this->stats['stock_inserted']++;
    }

    // =========================================================================
    // Sheet parser
    // =========================================================================

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

        $required = [
            'Book ID', 'Product ID', 'Book Name', 'SKU', 'Price', 'Status',
            'ISBN', 'Pages', 'Cover Type', 'Published Date', 'Language',
            'Is Translated', 'Translated From', 'Translated To', 'Translator',
            'Category', 'Sub Category', 'Author 1',
        ];

        $missing = array_diff($required, array_keys($headers));
        if (! empty($missing)) {
            throw new \RuntimeException('Missing columns: ' . implode(', ', $missing));
        }

        $get = fn(string $col, int $r) => $this->cellValue($sheet, $headers[$col] . $r);

        $rows = [];
        for ($r = 2; $r <= $maxRow; $r++) {
            $bookId    = (int) $get('Book ID', $r);
            $productId = (int) $get('Product ID', $r);
            if (! $bookId || ! $productId) {
                continue;
            }

            $isTranslated = strtolower($get('Is Translated', $r) ?? '') === 'yes';
            $rawAuthors   = $get('Author 1', $r) ?? '';
            $authors      = array_values(array_filter(
                array_map(fn($a) => trim(str_replace(["\n", "\r"], '', $a)), explode(',', $rawAuthors)),
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

    // =========================================================================
    // Resolvers
    // =========================================================================

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
            return $this->categoryCache[$key] = (int) $existing;
        }

        $id = DB::table('book_categories')->insertGetId([
            'name'       => $name,
            'parent_id'  => $parentId,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->stats['categories_created']++;
        return $this->categoryCache[$key] = $id;
    }

    private function resolveAuthor(string $name): int
    {
        $name = trim($name);

        if (isset($this->authorCache[$name])) {
            return (int) $this->authorCache[$name];
        }

        $existing = DB::table('authors')->where('full_name', $name)->value('id');

        if ($existing) {
            return $this->authorCache[$name] = (int) $existing;
        }

        $id = DB::table('authors')->insertGetId([
            'full_name'  => $name,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->stats['authors_created']++;
        return $this->authorCache[$name] = $id;
    }

    // =========================================================================
    // Helpers
    // =========================================================================

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
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)
                ->format('Y-m-d');
        }
        try {
            return (new \DateTime($value))->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    private function truncate(string $str, int $max): string
    {
        return mb_strlen($str) > $max ? mb_substr($str, 0, $max) . '…' : $str;
    }

    // =========================================================================
    // Output
    // =========================================================================

    private function printHeader(bool $dryRun, string $file, int $stock, int $sw, bool $skipStock): void
    {
        $mode = $dryRun ? '<fg=cyan>DRY RUN</>' : '<fg=green>LIVE</>';
        $this->line('');
        $this->line('  <fg=blue>┌─────────────────────────────────────────────┐</>');
        $this->line("  <fg=blue>│</> Books Import  {$mode}");
        $this->line('  <fg=blue>├─────────────────────────────────────────────┤</>');
        $this->line("  <fg=blue>│</> File          : " . basename($file));
        $this->line("  <fg=blue>│</> Stock qty     : {$stock}");
        $this->line("  <fg=blue>│</> Sub-warehouse : {$sw}");
        $this->line("  <fg=blue>│</> Skip stock    : " . ($skipStock ? 'yes' : 'no'));
        $this->line('  <fg=blue>└─────────────────────────────────────────────┘</>');
        $this->line('');
    }

    private function printSummary(bool $dryRun): void
    {
        $errCount = count($this->stats['errors']);
        $color    = $errCount > 0 ? 'red' : 'green';

        $this->line("  <fg=blue>┌─────────────────────────────────────────────┐</>");
        $this->line("  <fg=blue>│</> Summary" . ($dryRun ? ' <fg=cyan>(DRY RUN)</>' : ''));
        $this->line("  <fg=blue>├─────────────────────────────────────────────┤</>");
        $this->line("  <fg=blue>│</> Total rows parsed    : <fg=yellow>{$this->stats['total_rows']}</>");
        $this->line("  <fg=blue>│</> Categories created   : {$this->stats['categories_created']}");
        $this->line("  <fg=blue>│</> Products inserted    : <fg=green>{$this->stats['products_inserted']}</>");
        $this->line("  <fg=blue>│</> Products skipped     : {$this->stats['products_skipped']}");
        $this->line("  <fg=blue>│</> Books inserted       : <fg=green>{$this->stats['books_inserted']}</>");
        $this->line("  <fg=blue>│</> Books skipped        : {$this->stats['books_skipped']}");
        $this->line("  <fg=blue>│</> Authors created      : {$this->stats['authors_created']}");
        $this->line("  <fg=blue>│</> Contracts created    : {$this->stats['contracts_inserted']}");
        $this->line("  <fg=blue>│</> Stock rows created   : {$this->stats['stock_inserted']}");
        $this->line("  <fg=blue>│</> Errors               : <fg={$color}>{$errCount}</>");
        $this->line("  <fg=blue>└─────────────────────────────────────────────┘</>");

        if ($errCount > 0) {
            $this->line('');
            $this->error('  Rows with errors:');
            $this->table(
                ['Excel Row', 'Book ID', 'Book Name', 'Error'],
                array_map(fn($e) => [
                    $e['row'],
                    $e['book_id'],
                    $this->truncate($e['name'] ?? '', 35),
                    $e['message'],
                ], $this->stats['errors'])
            );
        } else {
            $this->line('');
            $icon = $dryRun ? '🔍  Dry run done. Run without --dry-run to apply.' : '✅  Import completed successfully.';
            $this->info("  {$icon}");
        }

        $this->line('');
    }
}