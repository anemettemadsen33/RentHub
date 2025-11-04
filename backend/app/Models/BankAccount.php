<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    protected $fillable = [
        'user_id',
        'account_name',
        'account_holder_name',
        'iban',
        'bic_swift',
        'bank_name',
        'bank_address',
        'currency',
        'is_default',
        'is_active',
        'account_type',
        'notes',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }

    // Set this account as default
    public function setAsDefault(): void
    {
        if ($this->user_id) {
            // Remove default from other accounts of this user
            static::where('user_id', $this->user_id)
                ->where('id', '!=', $this->id)
                ->update(['is_default' => false]);
        } else {
            // Remove default from all company accounts
            static::whereNull('user_id')
                ->where('id', '!=', $this->id)
                ->update(['is_default' => false]);
        }

        $this->update(['is_default' => true]);
    }

    // Get formatted IBAN (with spaces)
    public function getFormattedIbanAttribute(): string
    {
        return chunk_split($this->iban, 4, ' ');
    }

    // Scope for active accounts
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for company accounts (no user)
    public function scopeCompany($query)
    {
        return $query->whereNull('user_id');
    }

    // Scope for agent/owner accounts
    public function scopeAgent($query)
    {
        return $query->whereNotNull('user_id');
    }

    // Check if account belongs to specific user
    public function belongsToUser(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    // Check if account is company account
    public function isCompanyAccount(): bool
    {
        return $this->user_id === null;
    }

    // Check if all required fields are filled
    public function isComplete(): bool
    {
        return !empty($this->iban) 
            && !empty($this->bic_swift) 
            && !empty($this->bank_name) 
            && !empty($this->account_holder_name);
    }

    // Get account description for display
    public function getDescriptionAttribute(): string
    {
        $type = $this->isCompanyAccount() ? 'Company' : 'Personal';
        $status = $this->is_active ? 'Active' : 'Inactive';
        $default = $this->is_default ? ' (Default)' : '';
        
        return "{$this->account_name} - {$type} - {$status}{$default}";
    }
}
