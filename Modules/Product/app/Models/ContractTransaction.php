<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractTransaction extends Model
{
    protected $table = 'author_contract_transactions';

    protected $fillable = [
        'contract_id',
        'amount',
        'payment_date',
        'notes',
        'receipt_file',
        'created_by',
        'edited_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id');
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
    public function scopeForContract($query, int $contractId)
    {
        return $query->where('contract_id', $contractId);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('payment_date', now()->year);
    }
}
