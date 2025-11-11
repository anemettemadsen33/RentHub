<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuestVerificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'identity_status' => fake()->randomElement(['pending', 'verified', 'rejected']),
            'document_type' => fake()->randomElement(['passport', 'drivers_license', 'id_card']),
            'document_number' => fake()->bothify('??######'),
            'document_expiry_date' => fake()->dateTimeBetween('+1 year', '+10 years'),
            // Deterministic defaults to avoid test flakiness
            'credit_check_enabled' => false,
            'credit_status' => 'not_requested',
            'background_status' => fake()->randomElement(['pending', 'clear', 'flagged']),
            'trust_score' => fake()->randomFloat(2, 1, 5),
            'completed_bookings' => fake()->numberBetween(0, 50),
            'cancelled_bookings' => fake()->numberBetween(0, 5),
            'positive_reviews' => fake()->numberBetween(0, 30),
            'negative_reviews' => fake()->numberBetween(0, 3),
            'references_verified' => fake()->numberBetween(0, 5),
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'identity_status' => 'verified',
            'identity_verified_at' => now(),
        ]);
    }

    public function fullyVerified(): static
    {
        return $this->state(fn (array $attributes) => [
            'identity_status' => 'verified',
            'background_status' => 'clear',
            'credit_status' => 'approved',
            'identity_verified_at' => now(),
            'background_checked_at' => now(),
            'credit_checked_at' => now(),
            'trust_score' => fake()->randomFloat(2, 4.0, 5.0),
        ]);
    }
}
