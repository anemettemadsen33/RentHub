<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmartLock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'property_id',
        'provider',
        'lock_id',
        'name',
        'location',
        'credentials',
        'settings',
        'status',
        'auto_generate_codes',
        'battery_level',
        'last_synced_at',
        'error_message',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'auto_generate_codes' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = [
        'credentials',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function accessCodes(): HasMany
    {
        return $this->hasMany(AccessCode::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LockActivity::class);
    }

    public function activeAccessCodes(): HasMany
    {
        return $this->hasMany(AccessCode::class)
            ->where('status', 'active')
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            });
    }

    public function isOnline(): bool
    {
        return $this->status === 'active' &&
               $this->last_synced_at &&
               $this->last_synced_at->diffInMinutes(now()) < 30;
    }

    public function needsBatteryReplacement(): bool
    {
        if (! $this->battery_level) {
            return false;
        }

        return (int) $this->battery_level < 20;
    }
}
