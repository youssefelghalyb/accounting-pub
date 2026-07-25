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
        // Plain-text metadata — authors are no longer a business entity (see Contractor).
        'authors',
        'supervisor',
        'introduction_by',
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
     * A book has exactly one contractor (see contractor_books).
     */
    public function contractorBook(): HasOne
    {
        return $this->hasOne(ContractorBook::class);
    }

    public function bookSales(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BookSale::class);
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

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /**
     * Single shared search predicate for title / authors text / ISBN / SKU / contractor name —
     * reused across BookController, Finance's invoice item pickers, Warehouse's product search,
     * and SearchDrawer instead of four near-identical hand-written whereHas chains.
     */
    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('isbn', 'like', "%{$term}%")
                ->orWhere('authors', 'like', "%{$term}%")
                ->orWhereHas('product', function ($pq) use ($term) {
                    $pq->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%");
                })
                ->orWhereHas('contractorBook.contractor', function ($cq) use ($term) {
                    $cq->where('name', 'like', "%{$term}%");
                });
        });
    }
}