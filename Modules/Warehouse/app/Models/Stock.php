<?php

namespace Modules\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Product\Models\Product;
use App\Models\User;

class Stock extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'warehouse_name',
        'location',
        'description',
        'quantity',
        'reserved_quantity',
        'status',
        'minimum_quantity',
        'created_by',
        'edited_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'minimum_quantity' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'status_color',
        'stock_level',
        'is_low_stock',
    ];

    /**
     * Get the product that owns the stock.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who created the stock record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last edited the stock record.
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
            'active' => 'green',
            'inactive' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get the stock level status.
     */
    public function getStockLevelAttribute(): string
    {
        if ($this->quantity <= 0) {
            return 'out_of_stock';
        }

        if ($this->quantity <= $this->minimum_quantity) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * Check if stock is low.
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity > 0 && $this->quantity <= $this->minimum_quantity;
    }

    /**
     * Scope a query to only include active stocks.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include low stock items.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'minimum_quantity')
            ->where('quantity', '>', 0);
    }

    /**
     * Scope a query to only include out of stock items.
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    /**
     * Scope a query by warehouse.
     */
    public function scopeByWarehouse($query, string $warehouse)
    {
        return $query->where('warehouse_name', $warehouse);
    }
}
