<?php

namespace Modules\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Product\Models\Product;

class SubWarehouseProduct extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'sub_warehouse_id',
        'product_id',
        'quantity',
        'created_by',
        'edited_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the sub-warehouse that owns this product.
     */
    public function subWarehouse(): BelongsTo
    {
        return $this->belongsTo(SubWarehouse::class);
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
     * Scope to filter by product.
     */
    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to filter by sub-warehouse.
     */
    public function scopeBySubWarehouse($query, int $subWarehouseId)
    {
        return $query->where('sub_warehouse_id', $subWarehouseId);
    }

    /**
     * Check if stock is low (less than 10).
     */
    public function isLowStock(): bool
    {
        return $this->quantity < 10;
    }

    /**
     * Check if out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    /**
     * Get stock status color.
     */
    public function getStockStatusColorAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'red';
        }
        if ($this->isLowStock()) {
            return 'yellow';
        }
        return 'green';
    }
}
