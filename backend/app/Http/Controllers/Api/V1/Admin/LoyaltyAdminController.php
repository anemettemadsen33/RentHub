<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLoyalty;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoyaltyAdminController extends Controller
{
    protected $loyaltyService;

    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
        $this->loyaltyService = $loyaltyService;
    }

    /**
     * Award points to a user (admin only)
     */
    public function awardPoints(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer|min:1',
            'type' => 'required|in:earned,bonus',
            'description' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::findOrFail($request->user_id);

        $transaction = $this->loyaltyService->awardPoints(
            $user,
            $request->points,
            $request->type,
            $request->description
        );

        return response()->json([
            'success' => true,
            'message' => "Successfully awarded {$request->points} points to {$user->name}",
            'data' => $transaction,
        ]);
    }

    /**
     * Adjust points (can be negative)
     */
    public function adjustPoints(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer',
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::findOrFail($request->user_id);

        $transaction = $this->loyaltyService->adjustPoints(
            $user,
            $request->points,
            $request->reason
        );

        $action = $request->points > 0 ? 'added' : 'deducted';
        $absolutePoints = abs($request->points);

        return response()->json([
            'success' => true,
            'message' => "Successfully {$action} {$absolutePoints} points for {$user->name}",
            'data' => $transaction,
        ]);
    }

    /**
     * Get leaderboard
     */
    public function getLeaderboard(Request $request)
    {
        $limit = $request->input('limit', 10);
        $leaderboard = $this->loyaltyService->getLeaderboard($limit);

        return response()->json([
            'success' => true,
            'data' => $leaderboard,
        ]);
    }

    /**
     * Get loyalty statistics
     */
    public function getStatistics()
    {
        $stats = [
            'total_users_with_loyalty' => UserLoyalty::count(),
            'total_points_issued' => UserLoyalty::sum('total_points_earned'),
            'total_points_redeemed' => UserLoyalty::sum('redeemed_points'),
            'total_points_expired' => UserLoyalty::sum('expired_points'),
            'total_available_points' => UserLoyalty::sum('available_points'),
            'users_by_tier' => UserLoyalty::with('currentTier')
                ->get()
                ->groupBy('currentTier.name')
                ->map(fn ($group) => $group->count()),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get user loyalty details (admin view)
     */
    public function getUserLoyalty($userId)
    {
        $user = User::with(['loyalty.currentTier', 'loyaltyTransactions'])
            ->findOrFail($userId);

        $stats = $this->loyaltyService->getUserLoyaltyStats($user);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->only(['id', 'name', 'email']),
                'loyalty' => $stats,
                'recent_transactions' => $user->loyaltyTransactions()
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get(),
            ],
        ]);
    }

    /**
     * Manually expire points
     */
    public function expirePoints()
    {
        $totalExpired = $this->loyaltyService->expirePoints();

        return response()->json([
            'success' => true,
            'message' => "Successfully expired {$totalExpired} points",
            'data' => [
                'total_expired' => $totalExpired,
            ],
        ]);
    }

    /**
     * Get users with expiring points
     */
    public function getUsersWithExpiringPoints(Request $request)
    {
        $days = $request->input('days', 30);
        $users = $this->loyaltyService->getUsersWithExpiringPoints($days);

        return response()->json([
            'success' => true,
            'data' => $users->values(),
        ]);
    }

    /**
     * Award birthday bonuses
     */
    public function awardBirthdayBonuses()
    {
        $awarded = $this->loyaltyService->checkBirthdayBonuses();

        return response()->json([
            'success' => true,
            'message' => "Successfully awarded birthday bonuses to {$awarded} users",
            'data' => [
                'users_awarded' => $awarded,
            ],
        ]);
    }
}
