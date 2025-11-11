<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        // Total Bookings
        $totalBookings = Booking::count();
        $bookingsThisMonth = Booking::where('created_at', '>=', $thisMonth)->count();
        $bookingsLastMonth = Booking::whereBetween('created_at', [$lastMonth, $thisMonth])->count();
        $bookingsChange = $bookingsLastMonth > 0
            ? (($bookingsThisMonth - $bookingsLastMonth) / $bookingsLastMonth) * 100
            : 0;

        // Active Bookings
        $activeBookings = Booking::where('status', 'confirmed')
            ->where('check_in', '<=', now())
            ->where('check_out', '>=', now())
            ->count();

        // Pending Bookings
        $pendingBookings = Booking::where('status', 'pending')->count();

        // Today's Check-ins
        $todayCheckIns = Booking::whereDate('check_in', $today)->count();

        return [
            Stat::make('Total Rezervări', $totalBookings)
                ->description($bookingsChange >= 0 ? "+{$bookingsChange}% față de luna trecută" : "{$bookingsChange}% față de luna trecută")
                ->descriptionIcon($bookingsChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($bookingsChange >= 0 ? 'success' : 'danger')
                ->chart([7, 4, 6, 8, 10, 12, 15]),

            Stat::make('Rezervări Active', $activeBookings)
                ->description('Rezervări în curs')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),

            Stat::make('Rezervări în Așteptare', $pendingBookings)
                ->description('Necesită confirmare')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Check-in-uri Astăzi', $todayCheckIns)
                ->description('Check-in-uri programate')
                ->descriptionIcon('heroicon-m-arrow-right-on-rectangle')
                ->color('primary'),
        ];
    }
}
