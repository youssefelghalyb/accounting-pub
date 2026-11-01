<?php

namespace Modules\Product\Console;

use Illuminate\Console\Command;
use Modules\Product\Services\BooksPruneService;
use Modules\Product\Services\BooksSheetReader;
use Modules\Product\Services\BooksSyncService;

/**
 * php artisan product:prune-books /path/new.xlsx                     # CHECK ONLY (default)
 * php artisan product:prune-books /path/new.xlsx --delete            # delete SAFE ones (asks to confirm)
 * php artisan product:prune-books /path/new.xlsx --delete --include-history --deactivate-blocked --yes
 *
 * Books in the DB that are not in the sheet. Anything used by a sales invoice (or purchase invoice,
 * royalty accrual, contract transaction, or with stock on hand) is never deleted.
 */
class PruneBooksCommand extends Command
{
    protected $signature = 'product:prune-books
        {file : Path to the .xlsx master sheet}
        {--delete : Actually delete the SAFE ones (without it this is a read-only check)}
        {--include-history : Also delete items whose only reference is old stock_movements}
        {--deactivate-blocked : Set BLOCKED (used) items to inactive instead of leaving them}
        {--yes : Skip the confirmation question}
        {--report-dir= : CSV output directory (default storage/app/book-sync)}';

    protected $description = 'List (and optionally delete) books that are not in the master sheet, checking sales invoices and other usage first';

    public function handle(): int
    {
        $file = $this->argument('file');
        if (! is_file($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }
        $parsed = (new BooksSheetReader())->read($file);
        if ($parsed['errors'] || ! $parsed['rows']) {
            $this->error('Sheet validation failed (or empty) — refusing to compute a delete list from it:');
            foreach ($parsed['errors'] as $e) {
                $this->line("  - {$e}");
            }

            return self::FAILURE;
        }

        $svc = new BooksPruneService();
        $analysed = $svc->analyse(array_column($parsed['rows'], 'product_id'), array_column($parsed['rows'], 'book_id'));

        $counts = array_count_values(array_column($analysed, 'verdict'));
        $this->info(count($parsed['rows']) . ' books in sheet; ' . count($analysed) . ' DB books/products are NOT in the sheet:');
        $this->table(['Verdict', 'Count'], collect(['SAFE', 'SAFE_HISTORY', 'BLOCKED'])->map(fn ($v) => [$v, $counts[$v] ?? 0])->all());

        $blocked = array_values(array_filter($analysed, fn ($a) => $a['verdict'] === 'BLOCKED'));
        if ($blocked) {
            $this->warn('BLOCKED (used — will NOT be deleted) — first 20:');
            $this->table(['Product', 'Book', 'Name', 'Why'], array_map(
                fn ($a) => [$a['product_id'], $a['book_id'], mb_strimwidth((string) $a['name'], 0, 40, '…'), mb_strimwidth($a['reason'], 0, 80, '…')],
                array_slice($blocked, 0, 20)
            ));
        }
        if ($mm = $svc->pairingMismatches($parsed['rows'])) {
            $this->warn(count($mm) . ' sheet row(s) have a Product ID whose DB book has a different Book ID — fix with product:sync-books --relink:');
            $this->table(['Product', 'Sheet book', 'DB book', 'Name'], array_map(fn ($m) => array_values(array_map(fn ($v) => is_string($v) ? mb_strimwidth($v, 0, 40, '…') : $v, $m)), array_slice($mm, 0, 15)));
        }

        $dir = $this->option('report-dir') ?: storage_path('app/book-sync');
        @mkdir($dir, 0775, true);
        $path = "{$dir}/" . date('Ymd_His') . '_prune_check.csv';
        $h = fopen($path, 'w');
        fwrite($h, "\xEF\xBB\xBF");
        $cols = ['product_id', 'book_id', 'name', 'sku', 'isbn', 'verdict', 'reason', 'sales_lines', 'sales_invoices', 'live_invoices', 'purchase_lines', 'book_sales', 'contract_tx', 'stock_qty', 'stock_movements'];
        fputcsv($h, $cols);
        foreach ($analysed as $a) {
            fputcsv($h, array_map(fn ($c) => $a[$c] ?? '', $cols));
        }
        fclose($h);
        $this->line("Full list: {$path}");

        if (! $this->option('delete')) {
            $this->info('Check only — nothing deleted. Add --delete to remove the SAFE ones.');

            return self::SUCCESS;
        }

        $include = (bool) $this->option('include-history');
        $toDelete = count(array_filter($analysed, fn ($a) => $a['verdict'] === 'SAFE' || ($include && $a['verdict'] === 'SAFE_HISTORY')));
        if (! $this->option('yes') && ! $this->confirm("Delete {$toDelete} book(s)/product(s) permanently? (a DB backup should exist)")) {
            $this->warn('Cancelled.');

            return self::SUCCESS;
        }

        try {
            $res = $svc->delete($analysed, $include, (bool) $this->option('deactivate-blocked'), new BooksSyncService());
        } catch (\Throwable $e) {
            $this->error('ABORTED and rolled back: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info("Deleted {$res['deleted']}; set inactive {$res['deactivated']}; left untouched " . (count($res['skipped']) - $res['deactivated']) . '.');

        return self::SUCCESS;
    }
}
