<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        // Total Revenue
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        $revenueThisMonth = Payment::where('status', 'completed')
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount');
        $revenueLastMonth = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$lastMonth, $thisMonth])
            ->sum('amount');
        $revenueChange = $revenueLastMonth > 0
            ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100
            : 0;

        // Pending Payments
        $pendingPayments = Payment::where('status', 'pending')->sum('amount');

        // Average Transaction Value
        $avgTransaction = Payment::where('status', 'completed')->avg('amount') ?? 0;

        // Today's Revenue
        $todayRevenue = Payment::where('status', 'completed')
            ->whereDate('created_at', now())
            ->sum('amount');

        return [
            Stat::make('Venituri Totale', number_format($totalRevenue, 2).' RON')
                ->description($revenueChange >= 0 ? "+{$revenueChange}% față de luna trecută" : "{$revenueChange}% față de luna trecută")
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart([65, 78, 92, 103, 95, 88, 97]),

            Stat::make('Venituri Luna Curentă', number_format($revenueThisMonth, 2).' RON')
                ->description('Plăți procesate')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Plăți în Așteptare', number_format($pendingPayments, 2).' RON')
                ->description('De procesat')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Valoare Medie Tranzacție', number_format($avgTransaction, 2).' RON')
                ->description('Per rezervare')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
        ];
    }
}
