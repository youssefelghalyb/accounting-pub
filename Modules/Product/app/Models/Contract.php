<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    protected $table = 'author_book_contracts';

    protected $fillable = [
        'author_id',
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
        'contract_date' => 'date',
        'contract_price' => 'decimal:2',
        'percentage_from_book_profit' => 'decimal:2',
    ];

    // Relationships
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
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

    // Calculate total paid
    public function getTotalPaidAttribute(): float
    {
        return $this->transactions()->sum('amount');
    }

    // Calculate outstanding balance
    public function getOutstandingBalanceAttribute(): float
    {
        return max(0, $this->contract_price - $this->total_paid);
    }

    // Calculate payment percentage
    public function getPaymentPercentageAttribute(): float
    {
        if ($this->contract_price == 0) {
            return 0;
        }
        return round(($this->total_paid / $this->contract_price) * 100, 2);
    }

    // Check if fully paid
    public function isFullyPaid(): bool
    {
        return $this->outstanding_balance <= 0;
    }

    // Get payment status
    public function getPaymentStatusAttribute(): string
    {
        if ($this->isFullyPaid()) {
            return 'paid';
        } elseif ($this->total_paid > 0) {
            return 'partial';
        }
        return 'pending';
    }

    // Get status color
    public function getStatusColorAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'green',
            'partial' => 'yellow',
            'pending' => 'red',
            default => 'gray',
        };
    }

    // Scopes
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

    public function scopeForAuthor($query, int $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    public function scopeForBook($query, int $bookId)
    {
        return $query->where('book_id', $bookId);
    }
}
