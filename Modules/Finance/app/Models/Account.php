<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Finance\Models\ReceiptVoucher;
use Modules\Finance\Models\PaymentVoucher;

class Account extends Model
{
    protected $fillable = [
        'account_name',
        'account_number',
        'account_type',
        'bank_name',
        'branch_name',
        'swift_code',
        'iban',
        'opening_balance',
        'currency',
        'notes',
        'is_active',
        'created_by',
        'edited_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'opening_balance' => 'decimal:2',
    ];

    protected $appends = [
        'type_label',
        'type_color',
        'current_balance',
        'total_receipts',
        'total_payments',
    ];

    // ==================== Relationships ====================

    public function receiptVouchers(): HasMany
    {
        return $this->hasMany(ReceiptVoucher::class, 'account_id');
    }

    public function paymentVouchers(): HasMany
    {
        return $this->hasMany(PaymentVoucher::class, 'account_id');
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

    public function scopeCash($query)
    {
        return $query->where('account_type', 'cash');
    }

    public function scopeBank($query)
    {
        return $query->where('account_type', 'bank');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('account_type', $type);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('account_name', 'like', "%{$search}%")
              ->orWhere('account_number', 'like', "%{$search}%")
              ->orWhere('bank_name', 'like', "%{$search}%")
              ->orWhere('iban', 'like', "%{$search}%");
        });
    }

    // ==================== Accessors (Computed Attributes) ====================

    public function getTypeLabelAttribute(): string
    {
        return match($this->account_type) {
            'cash' => __('finance::account.types.cash'),
            'bank' => __('finance::account.types.bank'),
            default => $this->account_type,
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->account_type) {
            'cash' => 'green',
            'bank' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Get total receipts (money IN)
     */
    public function getTotalReceiptsAttribute(): float
    {
        return $this->receiptVouchers()->sum('amount');
    }

    /**
     * Get total payments (money OUT)
     */
    public function getTotalPaymentsAttribute(): float
    {
        return $this->paymentVouchers()->sum('amount');
    }

    /**
     * Calculate current balance
     * Balance = Opening Balance + Total Receipts - Total Payments
     */
    public function getCurrentBalanceAttribute(): float
    {
        $balance = $this->opening_balance + $this->total_receipts - $this->total_payments;
        return round($balance, 2);
    }

    /**
     * Get balance status color
     */
    public function getBalanceColorAttribute(): string
    {
        if ($this->current_balance < 0) {
            return 'red';
        } elseif ($this->current_balance == 0) {
            return 'gray';
        } else {
            return 'green';
        }
    }

    /**
     * Format account display name
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->account_type === 'bank' && $this->bank_name) {
            return "{$this->account_name} ({$this->bank_name})";
        }
        return $this->account_name;
    }
}