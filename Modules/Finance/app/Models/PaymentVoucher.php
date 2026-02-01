<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentVoucher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'voucher_number',
        'party_id',
        'account_id',
        'purchase_invoice_id',
        'voucher_date',
        'amount',
        'payment_method',
        'cheque_number',
        'cheque_date',
        'transaction_reference',
        'description',
        'created_by',
        'edited_by',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'cheque_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected $appends = ['payment_method_label'];

    /**
     * Relationships
     */
    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'edited_by');
    }

    /**
     * Scopes
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('voucher_number', 'like', "%{$search}%")
              ->orWhere('cheque_number', 'like', "%{$search}%")
              ->orWhere('transaction_reference', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhereHas('party', function($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%");
              });
        });
    }

    public function scopeByParty($query, $partyId)
    {
        return $query->where('party_id', $partyId);
    }

    public function scopeByAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Accessors
     */
    public function getPaymentMethodLabelAttribute()
    {
        return match($this->payment_method) {
            'cash' => __('finance::payment.method_cash'),
            'cheque' => __('finance::payment.method_cheque'),
            'bank_transfer' => __('finance::payment.method_bank_transfer'),
            'credit_card' => __('finance::payment.method_credit_card'),
            'other' => __('finance::payment.method_other'),
            default => $this->payment_method,
        };
    }
}