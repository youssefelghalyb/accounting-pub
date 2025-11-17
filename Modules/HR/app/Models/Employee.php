<?php
namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'hire_date',
        'salary',
        'daily_rate',
        'position',
        'department_id',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary' => 'decimal:2',
        'daily_rate' => 'decimal:2',
    ];

    protected $appends = [
        'full_name',
    ];

    // Relationships
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(Deduction::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function approvedLeaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'approved_by');
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getDailyRateAttribute($value): float
    {
        // If daily rate is set, return it
        if ($value) {
            return (float) $value;
        }
        
        // Otherwise, calculate from monthly salary (assuming 30 days/month)
        return $this->salary ? round($this->salary / 30, 2) : 0;
    }

    // Calculate net salary for a specific month
    public function calculateNetSalary(int $year, int $month): float
    {
        $grossSalary = $this->salary ?? 0;
        
        // Get all deductions for this month
        $deductionsTotal = $this->deductions()
            ->whereYear('deduction_date', $year)
            ->whereMonth('deduction_date', $month)
            ->sum('amount');
        
        return $grossSalary - $deductionsTotal;
    }

    // Get total deductions for a specific period
    public function getTotalDeductions($startDate = null, $endDate = null): float
    {
        $query = $this->deductions();
        
        if ($startDate) {
            $query->where('deduction_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('deduction_date', '<=', $endDate);
        }
        
        return $query->sum('amount') ?? 0;
    }

    // Get leave balance for specific leave type
    public function getLeaveBalance(int $leaveTypeId, int $year): array
    {
        $leaveType = LeaveType::find($leaveTypeId);
        
        if (!$leaveType || !$leaveType->max_days_per_year) {
            return [
                'total_allowed' => 0,
                'used' => 0,
                'remaining' => 0,
            ];
        }
        
        $usedDays = $this->leaves()
            ->where('leave_type_id', $leaveTypeId)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('total_days');
        
        return [
            'total_allowed' => $leaveType->max_days_per_year,
            'used' => $usedDays,
            'remaining' => max(0, $leaveType->max_days_per_year - $usedDays),
        ];
    }

    // Get all pending leaves for this employee
    public function getPendingLeavesAttribute()
    {
        return $this->leaves()->where('status', 'pending')->get();
    }

    // Calculate years of service
    public function getYearsOfServiceAttribute(): int
    {
        return $this->hire_date ? now()->diffInYears($this->hire_date) : 0;
    }

    public function advances(): HasMany
{
    return $this->hasMany(Advance::class);
}

public function advanceSettlements(): HasMany
{
    return $this->hasMany(AdvanceSettlement::class);
}
}