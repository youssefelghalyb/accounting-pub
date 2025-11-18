<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Advance extends Model
{
    protected $table = 'employee_advances';

    protected $fillable = [
        'advance_code',
        'employee_id',
        'amount',
        'issue_date',
        'expected_settlement_date',
        'actual_settlement_date',
        'type',
        'status',
        'purpose',
        'notes',
        'issued_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expected_settlement_date' => 'date',
        'actual_settlement_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'issued_by');
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(AdvanceSettlement::class, 'advance_id');
    }

    // Auto-generate advance code
    protected static function booted()
    {
        static::creating(function ($advance) {
            if (!$advance->advance_code) {
                $advance->advance_code = self::generateAdvanceCode();
            }
        });

        static::updated(function ($advance) {
            // Auto-update status based on settlements
            $advance->updateStatus();
        });
    }

    // Generate unique advance code
    public static function generateAdvanceCode(): string
    {
        $year = now()->year;
        $lastAdvance = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastAdvance ? intval(substr($lastAdvance->advance_code, -3)) + 1 : 1;

        return 'ADV-' . $year . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    // Calculate total cash returned
    public function getTotalCashReturnedAttribute(): float
    {
        return $this->settlements()->sum('cash_returned');
    }

    // Calculate total amount spent (with receipts)
    public function getTotalSpentAttribute(): float
    {
        return $this->settlements()->sum('amount_spent');
    }

    // Calculate total accounted for (cash + spent)
    public function getTotalAccountedAttribute(): float
    {
        return $this->total_cash_returned + $this->total_spent;
    }


    // Check if overdue
    public function isOverdue(): bool
    {
        if (!$this->expected_settlement_date || $this->status === 'settled') {
            return false;
        }

        return now()->gt($this->expected_settlement_date);
    }

    // Get overdue days
    public function getOverdueDaysAttribute(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return now()->diffInDays($this->expected_settlement_date);
    }



    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'settled')
            ->where('expected_settlement_date', '<', now());
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('issue_date', now()->year)
            ->whereMonth('issue_date', now()->month);
    }

    // Get status badge color
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'partial_settlement' => 'blue',
            'settled' => 'green',
            default => 'gray',
        };
    }

    // Get type badge color
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'cash' => 'blue',
            'salary_advance' => 'purple',
            'petty_cash' => 'orange',
            'travel' => 'green',
            'purchase' => 'indigo',
            default => 'gray',
        };
    }

    // Add to existing methods

    /**
     * Convert outstanding balance to deduction
     * Creates a deduction record for the remaining unpaid amount
     */
    public function convertToDeduction(): ?Deduction
    {
        if ($this->outstanding_balance <= 0) {
            return null;
        }

        $deduction = Deduction::create([
            'employee_id' => $this->employee_id,
            'type' => 'advance_recovery',
            'amount' => $this->outstanding_balance,
            'deduction_date' => now(),
            'advance_id' => $this->id,
            'reason' => __('hr::advance.deduction_from_advance', ['code' => $this->advance_code]),
            'notes' => __('hr::advance.converted_to_deduction'),
        ]);

        // Mark advance as settled via deduction
        $this->update([
            'status' => 'settled_via_deduction',
            'actual_settlement_date' => now(),
        ]);

        return $deduction;
    }

    /**
     * Check if advance has been converted to deduction
     */
    public function hasDeduction(): bool
    {
        return Deduction::where('advance_id', $this->id)->exists();
    }

    /**
     * Get the deduction record if exists
     */
    public function getDeduction()
    {
        return Deduction::where('advance_id', $this->id)->first();
    }
    // Calculate outstanding balance (what employee still owes OR company owes)
    public function getOutstandingBalanceAttribute(): float
    {
        return $this->amount - $this->total_accounted;
    }

    // Check if company owes employee (overpayment)
    public function hasOverpayment(): bool
    {
        return $this->outstanding_balance < 0;
    }

    // Get absolute overpayment amount
    public function getOverpaymentAmountAttribute(): float
    {
        return $this->hasOverpayment() ? abs($this->outstanding_balance) : 0;
    }

    // Update advance status based on settlements
    public function updateStatus(): void
    {
        $totalAccounted = $this->total_accounted;

        if ($totalAccounted >= $this->amount) {
            $this->status = 'settled';
            $this->actual_settlement_date = now();
        } elseif ($totalAccounted > 0) {
            $this->status = 'partial_settlement';
        } else {
            $this->status = 'pending';
        }

        // Don't trigger updated event again
        $this->saveQuietly();
    }
}
