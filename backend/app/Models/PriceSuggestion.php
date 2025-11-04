<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceSuggestion extends Model
{
    protected $fillable = [
        'property_id',
        'start_date',
        'end_date',
        'current_price',
        'suggested_price',
        'min_recommended_price',
        'max_recommended_price',
        'confidence_score',
        'factors',
        'market_average_price',
        'competitor_count',
        'occupancy_rate',
        'demand_score',
        'historical_price',
        'historical_occupancy',
        'status',
        'accepted_at',
        'rejected_at',
        'expires_at',
        'model_version',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'current_price' => 'decimal:2',
        'suggested_price' => 'decimal:2',
        'min_recommended_price' => 'decimal:2',
        'max_recommended_price' => 'decimal:2',
        'market_average_price' => 'decimal:2',
        'occupancy_rate' => 'decimal:2',
        'historical_price' => 'decimal:2',
        'historical_occupancy' => 'decimal:2',
        'factors' => 'array',
        'confidence_score' => 'integer',
        'competitor_count' => 'integer',
        'demand_score' => 'integer',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Accept the suggestion
     */
    public function accept(): void
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        // Optionally update property price
        $this->property->update([
            'price_per_night' => $this->suggested_price,
        ]);
    }

    /**
     * Reject the suggestion
     */
    public function reject(?string $reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'notes' => $reason,
        ]);
    }

    /**
     * Check if suggestion is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get price difference percentage
     */
    public function getPriceDifferenceAttribute(): float
    {
        if ($this->current_price == 0) {
            return 0;
        }

        return (($this->suggested_price - $this->current_price) / $this->current_price) * 100;
    }

    /**
     * Scope for pending suggestions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope for high confidence suggestions
     */
    public function scopeHighConfidence($query, int $threshold = 70)
    {
        return $query->where('confidence_score', '>=', $threshold);
    }
}
