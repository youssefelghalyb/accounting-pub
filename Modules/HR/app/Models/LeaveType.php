<?php
namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'max_days_per_year',
        'is_paid',
        'color',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'max_days_per_year' => 'integer',
    ];

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    // Get total days used for this leave type in current year
    public function getTotalDaysUsedThisYear(): int
    {
        return $this->leaves()
            ->where('status', 'approved')
            ->whereYear('start_date', now()->year)
            ->sum('total_days') ?? 0;
    }

    // Check if leave type has unlimited days
    public function hasUnlimitedDays(): bool
    {
        return is_null($this->max_days_per_year);
    }
}