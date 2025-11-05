<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conversation>
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'booking_id' => Booking::factory(),
            'tenant_id' => User::factory(),
            'owner_id' => User::factory(),
            'subject' => fake()->sentence(),
            'last_message_at' => now(),
            'is_archived' => false,
        ];
    }

    /**
     * Indicate that the conversation is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_archived' => true,
        ]);
    }

    /**
     * Indicate that the conversation has no booking.
     */
    public function withoutBooking(): static
    {
        return $this->state(fn (array $attributes) => [
            'booking_id' => null,
        ]);
    }
}
