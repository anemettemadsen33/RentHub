<?php

namespace Database\Factories;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NotificationPreference>
 */
class NotificationPreferenceFactory extends Factory
{
    protected $model = NotificationPreference::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'notification_type' => fake()->randomElement([
                'booking',
                'payment',
                'review',
                'account',
                'system',
            ]),
            'channel_email' => true,
            'channel_database' => true,
            'channel_sms' => false,
            'channel_push' => false,
        ];
    }

    /**
     * Indicate all channels are enabled.
     */
    public function allChannels(): static
    {
        return $this->state(fn (array $attributes) => [
            'channel_email' => true,
            'channel_database' => true,
            'channel_sms' => true,
            'channel_push' => true,
        ]);
    }

    /**
     * Indicate only email channel is enabled.
     */
    public function emailOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'channel_email' => true,
            'channel_database' => false,
            'channel_sms' => false,
            'channel_push' => false,
        ]);
    }
}
