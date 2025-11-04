<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    protected ReferralService $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    /**
     * Get user's referral information
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->referralService->getUserReferralStats($user);
        $link = $this->referralService->getReferralLink($user);

        return response()->json([
            'success' => true,
            'data' => [
                'referral_code' => $stats['referral_code'],
                'referral_link' => $link,
                'stats' => [
                    'total_referrals' => $stats['total_referrals'],
                    'successful_referrals' => $stats['successful_referrals'],
                    'pending_referrals' => $stats['pending_referrals'],
                    'completed_referrals' => $stats['completed_referrals'],
                    'total_points_earned' => $stats['total_points_earned'],
                    'total_amount_earned' => $stats['total_amount_earned'],
                ],
                'recent_referrals' => $stats['recent_referrals'],
            ],
        ]);
    }

    /**
     * Get referral code
     */
    public function getCode(Request $request): JsonResponse
    {
        $user = $request->user();
        $code = $this->referralService->getUserReferralCode($user);
        $link = $this->referralService->getReferralLink($user);

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $code,
                'link' => $link,
            ],
        ]);
    }

    /**
     * Generate new referral code
     */
    public function regenerateCode(Request $request): JsonResponse
    {
        $user = $request->user();
        $code = $this->referralService->generateReferralCode($user);
        $link = $this->referralService->getReferralLink($user);

        return response()->json([
            'success' => true,
            'message' => 'New referral code generated',
            'data' => [
                'code' => $code,
                'link' => $link,
            ],
        ]);
    }

    /**
     * Create referral invitation
     */
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'nullable|email',
            'expiry_days' => 'nullable|integer|min:1|max:365',
        ]);

        $user = $request->user();
        $referral = $this->referralService->createReferral(
            $user,
            $request->email,
            $request->expiry_days ?? 30
        );

        return response()->json([
            'success' => true,
            'message' => 'Referral created successfully',
            'data' => $referral,
        ]);
    }

    /**
     * Validate referral code
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $referrer = $this->referralService->validateReferralCode($request->code);

        if (! $referrer) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid referral code',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Valid referral code',
            'data' => [
                'referrer' => [
                    'id' => $referrer->id,
                    'name' => $referrer->name,
                ],
                'rewards' => [
                    'points' => config('referral.referred_points', 100),
                    'discount' => config('referral.referred_amount', 10),
                ],
            ],
        ]);
    }

    /**
     * Get available discount for referred user
     */
    public function getDiscount(Request $request): JsonResponse
    {
        $user = $request->user();
        $discount = $this->referralService->getReferredUserDiscount($user);

        return response()->json([
            'success' => true,
            'data' => [
                'discount_available' => $discount,
                'has_discount' => $discount > 0,
            ],
        ]);
    }

    /**
     * Apply referral discount
     */
    public function applyDiscount(Request $request): JsonResponse
    {
        $user = $request->user();
        $discount = $this->referralService->applyReferredUserDiscount($user);

        if ($discount === null) {
            return response()->json([
                'success' => false,
                'message' => 'No referral discount available',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Referral discount applied',
            'data' => [
                'discount_amount' => $discount,
            ],
        ]);
    }

    /**
     * Get referral leaderboard
     */
    public function leaderboard(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $leaderboard = $this->referralService->getLeaderboard($limit);

        return response()->json([
            'success' => true,
            'data' => $leaderboard,
        ]);
    }

    /**
     * Get referral program info
     */
    public function programInfo(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'referrer_rewards' => [
                    'points' => config('referral.referrer_points', 500),
                    'amount' => config('referral.referrer_amount', 0),
                    'description' => 'Earned when referred user completes first booking',
                ],
                'referred_rewards' => [
                    'points' => config('referral.referred_points', 100),
                    'amount' => config('referral.referred_amount', 10),
                    'description' => 'Received immediately upon registration',
                ],
                'how_it_works' => [
                    '1. Share your unique referral link with friends',
                    '2. They sign up using your link',
                    '3. They get instant rewards (points + discount)',
                    '4. You get rewards when they complete first booking',
                ],
                'terms' => [
                    'Referral codes are case-insensitive',
                    'Referred users must be new to the platform',
                    'Discount applies to first booking only',
                    'Points are awarded to loyalty account',
                    'Referrals expire after 30 days if not used',
                ],
            ],
        ]);
    }
}
