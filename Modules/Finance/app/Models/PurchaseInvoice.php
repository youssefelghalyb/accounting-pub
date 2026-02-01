<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'party_id',
        'invoice_date',
        'due_date',
        'subtotal_amount',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'outstanding_balance',
        'status',
        'notes',
        'reference_number',
        'created_by',
        'edited_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
    ];

    protected $appends = ['status_label', 'status_badge_class'];

    /**
     * Relationships
     */
    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function paymentVouchers(): HasMany
    {
        return $this->hasMany(PaymentVoucher::class);
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
        return $query->where(function ($q) use ($search) {
            $q->where('invoice_number', 'like', "%{$search}%")
                ->orWhere('reference_number', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
                ->orWhereHas('party', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        });
    }

    public function scopeByParty($query, $partyId)
    {
        return $query->where('party_id', $partyId);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeOverdue($query)
    {
        return $query->whereIn('status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }

    /**
     * Accessors
     */
    public function getOutstandingBalanceAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'unpaid' => __('finance::invoice.status_unpaid'),
            'partial' => __('finance::invoice.status_partial'),
            'paid' => __('finance::invoice.status_paid'),
            'cancelled' => __('finance::invoice.status_cancelled'),
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'unpaid' => 'badge-danger',
            'partial' => 'badge-warning',
            'paid' => 'badge-success',
            'cancelled' => 'badge-secondary',
            default => 'badge-secondary',
        };
    }

    /**
     * Methods
     */
    public function calculateTotals()
    {
        $this->subtotal_amount = $this->items()->sum('line_total');

        // Apply discount first
        $amountAfterDiscount = $this->subtotal_amount - $this->discount_amount;

        // Calculate tax ONLY if tax_rate > 0
        if ($this->tax_rate > 0) {
            $this->tax_amount = ($amountAfterDiscount * $this->tax_rate) / 100;
        } else {
            $this->tax_amount = 0;
        }

        // Calculate total (subtotal - discount + tax)
        $this->total_amount = $amountAfterDiscount + $this->tax_amount;

        // Calculate outstanding balance
        $this->outstanding_balance = $this->total_amount - $this->paid_amount;

        $this->save();
    }

    public function addPayment($amount)
    {
        $this->paid_amount += $amount;
        $this->updatePaymentStatus();
        $this->save();
    }

    public function updatePaymentStatus()
    {
        if ($this->paid_amount <= 0) {
            $this->status = 'unpaid';
        } elseif ($this->paid_amount >= $this->total_amount) {
            $this->status = 'paid';
            $this->paid_amount = $this->total_amount; // Ensure no overpayment
        } else {
            $this->status = 'partial';
        }

        $this->save();
    }

    public function cancel()
    {
        $this->status = 'cancelled';
        $this->save();
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && in_array($this->status, ['unpaid', 'partial']);
    }
}
