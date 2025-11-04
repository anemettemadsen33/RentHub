<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get dashboard overview
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        return response()->json([
            'overview' => $this->analyticsService->getOverview($user),
            'recent_bookings' => $this->analyticsService->getRecentBookings($user, 10),
            'revenue_stats' => $this->analyticsService->getRevenueStats($user),
            'property_performance' => $this->analyticsService->getPropertyPerformance($user),
        ]);
    }

    /**
     * Get revenue statistics
     */
    public function revenue(Request $request)
    {
        $user = Auth::user();
        $period = $request->input('period', '30days');

        return response()->json([
            'revenue' => $this->analyticsService->getRevenue($user, $period),
            'chart_data' => $this->analyticsService->getRevenueChart($user, $period),
        ]);
    }

    /**
     * Get booking statistics
     */
    public function bookings(Request $request)
    {
        $user = Auth::user();
        $period = $request->input('period', '30days');

        return response()->json([
            'total' => $this->analyticsService->getTotalBookings($user, $period),
            'by_status' => $this->analyticsService->getBookingsByStatus($user, $period),
            'chart_data' => $this->analyticsService->getBookingsChart($user, $period),
        ]);
    }

    /**
     * Get property statistics
     */
    public function properties(Request $request)
    {
        $user = Auth::user();

        return response()->json([
            'total' => $this->analyticsService->getTotalProperties($user),
            'occupancy_rate' => $this->analyticsService->getOccupancyRate($user),
            'top_performing' => $this->analyticsService->getTopProperties($user, 5),
        ]);
    }
}
