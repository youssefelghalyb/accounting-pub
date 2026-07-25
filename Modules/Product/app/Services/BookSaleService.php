<?php

namespace Modules\Product\Services;

use Illuminate\Support\Facades\Log;
use Modules\Finance\Models\SalesInvoice;
use Modules\Finance\Models\SalesInvoiceItem;
use Modules\Product\Models\BookSale;
use Modules\Product\Models\Product;

/**
 * Records the automatic royalty accrual (BookSale) for a sold book. Called by
 * Modules\Finance\Services\SalesInvoiceService right after each SalesInvoiceItem (and its
 * stock movement) is created — the only place BookSale rows are ever created going forward.
 *
 * Never blocks a sale: a non-book product, or a book with no contractor on file, simply
 * produces no BookSale row (logged, not thrown).
 */
class BookSaleService
{
    public function recordSale(SalesInvoiceItem $item, Product $product, bool $isGift = false): ?BookSale
    {
        $book = $product->book;

        if (! $book) {
            return null;
        }

        $contractorBook = $book->contractorBook;

        if (! $contractorBook) {
            Log::info("BookSaleService: book #{$book->id} (product #{$product->id}) has no contractor on file — skipping BookSale for invoice item #{$item->id}.");

            return null;
        }

        $quantity = (int) $item->quantity;
        $salePrice = (float) $item->unit_price;
        $basePrice = (float) $product->base_price;

        $profit = $isGift
            ? 0.0
            : $contractorBook->calculateProfit($salePrice, $basePrice, $quantity);

        return BookSale::create([
            'book_id' => $book->id,
            'contractor_book_id' => $contractorBook->id,
            'invoice_id' => $item->sales_invoice_id,
            'invoice_item_id' => $item->id,
            'quantity' => $quantity,
            'sale_price_snapshot' => $salePrice,
            'base_price_snapshot' => $basePrice,
            'percentage_snapshot' => $contractorBook->profit_percentage,
            'percentage_basis_snapshot' => $contractorBook->percentage_basis,
            'contractor_profit' => $profit,
            'is_gift' => $isGift,
        ]);
    }

    /**
     * Remove every BookSale tied to an invoice. Not strictly required before deleting the
     * invoice's items — book_sales.invoice_item_id cascades on delete — but kept explicit for
     * readability, mirroring SalesInvoiceService's explicit StockMovement reversal.
     */
    public function reverseForInvoice(SalesInvoice $invoice): void
    {
        BookSale::where('invoice_id', $invoice->id)->delete();
    }
}
