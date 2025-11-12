<?php

namespace App\Http\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class OwnerDashboardController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function stats(Request $request)
    {
        $stats = $this->analyticsService->getOwnerDashboardStats($request->user()->id);

        return response()->json($stats);
    }

    public function revenue(Request $request)
    {
        $period = $request->input('period', 'month');
        $revenue = $this->analyticsService->getRevenueOverTime($request->user()->id, $period);

        return response()->json($revenue);
    }

    public function properties(Request $request)
    {
        $performance = $this->analyticsService->getPropertyPerformance($request->user()->id);

        return response()->json($performance);
    }
}

