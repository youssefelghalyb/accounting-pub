<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'type',
        'phone',
        'email',
        'address',
        'tax_number',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'type_label',
        'status_label',
    ];


    // public function invoices(): HasMany
    // {
    //     return $this->hasMany(Invoice::class);
    // }

    // public function payments(): HasMany
    // {
    //     return $this->hasMany(Payment::class);
    // }

    // Accessors
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'individual' => __('customer::customer.types.individual'),
            'company' => __('customer::customer.types.company'),
            'online' => __('customer::customer.types.online'),
            default => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active 
            ? __('customer::customer.active') 
            : __('customer::customer.inactive');
    }

    public function getStatusColorAttribute(): string
    {
        return $this->is_active ? 'green' : 'red';
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'individual' => 'blue',
            'company' => 'purple',
            'online' => 'green',
            default => 'gray',
        };
    }

    

    /**
     * Scope: Active customers only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by customer type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Search customers
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('tax_number', 'like', "%{$search}%");
        });
    }

    /**
     * Deactivate customer
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Activate customer
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }
}