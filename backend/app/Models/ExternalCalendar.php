<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalCalendar extends Model
{
    protected $fillable = [
        'property_id',
        'platform',
        'url',
        'name',
        'sync_enabled',
        'last_synced_at',
        'sync_error',
    ];

    protected $casts = [
        'sync_enabled' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(CalendarSyncLog::class)->orderBy('synced_at', 'desc');
    }

    public function latestSyncLog()
    {
        return $this->hasOne(CalendarSyncLog::class)->latestOfMany('synced_at');
    }
}
