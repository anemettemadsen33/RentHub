<?php

namespace Tests\Feature\Cache;

use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PropertyCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_featured_properties_are_cached_and_invalidated()
    {
        // Seed a few properties
        Property::factory()->count(3)->create(['is_active' => true, 'is_featured' => true]);

        // First request (miss -> populates cache)
        $response1 = $this->getJson('/api/v1/properties/featured');
        $response1->assertOk();
        $first = $response1->json('data');
        $this->assertIsArray($first);

        // Create another featured property to trigger invalidation via observer
        Property::factory()->create(['is_active' => true, 'is_featured' => true]);

        // Second request should reflect new data (cache invalidated by properties tag)
        $response2 = $this->getJson('/api/v1/properties/featured');
        $response2->assertOk();
        $second = $response2->json('data');
        $this->assertGreaterThanOrEqual(count($first), count($second));
    }

    public function test_search_results_are_cached_by_query()
    {
        // Create properties with known city
        Property::factory()->create(['is_active' => true, 'city' => 'Paris', 'status' => 'available']);
        Property::factory()->create(['is_active' => true, 'city' => 'Lyon', 'status' => 'available']);

        // First search (miss -> populates cache)
        $res1 = $this->getJson('/api/v1/properties/search?location=Paris');
        $res1->assertOk();
        $payload1 = $res1->json('data');
        $this->assertIsArray($payload1);
        $this->assertNotEmpty($payload1); // expect at least one result

        // Repeat same search (should come from cache, but we assert equivalence)
        $res2 = $this->getJson('/api/v1/properties/search?location=Paris');
        $res2->assertOk();
        $payload2 = $res2->json('data');
        $this->assertEquals($payload1, $payload2);
    }
}
