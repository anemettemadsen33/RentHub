<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $owner;

    protected Property $property;

    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->property = Property::factory()->create([
            'owner_id' => $this->owner->id,
            'price' => 100,
        ]);
        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $this->property->id,
            'total_price' => 300,
            'status' => 'confirmed',
        ]);
    }

    public function test_user_can_create_payment()
    {
        $paymentData = [
            'booking_id' => $this->booking->id,
            'amount' => 300,
            'payment_method' => 'bank_transfer',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/payments', $paymentData);

        $response->assertCreated()
            ->assertJsonStructure(['id', 'booking_id', 'amount', 'status']);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'amount' => 300,
        ]);
    }

    public function test_user_can_view_their_payments()
    {
        Payment::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/payments');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_view_single_payment()
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/payments/{$payment->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $payment->id]);
    }

    public function test_payment_status_changes_to_completed()
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/payments/{$payment->id}/confirm");

        $response->assertOk();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed',
        ]);
    }

    public function test_user_cannot_view_other_user_payment()
    {
        $otherUser = User::factory()->create();
        $payment = Payment::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/payments/{$payment->id}");

        $response->assertStatus(403);
    }

    public function test_payment_amount_must_match_booking_total()
    {
        $paymentData = [
            'booking_id' => $this->booking->id,
            'amount' => 100, // Less than booking total of 300
            'payment_method' => 'bank_transfer',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/payments', $paymentData);

        $response->assertStatus(422);
    }

    public function test_owner_receives_payout_after_payment()
    {
        $payment = Payment::factory()->create([
            'booking_id' => $this->booking->id,
            'user_id' => $this->user->id,
            'amount' => 300,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson('/api/v1/payouts');

        $response->assertOk();
    }

    public function test_refund_can_be_processed()
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'booking_id' => $this->booking->id,
            'amount' => 300,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/payments/{$payment->id}/refund", [
                'reason' => 'Cancellation',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'refunded',
        ]);
    }

    public function test_payment_methods_validation()
    {
        $paymentData = [
            'booking_id' => $this->booking->id,
            'amount' => 300,
            'payment_method' => 'invalid_method',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/payments', $paymentData);

        $response->assertStatus(422);
    }

    public function test_cannot_create_duplicate_payment_for_booking()
    {
        Payment::factory()->create([
            'booking_id' => $this->booking->id,
            'user_id' => $this->user->id,
            'status' => 'completed',
        ]);

        $paymentData = [
            'booking_id' => $this->booking->id,
            'amount' => 300,
            'payment_method' => 'bank_transfer',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/payments', $paymentData);

        $response->assertStatus(422);
    }
}
