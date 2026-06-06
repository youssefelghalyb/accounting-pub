<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    protected $table = 'author_book_contracts';

    protected $fillable = [
        'book_id',
        'contract_date',
        'contract_price',
        'percentage_from_book_profit',
        'contract_file',
        'created_by',
        'edited_by',
        'book_name',
    ];

    protected $casts = [
        'contract_date'               => 'date',
        'contract_price'              => 'decimal:2',
        'percentage_from_book_profit' => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    /**
     * All authors on this contract (via pivot).
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'contract_authors')
                    ->withPivot('is_representative')
                    ->withTimestamps();
    }

    /**
     * The representative author — the one who signs and pays on behalf of the group.
     */
    public function representativeAuthor(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'contract_authors')
                    ->withPivot('is_representative')
                    ->wherePivot('is_representative', true);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(ContractTransaction::class, 'contract_id');
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

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->transactions()->sum('amount');
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return max(0, (float) $this->contract_price - $this->total_paid);
    }

    public function getPaymentPercentageAttribute(): float
    {
        if ((float) $this->contract_price == 0) {
            return 0;
        }
        return round(($this->total_paid / (float) $this->contract_price) * 100, 2);
    }

    /**
     * Comma-separated list of all author names on this contract.
     */
    public function getAuthorsNamesAttribute(): string
    {
        return $this->authors->pluck('full_name')->implode('، ');
    }

    public function getPaymentStatusAttribute(): string
    {
        if ($this->isFullyPaid()) {
            return 'paid';
        } elseif ($this->total_paid > 0) {
            return 'partial';
        }
        return 'pending';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->payment_status) {
            'paid'    => 'green',
            'partial' => 'yellow',
            'pending' => 'red',
            default   => 'gray',
        };
    }

    // ─── Methods ─────────────────────────────────────────────────────────────

    public function isFullyPaid(): bool
    {
        return $this->outstanding_balance <= 0;
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeFullyPaid($query)
    {
        return $query->whereHas('transactions', function ($q) {
            $q->selectRaw('contract_id, SUM(amount) as total_paid')
              ->groupBy('contract_id')
              ->havingRaw('total_paid >= contract_price');
        });
    }

    public function scopePending($query)
    {
        return $query->whereDoesntHave('transactions');
    }

    public function scopeForBook($query, int $bookId)
    {
        return $query->where('book_id', $bookId);
    }

    public function scopeForAuthor($query, int $authorId)
    {
        return $query->whereHas('authors', function ($q) use ($authorId) {
            $q->where('authors.id', $authorId);
        });
    }
}