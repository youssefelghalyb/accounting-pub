<?php

namespace Modules\Product\Console;

use Illuminate\Console\Command;
use Modules\Product\Services\BooksImportService;

/**
 * php artisan product:import-books /path/to/final.xlsx
 * php artisan product:import-books /path/to/final.xlsx --sub-warehouse=2
 *
 * Delegates all row-processing logic to BooksImportService — shared with the web upload
 * endpoint (BooksImport) rather than a second hand-duplicated copy.
 */
class ImportBooksCommand extends Command
{
    protected $signature = 'product:import-books
        {file              : Absolute or relative path to the .xlsx file}
        {--sub-warehouse=1 : Default sub-warehouse ID for stock reconciliation}';

    protected $description = 'Import products, books, categories, contractors and stock from the master Excel sheet';

    public function handle(): int
    {
        $filePath = $this->argument('file');
        $subWarehouse = (int) $this->option('sub-warehouse');

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return self::FAILURE;
        }

        $this->info("Importing {$filePath} (sub-warehouse #{$subWarehouse})...");

        $stats = (new BooksImportService())->importFromPath($filePath, $subWarehouse);

        $this->line('');
        $this->line("  Total rows parsed        : {$stats['total_rows']}");
        $this->line("  Categories created       : {$stats['categories_created']}");
        $this->line("  Products inserted        : {$stats['products_inserted']}");
        $this->line("  Products skipped         : {$stats['products_skipped']}");
        $this->line("  Books inserted           : {$stats['books_inserted']}");
        $this->line("  Books skipped            : {$stats['books_skipped']}");
        $this->line("  Contractors created      : {$stats['contractors_created']}");
        $this->line("  Contractor books upserted: {$stats['contractor_books_upserted']}");
        $this->line("  Stock rows reconciled    : {$stats['stock_reconciled']}");
        $this->line("  Errors                   : " . count($stats['errors']));
        $this->line('');

        if (! empty($stats['errors'])) {
            $this->error('Rows with errors:');
            $this->table(
                ['Excel Row', 'Book ID', 'Book Name', 'Error'],
                array_map(fn ($e) => [$e['row'], $e['book_id'], $e['name'] ?? '', $e['message']], $stats['errors'])
            );

            return self::FAILURE;
        }

        $this->info('Import completed successfully.');

        return self::SUCCESS;
    }
}
