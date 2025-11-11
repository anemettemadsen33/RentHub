<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatsCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_stats_are_cached_and_etag_returns_304()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        Property::factory()->count(1)->create(['user_id' => $user->id]);
        Booking::factory()->create(['user_id' => $user->id, 'status' => 'confirmed']);

        // First call - expect 200 with ETag header
        $first = $this->getJson('/api/v1/dashboard/stats')
            ->assertOk();
        $etag = $first->headers->get('ETag');
        $this->assertNotEmpty($etag, 'ETag should be present on first response');

        // Second call with If-None-Match should yield 304
        $second = $this->withHeaders(['If-None-Match' => $etag])->getJson('/api/v1/dashboard/stats');
        $second->assertStatus(304);
    }
}
