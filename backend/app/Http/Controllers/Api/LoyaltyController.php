<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyTier;
use App\Services\LoyaltyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    protected LoyaltyService $loyaltyService;

    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->loyaltyService = $loyaltyService;
    }

    /**
     * Get user's loyalty information
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->loyalty) {
            $this->loyaltyService->initializeLoyaltyAccount($user);
            $user->load('loyalty');
        }

        $stats = $this->loyaltyService->getUserLoyaltyStats($user);

        return response()->json([
            'success' => true,
            'data' => [
                'loyalty' => $user->loyalty,
                'tier' => $stats['tier'],
                'stats' => [
                    'total_earned' => $stats['total_earned'],
                    'available' => $stats['available'],
                    'redeemed' => $stats['redeemed'],
                    'expired' => $stats['expired'],
                    'progress_to_next_tier' => $stats['progress_to_next_tier'],
                    'points_to_next_tier' => $stats['points_to_next_tier'],
                ],
                'benefits' => $stats['tier_benefits'] ?? [],
            ],
        ]);
    }

    /**
     * Get all loyalty tiers
     */
    public function tiers(): JsonResponse
    {
        $tiers = LoyaltyTier::active()
            ->ordered()
            ->with('loyaltyBenefits')
            ->get()
            ->map(function ($tier) {
                return [
                    'id' => $tier->id,
                    'name' => $tier->name,
                    'slug' => $tier->slug,
                    'min_points' => $tier->min_points,
                    'max_points' => $tier->max_points,
                    'discount_percentage' => $tier->discount_percentage,
                    'points_multiplier' => $tier->points_multiplier,
                    'priority_booking' => $tier->priority_booking,
                    'badge_color' => $tier->badge_color,
                    'icon' => $tier->icon,
                    'benefits' => $tier->loyaltyBenefits()
                        ->active()
                        ->ordered()
                        ->get()
                        ->map(fn ($benefit) => [
                            'name' => $benefit->name,
                            'description' => $benefit->description,
                            'type' => $benefit->benefit_type,
                            'value' => $benefit->value,
                            'icon' => $benefit->icon,
                        ]),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $tiers,
        ]);
    }

    /**
     * Get user's loyalty transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $user = $request->user();

        $transactions = $user->loyaltyTransactions()
            ->with('booking')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * Redeem points for discount
     */
    public function redeem(Request $request): JsonResponse
    {
        $request->validate([
            'points' => 'required|integer|min:500',
            'booking_id' => 'nullable|exists:bookings,id',
        ]);

        $user = $request->user();

        try {
            $transaction = $this->loyaltyService->redeemPoints(
                $user,
                $request->points,
                $request->booking_id ? \App\Models\Booking::find($request->booking_id) : null,
                $request->description
            );

            $discountAmount = $this->loyaltyService->calculateDiscountFromPoints($request->points);

            return response()->json([
                'success' => true,
                'message' => 'Points redeemed successfully',
                'data' => [
                    'transaction' => $transaction,
                    'discount_amount' => $discountAmount,
                    'remaining_points' => $user->loyalty->available_points,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Calculate discount from points
     */
    public function calculateDiscount(Request $request): JsonResponse
    {
        $request->validate([
            'points' => 'required|integer|min:1',
        ]);

        $discount = $this->loyaltyService->calculateDiscountFromPoints($request->points);

        return response()->json([
            'success' => true,
            'data' => [
                'points' => $request->points,
                'discount_amount' => $discount,
            ],
        ]);
    }

    /**
     * Get leaderboard
     */
    public function leaderboard(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $leaderboard = $this->loyaltyService->getLeaderboard($limit);

        return response()->json([
            'success' => true,
            'data' => $leaderboard,
        ]);
    }

    /**
     * Claim birthday bonus
     */
    public function claimBirthdayBonus(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->date_of_birth) {
            return response()->json([
                'success' => false,
                'message' => 'Date of birth not set',
            ], 400);
        }

        $transaction = $this->loyaltyService->awardBirthdayBonus($user);

        if (! $transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Birthday bonus already claimed this year or not eligible',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Birthday bonus awarded!',
            'data' => [
                'transaction' => $transaction,
                'points_awarded' => $transaction->points,
            ],
        ]);
    }

    /**
     * Get points expiring soon
     */
    public function expiringPoints(Request $request): JsonResponse
    {
        $user = $request->user();
        $days = $request->input('days', 30);

        $expiringTransactions = $user->loyaltyTransactions()
            ->active()
            ->expiringSoon($days)
            ->get();

        $totalExpiring = $expiringTransactions->sum('points');

        return response()->json([
            'success' => true,
            'data' => [
                'total_expiring' => $totalExpiring,
                'transactions' => $expiringTransactions,
                'days' => $days,
            ],
        ]);
    }

    /**
     * Get tier benefits preview
     */
    public function tierBenefits(Request $request, $tierId): JsonResponse
    {
        $tier = LoyaltyTier::active()
            ->with('loyaltyBenefits')
            ->findOrFail($tierId);

        return response()->json([
            'success' => true,
            'data' => [
                'tier' => [
                    'id' => $tier->id,
                    'name' => $tier->name,
                    'slug' => $tier->slug,
                    'min_points' => $tier->min_points,
                    'discount_percentage' => $tier->discount_percentage,
                    'points_multiplier' => $tier->points_multiplier,
                    'priority_booking' => $tier->priority_booking,
                    'badge_color' => $tier->badge_color,
                ],
                'benefits' => $tier->loyaltyBenefits()
                    ->active()
                    ->ordered()
                    ->get()
                    ->map(fn ($benefit) => [
                        'name' => $benefit->name,
                        'description' => $benefit->description,
                        'type' => $benefit->benefit_type,
                        'value' => $benefit->value,
                        'icon' => $benefit->icon,
                    ]),
            ],
        ]);
    }
}

