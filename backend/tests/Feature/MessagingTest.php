<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $owner;
    protected Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->property = Property::factory()->create([
            'owner_id' => $this->owner->id,
        ]);
    }

    public function test_user_can_start_conversation_with_owner()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/conversations', [
                'property_id' => $this->property->id,
                'recipient_id' => $this->owner->id,
                'message' => 'Hello, I am interested in your property',
            ]);

        $response->assertCreated()
            ->assertJsonStructure(['id', 'messages']);

        $this->assertDatabaseHas('conversations', [
            'property_id' => $this->property->id,
        ]);
    }

    public function test_user_can_send_message()
    {
        $conversation = Conversation::factory()->create([
            'property_id' => $this->property->id,
            'sender_id' => $this->user->id,
            'receiver_id' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/conversations/{$conversation->id}/messages", [
                'message' => 'Is the property available next week?',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $this->user->id,
            'message' => 'Is the property available next week?',
        ]);
    }

    public function test_user_can_view_their_conversations()
    {
        Conversation::factory()->count(3)->create([
            'sender_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/conversations');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_view_conversation_messages()
    {
        $conversation = Conversation::factory()->create([
            'sender_id' => $this->user->id,
            'receiver_id' => $this->owner->id,
        ]);

        Message::factory()->count(5)->create([
            'conversation_id' => $conversation->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/conversations/{$conversation->id}/messages");

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_user_cannot_view_other_user_conversation()
    {
        $otherUser = User::factory()->create();
        $conversation = Conversation::factory()->create([
            'sender_id' => $otherUser->id,
            'receiver_id' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/conversations/{$conversation->id}");

        $response->assertStatus(403);
    }

    public function test_message_marks_conversation_as_read()
    {
        $conversation = Conversation::factory()->create([
            'sender_id' => $this->owner->id,
            'receiver_id' => $this->user->id,
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/conversations/{$conversation->id}/read");

        $response->assertOk();

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'is_read' => true,
        ]);
    }

    public function test_user_can_delete_conversation()
    {
        $conversation = Conversation::factory()->create([
            'sender_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/conversations/{$conversation->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('conversations', ['id' => $conversation->id]);
    }

    public function test_unread_conversation_count()
    {
        Conversation::factory()->count(3)->create([
            'receiver_id' => $this->user->id,
            'is_read' => false,
        ]);

        Conversation::factory()->count(2)->create([
            'receiver_id' => $this->user->id,
            'is_read' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/conversations/unread-count');

        $response->assertOk()
            ->assertJsonFragment(['count' => 3]);
    }

    public function test_cannot_send_empty_message()
    {
        $conversation = Conversation::factory()->create([
            'sender_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/conversations/{$conversation->id}/messages", [
                'message' => '',
            ]);

        $response->assertStatus(422);
    }

    public function test_message_sender_and_receiver_are_set_correctly()
    {
        $conversation = Conversation::factory()->create([
            'sender_id' => $this->user->id,
            'receiver_id' => $this->owner->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/conversations/{$conversation->id}/messages", [
                'message' => 'Test message',
            ]);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $this->user->id,
            'message' => 'Test message',
        ]);
    }
}
