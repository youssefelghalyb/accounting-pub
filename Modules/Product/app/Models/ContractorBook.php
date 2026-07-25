<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractorBook extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'book_id',
        'contractor_id',
        'contract_file',
        'profit_percentage',
        'percentage_basis',
        'contract_date',
        'end_contract_date',
    ];

    protected $casts = [
        'profit_percentage' => 'decimal:2',
        'contract_date' => 'date',
        'end_contract_date' => 'date',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    public function bookSales(): HasMany
    {
        return $this->hasMany(BookSale::class);
    }

    // ─── Business logic ───────────────────────────────────────────────────────

    /**
     * Royalty share for a single unit, given this contract's percentage/basis.
     * Single source of truth for the formula — used by BookSaleService so the
     * calculation is never duplicated between the normal sale path and the gift path.
     */
    public function calculateProfit(float $salePrice, float $basePrice): float
    {
        $amount = $this->percentage_basis === 'base_price' ? $basePrice : $salePrice;

        return round($amount * ((float) $this->profit_percentage / 100), 2);
    }
}
