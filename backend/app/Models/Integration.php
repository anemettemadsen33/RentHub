<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Integration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'status',
        'settings',
        'credentials',
        'last_sync_at',
        'last_sync_status',
        'sync_errors',
        'is_global',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'credentials' => 'encrypted:array',
        'sync_errors' => 'array',
        'last_sync_at' => 'datetime',
        'is_global' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'credentials',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeConnected($query)
    {
        return $query->where('status', 'connected');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }

    public function isDisconnected(): bool
    {
        return $this->status === 'disconnected';
    }

    public function hasError(): bool
    {
        return $this->status === 'error';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function markAsConnected(): void
    {
        $this->update([
            'status' => 'connected',
            'last_sync_status' => 'success',
            'sync_errors' => null,
        ]);
    }

    public function markAsDisconnected(): void
    {
        $this->update([
            'status' => 'disconnected',
            'last_sync_status' => 'disconnected',
        ]);
    }

    public function markAsError(string $error = null): void
    {
        $errors = $this->sync_errors ?? [];
        if ($error) {
            $errors[] = [
                'message' => $error,
                'timestamp' => now()->toIso8601String(),
            ];
        }

        $this->update([
            'status' => 'error',
            'last_sync_status' => 'error',
            'sync_errors' => array_slice($errors, -10), // Keep last 10 errors
        ]);
    }

    public function markAsPending(): void
    {
        $this->update([
            'status' => 'pending',
            'last_sync_status' => 'pending',
        ]);
    }

    public function updateLastSync(string $status = 'success'): void
    {
        $this->update([
            'last_sync_at' => now(),
            'last_sync_status' => $status,
        ]);
    }

    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->update(['settings' => $settings]);
    }

    public function getCredential(string $key, $default = null)
    {
        return data_get($this->credentials, $key, $default);
    }

    public function setCredential(string $key, $value): void
    {
        $credentials = $this->credentials ?? [];
        data_set($credentials, $key, $value);
        $this->update(['credentials' => $credentials]);
    }

    public function clearCredentials(): void
    {
        $this->update(['credentials' => null]);
    }
}