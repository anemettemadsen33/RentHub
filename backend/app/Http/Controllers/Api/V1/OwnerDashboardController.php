<?php

namespace App\Http\\Controllers\\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OwnerDashboardController extends Controller
{
    public function getOverview(Request $request)
    {
        $userId = $request->user()->id;
        $period = $request->input('period', '30');

        $startDate = Carbon::now()->subDays($period);

        try {
            $stats = [
                'total_properties' => Property::where('user_id', $userId)->count(),
                'active_properties' => Property::where('user_id', $userId)
                    ->where('status', 'published')
                    ->count(),
                'total_bookings' => Booking::whereHas('property', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })->count(),
                'active_bookings' => Booking::whereHas('property', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                    ->where('status', 'confirmed')
                    ->where('check_in', '<=', Carbon::now())
                    ->where('check_out', '>=', Carbon::now())
                    ->count(),
                'total_revenue' => 0, // Simplified
                'period_revenue' => 0, // Simplified
                'average_rating' => Review::whereHas('property', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })->avg('overall_rating') ?? 0,
                'total_reviews' => Review::whereHas('property', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            \Log::error('Owner dashboard error: '.$e->getMessage());

            return response()->json([
                'success' => true,
                'data' => [
                    'total_properties' => 0,
                    'active_properties' => 0,
                    'total_bookings' => 0,
                    'active_bookings' => 0,
                    'total_revenue' => 0,
                    'period_revenue' => 0,
                    'average_rating' => 0,
                    'total_reviews' => 0,
                ],
            ]);
        }
    }

    public function getBookingStatistics(Request $request)
    {
        $userId = $request->user()->id;
        $period = $request->input('period', '30');
        $groupBy = $request->input('group_by', 'day');

        $startDate = Carbon::now()->subDays($period);

        $bookings = Booking::whereHas('property', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw($this->getDateGrouping($groupBy).' as period'),
                DB::raw('COUNT(*) as total_bookings'),
                DB::raw('SUM(CASE WHEN status = "confirmed" THEN 1 ELSE 0 END) as confirmed'),
                DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    public function getRevenueReports(Request $request)
    {
        $userId = $request->user()->id;
        $period = $request->input('period', '30');
        $groupBy = $request->input('group_by', 'day');

        $startDate = Carbon::now()->subDays($period);

        $revenue = Payment::whereHas('booking.property', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw($this->getDateGrouping($groupBy).' as period'),
                DB::raw('SUM(amount) as total_revenue'),
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('AVG(amount) as average_transaction')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $byProperty = Payment::whereHas('booking.property', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->join('properties', 'bookings.property_id', '=', 'properties.id')
            ->select(
                'properties.id',
                'properties.title',
                DB::raw('SUM(payments.amount) as total_revenue'),
                DB::raw('COUNT(payments.id) as total_bookings')
            )
            ->groupBy('properties.id', 'properties.title')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'timeline' => $revenue,
                'by_property' => $byProperty,
            ],
        ]);
    }

    public function getOccupancyRate(Request $request)
    {
        $userId = $request->user()->id;
        $propertyId = $request->input('property_id');
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth());

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        $query = Property::where('user_id', $userId);

        if ($propertyId) {
            $query->where('id', $propertyId);
        }

        $properties = $query->get();

        $occupancyData = [];

        foreach ($properties as $property) {
            $bookedDays = Booking::where('property_id', $property->id)
                ->where('status', 'confirmed')
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('check_in', [$startDate, $endDate])
                        ->orWhereBetween('check_out', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('check_in', '<=', $startDate)
                                ->where('check_out', '>=', $endDate);
                        });
                })
                ->get()
                ->sum(function ($booking) use ($startDate, $endDate) {
                    $checkIn = Carbon::parse($booking->check_in)->max($startDate);
                    $checkOut = Carbon::parse($booking->check_out)->min($endDate);

                    return $checkIn->diffInDays($checkOut) + 1;
                });

            $occupancyRate = ($bookedDays / $totalDays) * 100;

            $occupancyData[] = [
                'property_id' => $property->id,
                'property_title' => $property->title,
                'total_days' => $totalDays,
                'booked_days' => $bookedDays,
                'available_days' => $totalDays - $bookedDays,
                'occupancy_rate' => round($occupancyRate, 2),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $occupancyData,
        ]);
    }

    public function getPropertyPerformance(Request $request)
    {
        $userId = $request->user()->id;
        $period = $request->input('period', '90');

        $startDate = Carbon::now()->subDays($period);

        $performance = Property::where('user_id', $userId)
            ->with(['bookings' => function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }])
            ->get()
            ->map(function ($property) use ($startDate) {
                $bookings = $property->bookings;

                $totalRevenue = Payment::whereIn('booking_id', $bookings->pluck('id'))
                    ->where('status', 'completed')
                    ->sum('amount');

                $averageRating = Review::where('property_id', $property->id)
                    ->where('created_at', '>=', $startDate)
                    ->avg('overall_rating');

                $reviewCount = Review::where('property_id', $property->id)
                    ->where('created_at', '>=', $startDate)
                    ->count();

                $views = $property->views ?? 0;

                return [
                    'property_id' => $property->id,
                    'title' => $property->title,
                    'total_bookings' => $bookings->count(),
                    'confirmed_bookings' => $bookings->where('status', 'confirmed')->count(),
                    'total_revenue' => $totalRevenue,
                    'average_rating' => round($averageRating ?? 0, 2),
                    'total_reviews' => $reviewCount,
                    'views' => $views,
                    'conversion_rate' => $views > 0 ? round(($bookings->count() / $views) * 100, 2) : 0,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $performance,
        ]);
    }

    public function getGuestDemographics(Request $request)
    {
        $userId = $request->user()->id;
        $period = $request->input('period', '90');

        $startDate = Carbon::now()->subDays($period);

        $bookings = Booking::whereHas('property', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('created_at', '>=', $startDate)
            ->with('user')
            ->get();

        $byLocation = $bookings->groupBy(function ($booking) {
            return $booking->user->country ?? 'Unknown';
        })->map->count();

        $repeatGuests = $bookings->groupBy('user_id')
            ->filter(function ($group) {
                return $group->count() > 1;
            })
            ->count();

        $avgBookingValue = Payment::whereIn('booking_id', $bookings->pluck('id'))
            ->where('status', 'completed')
            ->avg('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total_unique_guests' => $bookings->unique('user_id')->count(),
                'repeat_guests' => $repeatGuests,
                'by_location' => $byLocation,
                'average_booking_value' => round($avgBookingValue ?? 0, 2),
            ],
        ]);
    }

    private function getDateGrouping($groupBy)
    {
        return match ($groupBy) {
            'day' => 'DATE(created_at)',
            'week' => 'YEARWEEK(created_at)',
            'month' => 'DATE_FORMAT(created_at, "%Y-%m")',
            default => 'DATE(created_at)',
        };
    }
}

