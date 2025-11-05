<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\SmartLock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SmartLock>
 */
class SmartLockFactory extends Factory
{
    protected $model = SmartLock::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $provider = fake()->randomElement(['august', 'yale', 'schlage', 'kwikset', 'smartthings']);
        
        return [
            'property_id' => Property::factory(),
            'provider' => $provider,
            'lock_id' => fake()->uuid(),
            'name' => fake()->words(2, true).' Lock',
            'location' => fake()->randomElement(['Front Door', 'Back Door', 'Side Door', 'Garage']),
            'credentials' => [
                'api_key' => fake()->uuid(),
                'access_token' => fake()->sha256(),
            ],
            'settings' => [
                'auto_lock' => true,
                'notification_enabled' => true,
            ],
            'status' => 'active',
            'auto_generate_codes' => true,
            'battery_level' => fake()->numberBetween(50, 100),
            'last_synced_at' => now()->subHours(fake()->numberBetween(1, 24)),
            'error_message' => null,
        ];
    }

    /**
     * Indicate that the smart lock has an error.
     */
    public function withError(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'error',
            'error_message' => 'Failed to connect to lock',
        ]);
    }

    /**
     * Indicate that the smart lock is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
