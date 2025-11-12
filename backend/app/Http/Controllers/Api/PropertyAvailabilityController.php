<?php

namespace App\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class PropertyAvailabilityController extends Controller
{
    /**
     * Get property availability for test endpoint
     */
    public function show(Property $property): JsonResponse
    {
        // Generate a week of sample data
        $start = Carbon::now()->startOfDay();
        $availableDates = [];
        $blockedDates = $property->blocked_dates ?? [];

        for ($i = 0; $i < 30; $i++) {
            $date = $start->copy()->addDays($i)->format('Y-m-d');
            if (! in_array($date, $blockedDates)) {
                $availableDates[] = $date;
            }
        }

        return response()->json([
            'available_dates' => $availableDates,
            'blocked_dates' => $blockedDates,
        ]);
    }
}

