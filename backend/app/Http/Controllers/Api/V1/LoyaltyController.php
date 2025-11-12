<?php

namespace App\Http\Controllers\\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\LoyaltyTier;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoyaltyController extends Controller
{
    protected $loyaltyService;

    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->middleware('auth:sanctum');
        $this->loyaltyService = $loyaltyService;
    }

    /**
     * Get current user's loyalty points and tier
     */
    public function getPoints(Request $request)
    {
        $user = $request->user();
        $stats = $this->loyaltyService->getUserLoyaltyStats($user);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get loyalty points history
     */
    public function getPointsHistory(Request $request)
    {
        $user = $request->user();

        $perPage = $request->input('per_page', 15);
        $type = $request->input('type'); // earned, redeemed, expired, bonus

        $query = $user->loyaltyTransactions()
            ->with('booking:id,property_id,check_in_date,check_out_date,total_amount')
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        $transactions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * Redeem points for discount
     */
    public function redeemPoints(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'points' => 'required|integer|min:500',
            'booking_id' => 'nullable|exists:bookings,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $points = $request->input('points');
        $bookingId = $request->input('booking_id');

        try {
            $booking = $bookingId ? Booking::findOrFail($bookingId) : null;

            // Check if booking belongs to user
            if ($booking && $booking->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking does not belong to you',
                ], 403);
            }

            // Check maximum discount (50% of booking)
            if ($booking) {
                $maxDiscount = $booking->total_amount * 0.5;
                $requestedDiscount = $this->loyaltyService->calculateDiscountFromPoints($points);

                if ($requestedDiscount > $maxDiscount) {
                    $maxPoints = (int) floor($maxDiscount * 100);

                    return response()->json([
                        'success' => false,
                        'message' => "Maximum discount for this booking is \${$maxDiscount}. You can redeem up to {$maxPoints} points.",
                    ], 422);
                }
            }

            $transaction = $this->loyaltyService->redeemPoints($user, $points, $booking);
            $discount = $this->loyaltyService->calculateDiscountFromPoints($points);

            return response()->json([
                'success' => true,
                'message' => "Successfully redeemed {$points} points for \${$discount} discount",
                'data' => [
                    'transaction' => $transaction,
                    'discount_amount' => $discount,
                    'remaining_points' => $user->loyalty->available_points,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get all loyalty tiers
     */
    public function getTiers()
    {
        $tiers = LoyaltyTier::active()
            ->with('loyaltyBenefits')
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tiers,
        ]);
    }

    /**
     * Get specific tier details
     */
    public function getTierDetails($slug)
    {
        $tier = LoyaltyTier::where('slug', $slug)
            ->where('is_active', true)
            ->with('loyaltyBenefits')
            ->first();

        if (! $tier) {
            return response()->json([
                'success' => false,
                'message' => 'Tier not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tier,
        ]);
    }

    /**
     * Get points value calculator
     */
    public function calculateValue(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'points' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $points = $request->input('points');
        $discount = $this->loyaltyService->calculateDiscountFromPoints($points);

        return response()->json([
            'success' => true,
            'data' => [
                'points' => $points,
                'discount_amount' => $discount,
                'currency' => 'USD',
            ],
        ]);
    }

    /**
     * Get expiring points
     */
    public function getExpiringPoints(Request $request)
    {
        $user = $request->user();
        $days = $request->input('days', 30);

        $expiringTransactions = $user->loyaltyTransactions()
            ->expiringSoon($days)
            ->get();

        $totalExpiring = $expiringTransactions->sum('points');
        $nearestExpiration = $expiringTransactions->min('expires_at');

        return response()->json([
            'success' => true,
            'data' => [
                'total_expiring_points' => $totalExpiring,
                'nearest_expiration_date' => $nearestExpiration,
                'transactions' => $expiringTransactions,
            ],
        ]);
    }
}

