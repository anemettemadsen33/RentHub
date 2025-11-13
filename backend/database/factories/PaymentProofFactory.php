<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentProofFactory extends Factory
{
    protected $model = PaymentProof::class;

    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'file_path' => 'payment-proofs/proof_' . fake()->uuid() . '.pdf',
            'file_type' => fake()->randomElement(['pdf', 'jpg', 'png']),
            'file_size' => fake()->numberBetween(100000, 5000000),
            'status' => 'pending',
            'rejection_reason' => null,
            'verified_at' => null,
            'verified_by' => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => User::factory(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejection_reason' => fake()->sentence(),
            'verified_by' => User::factory(),
        ]);
    }
}
