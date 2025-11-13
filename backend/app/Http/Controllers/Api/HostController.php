<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HostController extends Controller
{
    /**
     * Get host dashboard statistics
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        $cacheKey = "host_stats_{$user->id}";

        $stats = Cache::remember($cacheKey, 300, function () use ($user) {
            $properties = Property::where('user_id', $user->id)->get();
            $propertyIds = $properties->pluck('id');

            $bookings = Booking::whereIn('property_id', $propertyIds)
                ->where('status', '!=', 'cancelled')
                ->get();

            $totalEarnings = $bookings->sum('total_price');
            $upcomingBookings = $bookings->where('check_in', '>=', now())->count();

            // Calculate earnings growth (mock for now, can be enhanced with period comparison)
            $earningsGrowth = 12.5;

            $reviews = Review::whereIn('property_id', $propertyIds)->get();
            $averageRating = $reviews->avg('overall_rating') ?? 0;

            $recentBookings = Booking::whereIn('property_id', $propertyIds)
                ->with(['property:id,title', 'user:id,name'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($booking) {
                    return [
                        'id' => $booking->id,
                        'propertyTitle' => $booking->property->title ?? 'Unknown',
                        'guestName' => $booking->user->name ?? 'Guest',
                        'checkIn' => $booking->check_in->format('M d, Y'),
                        'checkOut' => $booking->check_out->format('M d, Y'),
                        'total' => $booking->total_price,
                    ];
                });

            return [
                'totalEarnings' => $totalEarnings,
                'earningsGrowth' => $earningsGrowth,
                'activeProperties' => $properties->where('status', 'published')->count(),
                'totalProperties' => $properties->count(),
                'upcomingBookings' => $upcomingBookings,
                'totalBookings' => $bookings->count(),
                'averageRating' => round($averageRating, 1),
                'totalReviews' => $reviews->count(),
                'recentBookings' => $recentBookings,
            ];
        });

        return response()->json($stats);
    }

    /**
     * Get host properties
     */
    public function properties(Request $request)
    {
        $user = $request->user();

        $properties = Property::where('user_id', $user->id)
            ->with(['reviews', 'bookings'])
            ->get()
            ->map(function ($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'description' => $property->description,
                    'images' => $property->images ?? [],
                    'status' => $property->status,
                    'views' => $property->views ?? 0,
                    'rating' => $property->reviews->avg('overall_rating') ?? 0,
                ];
            });

        return response()->json(['data' => $properties]);
    }
}
