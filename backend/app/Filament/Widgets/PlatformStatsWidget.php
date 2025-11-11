<?php

namespace App\Filament\Widgets;

use App\Models\Property;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        
        // Properties
        $totalProperties = Property::count();
        $activeProperties = Property::where('status', 'active')->count();
        $propertiesThisMonth = Property::where('created_at', '>=', $thisMonth)->count();
        $propertiesLastMonth = Property::whereBetween('created_at', [$lastMonth, $thisMonth])->count();
        $propertiesChange = $propertiesLastMonth > 0 
            ? (($propertiesThisMonth - $propertiesLastMonth) / $propertiesLastMonth) * 100 
            : 0;

        // Users
        $totalUsers = User::count();
        $usersThisMonth = User::where('created_at', '>=', $thisMonth)->count();
        $usersLastMonth = User::whereBetween('created_at', [$lastMonth, $thisMonth])->count();
        $usersChange = $usersLastMonth > 0 
            ? (($usersThisMonth - $usersLastMonth) / $usersLastMonth) * 100 
            : 0;

        // Verified Users
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $verificationRate = $totalUsers > 0 ? ($verifiedUsers / $totalUsers) * 100 : 0;

        return [
            Stat::make('Proprietăți Active', $activeProperties . ' / ' . $totalProperties)
                ->description($propertiesChange >= 0 ? "+{$propertiesChange}% proprietăți noi luna aceasta" : "{$propertiesChange}% luna aceasta")
                ->descriptionIcon('heroicon-m-home')
                ->color('success')
                ->chart([3, 4, 5, 6, 7, 8, 9]),

            Stat::make('Total Utilizatori', $totalUsers)
                ->description($usersChange >= 0 ? "+{$usersChange}% față de luna trecută" : "{$usersChange}% față de luna trecută")
                ->descriptionIcon($usersChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($usersChange >= 0 ? 'success' : 'danger')
                ->chart([15, 18, 22, 28, 35, 42, 48]),

            Stat::make('Utilizatori Verificați', $verifiedUsers)
                ->description(number_format($verificationRate, 1) . '% rată de verificare')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('info'),

            Stat::make('Utilizatori Noi Luna Aceasta', $usersThisMonth)
                ->description('Înregistrări noi')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary'),
        ];
    }
}
