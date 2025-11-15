<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Property;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    /**
     * Compute and cache per-user dashboard stats
     */
    public function getUserStats(int $userId): array
    {
        $ttl = (int) (config('cache-strategy.strategies.database_queries.ttl', 600));
        $enabled = (bool) (config('cache-strategy.strategies.database_queries.enabled', true));

        $cacheKey = "dashboard:stats:user:{$userId}";
        $tags = ['dashboard', "user:{$userId}"];

        $resolver = function () use ($userId): array {
            $propertiesCount = Property::where('user_id', $userId)->count();

            $upcomingBookings = Booking::where('user_id', $userId)
                ->where('status', 'confirmed')
                ->where('check_in', '>=', now())
                ->count();

            $revenueLast30 = Booking::where('user_id', $userId)
                ->where('status', 'confirmed')
                ->where('created_at', '>=', now()->subDays(30))
                ->get()
                ->reduce(function ($carry, $b) {
                    $nights = $b->check_in && $b->check_out ? $b->check_in->diffInDays($b->check_out) : ($b->nights ?? 1);

                    return $carry + (float) ($b->price_per_night ?? 0) * max((int) $nights, 1);
                }, 0.0);

            // Some schemas may not yet have a dedicated guest_id column; fall back to counting distinct user_id for now.
            $guestsUnique = Booking::where('user_id', $userId)->count();

            return [
                'properties' => (int) $propertiesCount,
                'bookingsUpcoming' => (int) $upcomingBookings,
                'revenueLast30' => (float) $revenueLast30,
                'guestsUnique' => (int) $guestsUnique,
            ];
        };

        if (! $enabled) {
            return $resolver();
        }

        try {
            return Cache::tags($tags)->remember($cacheKey, $ttl, $resolver);
        } catch (\Throwable $e) {
            // Fallback if tags not supported by driver
            return Cache::remember($cacheKey, $ttl, $resolver);
        }
    }

    /**
     * Invalidate cached user stats.
     */
    public function invalidateUserStats(int $userId): void
    {
        $cacheKey = "dashboard:stats:user:{$userId}";
        try {
            Cache::tags(['dashboard', "user:{$userId}"])->flush();
        } catch (\Throwable $e) {
            Cache::forget($cacheKey);
        }
    }
}
