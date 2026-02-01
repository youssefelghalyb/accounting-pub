<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Finance\Models\Party;
use Modules\Finance\Models\SalesInvoiceItem;
use Modules\Finance\Models\ReceiptVoucher;

class SalesInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'party_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'discount_amount',
        'discount_type',
        'discount_value',
        'is_taxable',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'status',
        'payment_terms',
        'notes',
        'terms_conditions',
        'created_by',
        'edited_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'is_taxable' => 'boolean',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    protected $appends = [
        'status_label',
        'status_color',
        'outstanding_balance',
        'is_overdue',
        'days_overdue',
    ];

    // ==================== Relationships ====================

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function receiptVouchers(): HasMany
    {
        return $this->hasMany(ReceiptVoucher::class);
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

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereIn('status', ['unpaid', 'partial']);
    }

    public function scopeByParty($query, $partyId)
    {
        return $query->where('party_id', $partyId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('invoice_number', 'like', "%{$search}%")
              ->orWhereHas('party', function($sq) use ($search) {
                  $sq->where('name', 'like', "%{$search}%");
              });
        });
    }

    // ==================== Accessors ====================

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => __('finance::invoice.statuses.draft'),
            'pending' => __('finance::invoice.statuses.pending'),
            'unpaid' => __('finance::invoice.statuses.unpaid'),
            'partial' => __('finance::invoice.statuses.partial'),
            'paid' => __('finance::invoice.statuses.paid'),
            'cancelled' => __('finance::invoice.statuses.cancelled'),
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'pending' => 'yellow',
            'unpaid' => 'red',
            'partial' => 'orange',
            'paid' => 'green',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return round($this->total_amount - $this->paid_amount, 2);
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date || in_array($this->status, ['paid', 'cancelled'])) {
            return false;
        }

        return $this->due_date->isPast() && $this->outstanding_balance > 0;
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->is_overdue) {
            return 0;
        }

        return now()->diffInDays($this->due_date);
    }

    // ==================== Business Methods ====================

    /**
     * Calculate and update invoice totals
     */
    public function calculateTotals(): void
    {
        // Calculate subtotal from items
        $this->subtotal = $this->items()->sum('line_total');

        // Calculate discount
        if ($this->discount_type === 'percentage') {
            $this->discount_amount = ($this->subtotal * $this->discount_value) / 100;
        } else {
            $this->discount_amount = $this->discount_value;
        }

        $amountAfterDiscount = $this->subtotal - $this->discount_amount;

        // Calculate tax
        if ($this->is_taxable) {
            $this->tax_amount = ($amountAfterDiscount * $this->tax_rate) / 100;
        } else {
            $this->tax_amount = 0;
        }

        // Calculate total
        $this->total_amount = $amountAfterDiscount + $this->tax_amount;

        $this->save();
    }

    /**
     * Update payment status based on paid amount
     */
    public function updatePaymentStatus(): void
    {
        if ($this->paid_amount >= $this->total_amount) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'unpaid';
        }

        $this->save();
    }

    /**
     * Add payment to invoice
     */
    public function addPayment(float $amount): void
    {
        $this->paid_amount += $amount;
        $this->updatePaymentStatus();
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(): void
    {
        $this->paid_amount = $this->total_amount;
        $this->status = 'paid';
        $this->save();
    }

    /**
     * Cancel invoice
     */
    public function cancel(): void
    {
        $this->status = 'cancelled';
        $this->save();
    }
}