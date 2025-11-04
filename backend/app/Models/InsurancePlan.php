<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsurancePlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'price_per_night',
        'price_percentage',
        'fixed_price',
        'max_coverage',
        'coverage_details',
        'exclusions',
        'terms_and_conditions',
        'is_active',
        'is_mandatory',
        'min_nights',
        'max_nights',
        'min_booking_value',
        'max_booking_value',
        'display_order',
    ];

    protected $casts = [
        'coverage_details' => 'array',
        'exclusions' => 'array',
        'is_active' => 'boolean',
        'is_mandatory' => 'boolean',
        'price_per_night' => 'decimal:2',
        'price_percentage' => 'decimal:2',
        'fixed_price' => 'decimal:2',
        'max_coverage' => 'decimal:2',
        'min_booking_value' => 'decimal:2',
        'max_booking_value' => 'decimal:2',
    ];

    public function bookingInsurances(): HasMany
    {
        return $this->hasMany(BookingInsurance::class);
    }

    public function calculatePremium(float $bookingTotal, int $nights): float
    {
        $premium = 0;

        if ($this->fixed_price > 0) {
            $premium = $this->fixed_price;
        } elseif ($this->price_percentage > 0) {
            $premium = ($bookingTotal * $this->price_percentage) / 100;
        } elseif ($this->price_per_night > 0) {
            $premium = $this->price_per_night * $nights;
        }

        return round($premium, 2);
    }

    public function isEligibleForBooking(float $bookingTotal, int $nights): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($nights < $this->min_nights) {
            return false;
        }

        if ($this->max_nights && $nights > $this->max_nights) {
            return false;
        }

        if ($bookingTotal < $this->min_booking_value) {
            return false;
        }

        if ($this->max_booking_value && $bookingTotal > $this->max_booking_value) {
            return false;
        }

        return true;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    public function scopeOptional($query)
    {
        return $query->where('is_mandatory', false);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrderedByDisplay($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }
}
