<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a booking
     */
    public function test_authenticated_user_can_create_booking(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create([
            'status' => 'available',
            'price_per_night' => 100,
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/bookings', [
            'property_id' => $property->id,
            'check_in' => now()->addDays(7)->format('Y-m-d'),
            'check_out' => now()->addDays(10)->format('Y-m-d'),
            'guests' => 2,
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'property_id',
                'check_in',
                'check_out',
                'total_price',
            ]);
    }

    /**
     * Test fetching user bookings
     */
    public function test_user_can_fetch_their_bookings(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();
        
        Booking::factory()->count(3)->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/bookings');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test cancelling a booking
     */
    public function test_user_can_cancel_their_booking(): void
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'status' => 'confirmed',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/v1/bookings/{$booking->id}/cancel");

        $response->assertStatus(200);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }
}
