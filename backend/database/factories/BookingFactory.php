<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = Carbon::now()->addDays(fake()->numberBetween(7, 30));
        $nights = fake()->numberBetween(2, 7);
        $checkOut = $checkIn->copy()->addDays($nights);
        $pricePerNight = fake()->randomFloat(2, 50, 300);
        $subtotal = $pricePerNight * $nights;
        $cleaningFee = fake()->randomFloat(2, 20, 100);
        $securityDeposit = fake()->randomFloat(2, 100, 500);
        $taxes = $subtotal * 0.1; // 10% tax
        $totalAmount = $subtotal + $cleaningFee + $securityDeposit + $taxes;

        return [
            'property_id' => Property::factory(),
            'user_id' => User::factory(),
            'check_in' => $checkIn->format('Y-m-d'),
            'check_out' => $checkOut->format('Y-m-d'),
            'guests' => fake()->numberBetween(1, 4),
            'nights' => $nights,
            'price_per_night' => $pricePerNight,
            'subtotal' => $subtotal,
            'cleaning_fee' => $cleaningFee,
            'security_deposit' => $securityDeposit,
            'taxes' => $taxes,
            'total_amount' => $totalAmount,
            'total_price' => $totalAmount,  // Ensure total_price alias is also set
            'status' => 'confirmed',
            'guest_name' => fake()->name(),
            'guest_email' => fake()->safeEmail(),
            'guest_phone' => fake()->phoneNumber(),
            'special_requests' => null,
            'payment_status' => 'paid',
            'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'bank_transfer']),
            'payment_transaction_id' => fake()->uuid(),
            'paid_at' => now(),
            'confirmed_at' => now(),
            'cancelled_at' => null,
        ];
    }

    /**
     * Indicate that the booking is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'pending',
            'paid_at' => null,
            'confirmed_at' => null,
        ]);
    }

    /**
     * Indicate that the booking is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Indicate that the booking is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
}
