<?php

namespace Tests\Unit\Services;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardService();
    }

    public function test_stats_are_computed_and_cached()
    {
        $user = User::factory()->create();
        Property::factory()->count(2)->create(['user_id' => $user->id, 'is_active' => true]);
        Booking::factory()->create([
            'user_id' => $user->id,
            'status' => 'confirmed',
            'check_in' => now()->addDays(2),
            'check_out' => now()->addDays(5),
            'price_per_night' => 100,
        ]);

        $stats = $this->service->getUserStats($user->id);
        $this->assertSame(2, $stats['properties']);
        $this->assertSame(1, $stats['bookingsUpcoming']);
        $this->assertGreaterThan(0, $stats['revenueLast30']);

        // Second call should hit cache (mutate DB without firing observers and expect unchanged cached value)
        \App\Models\Property::withoutEvents(function () use ($user) {
            Property::factory()->create(['user_id' => $user->id, 'is_active' => true]);
        });
        $statsCached = $this->service->getUserStats($user->id);
        $this->assertSame(2, $statsCached['properties'], 'Expected cached properties count to remain 2');
    }

    public function test_invalidation_recomputes_stats()
    {
        $user = User::factory()->create();
        Property::factory()->count(1)->create(['user_id' => $user->id, 'is_active' => true]);
        $initial = $this->service->getUserStats($user->id);
        $this->assertSame(1, $initial['properties']);

        // Add another property (without events) but cache still holds old value
        \App\Models\Property::withoutEvents(function () use ($user) {
            Property::factory()->create(['user_id' => $user->id, 'is_active' => true]);
        });
        $cached = $this->service->getUserStats($user->id);
        $this->assertSame(1, $cached['properties']);

        // Invalidate and expect recompute
        $this->service->invalidateUserStats($user->id);
        $recomputed = $this->service->getUserStats($user->id);
        $this->assertSame(2, $recomputed['properties']);
    }

    public function test_fallback_without_tags()
    {
        $user = User::factory()->create();
        Property::factory()->create(['user_id' => $user->id, 'is_active' => true]);

        // Swap cache store to array for this test (no tags)
        config(['cache.default' => 'array']);

        $stats = $this->service->getUserStats($user->id);
        $this->assertSame(1, $stats['properties']);

        // Add another property; array store without tags caches separately.
        Property::factory()->create(['user_id' => $user->id, 'is_active' => true]);
        // Force forget to mimic tag flush fallback
        Cache::forget("dashboard:stats:user:{$user->id}");
        $stats2 = $this->service->getUserStats($user->id);
        $this->assertSame(2, $stats2['properties']);
    }
}
