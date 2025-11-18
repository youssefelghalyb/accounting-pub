<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'name',
        'type',
        'sku',
        'description',
        'base_price',
        'status',
        'created_by',
        'edited_by',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
    ];

    // Relationships
    public function book(): HasOne
    {
        return $this->hasOne(Book::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'edited_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'green',
            'inactive' => 'gray',
            default => 'gray',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'book' => 'blue',
            'ebook' => 'purple',
            'journal' => 'green',
            'course' => 'orange',
            'bundle' => 'indigo',
            default => 'gray',
        };
    }
}
