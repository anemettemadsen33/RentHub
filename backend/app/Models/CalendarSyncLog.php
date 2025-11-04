<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarSyncLog extends Model
{
    protected $fillable = [
        'external_calendar_id',
        'status',
        'dates_added',
        'dates_removed',
        'dates_updated',
        'error_message',
        'metadata',
        'synced_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'synced_at' => 'datetime',
        'dates_added' => 'integer',
        'dates_removed' => 'integer',
        'dates_updated' => 'integer',
    ];

    public function externalCalendar(): BelongsTo
    {
        return $this->belongsTo(ExternalCalendar::class);
    }
}
