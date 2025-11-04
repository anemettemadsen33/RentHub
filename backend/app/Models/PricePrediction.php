<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricePrediction extends Model
{
    protected $fillable = [
        'property_id',
        'date',
        'predicted_price',
        'confidence',
        'features',
        'actual_price',
        'actual_revenue',
        'booked',
        'model_version',
    ];

    protected $casts = [
        'date' => 'date',
        'features' => 'array',
        'booked' => 'boolean',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function recordActual(float $actualPrice, bool $booked, ?float $revenue = null): void
    {
        $this->update([
            'actual_price' => $actualPrice,
            'booked' => $booked,
            'actual_revenue' => $revenue,
        ]);
    }
}
