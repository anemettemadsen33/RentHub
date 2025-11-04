<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    public function getOwnerDashboardStats($userId)
    {
        return Cache::remember("owner_stats_{$userId}", 3600, function () use ($userId) {
            $properties = Property::where('user_id', $userId)->get();
            $propertyIds = $properties->pluck('id');
            
            $totalRevenue = Payment::whereIn('booking_id', function ($query) use ($propertyIds) {
                $query->select('id')->from('bookings')->whereIn('property_id', $propertyIds);
            })->where('status', 'completed')->sum('amount');
            
            $activeBookings = Booking::whereIn('property_id', $propertyIds)
                ->where('status', 'confirmed')
                ->where('check_in', '<=', now())
                ->where('check_out', '>=', now())
                ->count();
            
            $totalBookings = Booking::whereIn('property_id', $propertyIds)->count();
            
            $occupancyRate = $this->calculateOccupancyRate($propertyIds);
            
            return [
                'total_revenue' => $totalRevenue,
                'active_bookings' => $activeBookings,
                'total_properties' => $properties->count(),
                'occupancy_rate' => $occupancyRate,
                'total_bookings' => $totalBookings,
                'avg_booking_value' => $totalBookings > 0 ? $totalRevenue / $totalBookings : 0,
            ];
        });
    }
    
    public function getTenantDashboardStats($userId)
    {
        return Cache::remember("tenant_stats_{$userId}", 3600, function () use ($userId) {
            $bookings = Booking::where('user_id', $userId)->get();
            
            $totalSpending = Payment::whereIn('booking_id', $bookings->pluck('id'))
                ->where('status', 'completed')
                ->sum('amount');
            
            $upcomingBookings = $bookings->where('status', 'confirmed')
                ->where('check_in', '>', now())
                ->count();
            
            return [
                'total_spending' => $totalSpending,
                'upcoming_bookings' => $upcomingBookings,
                'past_bookings' => $bookings->where('status', 'completed')->count(),
                'total_bookings' => $bookings->count(),
                'favorite_properties' => $this->getFavoriteCount($userId),
            ];
        });
    }
    
    public function getRevenueOverTime($userId, $period = 'month')
    {
        $properties = Property::where('user_id', $userId)->pluck('id');
        
        $query = Payment::whereIn('booking_id', function ($q) use ($properties) {
            $q->select('id')->from('bookings')->whereIn('property_id', $properties);
        })->where('status', 'completed');
        
        switch ($period) {
            case 'week':
                $groupBy = DB::raw('DATE(created_at)');
                $query->where('created_at', '>=', now()->subWeeks(1));
                break;
            case 'year':
                $groupBy = DB::raw('MONTH(created_at)');
                $query->where('created_at', '>=', now()->subYear());
                break;
            default:
                $groupBy = DB::raw('DATE(created_at)');
                $query->where('created_at', '>=', now()->subMonth());
        }
        
        return $query->select($groupBy . ' as date', DB::raw('SUM(amount) as revenue'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
    
    public function getPropertyPerformance($userId)
    {
        $properties = Property::where('user_id', $userId)->get();
        
        $performance = [];
        foreach ($properties as $property) {
            $bookings = Booking::where('property_id', $property->id);
            $revenue = Payment::whereIn('booking_id', $bookings->pluck('id'))
                ->where('status', 'completed')
                ->sum('amount');
            
            $performance[] = [
                'property_id' => $property->id,
                'property_name' => $property->title,
                'bookings_count' => $bookings->count(),
                'revenue' => $revenue,
                'occupancy_rate' => $this->calculateOccupancyRate([$property->id]),
            ];
        }
        
        return collect($performance)->sortByDesc('revenue')->values();
    }
    
    private function calculateOccupancyRate($propertyIds)
    {
        $startDate = now()->startOfYear();
        $endDate = now();
        $totalDays = $startDate->diffInDays($endDate) * count($propertyIds);
        
        $bookedDays = Booking::whereIn('property_id', $propertyIds)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('check_in', [$startDate, $endDate])
                    ->orWhereBetween('check_out', [$startDate, $endDate]);
            })
            ->get()
            ->sum(function ($booking) {
                return Carbon::parse($booking->check_in)->diffInDays(Carbon::parse($booking->check_out));
            });
        
        return $totalDays > 0 ? round(($bookedDays / $totalDays) * 100, 2) : 0;
    }
    
    private function getFavoriteCount($userId)
    {
        // Assuming wishlist table exists
        return DB::table('wishlists')->where('user_id', $userId)->count();
    }
}
