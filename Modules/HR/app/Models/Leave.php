<?php
namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Leave extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'deduction_applied',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'deduction_applied' => 'boolean',
        'total_days' => 'integer',
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function deduction(): HasOne
    {
        return $this->hasOne(Deduction::class);
    }

    // Auto-calculate total days before saving
    protected static function booted()
    {
        static::creating(function ($leave) {
            if (!$leave->total_days && $leave->start_date && $leave->end_date) {
                $leave->total_days = $leave->calculateTotalDays();
            }
        });

        static::updating(function ($leave) {
            // Recalculate if dates changed
            if ($leave->isDirty(['start_date', 'end_date'])) {
                $leave->total_days = $leave->calculateTotalDays();
            }
        });
    }

    // Calculate total days between start and end date
    public function calculateTotalDays(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }
        
        return Carbon::parse($this->start_date)
            ->diffInDays(Carbon::parse($this->end_date)) + 1; // +1 to include both start and end days
    }

    // Create deduction for unpaid leave when approved
    public function createDeductionIfUnpaid(): ?Deduction
    {
        // Only create deduction if:
        // 1. Leave type is unpaid
        // 2. Leave is approved
        // 3. Deduction hasn't been created yet
        if (!$this->leaveType->is_paid && 
            $this->status === 'approved' && 
            !$this->deduction_applied) {
            
            $deduction = Deduction::create([
                'employee_id' => $this->employee_id,
                'type' => 'unpaid_leave',
                'days' => $this->total_days,
                'deduction_date' => $this->start_date,
                'leave_id' => $this->id,
                'reason' => 'Unpaid leave: ' . $this->leaveType->name,
                'notes' => "Leave from {$this->start_date->format('Y-m-d')} to {$this->end_date->format('Y-m-d')}"
            ]);

            $this->update(['deduction_applied' => true]);
            
            return $deduction;
        }
        
        return null;
    }

    // Check if leave can be approved
    public function canBeApproved(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }
        
        // Check if employee has enough leave balance
        if ($this->leaveType->max_days_per_year) {
            $balance = $this->employee->getLeaveBalance(
                $this->leave_type_id, 
                $this->start_date->year
            );
            
            return $balance['remaining'] >= $this->total_days;
        }
        
        return true;
    }

    // Approve leave
    public function approve(int $approvedBy): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }
        
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
        
        // Create deduction if unpaid
        $this->createDeductionIfUnpaid();
        
        return true;
    }

    // Reject leave
    public function reject(int $rejectedBy, string $reason): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }
        
        $this->update([
            'status' => 'rejected',
            'approved_by' => $rejectedBy,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
        
        return true;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('start_date', $year);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('start_date', $year)
                     ->whereMonth('start_date', $month);
    }

    // Check if leave is currently active
    public function isActive(): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }
        
        $now = now()->toDateString();
        return $now >= $this->start_date->toDateString() && 
               $now <= $this->end_date->toDateString();
    }

    // Get status badge color
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }
}