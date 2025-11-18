<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvanceSettlement extends Model
{
    protected $fillable = [
        'settlement_code',
        'employee_id',
        'advance_id',
        'cash_returned',
        'amount_spent',
        'settlement_date',
        'settlement_notes',
        'receipt_file',
        'received_by',
    ];

    protected $casts = [
        'settlement_date' => 'date',
        'cash_returned' => 'decimal:2',
        'amount_spent' => 'decimal:2',
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function advance(): BelongsTo
    {
        return $this->belongsTo(Advance::class, 'advance_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'received_by');
    }

    // Auto-generate settlement code and update advance status
    protected static function booted()
    {
        static::creating(function ($settlement) {
            if (!$settlement->settlement_code) {
                $settlement->settlement_code = self::generateSettlementCode();
            }
        });

        static::saved(function ($settlement) {
            // Only update advance if linked
            if ($settlement->advance_id) {
                $settlement->advance->updateStatus();
            }
        });

        static::deleted(function ($settlement) {
            // Only update advance if linked
            if ($settlement->advance_id) {
                $settlement->advance->updateStatus();
            }
        });
    }

    // Generate unique settlement code
    public static function generateSettlementCode(): string
    {
        $year = now()->year;
        $lastSettlement = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastSettlement ? intval(substr($lastSettlement->settlement_code, -3)) + 1 : 1;
        
        return 'SET-' . $year . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    // Get total accounted (cash + spent)
    public function getTotalAccountedAttribute(): float
    {
        return $this->cash_returned + $this->amount_spent;
    }

    // Check if settlement is linked to an advance
    public function isLinked(): bool
    {
        return !is_null($this->advance_id);
    }

    // Check if settlement is standalone
    public function isStandalone(): bool
    {
        return is_null($this->advance_id);
    }

    // Scopes
    public function scopeStandalone($query)
    {
        return $query->whereNull('advance_id');
    }

    public function scopeLinked($query)
    {
        return $query->whereNotNull('advance_id');
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
}