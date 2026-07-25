<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Modules\Finance\Models\Party;

class Contractor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'party_id',
        'name',
        'phone',
        'secondary_phone',
        'email',
        'secondary_email',
        'nationality',
        'address',
        'national_id_file',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function contractorBooks(): HasMany
    {
        return $this->hasMany(ContractorBook::class);
    }

    /**
     * Every book sale (royalty-earning or gift) recorded against any of this
     * contractor's books, reached through the contractor_books join table.
     */
    public function bookSales(): HasManyThrough
    {
        return $this->hasManyThrough(
            BookSale::class,
            ContractorBook::class,
            'contractor_id',      // FK on contractor_books referencing this contractor
            'contractor_book_id', // FK on book_sales referencing contractor_books
            'id',
            'id'
        );
    }

    /**
     * Books this contractor is signed for. Returns a Collection (not a relation)
     * since it goes through contractor_books in the "wrong" direction for hasManyThrough.
     */
    public function books()
    {
        return Book::whereHas('contractorBook', function ($query) {
            $query->where('contractor_id', $this->id);
        })->get();
    }

    public function isClient(): bool
    {
        return $this->party_id !== null;
    }
}
