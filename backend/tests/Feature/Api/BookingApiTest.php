<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\TestHelper;

class BookingApiTest extends TestCase
{
    use RefreshDatabase, TestHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    #[Test]
    public function guest_can_create_booking()
    {
        $guest = $this->authenticateGuest();
        $property = Property::factory()->create([
            'status' => 'available',
            'price' => 100,
            'guests' => 4,
        ]);

        $bookingData = [
            'property_id' => $property->id,
            'check_in' => now()->addDays(7)->format('Y-m-d'),
            'check_out' => now()->addDays(10)->format('Y-m-d'),
            'guests' => 2,
        ];

        $response = $this->postJson('/api/v1/bookings', $bookingData);

        $response->assertCreated()
            ->assertJsonStructure([
                'id',
                'property_id',
                'user_id',
                'status',
                'total_price',
            ]);

        $this->assertDatabaseHas('bookings', [
            'property_id' => $property->id,
            'user_id' => $guest->id,
            'status' => 'pending',
        ]);
    }

    #[Test]
    public function it_calculates_correct_total_price()
    {
        $this->authenticateGuest();
        $property = Property::factory()->create([
            'status' => 'available',
            'price' => 100,
            'guests' => 4,
        ]);

        $checkIn = now()->addDays(7);
        $checkOut = now()->addDays(10);

        $bookingData = [
            'property_id' => $property->id,
            'check_in' => $checkIn->format('Y-m-d'),
            'check_out' => $checkOut->format('Y-m-d'),
            'guests' => 2,
        ];

        $response = $this->postJson('/api/v1/bookings', $bookingData);

        $nights = $checkIn->diffInDays($checkOut);
        $expectedTotal = $nights * $property->price;

        $response->assertCreated()
            ->assertJsonPath('total_price', number_format($expectedTotal, 2, '.', ''));
    }

    #[Test]
    public function it_prevents_overlapping_bookings()
    {
        $this->authenticateGuest();
        $property = Property::factory()->create(['status' => 'available']);

        Booking::factory()->create([
            'property_id' => $property->id,
            'check_in' => now()->addDays(5),
            'check_out' => now()->addDays(10),
            'status' => 'confirmed',
        ]);

        $bookingData = [
            'property_id' => $property->id,
            'check_in' => now()->addDays(7)->format('Y-m-d'),
            'check_out' => now()->addDays(12)->format('Y-m-d'),
            'guests' => 2,
        ];

        $response = $this->postJson('/api/v1/bookings', $bookingData);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Property is not available for the selected dates',
            ]);
    }

    #[Test]
    public function host_can_confirm_booking()
    {
        $host = $this->authenticateHost();
        $property = Property::factory()->create(['user_id' => $host->id]);
        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/v1/bookings/{$booking->id}/confirm");

        $response->assertSuccessful()
            ->assertJsonFragment(['status' => 'confirmed']);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
        ]);
    }

    #[Test]
    public function guest_can_cancel_own_booking()
    {
        $guest = $this->authenticateGuest();
        $booking = Booking::factory()->create([
            'user_id' => $guest->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/v1/bookings/{$booking->id}/cancel");

        $response->assertSuccessful()
            ->assertJsonFragment(['status' => 'cancelled']);
    }

    #[Test]
    public function it_validates_check_in_before_check_out()
    {
        $this->authenticateGuest();
        $property = Property::factory()->create(['status' => 'available', 'guests' => 4]);

        $bookingData = [
            'property_id' => $property->id,
            'check_in' => now()->addDays(10)->format('Y-m-d'),
            'check_out' => now()->addDays(5)->format('Y-m-d'),
            'guests' => 2,
        ];

        $response = $this->postJson('/api/v1/bookings', $bookingData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('check_out');
    }

    #[Test]
    public function it_validates_guests_not_exceeding_max_capacity()
    {
        $this->authenticateGuest();
        $property = Property::factory()->create([
            'status' => 'available',
            'guests' => 4,
        ]);

        $bookingData = [
            'property_id' => $property->id,
            'check_in' => now()->addDays(7)->format('Y-m-d'),
            'check_out' => now()->addDays(10)->format('Y-m-d'),
            'guests' => 5,
        ];

        $response = $this->postJson('/api/v1/bookings', $bookingData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('guests');
    }

    #[Test]
    public function guest_can_view_own_bookings()
    {
        $guest = $this->authenticateGuest();
        
        Booking::factory()->count(3)->create(['user_id' => $guest->id]);
        Booking::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/bookings');

        $response->assertSuccessful()
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function host_can_view_property_bookings()
    {
        $host = $this->authenticateHost();
        $property = Property::factory()->create(['user_id' => $host->id]);
        
        Booking::factory()->count(5)->create(['property_id' => $property->id]);
        Booking::factory()->count(3)->create();

        $response = $this->getJson("/api/v1/properties/{$property->id}/bookings");

        $response->assertSuccessful()
            ->assertJsonCount(5, 'data');
    }
}
