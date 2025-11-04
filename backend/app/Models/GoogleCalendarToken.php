<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoogleCalendarToken extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'property_id',
        'access_token',
        'refresh_token',
        'token_type',
        'expires_at',
        'calendar_id',
        'calendar_name',
        'webhook_id',
        'webhook_resource_id',
        'webhook_expiration',
        'sync_enabled',
        'last_sync_at',
        'sync_errors',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'webhook_expiration' => 'datetime',
        'last_sync_at' => 'datetime',
        'sync_enabled' => 'boolean',
        'sync_errors' => 'array',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Check if the access token is expired
     */
    public function isTokenExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the webhook is expired
     */
    public function isWebhookExpired(): bool
    {
        return $this->webhook_expiration && $this->webhook_expiration->isPast();
    }

    /**
     * Mark sync as successful
     */
    public function markSyncSuccess(): void
    {
        $this->update([
            'last_sync_at' => now(),
            'sync_errors' => null,
        ]);
    }

    /**
     * Mark sync as failed
     */
    public function markSyncFailure(string $error): void
    {
        $errors = $this->sync_errors ?? [];
        $errors[] = [
            'error' => $error,
            'timestamp' => now()->toISOString(),
        ];

        // Keep only last 10 errors
        $errors = array_slice($errors, -10);

        $this->update([
            'sync_errors' => $errors,
        ]);
    }
}
