<?php

namespace Database\Factories;

use App\Models\AccessCode;
use App\Models\Booking;
use App\Models\SmartLock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccessCode>
 */
class AccessCodeFactory extends Factory
{
    protected $model = AccessCode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $validFrom = Carbon::now();
        $validUntil = $validFrom->copy()->addDays(fake()->numberBetween(1, 7));

        return [
            'smart_lock_id' => SmartLock::factory(),
            'booking_id' => Booking::factory(),
            'user_id' => User::factory(),
            'code' => str_pad((string) fake()->numberBetween(100000, 999999), 6, '0', STR_PAD_LEFT),
            'external_code_id' => fake()->uuid(),
            'type' => fake()->randomElement(['temporary', 'permanent', 'one_time']),
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'status' => 'active',
            'max_uses' => null,
            'uses_count' => 0,
            'notified' => false,
            'notified_at' => null,
            'notes' => null,
        ];
    }

    /**
     * Indicate that the access code is for a one-time use.
     */
    public function oneTime(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'one_time',
            'max_uses' => 1,
        ]);
    }

    /**
     * Indicate that the access code is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'valid_until' => Carbon::now()->subDays(1),
        ]);
    }

    /**
     * Indicate that the access code has been used.
     */
    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'uses_count' => 1,
            'status' => 'used',
        ]);
    }

    /**
     * Indicate that the user has been notified of the code.
     */
    public function notified(): static
    {
        return $this->state(fn (array $attributes) => [
            'notified' => true,
            'notified_at' => now(),
        ]);
    }
}
