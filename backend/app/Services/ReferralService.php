<?php

namespace App\Services;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferralService
{
    protected LoyaltyService $loyaltyService;

    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->loyaltyService = $loyaltyService;
    }

    /**
     * Generate unique referral code for user
     */
    public function generateReferralCode(User $user): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists() || 
                 Referral::where('referral_code', $code)->exists());

        $user->update(['referral_code' => $code]);

        return $code;
    }

    /**
     * Get or create referral code for user
     */
    public function getUserReferralCode(User $user): string
    {
        if ($user->referral_code) {
            return $user->referral_code;
        }

        return $this->generateReferralCode($user);
    }

    /**
     * Create referral invitation
     */
    public function createReferral(User $referrer, ?string $email = null, int $expiryDays = 30): Referral
    {
        $referralCode = $this->getUserReferralCode($referrer);

        return Referral::create([
            'referrer_id' => $referrer->id,
            'referral_code' => $referralCode,
            'referred_email' => $email,
            'status' => 'pending',
            'referrer_reward_points' => config('referral.referrer_points', 500),
            'referred_reward_points' => config('referral.referred_points', 100),
            'referrer_reward_amount' => config('referral.referrer_amount', 0),
            'referred_reward_amount' => config('referral.referred_amount', 10),
            'expires_at' => $expiryDays > 0 ? now()->addDays($expiryDays) : null,
        ]);
    }

    /**
     * Process referral when user registers with code
     */
    public function processReferralRegistration(User $newUser, string $referralCode): ?Referral
    {
        // Find active referral by code
        $referrer = User::where('referral_code', $referralCode)->first();

        if (!$referrer) {
            return null;
        }

        // Check if referral already exists
        $referral = Referral::where('referral_code', $referralCode)
            ->where('referred_email', $newUser->email)
            ->first();

        if (!$referral) {
            // Create new referral record
            $referral = Referral::create([
                'referrer_id' => $referrer->id,
                'referred_id' => $newUser->id,
                'referral_code' => $referralCode,
                'referred_email' => $newUser->email,
                'status' => 'registered',
                'registered_at' => now(),
                'referrer_reward_points' => config('referral.referrer_points', 500),
                'referred_reward_points' => config('referral.referred_points', 100),
                'referrer_reward_amount' => config('referral.referrer_amount', 0),
                'referred_reward_amount' => config('referral.referred_amount', 10),
            ]);
        } else {
            $referral->markAsRegistered($newUser);
        }

        // Update user's referred_by
        $newUser->update(['referred_by' => $referrer->id]);
        
        // Update referrer's total referrals
        $referrer->increment('total_referrals');

        // Award welcome bonus to new user immediately
        $this->awardReferredUserBonus($referral);

        return $referral;
    }

    /**
     * Complete referral and award referrer (after first booking/action)
     */
    public function completeReferral(Referral $referral): bool
    {
        if ($referral->isCompleted()) {
            return false;
        }

        return DB::transaction(function () use ($referral) {
            // Award referrer bonus
            $this->awardReferrerBonus($referral);

            // Mark as completed
            $referral->markAsCompleted();

            // Update referrer's successful referrals count
            $referral->referrer->increment('successful_referrals');

            return true;
        });
    }

    /**
     * Award bonus to the referred user (on registration)
     */
    protected function awardReferredUserBonus(Referral $referral): void
    {
        $referred = $referral->referred;

        if (!$referred) {
            return;
        }

        // Award loyalty points if enabled
        if ($referral->referred_reward_points > 0) {
            $this->loyaltyService->awardPoints(
                $referred,
                $referral->referred_reward_points,
                'bonus',
                "Welcome bonus for using referral code {$referral->referral_code}"
            );
        }

        // Store discount amount for first booking in metadata
        if ($referral->referred_reward_amount > 0) {
            $referral->update([
                'metadata' => array_merge($referral->metadata ?? [], [
                    'referred_discount_available' => $referral->referred_reward_amount,
                ]),
            ]);
        }
    }

    /**
     * Award bonus to the referrer (on completion)
     */
    protected function awardReferrerBonus(Referral $referral): void
    {
        $referrer = $referral->referrer;

        // Award loyalty points
        if ($referral->referrer_reward_points > 0) {
            $this->loyaltyService->awardReferralBonus(
                $referrer,
                $referral->referred,
                $referral->referrer_reward_points
            );
        }

        // Award cash bonus (store for payout)
        if ($referral->referrer_reward_amount > 0) {
            // This could be credited to user's account balance
            // For now, store in metadata
            $referral->update([
                'metadata' => array_merge($referral->metadata ?? [], [
                    'referrer_bonus_credited' => true,
                    'bonus_amount' => $referral->referrer_reward_amount,
                    'credited_at' => now()->toISOString(),
                ]),
            ]);
        }
    }

    /**
     * Get referred user's discount amount for first booking
     */
    public function getReferredUserDiscount(User $user): float
    {
        $referral = Referral::where('referred_id', $user->id)
            ->where('status', 'registered')
            ->first();

        if (!$referral) {
            return 0;
        }

        $metadata = $referral->metadata ?? [];
        return $metadata['referred_discount_available'] ?? $referral->referred_reward_amount;
    }

    /**
     * Apply referred user discount to booking
     */
    public function applyReferredUserDiscount(User $user): ?float
    {
        $referral = Referral::where('referred_id', $user->id)
            ->where('status', 'registered')
            ->first();

        if (!$referral) {
            return null;
        }

        $discount = $this->getReferredUserDiscount($user);

        // Mark discount as used
        $referral->update([
            'metadata' => array_merge($referral->metadata ?? [], [
                'referred_discount_available' => 0,
                'referred_discount_used' => true,
                'used_at' => now()->toISOString(),
            ]),
        ]);

        // Complete the referral (award referrer)
        $this->completeReferral($referral);

        return $discount;
    }

    /**
     * Get referral statistics for user
     */
    public function getUserReferralStats(User $user): array
    {
        return [
            'referral_code' => $this->getUserReferralCode($user),
            'total_referrals' => $user->total_referrals,
            'successful_referrals' => $user->successful_referrals,
            'pending_referrals' => Referral::where('referrer_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            'completed_referrals' => Referral::where('referrer_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'total_points_earned' => Referral::where('referrer_id', $user->id)
                ->where('status', 'completed')
                ->sum('referrer_reward_points'),
            'total_amount_earned' => Referral::where('referrer_id', $user->id)
                ->where('status', 'completed')
                ->sum('referrer_reward_amount'),
            'recent_referrals' => Referral::where('referrer_id', $user->id)
                ->with('referred')
                ->latest()
                ->take(10)
                ->get(),
        ];
    }

    /**
     * Get referral link for user
     */
    public function getReferralLink(User $user, string $baseUrl = null): string
    {
        $code = $this->getUserReferralCode($user);
        $baseUrl = $baseUrl ?? config('app.frontend_url', config('app.url'));

        return $baseUrl . '/register?ref=' . $code;
    }

    /**
     * Validate referral code
     */
    public function validateReferralCode(string $code): ?User
    {
        return User::where('referral_code', $code)->first();
    }

    /**
     * Expire old referrals
     */
    public function expireOldReferrals(): int
    {
        $count = Referral::whereIn('status', ['pending', 'registered'])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        return $count;
    }

    /**
     * Get leaderboard of top referrers
     */
    public function getLeaderboard(int $limit = 10): array
    {
        return User::where('successful_referrals', '>', 0)
            ->orderBy('successful_referrals', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'successful_referrals' => $user->successful_referrals,
                'total_referrals' => $user->total_referrals,
            ])
            ->toArray();
    }
}
