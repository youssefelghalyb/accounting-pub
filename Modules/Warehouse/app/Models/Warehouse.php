<?php

namespace Modules\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
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
     * Get the sub-warehouses for this warehouse.
     */
    public function subWarehouses(): HasMany
    {
        return $this->hasMany(SubWarehouse::class);
    }

    /**
     * Get the user who created this warehouse.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last edited this warehouse.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'edited_by');
    }

    /**
     * Get total number of sub-warehouses.
     */
    public function getTotalSubWarehousesAttribute(): int
    {
        return $this->subWarehouses()->count();
    }

    /**
     * Get total products across all sub-warehouses.
     */
    public function getTotalProductsAttribute(): int
    {
        return $this->subWarehouses()
            ->withCount('products')
            ->get()
            ->sum('products_count');
    }

    /**
     * Get total stock quantity across all sub-warehouses.
     */
    public function getTotalStockAttribute(): int
    {
        return $this->subWarehouses()
            ->join('sub_warehouse_products', 'sub_warehouses.id', '=', 'sub_warehouse_products.sub_warehouse_id')
            ->sum('sub_warehouse_products.quantity');
    }
}
