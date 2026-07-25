<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Finance\Models\PaymentVoucher;
use Modules\Finance\Models\ReceiptVoucher;

/**
 * Neutral, accounting-backed ledger entry against a contractor_book: publishing fees,
 * royalty payouts, advances, refunds, adjustments. Table: contract_transactions.
 *
 * Temporarily named ContractorTransaction (not ContractTransaction) because the legacy
 * Modules\Product\Models\ContractTransaction (author installment payments, table
 * author_contract_transactions) is still active until the Author/Contract system is deleted.
 * Gets a pure rename to ContractTransaction in that same destructive phase.
 * See docs/contractor-migration-plan.md §2.2.
 */
class ContractorTransaction extends Model
{
    protected $table = 'contract_transactions';

    protected $fillable = [
        'contractor_book_id',
        'type',
        'amount',
        'transaction_date',
        'notes',
        'receipt_file',
        'receipt_voucher_id',
        'payment_voucher_id',
        'created_by',
        'edited_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $transaction) {
            $hasReceipt = ! is_null($transaction->receipt_voucher_id);
            $hasPayment = ! is_null($transaction->payment_voucher_id);

            if ($hasReceipt === $hasPayment) {
                throw new \LogicException(
                    'A contract transaction must reference exactly one of receipt_voucher_id or '
                    . 'payment_voucher_id — never both, never neither.'
                );
            }
        });
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function contractorBook(): BelongsTo
    {
        return $this->belongsTo(ContractorBook::class);
    }

    public function receiptVoucher(): BelongsTo
    {
        return $this->belongsTo(ReceiptVoucher::class);
    }

    public function paymentVoucher(): BelongsTo
    {
        return $this->belongsTo(PaymentVoucher::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'edited_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Money that came IN from the contractor (backed by a ReceiptVoucher).
     */
    public function scopeIncoming($query)
    {
        return $query->whereNotNull('receipt_voucher_id');
    }

    /**
     * Money that went OUT to the contractor (backed by a PaymentVoucher).
     */
    public function scopeOutgoing($query)
    {
        return $query->whereNotNull('payment_voucher_id');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    /**
     * 'in' when money came from the contractor (ReceiptVoucher), 'out' when money was
     * paid to the contractor (PaymentVoucher). Derived from the voucher link, never stored.
     */
    public function getDirectionAttribute(): ?string
    {
        if ($this->receipt_voucher_id) {
            return 'in';
        }

        if ($this->payment_voucher_id) {
            return 'out';
        }

        return null;
    }
}
