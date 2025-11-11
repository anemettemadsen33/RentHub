<?php

namespace Tests\Feature\Api;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PropertyImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function unauthenticated_users_cannot_import_properties(): void
    {
        $response = $this->postJson('/api/v1/properties/import', [
            'platform' => 'booking',
            'url' => 'https://www.booking.com/hotel/ro/test-property.html',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_validates_required_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/properties/import', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['platform', 'url']);
    }

    /** @test */
    public function it_validates_platform_is_supported(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/properties/import', [
            'platform' => 'unsupported-platform',
            'url' => 'https://example.com/property',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['platform']);
    }

    /** @test */
    public function it_validates_url_format(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/properties/import', [
            'platform' => 'booking',
            'url' => 'not-a-valid-url',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    /** @test */
    public function it_can_import_from_booking_com(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/properties/import', [
            'platform' => 'booking',
            'url' => 'https://www.booking.com/hotel/ro/beautiful-apartment.html',
        ]);

        // Debug: See what error we get
        if ($response->status() !== 200) {
            dump($response->json());
        }

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Property imported successfully',
            ])
            ->assertJsonStructure([
                'success',
                'property_id',
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'type',
                    'price_per_night',
                    'city',
                    'country',
                ],
            ]);

        $this->assertDatabaseHas('properties', [
            'user_id' => $this->user->id,
            'imported_from' => 'booking',
            'status' => 'maintenance',
            'is_active' => false,
        ]);
    }

    /** @test */
    public function it_can_import_from_airbnb(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/properties/import', [
            'platform' => 'airbnb',
            'url' => 'https://www.airbnb.com/rooms/12345678',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('properties', [
            'user_id' => $this->user->id,
            'imported_from' => 'airbnb',
        ]);
    }

    /** @test */
    public function it_can_import_from_vrbo(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/properties/import', [
            'platform' => 'vrbo',
            'url' => 'https://www.vrbo.com/12345678ha',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('properties', [
            'user_id' => $this->user->id,
            'imported_from' => 'vrbo',
        ]);
    }

    /** @test */
    public function it_can_validate_url_before_import(): void
    {
        Sanctum::actingAs($this->user);

        // Valid Booking.com URL
        $response = $this->postJson('/api/v1/properties/import/validate', [
            'platform' => 'booking',
            'url' => 'https://www.booking.com/hotel/ro/test.html',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'valid' => true,
            ]);

        // Invalid Booking.com URL
        $response = $this->postJson('/api/v1/properties/import/validate', [
            'platform' => 'booking',
            'url' => 'https://www.airbnb.com/rooms/12345',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'valid' => false,
            ]);
    }

    /** @test */
    public function it_can_get_import_statistics(): void
    {
        Sanctum::actingAs($this->user);

        // Create some imported properties
        Property::factory()->create([
            'user_id' => $this->user->id,
            'imported_from' => 'booking',
        ]);

        Property::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'imported_from' => 'airbnb',
        ]);

        Property::factory()->create([
            'user_id' => $this->user->id,
            'imported_from' => 'vrbo',
        ]);

        $response = $this->getJson('/api/v1/properties/import/stats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_imported' => 4,
                    'by_platform' => [
                        'booking' => 1,
                        'airbnb' => 2,
                        'vrbo' => 1,
                    ],
                ],
            ]);
    }

    /** @test */
    public function imported_properties_start_as_maintenance(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/properties/import', [
            'platform' => 'booking',
            'url' => 'https://www.booking.com/hotel/ro/test.html',
        ]);

        $property = Property::where('user_id', $this->user->id)->first();

        $this->assertEquals('maintenance', $property->status);
        $this->assertFalse($property->is_active);
        $this->assertEquals('booking', $property->imported_from);
        $this->assertNotNull($property->external_id);
    }

    /** @test */
    public function it_rejects_invalid_platform_urls(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/properties/import', [
            'platform' => 'booking',
            'url' => 'https://www.airbnb.com/rooms/12345', // Wrong platform URL
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }
}
