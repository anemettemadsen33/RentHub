<?php

namespace Database\Factories;

use App\Models\GuestVerification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GuestReferenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'guest_verification_id' => GuestVerification::factory(),
            // Ensure NOT NULL user_id is set (matches the verification user)
            'user_id' => User::factory(),
            'reference_name' => fake()->name(),
            'reference_email' => fake()->unique()->safeEmail(),
            'reference_phone' => fake()->phoneNumber(),
            'reference_type' => fake()->randomElement(['previous_landlord', 'employer', 'personal', 'other']),
            // Use enum-friendly relationship values
            'relationship' => fake()->randomElement(['previous_landlord','employer','colleague','friend','family','other']),
            'status' => 'pending',
            'verification_token' => Str::random(64),
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'verified',
            'rating' => fake()->numberBetween(3, 5),
            'comments' => fake()->sentence(),
            'verified_at' => now(),
        ]);
    }
}
