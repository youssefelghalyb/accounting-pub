<?php

namespace Modules\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Product\Models\Product;

class StockMovementItem extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'stock_movement_id',
        'product_id',
        'quantity',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Get the stock movement that owns the item.
     */
    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }

    /**
     * Get the product that this item references.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
