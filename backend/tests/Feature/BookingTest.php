<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $owner;
    protected Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->property = Property::factory()->create([
            'owner_id' => $this->owner->id,
            'price' => 100,
            'status' => 'published',
        ]);
    }

    public function test_user_can_create_booking()
    {
        $bookingData = [
            'property_id' => $this->property->id,
            'check_in' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'check_out' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'guests' => 2,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/bookings', $bookingData);

        $response->assertCreated()
            ->assertJsonStructure(['id', 'property_id', 'user_id', 'total_price']);

        $this->assertDatabaseHas('bookings', [
            'property_id' => $this->property->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_view_their_bookings()
    {
        Booking::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/bookings');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_view_single_booking()
    {
        $booking = Booking::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/bookings/{$booking->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $booking->id]);
    }

    public function test_user_can_cancel_booking()
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/cancel");

        $response->assertOk();

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_cannot_book_overlapping_dates()
    {
        $checkIn = Carbon::now()->addDays(7);
        $checkOut = Carbon::now()->addDays(10);

        Booking::factory()->create([
            'property_id' => $this->property->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'status' => 'confirmed',
        ]);

        $bookingData = [
            'property_id' => $this->property->id,
            'check_in' => $checkIn->format('Y-m-d'),
            'check_out' => $checkOut->format('Y-m-d'),
            'guests' => 2,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/bookings', $bookingData);

        $response->assertStatus(422);
    }

    public function test_total_price_calculation()
    {
        $bookingData = [
            'property_id' => $this->property->id,
            'check_in' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'check_out' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'guests' => 2,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/bookings', $bookingData);

        $response->assertCreated();

        // 3 nights * $100 = $300
        $this->assertEquals(300, $response->json('total_price'));
    }

    public function test_user_cannot_view_other_user_booking()
    {
        $otherUser = User::factory()->create();
        $booking = Booking::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/bookings/{$booking->id}");

        $response->assertStatus(403);
    }

    public function test_owner_can_view_property_bookings()
    {
        Booking::factory()->count(3)->create([
            'property_id' => $this->property->id,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/v1/properties/{$this->property->id}/bookings");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_owner_can_confirm_booking()
    {
        $booking = Booking::factory()->create([
            'property_id' => $this->property->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/confirm");

        $response->assertOk();

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_cannot_book_past_dates()
    {
        $bookingData = [
            'property_id' => $this->property->id,
            'check_in' => Carbon::now()->subDays(7)->format('Y-m-d'),
            'check_out' => Carbon::now()->subDays(4)->format('Y-m-d'),
            'guests' => 2,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/bookings', $bookingData);

        $response->assertStatus(422);
    }

    public function test_check_out_must_be_after_check_in()
    {
        $bookingData = [
            'property_id' => $this->property->id,
            'check_in' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'check_out' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'guests' => 2,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/bookings', $bookingData);

        $response->assertStatus(422);
    }
}
