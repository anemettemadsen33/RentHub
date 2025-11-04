<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyTier extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'min_points',
        'max_points',
        'discount_percentage',
        'points_multiplier',
        'priority_booking',
        'benefits',
        'badge_color',
        'icon',
        'order',
        'is_active',
    ];

    protected $casts = [
        'benefits' => 'array',
        'priority_booking' => 'boolean',
        'is_active' => 'boolean',
        'discount_percentage' => 'decimal:2',
        'points_multiplier' => 'decimal:2',
    ];

    public function userLoyalties(): HasMany
    {
        return $this->hasMany(UserLoyalty::class, 'current_tier_id');
    }

    public function loyaltyBenefits(): HasMany
    {
        return $this->hasMany(LoyaltyBenefit::class, 'tier_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public static function getTierForPoints(int $points): ?self
    {
        return self::active()
            ->where('min_points', '<=', $points)
            ->where(function ($query) use ($points) {
                $query->whereNull('max_points')
                    ->orWhere('max_points', '>=', $points);
            })
            ->orderBy('min_points', 'desc')
            ->first();
    }
}
