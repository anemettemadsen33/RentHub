<?php

namespace Tests\Feature\Api;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test fetching all properties
     */
    public function test_can_fetch_properties_list(): void
    {
        Property::factory()->count(5)->create(['status' => 'available']);

        $response = $this->getJson('/api/v1/properties');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'price_per_night',
                        'city',
                        'country',
                    ],
                ],
            ]);
    }

    /**
     * Test fetching single property
     */
    public function test_can_fetch_single_property(): void
    {
        $property = Property::factory()->create(['status' => 'available']);

        $response = $this->getJson("/api/v1/properties/{$property->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $property->id,
                    'title' => $property->title,
                ],
            ]);
    }

    /**
     * Test creating a property
     */
    public function test_authenticated_user_can_create_property(): void
    {
        $user = User::factory()->create(['role' => 'host']);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/properties', [
            'title' => 'Test Property',
            'description' => 'A beautiful test property with amazing views and great amenities for your perfect vacation stay',
            'type' => 'apartment',
            'price_per_night' => 100.00,
            'bedrooms' => 2,
            'bathrooms' => 1,
            'guests' => 4,
            'street_address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'country' => 'Test Country',
            'postal_code' => '12345',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'price_per_night',
                ],
            ]);

        $this->assertDatabaseHas('properties', [
            'title' => 'Test Property',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test updating a property
     */
    public function test_owner_can_update_their_property(): void
    {
        $user = User::factory()->create(['role' => 'host']);
        $property = Property::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/v1/properties/{$property->id}", [
            'title' => 'Updated Property Title',
            'price_per_night' => 150.00,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'title' => 'Updated Property Title',
        ]);
    }

    /**
     * Test property search with filters
     */
    public function test_can_search_properties_with_filters(): void
    {
        Property::factory()->create([
            'city' => 'Paris',
            'price_per_night' => 100,
            'guests' => 4,
            'status' => 'available',
        ]);

        Property::factory()->create([
            'city' => 'London',
            'price_per_night' => 200,
            'guests' => 2,
            'status' => 'available',
        ]);

        $response = $this->getJson('/api/v1/properties?city=Paris&max_price=150');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Paris', $data[0]['city']);
    }
}
