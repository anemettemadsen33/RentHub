<?php

namespace Database\Factories;

use App\Models\BlockedDate;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlockedDate>
 */
class BlockedDateFactory extends Factory
{
    protected $model = BlockedDate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = Carbon::now()->addDays(fake()->numberBetween(10, 30));
        $endDate = $startDate->copy()->addDays(fake()->numberBetween(1, 7));

        return [
            'property_id' => Property::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'reason' => fake()->randomElement(['Maintenance', 'Personal Use', 'Renovation', 'Inspection']),
            'google_event_id' => null,
        ];
    }

    /**
     * Indicate that the blocked date is for maintenance.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'reason' => 'Maintenance',
        ]);
    }

    /**
     * Indicate that the blocked date is linked to Google Calendar.
     */
    public function withGoogleEvent(): static
    {
        return $this->state(fn (array $attributes) => [
            'google_event_id' => fake()->uuid(),
        ]);
    }
}
