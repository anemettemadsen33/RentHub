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

    /**
     * Test cannot book dates that overlap with existing booking
     */
    public function test_cannot_create_booking_with_overlapping_dates(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['status' => 'available']);
        
        // Create existing booking
        Booking::factory()->create([
            'property_id' => $property->id,
            'check_in' => now()->addDays(5),
            'check_out' => now()->addDays(10),
            'status' => 'confirmed',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        // Try to book overlapping dates
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/bookings', [
            'property_id' => $property->id,
            'check_in' => now()->addDays(7)->format('Y-m-d'),
            'check_out' => now()->addDays(12)->format('Y-m-d'),
            'guests' => 2,
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'The selected dates are not available.']);
    }

    /**
     * Test total price calculation
     */
    public function test_total_price_calculated_correctly(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create([
            'status' => 'available',
            'price_per_night' => 100,
            'cleaning_fee' => 50,
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/bookings', [
            'property_id' => $property->id,
            'check_in' => now()->addDays(7)->format('Y-m-d'),
            'check_out' => now()->addDays(10)->format('Y-m-d'), // 3 nights
            'guests' => 2,
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
        ]);

        $response->assertStatus(201);
        
        // 3 nights * 100 + 50 cleaning fee = 350
        $this->assertEquals(350, $response->json('total_price'));
    }

    /**
     * Test host can approve pending booking
     */
    public function test_host_can_approve_pending_booking(): void
    {
        $host = User::factory()->create(['role' => 'host']);
        $property = Property::factory()->create(['user_id' => $host->id]);
        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'status' => 'pending',
        ]);

        $token = $host->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/v1/bookings/{$booking->id}/approve");

        $response->assertStatus(200);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
        ]);
    }

    /**
     * Test host can reject pending booking
     */
    public function test_host_can_reject_pending_booking(): void
    {
        $host = User::factory()->create(['role' => 'host']);
        $property = Property::factory()->create(['user_id' => $host->id]);
        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'status' => 'pending',
        ]);

        $token = $host->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/v1/bookings/{$booking->id}/reject", [
            'reason' => 'Property not available',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'rejected',
        ]);
    }

    /**
     * Test only host of property can approve bookings
     */
    public function test_only_property_host_can_approve_booking(): void
    {
        $host = User::factory()->create(['role' => 'host']);
        $otherHost = User::factory()->create(['role' => 'host']);
        $property = Property::factory()->create(['user_id' => $host->id]);
        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'status' => 'pending',
        ]);

        $token = $otherHost->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/v1/bookings/{$booking->id}/approve");

        $response->assertStatus(403);
    }

    /**
     * Test cannot cancel completed booking
     */
    public function test_cannot_cancel_completed_booking(): void
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/v1/bookings/{$booking->id}/cancel");

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Cannot cancel a completed booking.']);
    }

    /**
     * Test validates check-out after check-in
     */
    public function test_validates_checkout_after_checkin(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['status' => 'available']);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/bookings', [
            'property_id' => $property->id,
            'check_in' => now()->addDays(10)->format('Y-m-d'),
            'check_out' => now()->addDays(7)->format('Y-m-d'), // Before check-in
            'guests' => 2,
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['check_out']);
    }

    /**
     * Test validates guest count within property limits
     */
    public function test_validates_guest_count_within_limits(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create([
            'status' => 'available',
            'max_guests' => 4,
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/bookings', [
            'property_id' => $property->id,
            'check_in' => now()->addDays(7)->format('Y-m-d'),
            'check_out' => now()->addDays(10)->format('Y-m-d'),
            'guests' => 6, // Exceeds max_guests
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guests']);
    }

    /**
     * Test cannot book past dates
     */
    public function test_cannot_book_past_dates(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['status' => 'available']);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/bookings', [
            'property_id' => $property->id,
            'check_in' => now()->subDays(5)->format('Y-m-d'), // Past date
            'check_out' => now()->subDays(3)->format('Y-m-d'),
            'guests' => 2,
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['check_in']);
    }

    /**
     * Test can filter bookings by status
     */
    public function test_can_filter_bookings_by_status(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();
        
        Booking::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'status' => 'confirmed',
        ]);
        Booking::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'status' => 'pending',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/bookings?status=confirmed');

        $response->assertStatus(200);
        $bookings = $response->json('data');
        
        $this->assertCount(1, $bookings);
        $this->assertEquals('confirmed', $bookings[0]['status']);
    }

    /**
     * Test host can get bookings for their properties
     */
    public function test_host_can_get_property_bookings(): void
    {
        $host = User::factory()->create(['role' => 'host']);
        $property = Property::factory()->create(['user_id' => $host->id]);
        
        Booking::factory()->count(3)->create([
            'property_id' => $property->id,
        ]);

        $token = $host->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/v1/properties/{$property->id}/bookings");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}
