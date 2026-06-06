<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Finance\Models\Party;
use Modules\Finance\Models\SalesInvoice;
use Modules\Finance\Models\SalesInvoiceItem;

class Author extends Model
{
    protected $fillable = [
        'full_name',
        'nationality',
        'country_of_residence',
        'bio',
        'occupation',
        'phone_number',
        'whatsapp_number',
        'email',
        'id_image',
        'created_by',
        'edited_by',
        'party_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'total_contract_value',
        'total_paid',
        'outstanding_balance',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    /**
     * All contracts this author is part of (via pivot).
     */
    public function contracts(): BelongsToMany
    {
        return $this->belongsToMany(Contract::class, 'contract_authors')
            ->withPivot('is_representative')
            ->withTimestamps();
    }

    /**
     * Contracts where this author is the representative.
     */
    public function representativeContracts(): BelongsToMany
    {
        return $this->belongsToMany(Contract::class, 'contract_authors')
            ->withPivot('is_representative')
            ->wherePivot('is_representative', true);
    }

    /**
     * Books this author is linked to — through their contracts.
     */
    public function books()
    {
        return Book::whereHas('contract', function ($q) {
            $q->whereHas('authors', function ($aq) {
                $aq->where('authors.id', $this->id);
            });
        })->get();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'edited_by');
    }


    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function isClient(): bool
    {
        return $this->party_id !== null;
    }

    // All invoices where this author was the buyer
    public function salesInvoices()
    {
        if (!$this->party_id) return collect();
        return SalesInvoice::where('party_id', $this->party_id)
            ->orderByDesc('invoice_date')
            ->get();
    }

    // Invoice line items that are gifts (100% discounted) on the author's own books
    public function giftCopies()
    {
        // Get all product IDs linked to books this author wrote
        $productIds = $this->contracts()
            ->with('book.product')
            ->get()
            ->pluck('book.product.id')
            ->filter()
            ->unique();

        if ($productIds->isEmpty()) return collect();

        return SalesInvoiceItem::with(['salesInvoice.party'])
            ->whereIn('product_id', $productIds)
            ->whereRaw('discount_amount >= (unit_price * quantity)')
            ->where('quantity', '>', 0)
            ->get();
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getTotalContractValueAttribute(): float
    {
        return (float) $this->contracts()->sum('contract_price');
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->contracts()
            ->with('transactions')
            ->get()
            ->sum(fn($contract) => $contract->transactions->sum('amount'));
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return $this->total_contract_value - $this->total_paid;
    }

    // ─── Methods ──────────────────────────────────────────────────────────────

    /**
     * All payment transactions across all contracts this author is on.
     */
    public function getAllTransactions()
    {
        return ContractTransaction::whereHas('contract', function ($query) {
            $query->whereHas('authors', function ($q) {
                $q->where('authors.id', $this->id);
            });
        })->get();
    }


    public function receiptVouchers()
    {
        if (!$this->party_id) return collect();
        return \Modules\Finance\Models\ReceiptVoucher::where('party_id', $this->party_id)
            ->orderByDesc('voucher_date')
            ->get();
    }

    public function paymentVouchers()
    {
        if (!$this->party_id) return collect();
        return \Modules\Finance\Models\PaymentVoucher::where('party_id', $this->party_id)
            ->orderByDesc('voucher_date')
            ->get();
    }
}
