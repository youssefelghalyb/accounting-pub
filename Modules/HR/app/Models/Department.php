<?php
namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    // Get total employees in department
    public function getTotalEmployeesAttribute(): int
    {
        return $this->employees()->count();
    }

    // Get total salary cost for department
    public function getTotalSalaryCostAttribute(): float
    {
        return $this->employees()->sum('salary') ?? 0;
    }
}