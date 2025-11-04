<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TenantDashboardController extends Controller
{
    public function getOverview(Request $request)
    {
        $userId = $request->user()->id;
        $period = $request->input('period', '30');

        $startDate = Carbon::now()->subDays($period);

        $stats = [
            'total_bookings' => Booking::where('user_id', $userId)->count(),
            'active_bookings' => Booking::where('user_id', $userId)
                ->where('status', 'confirmed')
                ->where('check_in', '<=', Carbon::now())
                ->where('check_out', '>=', Carbon::now())
                ->count(),
            'upcoming_bookings' => Booking::where('user_id', $userId)
                ->where('status', 'confirmed')
                ->where('check_in', '>', Carbon::now())
                ->count(),
            'completed_bookings' => Booking::where('user_id', $userId)
                ->where('status', 'completed')
                ->count(),
            'total_spent' => Payment::whereHas('booking', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->where('status', 'completed')
                ->sum('amount'),
            'period_spent' => Payment::whereHas('booking', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->where('status', 'completed')
                ->where('created_at', '>=', $startDate)
                ->sum('amount'),
            'saved_properties' => Wishlist::where('user_id', $userId)
                ->withCount('items')
                ->get()
                ->sum('items_count'),
            'reviews_given' => Review::where('user_id', $userId)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function getBookingHistory(Request $request)
    {
        $userId = $request->user()->id;
        $status = $request->input('status');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        $query = Booking::where('user_id', $userId)
            ->with(['property', 'payments']);

        if ($status) {
            $query->where('status', $status);
        }

        $bookings = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $bookings->items(),
            'pagination' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    public function getSpendingReports(Request $request)
    {
        $userId = $request->user()->id;
        $period = $request->input('period', '365');
        $groupBy = $request->input('group_by', 'month');

        $startDate = Carbon::now()->subDays($period);

        $spending = Payment::whereHas('booking', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw($this->getDateGrouping($groupBy) . ' as period'),
                DB::raw('SUM(amount) as total_spent'),
                DB::raw('COUNT(*) as total_bookings'),
                DB::raw('AVG(amount) as average_booking_cost')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $byProperty = Payment::whereHas('booking', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->join('properties', 'bookings.property_id', '=', 'properties.id')
            ->select(
                'properties.id',
                'properties.title',
                DB::raw('SUM(payments.amount) as total_spent'),
                DB::raw('COUNT(payments.id) as total_bookings')
            )
            ->groupBy('properties.id', 'properties.title')
            ->orderBy('total_spent', 'desc')
            ->limit(10)
            ->get();

        $totalSpent = Payment::whereHas('booking', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('status', 'completed')
            ->sum('amount');

        $averageSpent = Payment::whereHas('booking', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('status', 'completed')
            ->avg('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'timeline' => $spending,
                'by_property' => $byProperty,
                'total_spent' => $totalSpent,
                'average_booking_cost' => round($averageSpent ?? 0, 2),
            ],
        ]);
    }

    public function getSavedProperties(Request $request)
    {
        $userId = $request->user()->id;

        $wishlists = Wishlist::where('user_id', $userId)
            ->with(['items.property'])
            ->get()
            ->map(function ($wishlist) {
                return [
                    'id' => $wishlist->id,
                    'name' => $wishlist->name,
                    'description' => $wishlist->description,
                    'properties_count' => $wishlist->items->count(),
                    'properties' => $wishlist->items->map(function ($item) {
                        return [
                            'id' => $item->property->id,
                            'title' => $item->property->title,
                            'price' => $item->property->price_per_night,
                            'location' => $item->property->city . ', ' . $item->property->country,
                            'added_at' => $item->created_at,
                        ];
                    }),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $wishlists,
        ]);
    }

    public function getReviewHistory(Request $request)
    {
        $userId = $request->user()->id;
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        $reviews = Review::where('user_id', $userId)
            ->with(['property', 'response'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $stats = [
            'total_reviews' => Review::where('user_id', $userId)->count(),
            'average_rating_given' => Review::where('user_id', $userId)->avg('overall_rating'),
            'reviews_with_response' => Review::where('user_id', $userId)
                ->has('response')
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $reviews->items(),
                'stats' => $stats,
            ],
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    public function getUpcomingTrips(Request $request)
    {
        $userId = $request->user()->id;

        $upcomingBookings = Booking::where('user_id', $userId)
            ->where('status', 'confirmed')
            ->where('check_in', '>', Carbon::now())
            ->with(['property', 'payments'])
            ->orderBy('check_in', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $upcomingBookings,
        ]);
    }

    public function getTravelStatistics(Request $request)
    {
        $userId = $request->user()->id;

        $bookings = Booking::where('user_id', $userId)
            ->where('status', 'completed')
            ->with('property')
            ->get();

        $totalNights = $bookings->sum(function ($booking) {
            return Carbon::parse($booking->check_in)->diffInDays(Carbon::parse($booking->check_out));
        });

        $citiesVisited = $bookings->pluck('property.city')->unique()->count();
        $countriesVisited = $bookings->pluck('property.country')->unique()->count();

        $favoriteCity = $bookings->groupBy('property.city')
            ->sortByDesc(function ($group) {
                return $group->count();
            })
            ->keys()
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total_nights' => $totalNights,
                'cities_visited' => $citiesVisited,
                'countries_visited' => $countriesVisited,
                'favorite_city' => $favoriteCity,
                'total_trips' => $bookings->count(),
            ],
        ]);
    }

    private function getDateGrouping($groupBy)
    {
        return match ($groupBy) {
            'day' => 'DATE(created_at)',
            'week' => 'YEARWEEK(created_at)',
            'month' => 'DATE_FORMAT(created_at, "%Y-%m")',
            'year' => 'YEAR(created_at)',
            default => 'DATE_FORMAT(created_at, "%Y-%m")',
        };
    }
}
