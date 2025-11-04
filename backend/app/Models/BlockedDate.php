<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlockedDate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'start_date',
        'end_date',
        'reason',
        'google_event_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Check if a date range overlaps with this blocked period
     */
    public function overlaps($startDate, $endDate): bool
    {
        return $this->start_date < $endDate && $this->end_date > $startDate;
    }
}
