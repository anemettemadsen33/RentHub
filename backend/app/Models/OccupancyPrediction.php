<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OccupancyPrediction extends Model
{
    protected $fillable = [
        'property_id',
        'prediction_date',
        'predicted_occupancy',
        'confidence',
        'factors',
        'actual_booked',
    ];

    protected $casts = [
        'prediction_date' => 'date',
        'factors' => 'array',
        'actual_booked' => 'boolean',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function recordActual(bool $actualBooked): void
    {
        $this->update(['actual_booked' => $actualBooked]);
    }
}
