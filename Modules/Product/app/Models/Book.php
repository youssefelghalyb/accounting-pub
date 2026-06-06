<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Book extends Model
{
    protected $fillable = [
        'product_id',
        'category_id',
        'sub_category_id',
        'isbn',
        'num_of_pages',
        'cover_type',
        'published_at',
        'language',
        'is_translated',
        'translated_from',
        'translated_to',
        'translator_name',
        'created_by',
        'edited_by',
    ];

    protected $casts = [
        'published_at'  => 'date',
        'is_translated' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class, 'category_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class, 'sub_category_id');
    }

    /**
     * A book has exactly one contract.
     */
    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class);
    }

    /**
     * Convenience: get all authors of this book through its contract.
     */
    public function getAuthorsAttribute()
    {
        return $this->contract ? $this->contract->authors : collect();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'edited_by');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getCoverTypeColorAttribute(): string
    {
        return match ($this->cover_type) {
            'hard'  => 'blue',
            'soft'  => 'green',
            default => 'gray',
        };
    }

    public function getFullTitleAttribute(): string
    {
        return $this->product ? $this->product->name : '';
    }

    public function getTranslationInfoAttribute(): ?string
    {
        if (! $this->is_translated) {
            return null;
        }
        $info = [];
        if ($this->translated_from) $info[] = "From: {$this->translated_from}";
        if ($this->translated_to)   $info[] = "To: {$this->translated_to}";
        if ($this->translator_name) $info[] = "By: {$this->translator_name}";
        return implode(' | ', $info);
    }

    /**
     * Authors names as a display string — e.g. for listings and cards.
     */
    public function getAuthorsNamesAttribute(): string
    {
        return $this->authors->pluck('full_name')->implode('، ');
    }
}