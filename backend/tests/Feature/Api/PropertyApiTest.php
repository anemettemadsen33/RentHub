<?php

namespace Tests\Feature\Api;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\TestHelper;

class PropertyApiTest extends TestCase
{
    use RefreshDatabase, TestHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    /** @test */
    public function it_can_list_properties()
    {
        Property::factory()->count(5)->create(['status' => 'available']);

        $response = $this->getJson('/api/v1/properties');

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'price',
                        'status',
                    ]
                ],
            ])
            ->assertJson(['success' => true])
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function it_can_show_property_details()
    {
        $property = Property::factory()->create(['status' => 'active']);

        $response = $this->getJson("/api/v1/properties/{$property->id}");

        $response->assertSuccessful()
            ->assertJson([
                'data' => [
                    'id' => $property->id,
                    'title' => $property->title,
                ]
            ]);
    }

    /** @test */
    public function it_can_create_property_as_host()
    {
    $host = $this->authenticateUser('owner');

        $propertyData = [
            'title' => 'Test Property',
            'description' => 'A beautiful test property with amazing views and modern amenities. Perfect for families.',
            'type' => 'apartment',
            'price' => 150.00,
            'bedrooms' => 2,
            'bathrooms' => 1,
            'max_guests' => 4,
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TX',
            'postal_code' => '12345',
            'country' => 'US',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ];

        $response = $this->postJson('/api/v1/properties', $propertyData);

        $response->assertCreated()
            ->assertJsonFragment([
                'title' => 'Test Property',
                'price' => '150.00',
            ]);

        $this->assertDatabaseHas('properties', [
            'title' => 'Test Property',
            'user_id' => $host->id,
        ]);
    }

    /** @test */
    public function it_cannot_create_property_as_guest()
    {
        $this->authenticateGuest();

        $propertyData = [
            'title' => 'Test Property',
            'description' => 'A beautiful test property',
            'price' => 150.00,
        ];

        $response = $this->postJson('/api/v1/properties', $propertyData);

        $response->assertForbidden();
    }

    /** @test */
    public function it_can_update_own_property()
    {
    $host = $this->authenticateUser('owner');
        $property = Property::factory()->create(['user_id' => $host->id]);

        $updateData = [
            'title' => 'Updated Property Title',
            // Use a numeric value; response will cast to string decimal
            'price' => 200,
        ];

        $response = $this->putJson("/api/v1/properties/{$property->id}", $updateData);

        $response->assertSuccessful()
            ->assertJsonFragment([
                'title' => 'Updated Property Title',
                // Decimal is returned as string from DB casting
                'price' => '200.00',
            ]);
    }

    /** @test */
    public function it_cannot_update_other_host_property()
    {
    $this->authenticateUser('owner');
        $otherHost = User::factory()->create();
    $otherHost->assignRole('owner');
        $property = Property::factory()->create(['user_id' => $otherHost->id]);

        $updateData = ['title' => 'Hacked Title'];

        $response = $this->putJson("/api/v1/properties/{$property->id}", $updateData);

        $response->assertForbidden();
    }

    /** @test */
    public function it_can_delete_own_property()
    {
    $host = $this->authenticateUser('owner');
        $property = Property::factory()->create(['user_id' => $host->id]);

        $response = $this->deleteJson("/api/v1/properties/{$property->id}");
        // Controller returns 200 with JSON (not 204 no content)
        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Property deleted successfully',
            ]);
        // Property is hard deleted (no SoftDeletes on model) so ensure row is gone
        $this->assertDatabaseMissing('properties', ['id' => $property->id]);
    }

    /** @test */
    public function it_can_search_properties_by_location()
    {
        Property::factory()->create([
            'city' => 'New York',
            'status' => 'active',
        ]);
        
        Property::factory()->create([
            'city' => 'Los Angeles',
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/v1/properties?city=New York');

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['city' => 'New York']);
    }

    /** @test */
    public function it_can_filter_properties_by_price_range()
    {
        Property::factory()->create(['price' => 100, 'status' => 'active']);
        Property::factory()->create(['price' => 200, 'status' => 'active']);
        Property::factory()->create(['price' => 300, 'status' => 'active']);

        $response = $this->getJson('/api/v1/properties?min_price=150&max_price=250');

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['price' => '200.00']);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_property()
    {
    $this->authenticateUser('owner');

        $response = $this->postJson('/api/v1/properties', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
                'type',
                'bedrooms',
                'bathrooms',
                'guests',
                'street_address', // mapped from legacy 'address'
                'city',
                'country',
                'postal_code',
                'price_per_night', // price fields
            ]);
    }
}
