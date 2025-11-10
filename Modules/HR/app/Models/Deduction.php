<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deduction extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'days',
        'amount',
        'deduction_date',
        'leave_id',
        'reason',
        'notes',
    ];

    protected $casts = [
        'deduction_date' => 'date',
        'amount' => 'decimal:2',
        'days' => 'integer',
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leave(): BelongsTo
    {
        return $this->belongsTo(Leave::class);
    }

    // Auto-calculate amount if deducting by days
    protected static function booted()
    {
        static::creating(function ($deduction) {
            if ($deduction->type === 'days' && $deduction->days > 0 && !$deduction->amount) {
                $deduction->amount = $deduction->employee->daily_rate * $deduction->days;
            }
            
            if ($deduction->type === 'unpaid_leave' && $deduction->days > 0 && !$deduction->amount) {
                $deduction->amount = $deduction->employee->daily_rate * $deduction->days;
            }
        });

        static::updating(function ($deduction) {
            // Recalculate if days changed
            if ($deduction->isDirty('days') && in_array($deduction->type, ['days', 'unpaid_leave'])) {
                $deduction->amount = $deduction->employee->daily_rate * $deduction->days;
            }
        });
    }

    // Scopes
    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('deduction_date', $year);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('deduction_date', $year)
                     ->whereMonth('deduction_date', $month);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('deduction_date', [$startDate, $endDate]);
    }

    // Get formatted type name
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'days' => 'Days Deduction',
            'amount' => 'Fixed Amount',
            'unpaid_leave' => 'Unpaid Leave',
            default => 'Unknown',
        };
    }

    // Get type badge color
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'days' => 'warning',
            'amount' => 'info',
            'unpaid_leave' => 'danger',
            default => 'secondary',
        };
    }

    // Check if deduction is linked to a leave
    public function isFromLeave(): bool
    {
        return !is_null($this->leave_id);
    }
}