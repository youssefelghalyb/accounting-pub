<?php

namespace Modules\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class StockMovement extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'reference_number',
        'type',
        'movement_date',
        'source_warehouse',
        'destination_warehouse',
        'notes',
        'status',
        'total_items',
        'created_by',
        'edited_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'movement_date' => 'date',
        'total_items' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'status_color',
        'type_color',
        'type_label',
    ];

    /**
     * Get the stock movement items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockMovementItem::class);
    }

    /**
     * Get the user who created the movement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last edited the movement.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Get the status color attribute.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get the type color attribute.
     */
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'in' => 'blue',
            'out' => 'orange',
            'transfer' => 'purple',
            'adjustment' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get the type label attribute.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'in' => __('warehouse::movements.type_in'),
            'out' => __('warehouse::movements.type_out'),
            'transfer' => __('warehouse::movements.type_transfer'),
            'adjustment' => __('warehouse::movements.type_adjustment'),
            default => $this->type,
        };
    }

    /**
     * Scope a query to only include completed movements.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include pending movements.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('movement_date', [$startDate, $endDate]);
    }

    /**
     * Check if movement is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if movement can be edited.
     */
    public function canBeEdited(): bool
    {
        return $this->status === 'pending';
    }
}
