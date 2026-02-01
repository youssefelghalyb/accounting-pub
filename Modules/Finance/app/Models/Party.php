<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Finance\Models\SalesInvoice;
use Modules\Finance\Models\ReceiptVoucher;
use Modules\Finance\Models\PurchaseInvoice;
use Modules\Finance\Models\PaymentVoucher;

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

    protected $appends = [
        'type_label',
        'type_color',
        'is_customer',
        'is_vendor',
        'customer_balance',
        'vendor_balance',
        'role_label',
    ];

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

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('tax_number', 'like', "%{$search}%");
        });
    }

    // ==================== Accessors (Computed Attributes) ====================

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'individual' => __('finance::party.types.individual'),
            'company' => __('finance::party.types.company'),
            'online' => __('finance::party.types.online'),
            default => $this->type,
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'individual' => 'blue',
            'company' => 'purple',
            'online' => 'green',
            default => 'gray',
        };
    }

    /**
     * Determine if party is a customer (has sales invoices)
     */
    public function getIsCustomerAttribute(): bool
    {
        return $this->salesInvoices()->exists();
    }

    /**
     * Determine if party is a vendor (has purchase invoices)
     */
    public function getIsVendorAttribute(): bool
    {
        return $this->purchaseInvoices()->exists();
    }

    /**
     * Get role label (Customer, Vendor, or Both)
     */
    public function getRoleLabelAttribute(): string
    {
        $isCustomer = $this->is_customer;
        $isVendor = $this->is_vendor;

        if ($isCustomer && $isVendor) {
            return __('finance::party.roles.both');
        } elseif ($isCustomer) {
            return __('finance::party.roles.customer');
        } elseif ($isVendor) {
            return __('finance::party.roles.vendor');
        }

        return __('finance::party.roles.none');
    }

    /**
     * Calculate customer balance
     * Balance = Total Sales Invoices - Total Receipt Vouchers
     */
    public function getCustomerBalanceAttribute(): float
    {
        $totalSales     = $this->salesInvoices()->sum('total_amount');
        $totalReceipts  = $this->receiptVouchers()->sum('amount'); // تحصيل من العميل

        $totalPurchase  = $this->purchaseInvoices()->sum('total_amount');
        $totalPayments  = $this->paymentVouchers()->sum('amount'); // دفع للمورد

        $net = ($totalSales - $totalReceipts) - ($totalPurchase - $totalPayments);

        return round($net, 2);
    }

    /**
     * Calculate vendor balance
     * Balance = Total Purchase Invoices - Total Payment Vouchers
     */
    public function getVendorBalanceAttribute(): float
    {
        $totalPurchases = $this->purchaseInvoices()->sum('total_amount');
        $totalPayments = $this->paymentVouchers()->sum('amount');
        return round($totalPurchases - $totalPayments, 2);
    }

    /**
     * Get total sales amount
     */
    public function getTotalSalesAttribute(): float
    {
        return $this->salesInvoices()->sum('total_amount');
    }

    /**
     * Get total payments received
     */
    public function getTotalPaymentsReceivedAttribute(): float
    {
        return $this->receiptVouchers()->sum('amount');
    }

    /**
     * Get count of unpaid invoices
     */
    public function getUnpaidInvoicesCountAttribute(): int
    {
        return $this->salesInvoices()
            ->whereIn('status', ['unpaid', 'partial'])
            ->count();
    }

    /**
     * Get count of fully paid invoices
     */
    public function getPaidInvoicesCountAttribute(): int
    {
        return $this->salesInvoices()
            ->where('status', 'paid')
            ->count();
    }

    /**
     * Get count of unpaid purchase invoices
     */
    public function getUnpaidPurchaseInvoicesCountAttribute(): int
    {
        return $this->purchaseInvoices()
            ->whereIn('status', ['unpaid', 'partial'])
            ->count();
    }

    /**
     * Get count of fully paid purchase invoices
     */
    public function getPaidPurchaseInvoicesCountAttribute(): int
    {
        return $this->purchaseInvoices()
            ->where('status', 'paid')
            ->count();
    }
}
