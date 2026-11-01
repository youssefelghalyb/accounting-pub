<?php

namespace Modules\Product\Console;

use Illuminate\Console\Command;
use Modules\Product\Services\BooksSheetReader;
use Modules\Product\Services\BooksSyncService;

/**
 * php artisan product:sync-books /path/new.xlsx            # DRY RUN (default): nothing is saved
 * php artisan product:sync-books /path/new.xlsx --apply    # really update
 * php artisan product:sync-books /path/new.xlsx --apply --relink   # also repair Book ID <-> Product ID pairs when provably safe
 *
 * See docs/books-sync-runbook.md.
 */
class SyncBooksCommand extends Command
{
    protected $signature = 'product:sync-books
        {file : Path to the .xlsx master sheet}
        {--apply : Save the changes (without it everything is rolled back)}
        {--relink : Repair Book ID/Product ID mismatches that have no invoice/royalty history}
        {--report-dir= : Where to write the CSV reports (default storage/app/book-sync)}';

    protected $description = 'Update existing products/books/categories/contractors in place from the master Excel sheet, without touching invoices, payments, stock or royalty history';

    public function handle(): int
    {
        $file = $this->argument('file');
        if (! is_file($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $parsed = (new BooksSheetReader())->read($file);
        if ($parsed['errors']) {
            $this->error('Sheet validation failed — nothing was changed:');
            foreach ($parsed['errors'] as $e) {
                $this->line("  - {$e}");
            }

            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');
        $this->info(($apply ? 'APPLYING' : 'DRY RUN (nothing will be saved)') . ' — ' . count($parsed['rows']) . ' rows from ' . basename($file));

        $svc = new BooksSyncService();
        try {
            $stats = $svc->run($parsed['rows'], $apply, (bool) $this->option('relink'));
        } catch (\Throwable $e) {
            $this->error('ABORTED and rolled back: ' . $e->getMessage());

            return self::FAILURE;
        }

        $dir = $this->option('report-dir') ?: storage_path('app/book-sync');
        @mkdir($dir, 0775, true);
        $stamp = date('Ymd_His');
        $this->csv("{$dir}/{$stamp}_changes.csv", ['book_id', 'product_id', 'name', 'table', 'column', 'old', 'new'], $svc->changes);
        $this->csv("{$dir}/{$stamp}_conflicts.csv", ['excel_row', 'book_id', 'product_id', 'name', 'type', 'detail'], $svc->conflicts);

        $this->table(['Metric', 'Count'], collect($stats)->except(['changed_columns', 'applied'])->map(fn ($v, $k) => [$k, $v])->values()->all());
        if ($stats['changed_columns']) {
            ksort($stats['changed_columns']);
            $this->line('Changed values per column:');
            $this->table(['table.column', 'rows changed'], collect($stats['changed_columns'])->map(fn ($v, $k) => [$k, $v])->values()->all());
        }
        if ($svc->conflicts) {
            $this->warn(count($svc->conflicts) . ' row(s) NOT applied (need a human) — first 15:');
            $this->table(['Row', 'Book', 'Product', 'Type', 'Detail'], array_map(
                fn ($c) => [$c['excel_row'], $c['book_id'], $c['product_id'], $c['type'], mb_strimwidth($c['detail'], 0, 110, '…')],
                array_slice($svc->conflicts, 0, 15)
            ));
        }
        $this->line("Reports: {$dir}/{$stamp}_changes.csv , {$dir}/{$stamp}_conflicts.csv");
        $this->info($apply ? 'Done — safety fingerprint of invoices/payments/stock/royalties matched before and after.' : 'Dry run finished. Re-run with --apply to save.');

        return self::SUCCESS;
    }

    private function csv(string $path, array $cols, array $rows): void
    {
        $h = fopen($path, 'w');
        fwrite($h, "\xEF\xBB\xBF"); // BOM so Excel opens Arabic correctly
        fputcsv($h, $cols);
        foreach ($rows as $r) {
            fputcsv($h, array_map(fn ($c) => $r[$c] ?? '', $cols));
        }
        fclose($h);
    }
}
