<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $booking = Booking::factory()->create();
        return [
            'payment_number' => Payment::generatePaymentNumber(),
            'booking_id' => $booking->id,
            'invoice_id' => null,
            'user_id' => $booking->user_id ?? User::factory(),
            'amount' => $booking->total_price ?? $this->faker->randomFloat(2, 50, 500),
            'currency' => 'EUR',
            'type' => 'full',
            'status' => 'pending',
            'payment_method' => 'bank_transfer',
            'initiated_at' => now(),
        ];
    }

    public function completed(): self
    {
        return $this->state(fn() => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function refunded(): self
    {
        return $this->state(fn() => [
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);
    }
}
