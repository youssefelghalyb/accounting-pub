<?php

namespace Modules\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Product\Models\Product;

class StockMovement extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'from_sub_warehouse_id',
        'to_sub_warehouse_id',
        'quantity',
        'movement_type',
        'reason',
        'reference_id',
        'notes',
        'user_id',
        'created_by',
        'edited_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
        'reference_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the source sub-warehouse.
     */
    public function fromSubWarehouse(): BelongsTo
    {
        return $this->belongsTo(SubWarehouse::class, 'from_sub_warehouse_id');
    }

    /**
     * Get the destination sub-warehouse.
     */
    public function toSubWarehouse(): BelongsTo
    {
        return $this->belongsTo(SubWarehouse::class, 'to_sub_warehouse_id');
    }

    /**
     * Get the user who performed the movement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Get the user who created this record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last edited this record.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'edited_by');
    }

    /**
     * Scope to filter by movement type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('movement_type', $type);
    }

    /**
     * Scope to filter by product.
     */
    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to filter by sub-warehouse (from or to).
     */
    public function scopeBySubWarehouse($query, int $subWarehouseId)
    {
        return $query->where(function ($q) use ($subWarehouseId) {
            $q->where('from_sub_warehouse_id', $subWarehouseId)
              ->orWhere('to_sub_warehouse_id', $subWarehouseId);
        });
    }

    /**
     * Get the movement type color.
     */
    public function getMovementTypeColorAttribute(): string
    {
        return match ($this->movement_type) {
            'transfer' => 'blue',
            'inbound' => 'green',
            'outbound' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get movement direction icon.
     */
    public function getMovementIconAttribute(): string
    {
        return match ($this->movement_type) {
            'transfer' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
            'inbound' => 'M7 16V4m0 0L3 8m4-4l4 4',
            'outbound' => 'M17 8l4 4m0 0l-4 4m4-4H3',
            default => 'M13 10V3L4 14h7v7l9-11h-7z',
        };
    }
}
