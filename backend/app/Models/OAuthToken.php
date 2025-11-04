<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OAuthToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'access_token',
        'refresh_token',
        'scopes',
        'expires_at',
        'refresh_expires_at',
        'last_used_at',
        'revoked',
        'client_id',
        'client_name',
    ];

    protected $casts = [
        'scopes' => 'array',
        'expires_at' => 'datetime',
        'refresh_expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'revoked' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if refresh token is expired
     */
    public function isRefreshExpired(): bool
    {
        return $this->refresh_expires_at->isPast();
    }

    /**
     * Check if token is valid
     */
    public function isValid(): bool
    {
        return ! $this->revoked && ! $this->isExpired();
    }
}
