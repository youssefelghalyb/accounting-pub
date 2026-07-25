<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Data migration step 5 (docs/contractor-migration-plan.md §5): one `book_sales` row per
     * existing `sales_invoice_items` row whose book now has a `contractor_books` row. Uses
     * *today's* contractor_books percentage/basis as the best available snapshot — the true
     * historical percentage at time of sale was never captured (see plan §9).
     *
     * `is_gift` is backfilled with the existing one-time heuristic
     * (discount_amount >= unit_price * quantity) — a best-effort historical label, not a live
     * rule going forward (Phase 4 sets is_gift explicitly at creation time instead).
     *
     * Never touches sales_invoices, sales_invoice_items, payment/receipt vouchers, or warehouse
     * stock/movements — purely additive INSERTs into book_sales. Idempotent via the unique
     * constraint on book_sales.invoice_item_id.
     */
    public function up(): void
    {
        $alreadyBackfilledItemIds = DB::table('book_sales')->pluck('invoice_item_id')->all();

        $items = DB::table('sales_invoice_items as sii')
            ->join('books as b', 'b.product_id', '=', 'sii.product_id')
            ->join('products as p', 'p.id', '=', 'sii.product_id')
            ->join('contractor_books as cb', 'cb.book_id', '=', 'b.id')
            ->whereNotIn('sii.id', $alreadyBackfilledItemIds ?: [0])
            ->select(
                'sii.id as item_id',
                'sii.sales_invoice_id',
                'sii.quantity',
                'sii.unit_price',
                'sii.discount_amount',
                'b.id as book_id',
                'cb.id as contractor_book_id',
                'cb.profit_percentage',
                'cb.percentage_basis',
                'p.base_price'
            )
            ->get();

        $now = now();
        $created = 0;

        foreach ($items as $item) {
            $quantity = (int) $item->quantity;
            $salePrice = (float) $item->unit_price;
            $basePrice = (float) $item->base_price;
            $percentage = (float) $item->profit_percentage;
            $basis = $item->percentage_basis;

            $unitAmount = $basis === 'base_price' ? $basePrice : $salePrice;
            $profit = round($unitAmount * $quantity * ($percentage / 100), 2);

            $isGift = $quantity > 0 && ((float) $item->discount_amount) >= ($salePrice * $quantity);

            try {
                DB::table('book_sales')->insert([
                    'book_id' => $item->book_id,
                    'contractor_book_id' => $item->contractor_book_id,
                    'invoice_id' => $item->sales_invoice_id,
                    'invoice_item_id' => $item->item_id,
                    'quantity' => $quantity,
                    'sale_price_snapshot' => $salePrice,
                    'base_price_snapshot' => $basePrice,
                    'percentage_snapshot' => $percentage,
                    'percentage_basis_snapshot' => $basis,
                    'contractor_profit' => $isGift ? 0 : $profit,
                    'is_gift' => $isGift,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $created++;
            } catch (\Throwable $e) {
                Log::error("Author->Contractor migration: failed to create historical book_sales for invoice item #{$item->item_id}: {$e->getMessage()}");
            }
        }

        $skippedNoContractor = DB::table('sales_invoice_items as sii')
            ->join('books as b', 'b.product_id', '=', 'sii.product_id')
            ->leftJoin('contractor_books as cb', 'cb.book_id', '=', 'b.id')
            ->whereNull('cb.id')
            ->count();

        Log::info('Author->Contractor migration: historical book_sales generated.', [
            'created' => $created,
            'skipped_no_contractor_on_file' => $skippedNoContractor,
        ]);
    }

    public function down(): void
    {
        // No-op: book_sales is also written to going forward by the live sales flow (Phase 4),
        // so blindly deleting rows on rollback of this specific migration risks destroying real
        // data created after it ran. Matches the plan's documented forward-only caveat.
    }
};
