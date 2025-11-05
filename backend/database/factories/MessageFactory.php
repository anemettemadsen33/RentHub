<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender_id' => User::factory(),
            'message' => fake()->paragraph(),
            'attachments' => null,
            'read_at' => null,
            'is_system_message' => false,
        ];
    }

    /**
     * Indicate that the message has been read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => now(),
        ]);
    }

    /**
     * Indicate that the message is a system message.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system_message' => true,
            'message' => fake()->randomElement([
                'Booking confirmed',
                'Payment received',
                'Check-in reminder',
                'Check-out completed',
            ]),
        ]);
    }

    /**
     * Indicate that the message has attachments.
     */
    public function withAttachments(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachments' => [
                'https://example.com/file1.pdf',
                'https://example.com/image1.jpg',
            ],
        ]);
    }
}
