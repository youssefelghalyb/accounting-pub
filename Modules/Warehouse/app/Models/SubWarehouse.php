<?php

namespace Modules\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Product\Models\Product;

class SubWarehouse extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'warehouse_id',
        'name',
        'type',
        'address',
        'country',
        'notes',
        'created_by',
        'edited_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the warehouse that owns this sub-warehouse.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the products in this sub-warehouse.
     */
    public function products(): HasMany
    {
        return $this->hasMany(SubWarehouseProduct::class);
    }

    /**
     * Get stock movements from this sub-warehouse.
     */
    public function outgoingMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'from_sub_warehouse_id');
    }

    /**
     * Get stock movements to this sub-warehouse.
     */
    public function incomingMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'to_sub_warehouse_id');
    }

    /**
     * Get the user who created this sub-warehouse.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last edited this sub-warehouse.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'edited_by');
    }

    /**
     * Scope to filter by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the status color for the type.
     */
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'main' => 'blue',
            'branch' => 'green',
            'book_fair' => 'purple',
            'temporary' => 'yellow',
            'other' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get total quantity of all products in this sub-warehouse.
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->products()->sum('quantity');
    }

    /**
     * Get number of different products in this sub-warehouse.
     */
    public function getTotalProductsAttribute(): int
    {
        return $this->products()->count();
    }

    /**
     * Get the quantity for a specific product.
     */
    public function getProductQuantity(int $productId): int
    {
        return $this->products()
            ->where('product_id', $productId)
            ->first()
            ->quantity ?? 0;
    }
}
