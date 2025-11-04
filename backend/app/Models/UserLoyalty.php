<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserLoyalty extends Model
{
    protected $table = 'user_loyalty';

    protected $fillable = [
        'user_id',
        'current_tier_id',
        'total_points_earned',
        'available_points',
        'redeemed_points',
        'expired_points',
        'tier_achieved_at',
        'next_tier_points',
        'last_birthday_bonus_at',
    ];

    protected $casts = [
        'tier_achieved_at' => 'datetime',
        'last_birthday_bonus_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentTier(): BelongsTo
    {
        return $this->belongsTo(LoyaltyTier::class, 'current_tier_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class, 'user_id', 'user_id');
    }

    public function updateTier(): void
    {
        $newTier = LoyaltyTier::getTierForPoints($this->total_points_earned);
        
        if ($newTier && (!$this->current_tier_id || $newTier->id !== $this->current_tier_id)) {
            $this->current_tier_id = $newTier->id;
            $this->tier_achieved_at = now();
            
            // Calculate points needed for next tier
            $nextTier = LoyaltyTier::active()
                ->where('min_points', '>', $newTier->min_points)
                ->orderBy('min_points')
                ->first();
            
            $this->next_tier_points = $nextTier 
                ? $nextTier->min_points - $this->total_points_earned 
                : null;
            
            $this->save();
        }
    }

    public function canRedeemPoints(int $points): bool
    {
        return $this->available_points >= $points;
    }

    public function getProgressToNextTierAttribute(): ?float
    {
        if (!$this->currentTier || !$this->next_tier_points) {
            return null;
        }

        $nextTier = LoyaltyTier::active()
            ->where('min_points', '>', $this->currentTier->min_points)
            ->orderBy('min_points')
            ->first();

        if (!$nextTier) {
            return 100; // Max tier reached
        }

        $pointsInCurrentTier = $this->total_points_earned - $this->currentTier->min_points;
        $pointsNeededForNextTier = $nextTier->min_points - $this->currentTier->min_points;

        return ($pointsInCurrentTier / $pointsNeededForNextTier) * 100;
    }
}
