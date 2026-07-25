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
     * A book has exactly one contract.
     *
     * @deprecated Legacy Author/Contract system, being replaced by Contractor/ContractorBook.
     *             Kept alive until the Author→Contractor migration's destructive phase removes
     *             the Contract model entirely — do not build new features on this relation.
     */
    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class);
    }

    /**
     * A book has exactly one contractor (see contractor_books).
     */
    public function contractorBook(): HasOne
    {
        return $this->hasOne(ContractorBook::class);
    }

    /**
     * Convenience: get all authors of this book through its (legacy) contract.
     *
     * @deprecated Renamed off `authors`/`authors_names` because those names now belong to the
     *             real `books.authors` plain-text column. Use the `authors` attribute directly
     *             for the new free-text author list; this accessor only serves the still-active
     *             legacy Author/Contract UI until it is removed.
     */
    public function getContractAuthorsAttribute()
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
     * Legacy contract authors as a display string — e.g. for listings and cards.
     *
     * @deprecated see getContractAuthorsAttribute().
     */
    public function getContractAuthorsNamesAttribute(): string
    {
        return $this->contract_authors->pluck('full_name')->implode('، ');
    }
}