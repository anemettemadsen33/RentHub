<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->owner = User::factory()->create(['role' => 'owner']);
        Storage::fake('public');
    }

    public function test_can_list_properties()
    {
        Property::factory()->count(5)->create(['status' => 'available']);

        $response = $this->getJson('/api/v1/properties');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'price', 'type', 'status'],
                ],
            ]);
    }

    public function test_can_view_single_property()
    {
        $property = Property::factory()->create(['status' => 'available']);

        $response = $this->getJson("/api/v1/properties/{$property->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $property->id]);
    }

    public function test_owner_can_create_property()
    {
        $propertyData = [
            'title' => 'Beautiful Apartment',
            'description' => 'A lovely place to stay with plenty of light, modern finishes, and close to transit and shops. Perfect for families.',
            'type' => 'apartment',
            'price' => 100,
            'bedrooms' => 2,
            'bathrooms' => 1,
            'max_guests' => 4,
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'country' => 'USA',
            'postal_code' => '10001',
        ];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson('/api/v1/properties', $propertyData);

        $response->assertCreated()
            ->assertJsonFragment(['title' => 'Beautiful Apartment']);

        $this->assertDatabaseHas('properties', [
            'title' => 'Beautiful Apartment',
            'owner_id' => $this->owner->id,
        ]);
    }

    public function test_owner_can_update_property()
    {
        $property = Property::factory()->create(['owner_id' => $this->owner->id]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->putJson("/api/v1/properties/{$property->id}", [
                'title' => 'Updated Title',
                'price' => 150,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'title' => 'Updated Title',
            'price' => 150,
        ]);
    }

    public function test_owner_can_delete_property()
    {
        $property = Property::factory()->create(['owner_id' => $this->owner->id]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/v1/properties/{$property->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('properties', ['id' => $property->id]);
    }

    public function test_can_search_properties()
    {
        Property::factory()->create([
            'title' => 'Beachfront Villa',
            'city' => 'Miami',
            'status' => 'available',
        ]);

        Property::factory()->create([
            'title' => 'Mountain Cabin',
            'city' => 'Denver',
            'status' => 'available',
        ]);

        $response = $this->getJson('/api/v1/properties?search=beach');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_properties_by_price()
    {
        Property::factory()->create(['price' => 50, 'status' => 'available']);
        Property::factory()->create(['price' => 150, 'status' => 'available']);
        Property::factory()->create(['price' => 250, 'status' => 'available']);

        $response = $this->getJson('/api/v1/properties?min_price=100&max_price=200');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_properties_by_type()
    {
    Property::factory()->create(['type' => 'apartment', 'status' => 'available']);
    Property::factory()->create(['type' => 'house', 'status' => 'available']);

        $response = $this->getJson('/api/v1/properties?type=apartment');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_sort_properties()
    {
    Property::factory()->create(['price' => 100, 'price_per_night' => 100, 'status' => 'available']);
    Property::factory()->create(['price' => 200, 'price_per_night' => 200, 'status' => 'available']);
    Property::factory()->create(['price' => 50, 'price_per_night' => 50, 'status' => 'available']);

        $response = $this->getJson('/api/v1/properties?sort=price&order=asc');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals(50, $data[0]['price']);
    }

    public function test_guest_cannot_create_property()
    {
        $propertyData = [
            'title' => 'Test Property',
            'price' => 100,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/properties', $propertyData);

        $response->assertStatus(403);
    }

    public function test_owner_cannot_update_other_owner_property()
    {
        $otherOwner = User::factory()->create(['role' => 'owner']);
        $property = Property::factory()->create(['owner_id' => $otherOwner->id]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->putJson("/api/v1/properties/{$property->id}", [
                'title' => 'Hacked Title',
            ]);

        $response->assertStatus(403);
    }
}
