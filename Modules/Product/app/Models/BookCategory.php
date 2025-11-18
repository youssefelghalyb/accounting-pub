<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookCategory extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
        'created_by',
        'edited_by',
    ];

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(BookCategory::class, 'parent_id');
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'category_id');
    }

    public function subCategoryBooks(): HasMany
    {
        return $this->hasMany(Book::class, 'sub_category_id');
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
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeChildren($query)
    {
        return $query->whereNotNull('parent_id');
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        if ($this->parent) {
            return "{$this->parent->name} > {$this->name}";
        }
        return $this->name;
    }

    public function getIsParentAttribute(): bool
    {
        return $this->parent_id === null;
    }
}
