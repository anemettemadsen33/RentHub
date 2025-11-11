<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\TestHelper;

class PaymentApiTest extends TestCase
{
    use RefreshDatabase, TestHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    /** @test */
    public function guest_can_initiate_payment_for_booking()
    {
        $guest = $this->authenticateGuest();
        $property = Property::factory()->create(['status' => 'available', 'price' => 100]);

        $booking = Booking::factory()->create([
            'user_id' => $guest->id,
            'property_id' => $property->id,
            'total_price' => 300,
            'status' => 'confirmed',
        ]);

        $response = $this->postJson('/api/v1/payments', [
            'booking_id' => $booking->id,
            'payment_method' => 'bank_transfer',
            'amount' => 300,
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'id',
                'booking_id',
                'amount',
                'status',
            ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'amount' => 300,
        ]);
    }

    /** @test */
    public function payment_updates_booking_status_on_success()
    {
        $guest = $this->authenticateGuest();
        $booking = Booking::factory()->create([
            'user_id' => $guest->id,
            'status' => 'confirmed',
            'total_price' => 200,
        ]);

        $payment = Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => 200,
            'status' => 'pending',
        ]);

        // Simulate gateway completion
        $payment->markAsCompleted();
        $booking->refresh();

        $this->assertEquals('paid', $booking->payment_status);
    }

    /** @test */
    public function it_can_process_refund()
    {
        $guest = $this->authenticateGuest();
        $booking = Booking::factory()->create([
            'user_id' => $guest->id,
            'status' => 'cancelled',
        ]);

        $payment = Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => 300,
            'status' => 'completed',
            'user_id' => $guest->id,
        ]);

        $response = $this->postJson("/api/v1/payments/{$payment->id}/refund", [
            'reason' => 'Customer request',
        ]);

        $response->assertSuccessful()
            ->assertJsonFragment(['status' => 'refunded']);
    }

    /** @test */
    public function it_validates_payment_amount()
    {
        $guest = $this->authenticateGuest();
        $booking = Booking::factory()->create([
            'user_id' => $guest->id,
            'total_price' => 300,
            'status' => 'confirmed',
        ]);

        $response = $this->postJson('/api/v1/payments', [
            'booking_id' => $booking->id,
            'payment_method' => 'bank_transfer',
            'amount' => 100, // Wrong amount
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function guest_can_view_payment_history()
    {
        $guest = $this->authenticateGuest();
        $booking = Booking::factory()->create(['user_id' => $guest->id]);

        Payment::factory()->count(3)->create([
            'booking_id' => $booking->id,
            'user_id' => $guest->id,
        ]);

        $response = $this->getJson('/api/v1/payments');

        $response->assertSuccessful()
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_prevents_duplicate_payments()
    {
        $guest = $this->authenticateGuest();
        $booking = Booking::factory()->create([
            'user_id' => $guest->id,
            'total_price' => 300,
            'status' => 'confirmed',
        ]);

        // First payment
        Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => 300,
            'status' => 'completed',
        ]);

        // Try second payment
        $response = $this->postJson('/api/v1/payments', [
            'booking_id' => $booking->id,
            'payment_method' => 'bank_transfer',
            'amount' => 300,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['booking_id']]);
    }
}
