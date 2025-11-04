<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LockActivity extends Model
{
    protected $fillable = [
        'smart_lock_id',
        'access_code_id',
        'user_id',
        'event_type',
        'code_used',
        'access_method',
        'metadata',
        'description',
        'ip_address',
        'user_agent',
        'event_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'event_at' => 'datetime',
    ];

    public function smartLock(): BelongsTo
    {
        return $this->belongsTo(SmartLock::class);
    }

    public function accessCode(): BelongsTo
    {
        return $this->belongsTo(AccessCode::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isSecurityEvent(): bool
    {
        return in_array($this->event_type, ['error', 'manual_override', 'unauthorized_access']);
    }

    public function scopeSecurityEvents($query)
    {
        return $query->whereIn('event_type', ['error', 'manual_override', 'unauthorized_access']);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('event_at', '>=', now()->subDays($days));
    }
}
