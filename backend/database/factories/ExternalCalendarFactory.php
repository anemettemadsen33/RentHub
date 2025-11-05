<?php

namespace Database\Factories;

use App\Models\ExternalCalendar;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExternalCalendar>
 */
class ExternalCalendarFactory extends Factory
{
    protected $model = ExternalCalendar::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platform = fake()->randomElement(['airbnb', 'booking_com', 'vrbo', 'ical', 'google']);
        
        return [
            'property_id' => Property::factory(),
            'platform' => $platform,
            'url' => fake()->url(),
            'name' => ucfirst($platform).' Calendar',
            'sync_enabled' => true,
            'last_synced_at' => now()->subHours(fake()->numberBetween(1, 24)),
            'sync_error' => null,
        ];
    }

    /**
     * Indicate that sync is disabled.
     */
    public function syncDisabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'sync_enabled' => false,
        ]);
    }

    /**
     * Indicate that the calendar has a sync error.
     */
    public function withError(): static
    {
        return $this->state(fn (array $attributes) => [
            'sync_error' => 'Failed to sync calendar: Connection timeout',
        ]);
    }
}
