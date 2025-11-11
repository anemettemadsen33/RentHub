<?php

namespace App\\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Get availability calendar for a property
     * Returns availability and pricing for a date range
     */
    public function getAvailability(Request $request, Property $property): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'months' => 'sometimes|integer|min:1|max:12', // Alternative: get N months from start_date
        ]);

        // If months is provided, calculate end_date
        if ($request->filled('months')) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = $startDate->copy()->addMonths($request->months);
        } else {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
        }

        // Limit to max 1 year
        if ($startDate->diffInDays($endDate) > 365) {
            return response()->json([
                'success' => false,
                'message' => 'Date range cannot exceed 365 days',
            ], 422);
        }

        $calendar = [];
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');

            $calendar[] = [
                'date' => $dateString,
                'available' => ! $this->isDateUnavailable($property, $dateString),
                'blocked' => $property->isDateBlocked($dateString),
                'booked' => $this->isDateBooked($property, $dateString),
                'price' => $this->getPriceForDate($property, $dateString),
                'is_custom_price' => isset($property->custom_pricing[$dateString]),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'property_id' => $property->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'base_price' => $property->price_per_night,
                'calendar' => $calendar,
            ],
        ]);
    }

    /**
     * Get pricing calendar (simplified view for pricing overview)
     */
    public function getPricingCalendar(Request $request, Property $property): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $calendar = [];
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $customPricing = $property->custom_pricing ?? [];

            if (isset($customPricing[$dateString])) {
                $calendar[$dateString] = [
                    'price' => $customPricing[$dateString],
                    'is_custom' => true,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'property_id' => $property->id,
                'base_price' => $property->price_per_night,
                'custom_pricing' => $calendar,
            ],
        ]);
    }

    /**
     * Bulk block dates by range
     */
    public function bulkBlockDates(Request $request, Property $property): JsonResponse
    {
        // Check authorization
        if ($property->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'sometimes|string|max:255', // Optional reason for blocking
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Limit to max 1 year at a time
        if ($startDate->diffInDays($endDate) > 365) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot block more than 365 days at once',
            ], 422);
        }

        $period = CarbonPeriod::create($startDate, $endDate);
        $blockedCount = 0;

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            if ($property->blockDate($dateString)) {
                $blockedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$blockedCount} dates blocked successfully",
            'data' => [
                'blocked_count' => $blockedCount,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'property' => $property->fresh(),
            ],
        ]);
    }

    /**
     * Bulk unblock dates by range
     */
    public function bulkUnblockDates(Request $request, Property $property): JsonResponse
    {
        // Check authorization
        if ($property->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $period = CarbonPeriod::create($startDate, $endDate);
        $unblockedCount = 0;

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            if ($property->unblockDate($dateString)) {
                $unblockedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$unblockedCount} dates unblocked successfully",
            'data' => [
                'unblocked_count' => $unblockedCount,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'property' => $property->fresh(),
            ],
        ]);
    }

    /**
     * Bulk set custom pricing for date range
     */
    public function bulkSetPricing(Request $request, Property $property): JsonResponse
    {
        // Check authorization
        if ($property->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'price' => 'required|numeric|min:1',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Limit to max 1 year at a time
        if ($startDate->diffInDays($endDate) > 365) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot set pricing for more than 365 days at once',
            ], 422);
        }

        $period = CarbonPeriod::create($startDate, $endDate);
        $updatedCount = 0;

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            if ($property->setCustomPrice($dateString, $request->price)) {
                $updatedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Custom pricing set for {$updatedCount} dates",
            'data' => [
                'updated_count' => $updatedCount,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'price' => $request->price,
                'property' => $property->fresh(),
            ],
        ]);
    }

    /**
     * Remove custom pricing for date range
     */
    public function bulkRemovePricing(Request $request, Property $property): JsonResponse
    {
        // Check authorization
        if ($property->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $period = CarbonPeriod::create($startDate, $endDate);
        $removedCount = 0;

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            if ($property->removeCustomPrice($dateString)) {
                $removedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Custom pricing removed for {$removedCount} dates",
            'data' => [
                'removed_count' => $removedCount,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'property' => $property->fresh(),
            ],
        ]);
    }

    /**
     * Get blocked dates list
     */
    public function getBlockedDates(Property $property): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'property_id' => $property->id,
                'blocked_dates' => $property->blocked_dates ?? [],
                'count' => count($property->blocked_dates ?? []),
            ],
        ]);
    }

    /**
     * Helper: Check if date is unavailable (blocked or booked)
     */
    private function isDateUnavailable(Property $property, string $date): bool
    {
        return $property->isDateBlocked($date) || $this->isDateBooked($property, $date);
    }

    /**
     * Helper: Check if date is booked
     */
    private function isDateBooked(Property $property, string $date): bool
    {
        return $property->bookings()
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where('check_in', '<=', $date)
            ->where('check_out', '>', $date)
            ->exists();
    }

    /**
     * Helper: Get price for specific date
     */
    private function getPriceForDate(Property $property, string $date): float
    {
        $customPricing = $property->custom_pricing ?? [];

        if (isset($customPricing[$date])) {
            return (float) $customPricing[$date];
        }

        return (float) $property->price_per_night;
    }
}

