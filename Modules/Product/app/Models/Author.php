<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Author extends Model
{
    protected $fillable = [
        'full_name',
        'nationality',
        'country_of_residence',
        'bio',
        'occupation',
        'phone_number',
        'whatsapp_number',
        'email',
        'id_image',
        'created_by',
        'edited_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'total_contract_value',
        'total_paid',
        'outstanding_balance',
    ];

    // Relationships
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'edited_by');
    }

    // Get total contract value for this author
    public function getTotalContractValueAttribute(): float
    {
        return $this->contracts()->sum('contract_price');
    }

    // Get total paid to this author
    public function getTotalPaidAttribute(): float
    {
        return $this->contracts()
            ->with('transactions')
            ->get()
            ->sum(function ($contract) {
                return $contract->transactions->sum('amount');
            });
    }

    // Get outstanding balance for this author
    public function getOutstandingBalanceAttribute(): float
    {
        return $this->total_contract_value - $this->total_paid;
    }

    // Get all transactions for this author across all contracts
    public function getAllTransactions()
    {
        return ContractTransaction::whereHas('contract', function ($query) {
            $query->where('author_id', $this->id);
        })->get();
    }
}
