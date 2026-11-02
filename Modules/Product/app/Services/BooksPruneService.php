<?php

namespace Modules\Product\Services;

use Illuminate\Support\Facades\DB;

/**
 * Finds books/products that exist in the system but NOT in the master sheet, checks every place
 * that depends on them, and (only on request) deletes the ones nothing depends on.
 *
 * Verdicts:
 *   SAFE        – no invoice/purchase/royalty/stock references → may be deleted.
 *   SAFE_HISTORY– only old stock_movements (e.g. past Excel stock reconciliations) → may be deleted,
 *                 those movement rows go with the product (still listed so you can decide).
 *   BLOCKED     – used by a sales invoice line, purchase invoice line, royalty accrual (book_sales),
 *                 contract transaction, or still has stock on hand → NEVER deleted; optionally set
 *                 inactive instead so it disappears from selling screens but history stays intact.
 */
class BooksPruneService
{
    /** @return array<int,array> */
    public function analyse(array $sheetProductIds, array $sheetBookIds): array
    {
        $candidates = DB::table('products as p')
            ->leftJoin('books as b', 'b.product_id', '=', 'p.id')
            ->where(function ($q) {
                $q->where('p.type', 'book')->orWhereNotNull('b.id');
            })
            ->whereNotIn('p.id', $sheetProductIds)
            ->select('p.id as product_id', 'p.name', 'p.sku', 'p.status', 'b.id as book_id', 'b.isbn')
            ->orderBy('p.id')
            ->get();

        return $candidates->map(fn ($c) => $this->references($c))->all();
    }

    /** Books whose product IS in the sheet but whose Book ID is not the one the sheet pairs it with. */
    public function pairingMismatches(array $rows): array
    {
        $out = [];
        foreach ($rows as $r) {
            $dbBook = DB::table('books')->where('product_id', $r['product_id'])->value('id');
            if ($dbBook !== null && (int) $dbBook !== $r['book_id']) {
                $out[] = ['product_id' => $r['product_id'], 'sheet_book_id' => $r['book_id'], 'db_book_id' => (int) $dbBook, 'name' => $r['name']];
            }
        }

        return $out;
    }

    public function references(object $c): array
    {
        $pid = (int) $c->product_id;
        $bid = $c->book_id ? (int) $c->book_id : null;

        $salesLines = DB::table('sales_invoice_items')->where('product_id', $pid)->count();
        $salesInvoices = DB::table('sales_invoice_items')->where('product_id', $pid)->distinct()->count('sales_invoice_id');
        $liveInvoices = DB::table('sales_invoice_items as i')->join('sales_invoices as s', 's.id', '=', 'i.sales_invoice_id')
            ->where('i.product_id', $pid)->whereNull('s.deleted_at')->where('s.status', '!=', 'cancelled')
            ->distinct()->count('s.id');
        $purchaseLines = DB::table('purchase_invoice_items')->where('product_id', $pid)->count();
        $bookSales = $bid ? DB::table('book_sales')->where('book_id', $bid)->count() : 0;
        $contractTx = $bid ? DB::table('contract_transactions')->whereIn('contractor_book_id',
            DB::table('contractor_books')->where('book_id', $bid)->select('id'))->count() : 0;
        $stockQty = (int) DB::table('sub_warehouse_products')->where('product_id', $pid)->sum('quantity');
        $movements = DB::table('stock_movements')->where('product_id', $pid)->count();

        $blocked = array_filter([
            $salesLines ? "{$salesLines} sales invoice line(s) in {$salesInvoices} invoice(s) ({$liveInvoices} live)" : null,
            $purchaseLines ? "{$purchaseLines} purchase invoice line(s)" : null,
            $bookSales ? "{$bookSales} royalty accrual(s)" : null,
            $contractTx ? "{$contractTx} contract transaction(s)" : null,
            $stockQty !== 0 ? "stock on hand = {$stockQty}" : null,
        ]);

        return [
            'product_id' => $pid, 'book_id' => $bid, 'name' => $c->name, 'sku' => $c->sku,
            'isbn' => $c->isbn, 'status' => $c->status,
            'sales_lines' => $salesLines, 'sales_invoices' => $salesInvoices, 'live_invoices' => $liveInvoices,
            'purchase_lines' => $purchaseLines, 'book_sales' => $bookSales, 'contract_tx' => $contractTx,
            'stock_qty' => $stockQty, 'stock_movements' => $movements,
            'stock_only' => $blocked && $stockQty !== 0 && ! $salesLines && ! $purchaseLines && ! $bookSales && ! $contractTx,
            'verdict' => $blocked ? 'BLOCKED' : ($movements ? 'SAFE_HISTORY' : 'SAFE'),
            'reason' => implode('; ', $blocked) ?: ($movements ? "{$movements} old stock movement(s) will be deleted with it" : ''),
        ];
    }

    /**
     * Deletes SAFE (and optionally SAFE_HISTORY) items. Re-checks every candidate inside the
     * transaction, so a row that became used since the check is skipped, never deleted.
     *
     * With $zeroStock, BLOCKED items whose ONLY blocker is stock on hand (no invoice/purchase/royalty/
     * contract reference) are removed too; the quantities destroyed are returned in 'zeroed' for the report.
     *
     * @return array{deleted:int, skipped:array, deactivated:int, zeroed:array}
     */
    public function delete(array $analysed, bool $includeHistory, bool $deactivateBlocked, BooksSyncService $guard, bool $zeroStock = false): array
    {
        $deleted = $deactivated = 0;
        $skipped = $zeroed = [];
        $before = $guard->fingerprint();

        DB::transaction(function () use ($analysed, $includeHistory, $deactivateBlocked, $zeroStock, &$deleted, &$deactivated, &$skipped, &$zeroed, $guard, $before) {
            foreach ($analysed as $item) {
                $fresh = $this->references((object) [
                    'product_id' => $item['product_id'], 'book_id' => $item['book_id'], 'name' => $item['name'],
                    'sku' => $item['sku'], 'isbn' => $item['isbn'], 'status' => $item['status'],
                ]);

                $deletable = $fresh['verdict'] === 'SAFE' || ($includeHistory && $fresh['verdict'] === 'SAFE_HISTORY');

                if (! $deletable && $zeroStock && $fresh['verdict'] === 'BLOCKED' && $fresh['stock_only']) {
                    foreach (DB::table('sub_warehouse_products')->where('product_id', $fresh['product_id'])->where('quantity', '!=', 0)->get() as $row) {
                        $zeroed[] = ['product_id' => $fresh['product_id'], 'book_id' => $fresh['book_id'], 'name' => $fresh['name'],
                            'sub_warehouse_id' => $row->sub_warehouse_id, 'quantity_removed' => $row->quantity];
                    }
                    $deletable = true;
                }

                if ($deletable) {
                    $this->deleteOne($fresh['product_id'], $fresh['book_id']);
                    $deleted++;
                } elseif ($fresh['verdict'] === 'BLOCKED' && $deactivateBlocked) {
                    DB::table('products')->where('id', $fresh['product_id'])->update(['status' => 'inactive', 'updated_at' => now()]);
                    $deactivated++;
                    $skipped[] = $fresh;
                } else {
                    $skipped[] = $fresh;
                }
            }

            // Money tables must be untouched; stock may legitimately lose rows of deleted products.
            $after = $guard->fingerprint();
            foreach (['sales_invoices', 'sales_invoice_items', 'book_sales', 'receipt_vouchers', 'payment_vouchers',
                'purchase_invoices', 'purchase_invoice_items', 'contract_transactions'] as $t) {
                if ($before[$t] !== $after[$t]) {
                    throw new \RuntimeException("Safety check failed — {$t} changed during prune; rolled back.");
                }
            }
        });

        return ['deleted' => $deleted, 'deactivated' => $deactivated, 'skipped' => $skipped, 'zeroed' => $zeroed];
    }

    private function deleteOne(int $productId, ?int $bookId): void
    {
        if ($bookId) {
            DB::table('contractor_books')->where('book_id', $bookId)->delete();
            DB::table('books')->where('id', $bookId)->delete();
        }
        DB::table('sub_warehouse_products')->where('product_id', $productId)->delete(); // zero rows, or stock-only rows approved with --zero-stock
        DB::table('stock_movements')->where('product_id', $productId)->delete();
        DB::table('products')->where('id', $productId)->delete();
    }
}
