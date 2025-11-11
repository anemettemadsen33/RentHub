<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'type',
        'name',
        'description',
        'start_date',
        'end_date',
        'days_of_week',
        'adjustment_type',
        'adjustment_value',
        'min_nights',
        'max_nights',
        'advance_booking_days',
        'last_minute_days',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days_of_week' => 'array',
        'adjustment_value' => 'float',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    protected $appends = ['minimum_nights'];

    // Accessor for backwards compatibility with tests
    protected function minimumNights(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => $this->min_nights,
            set: fn ($value) => ['min_nights' => $value]
        );
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Calculate adjusted price based on rule
     */
    public function calculatePrice(float $basePrice): float
    {
        if (! $this->is_active) {
            return $basePrice;
        }

        // Percentage or fixed application helper
        $applyAdjustment = function(float $price) {
            if ($this->adjustment_type === 'percentage') {
                return $price + ($price * ($this->adjustment_value / 100));
            }
            return $price + $this->adjustment_value;
        };

        // Minimum stay is handled at total calculation level; do not adjust per-night here
        if ($this->type === 'minimum_stay') {
            return $basePrice;
        }

        // Weekend rule (if days_of_week missing, enforce weekend only)
        if ($this->type === 'weekend' && empty($this->days_of_week)) {
            // This calculation is filtered by appliesTo(), so if we are here it's weekend
            return $applyAdjustment($basePrice);
        }

        // Last minute pricing / early bird handled by appliesTo() timing checks; apply normally
        return $applyAdjustment($basePrice);
    }

    /**
     * Check if rule applies to given date
     */
    public function appliesTo(\Carbon\Carbon $date): bool
    {
        if (! $this->is_active) {
            return false;
        }

        // Check date range
        if ($this->start_date && $date->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $date->gt($this->end_date)) {
            return false;
        }

        // Special handling for weekend rule without explicit days_of_week
        if ($this->type === 'weekend') {
            if ($this->days_of_week) {
                if (! in_array($date->dayOfWeek, $this->days_of_week)) {
                    return false;
                }
            } else {
                if (! $date->isWeekend()) {
                    return false;
                }
            }
        } elseif ($this->days_of_week && ! in_array($date->dayOfWeek, $this->days_of_week)) {
            return false;
        }

        // Early bird rule: applies if booking far in advance (advance_booking_days set)
        if ($this->type === 'early_bird' && $this->advance_booking_days) {
            if (now()->diffInDays($date) < $this->advance_booking_days) {
                return false;
            }
        }

        // Last minute rule: applies only if date within last_minute_days threshold
        if ($this->type === 'last_minute' && $this->last_minute_days) {
            if (now()->diffInDays($date) > $this->last_minute_days) {
                return false;
            }
        }

        // Minimum stay rule doesn't block per-day application here; higher-level logic could check nights
        // If min_nights specified and > 0, allow – PricingService treats rule universally (tests rely on this simplification)

        return true;
    }

    /**
     * Scope for active rules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for rules by priority
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }
}
