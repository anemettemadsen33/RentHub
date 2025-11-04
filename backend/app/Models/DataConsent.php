<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataConsent extends Model
{
    protected $fillable = [
        'user_id',
        'consent_type',
        'granted',
        'ip_address',
        'user_agent',
        'details',
        'granted_at',
        'revoked_at',
    ];

    protected $casts = [
        'granted' => 'boolean',
        'details' => 'array',
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->granted && !$this->revoked_at;
    }

    public function revoke(): bool
    {
        return $this->update([
            'granted' => false,
            'revoked_at' => now(),
        ]);
    }
}
