<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRule extends Model
{
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
        'adjustment_value' => 'decimal:2',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Calculate adjusted price based on rule
     */
    public function calculatePrice(float $basePrice): float
    {
        if (!$this->is_active) {
            return $basePrice;
        }

        if ($this->adjustment_type === 'percentage') {
            return $basePrice + ($basePrice * ($this->adjustment_value / 100));
        }

        return $basePrice + $this->adjustment_value;
    }

    /**
     * Check if rule applies to given date
     */
    public function appliesTo(\Carbon\Carbon $date): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check date range
        if ($this->start_date && $date->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $date->gt($this->end_date)) {
            return false;
        }

        // Check day of week
        if ($this->days_of_week && !in_array($date->dayOfWeek, $this->days_of_week)) {
            return false;
        }

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
