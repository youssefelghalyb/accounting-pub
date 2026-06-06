<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Party extends Model
{
    protected $fillable = [
        'name',
        'type',
        'phone',
        'email',
        'address',
        'tax_number',
        'is_active',
        'created_by',
        'edited_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // $appends is intentionally empty — accessors are lazy,
    // called explicitly only when needed, never auto-serialized
    protected $appends = [];

    // ==================== Relationships ====================

    public function salesInvoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class, 'party_id');
    }

    public function receiptVouchers(): HasMany
    {
        return $this->hasMany(ReceiptVoucher::class, 'party_id');
    }

    public function purchaseInvoices(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class, 'party_id');
    }

    public function paymentVouchers(): HasMany
    {
        return $this->hasMany(PaymentVoucher::class, 'party_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function editedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'edited_by');
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCustomers($query)
    {
        return $query->whereHas('salesInvoices');
    }

    public function scopeVendors($query)
    {
        return $query->whereHas('purchaseInvoices');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('tax_number', 'like', "%{$search}%");
        });
    }

    // ==================== Helpers ====================

    /**
     * True if the model was loaded with withSum aggregates from the controller.
     * Used by accessors to avoid firing individual queries per party.
     */
    private function hasPreloadedSums(): bool
    {
        return array_key_exists('total_sales', $this->attributes)
            && array_key_exists('total_receipts', $this->attributes)
            && array_key_exists('total_purchases', $this->attributes)
            && array_key_exists('total_payments', $this->attributes);
    }

    private function preloadedSum(string $key): float
    {
        return (float) ($this->attributes[$key] ?? 0);
    }

    // ==================== Accessors ====================

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'individual' => __('finance::party.types.individual'),
            'company'    => __('finance::party.types.company'),
            'online'     => __('finance::party.types.online'),
            default      => $this->type,
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'individual' => 'blue',
            'company'    => 'purple',
            'online'     => 'green',
            default      => 'gray',
        };
    }

    /**
     * True if the party has any sales invoices.
     * Uses pre-loaded total_sales sum when available — zero queries.
     */
    public function getIsCustomerAttribute(): bool
    {
        if (array_key_exists('total_sales', $this->attributes)) {
            return $this->preloadedSum('total_sales') > 0;
        }

        return $this->salesInvoices()->exists();
    }

    /**
     * True if the party has any purchase invoices.
     * Uses pre-loaded total_purchases sum when available — zero queries.
     */
    public function getIsVendorAttribute(): bool
    {
        if (array_key_exists('total_purchases', $this->attributes)) {
            return $this->preloadedSum('total_purchases') > 0;
        }

        return $this->purchaseInvoices()->exists();
    }

    /**
     * Customer / Vendor / Both / None label.
     * Relies on is_customer and is_vendor — both are pre-load aware.
     */
    public function getRoleLabelAttribute(): string
    {
        $isCustomer = $this->is_customer;
        $isVendor   = $this->is_vendor;

        if ($isCustomer && $isVendor) return __('finance::party.roles.both');
        if ($isCustomer)              return __('finance::party.roles.customer');
        if ($isVendor)                return __('finance::party.roles.vendor');

        return __('finance::party.roles.none');
    }

    /**
     * Net balance across both sales and purchase sides.
     * Formula: (Sales - Receipts) - (Purchases - Payments)
     *
     * Uses withSum pre-loaded aggregates when available (index/list pages).
     * Falls back to direct queries for single-party contexts (show page, etc.).
     */
    public function getCustomerBalanceAttribute(): float
    {
        if ($this->hasPreloadedSums()) {
            $net = (
                ($this->preloadedSum('total_sales')     - $this->preloadedSum('total_receipts')) -
                ($this->preloadedSum('total_purchases') - $this->preloadedSum('total_payments'))
            );

            return round($net, 2);
        }

        $net = (
            ($this->salesInvoices()->sum('total_amount')    - $this->receiptVouchers()->sum('amount')) -
            ($this->purchaseInvoices()->sum('total_amount') - $this->paymentVouchers()->sum('amount'))
        );

        return round($net, 2);
    }

    /**
     * Vendor-only balance: Purchases - Payments.
     * Uses pre-loaded aggregates when available.
     */
    public function getVendorBalanceAttribute(): float
    {
        if (array_key_exists('total_purchases', $this->attributes)
            && array_key_exists('total_payments', $this->attributes)) {
            return round(
                $this->preloadedSum('total_purchases') - $this->preloadedSum('total_payments'),
                2
            );
        }

        return round(
            $this->purchaseInvoices()->sum('total_amount') - $this->paymentVouchers()->sum('amount'),
            2
        );
    }

    /**
     * Raw total of all sales invoices.
     */
    public function getTotalSalesAttribute(): float
    {
        if (array_key_exists('total_sales', $this->attributes)) {
            return $this->preloadedSum('total_sales');
        }

        return (float) $this->salesInvoices()->sum('total_amount');
    }

    /**
     * Raw total of all receipts collected from customer.
     */
    public function getTotalPaymentsReceivedAttribute(): float
    {
        if (array_key_exists('total_receipts', $this->attributes)) {
            return $this->preloadedSum('total_receipts');
        }

        return (float) $this->receiptVouchers()->sum('amount');
    }

    /**
     * Count of unpaid / partially paid sales invoices.
     * Always queries — no aggregate shortcut for filtered counts.
     */
    public function getUnpaidInvoicesCountAttribute(): int
    {
        return $this->salesInvoices()
            ->whereIn('status', ['unpaid', 'partial'])
            ->count();
    }

    /**
     * Count of fully paid sales invoices.
     */
    public function getPaidInvoicesCountAttribute(): int
    {
        return $this->salesInvoices()
            ->where('status', 'paid')
            ->count();
    }

    /**
     * Count of unpaid / partially paid purchase invoices.
     */
    public function getUnpaidPurchaseInvoicesCountAttribute(): int
    {
        return $this->purchaseInvoices()
            ->whereIn('status', ['unpaid', 'partial'])
            ->count();
    }

    /**
     * Count of fully paid purchase invoices.
     */
    public function getPaidPurchaseInvoicesCountAttribute(): int
    {
        return $this->purchaseInvoices()
            ->where('status', 'paid')
            ->count();
    }
}