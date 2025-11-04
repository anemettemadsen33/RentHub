<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyRecommendation extends Model
{
    protected $fillable = [
        'user_id',
        'property_id',
        'score',
        'reason',
        'factors',
        'shown',
        'clicked',
        'booked',
        'valid_until',
    ];

    protected $casts = [
        'factors' => 'array',
        'shown' => 'boolean',
        'clicked' => 'boolean',
        'booked' => 'boolean',
        'valid_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function markShown(): void
    {
        $this->update(['shown' => true]);
    }

    public function markClicked(): void
    {
        $this->update(['clicked' => true]);
    }

    public function markBooked(): void
    {
        $this->update(['booked' => true]);
    }
}
