<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\LoyaltyTier;
use App\Models\LoyaltyTransaction;
use App\Models\User;
use App\Models\UserLoyalty;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    /**
     * Initialize loyalty account for a new user
     */
    public function initializeLoyaltyAccount(User $user): UserLoyalty
    {
        $startingTier = LoyaltyTier::active()->ordered()->first();

        return UserLoyalty::create([
            'user_id' => $user->id,
            'current_tier_id' => $startingTier?->id,
            'total_points_earned' => 0,
            'available_points' => 0,
            'redeemed_points' => 0,
            'expired_points' => 0,
            'tier_achieved_at' => now(),
        ]);
    }

    /**
     * Award points to user
     */
    public function awardPoints(
        User $user,
        int $points,
        string $type = 'earned',
        ?string $description = null,
        ?Booking $booking = null,
        ?int $expirationMonths = 12
    ): LoyaltyTransaction {
        $loyalty = $user->loyalty ?? $this->initializeLoyaltyAccount($user);

        // Apply tier multiplier for earned points
        if ($type === 'earned' && $loyalty->currentTier) {
            $points = (int) round($points * $loyalty->currentTier->points_multiplier);
        }

        $transaction = LoyaltyTransaction::create([
            'user_id' => $user->id,
            'type' => $type,
            'points' => $points,
            'booking_id' => $booking?->id,
            'description' => $description,
            'expires_at' => $expirationMonths ? now()->addMonths($expirationMonths) : null,
        ]);

        // Update loyalty account
        $loyalty->increment('total_points_earned', $points);
        $loyalty->increment('available_points', $points);
        $loyalty->updateTier();

        return $transaction;
    }

    /**
     * Award points based on booking amount
     */
    public function awardPointsForBooking(Booking $booking): ?LoyaltyTransaction
    {
        if ($booking->status !== 'completed') {
            return null;
        }

        $user = $booking->user;
        $pointsEarned = $this->calculatePointsFromAmount($booking->total_amount);

        return $this->awardPoints(
            $user,
            $pointsEarned,
            'earned',
            "Points earned from booking #{$booking->id}",
            $booking
        );
    }

    /**
     * Calculate points from dollar amount (1 point per $1)
     */
    public function calculatePointsFromAmount(float $amount): int
    {
        return (int) floor($amount);
    }

    /**
     * Calculate discount amount from points (100 points = $1)
     */
    public function calculateDiscountFromPoints(int $points): float
    {
        return round($points / 100, 2);
    }

    /**
     * Redeem points for discount
     */
    public function redeemPoints(
        User $user,
        int $points,
        ?Booking $booking = null,
        ?string $description = null
    ): LoyaltyTransaction {
        $loyalty = $user->loyalty;

        if (! $loyalty || ! $loyalty->canRedeemPoints($points)) {
            throw new \Exception('Insufficient points to redeem');
        }

        if ($points < 500) {
            throw new \Exception('Minimum redemption is 500 points');
        }

        $transaction = LoyaltyTransaction::create([
            'user_id' => $user->id,
            'type' => 'redeemed',
            'points' => -$points, // Negative for redemption
            'booking_id' => $booking?->id,
            'description' => $description ?? "Redeemed {$points} points",
        ]);

        // Update loyalty account
        $loyalty->decrement('available_points', $points);
        $loyalty->increment('redeemed_points', $points);

        return $transaction;
    }

    /**
     * Award welcome bonus to new user
     */
    public function awardWelcomeBonus(User $user, int $points = 100): LoyaltyTransaction
    {
        return $this->awardPoints(
            $user,
            $points,
            'bonus',
            'Welcome bonus',
            null,
            12 // 12 months expiration
        );
    }

    /**
     * Award birthday bonus
     */
    public function awardBirthdayBonus(User $user): ?LoyaltyTransaction
    {
        $loyalty = $user->loyalty;

        if (! $loyalty || ! $user->date_of_birth) {
            return null;
        }

        // Check if already awarded this year
        if ($loyalty->last_birthday_bonus_at && $loyalty->last_birthday_bonus_at->isCurrentYear()) {
            return null;
        }

        // Determine bonus based on tier
        $bonusPoints = match ($loyalty->currentTier?->slug) {
            'platinum' => 500,
            'gold' => 250,
            'silver' => 100,
            default => 100,
        };

        $transaction = $this->awardPoints(
            $user,
            $bonusPoints,
            'bonus',
            'Birthday bonus',
            null,
            12
        );

        $loyalty->update(['last_birthday_bonus_at' => now()]);

        return $transaction;
    }

    /**
     * Award referral bonus
     */
    public function awardReferralBonus(User $referrer, User $referred, int $points = 500): LoyaltyTransaction
    {
        return $this->awardPoints(
            $referrer,
            $points,
            'bonus',
            "Referral bonus for user #{$referred->id}",
            null,
            12
        );
    }

    /**
     * Expire old points
     */
    public function expirePoints(): int
    {
        $expiredTransactions = LoyaltyTransaction::earned()
            ->where('is_expired', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        $totalExpired = 0;

        foreach ($expiredTransactions as $transaction) {
            DB::transaction(function () use ($transaction, &$totalExpired) {
                // Mark transaction as expired
                $transaction->update(['is_expired' => true]);

                // Create expiration record
                LoyaltyTransaction::create([
                    'user_id' => $transaction->user_id,
                    'type' => 'expired',
                    'points' => -$transaction->points,
                    'description' => "Points expired from transaction #{$transaction->id}",
                ]);

                // Update user loyalty
                $loyalty = UserLoyalty::where('user_id', $transaction->user_id)->first();
                if ($loyalty) {
                    $loyalty->decrement('available_points', $transaction->points);
                    $loyalty->increment('expired_points', $transaction->points);
                }

                $totalExpired += $transaction->points;
            });
        }

        return $totalExpired;
    }

    /**
     * Get users with points expiring soon
     */
    public function getUsersWithExpiringPoints(int $days = 30)
    {
        return LoyaltyTransaction::expiringSoon($days)
            ->with('user')
            ->get()
            ->groupBy('user_id')
            ->map(function ($transactions) {
                return [
                    'user' => $transactions->first()->user,
                    'expiring_points' => $transactions->sum('points'),
                    'expiration_date' => $transactions->min('expires_at'),
                ];
            });
    }

    /**
     * Adjust points manually (admin)
     */
    public function adjustPoints(
        User $user,
        int $points,
        string $reason
    ): LoyaltyTransaction {
        $loyalty = $user->loyalty ?? $this->initializeLoyaltyAccount($user);

        $transaction = LoyaltyTransaction::create([
            'user_id' => $user->id,
            'type' => 'adjustment',
            'points' => $points,
            'description' => $reason,
        ]);

        if ($points > 0) {
            $loyalty->increment('total_points_earned', $points);
            $loyalty->increment('available_points', $points);
        } else {
            $loyalty->decrement('available_points', abs($points));
        }

        $loyalty->updateTier();

        return $transaction;
    }

    /**
     * Get loyalty statistics for a user
     */
    public function getUserLoyaltyStats(User $user): array
    {
        $loyalty = $user->loyalty;

        if (! $loyalty) {
            return [
                'tier' => null,
                'total_earned' => 0,
                'available' => 0,
                'redeemed' => 0,
                'expired' => 0,
                'progress_to_next_tier' => 0,
                'points_to_next_tier' => null,
            ];
        }

        return [
            'tier' => $loyalty->currentTier,
            'total_earned' => $loyalty->total_points_earned,
            'available' => $loyalty->available_points,
            'redeemed' => $loyalty->redeemed_points,
            'expired' => $loyalty->expired_points,
            'progress_to_next_tier' => $loyalty->progress_to_next_tier,
            'points_to_next_tier' => $loyalty->next_tier_points,
            'tier_benefits' => $loyalty->currentTier?->loyaltyBenefits()->active()->ordered()->get(),
        ];
    }

    /**
     * Get leaderboard
     */
    public function getLeaderboard(int $limit = 10)
    {
        return UserLoyalty::with(['user', 'currentTier'])
            ->orderBy('total_points_earned', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($loyalty) {
                return [
                    'user' => $loyalty->user->only(['id', 'name', 'email']),
                    'tier' => $loyalty->currentTier,
                    'total_points' => $loyalty->total_points_earned,
                    'available_points' => $loyalty->available_points,
                ];
            });
    }

    /**
     * Check if user can get birthday bonus today
     */
    public function checkBirthdayBonuses(): int
    {
        $awarded = 0;
        $today = now()->format('m-d');

        $users = User::whereNotNull('date_of_birth')
            ->whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') = ?", [$today])
            ->with('loyalty')
            ->get();

        foreach ($users as $user) {
            if ($this->awardBirthdayBonus($user)) {
                $awarded++;
            }
        }

        return $awarded;
    }
}
