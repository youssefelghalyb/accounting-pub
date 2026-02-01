<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Finance\Models\Party;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\SalesInvoice;

class ReceiptVoucher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'voucher_number',
        'party_id',
        'account_id',
        'sales_invoice_id',
        'amount',
        'voucher_date',
        'payment_method',
        'reference_number',
        'description',
        'notes',
        'created_by',
        'edited_by',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected $appends = [
        'payment_method_label',
        'payment_method_color',
    ];

    // ==================== Relationships ====================

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function editedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'edited_by');
    }

    // ==================== Scopes ====================

    public function scopeByParty($query, $partyId)
    {
        return $query->where('party_id', $partyId);
    }

    public function scopeByAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeByInvoice($query, $invoiceId)
    {
        return $query->where('sales_invoice_id', $invoiceId);
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('voucher_number', 'like', "%{$search}%")
              ->orWhere('reference_number', 'like', "%{$search}%")
              ->orWhereHas('party', function($sq) use ($search) {
                  $sq->where('name', 'like', "%{$search}%");
              });
        });
    }

    // ==================== Accessors ====================

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => __('finance::receipt.payment_methods.cash'),
            'cheque' => __('finance::receipt.payment_methods.cheque'),
            'bank_transfer' => __('finance::receipt.payment_methods.bank_transfer'),
            'credit_card' => __('finance::receipt.payment_methods.credit_card'),
            'other' => __('finance::receipt.payment_methods.other'),
            default => $this->payment_method,
        };
    }

    public function getPaymentMethodColorAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => 'green',
            'cheque' => 'blue',
            'bank_transfer' => 'purple',
            'credit_card' => 'orange',
            'other' => 'gray',
            default => 'gray',
        };
    }
}