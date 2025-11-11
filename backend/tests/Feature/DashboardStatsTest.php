<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_authentication()
    {
        $this->getJson('/api/v1/dashboard/stats')
            ->assertStatus(401);
    }

    public function test_returns_aggregated_stats_for_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Create properties owned by user
        Property::factory()->count(2)->create([
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        // Create bookings for user
        $future = now()->addDays(5);
        Booking::factory()->create([
            'user_id' => $user->id,
            'status' => 'confirmed',
            'check_in' => $future,
            'check_out' => $future->copy()->addDays(3),
            'price_per_night' => 100.00,
        ]);
        // Another booking in last 30 days for revenue
        Booking::factory()->create([
            'user_id' => $user->id,
            'status' => 'confirmed',
            'created_at' => now()->subDays(3),
            'check_in' => now()->addDays(10),
            'check_out' => now()->addDays(12),
            'price_per_night' => 120.00,
        ]);

        $response = $this->getJson('/api/v1/dashboard/stats')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_properties',
                    'active_bookings',
                    'total_revenue',
                    'pending_reviews',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals(2, $data['total_properties']);
        $this->assertGreaterThanOrEqual(1, $data['active_bookings']);
        $this->assertIsNumeric($data['total_revenue']);
    }
}
