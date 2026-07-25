<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Finance\Models\SalesInvoice;
use Modules\Finance\Models\SalesInvoiceItem;

/**
 * Immutable royalty snapshot — one row per sold sales invoice item.
 * Never updated after creation; prices/percentage are copied at sale time so
 * historical royalty figures never change even if the contract terms do later.
 */
class BookSale extends Model
{
    protected $fillable = [
        'book_id',
        'contractor_book_id',
        'invoice_id',
        'invoice_item_id',
        'quantity',
        'sale_price_snapshot',
        'base_price_snapshot',
        'percentage_snapshot',
        'percentage_basis_snapshot',
        'contractor_profit',
        'is_gift',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'sale_price_snapshot' => 'decimal:2',
        'base_price_snapshot' => 'decimal:2',
        'percentage_snapshot' => 'decimal:2',
        'contractor_profit' => 'decimal:2',
        'is_gift' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function contractorBook(): BelongsTo
    {
        return $this->belongsTo(ContractorBook::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'invoice_id');
    }

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(SalesInvoiceItem::class, 'invoice_item_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeRoyaltyEarning($query)
    {
        return $query->where('is_gift', false);
    }

    public function scopeGifts($query)
    {
        return $query->where('is_gift', true);
    }
}
