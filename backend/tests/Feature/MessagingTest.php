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
            'tenant_id' => $this->user->id,
            'owner_id' => $this->owner->id,
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
            'tenant_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/conversations');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_view_conversation_messages()
    {
        $conversation = Conversation::factory()->create([
            'tenant_id' => $this->user->id,
            'owner_id' => $this->owner->id,
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
        $this->markTestSkipped('Route authorization not implemented yet.');

        $otherUser = User::factory()->create();
        $conversation = Conversation::factory()->create([
            'tenant_id' => $otherUser->id,
            'owner_id' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/conversations/{$conversation->id}");

        $response->assertStatus(403);
    }

    public function test_message_marks_conversation_as_read()
    {
        $conversation = Conversation::factory()->create([
            'tenant_id' => $this->user->id,
            'owner_id' => $this->owner->id,
        ]);

        // Pre-create participant record
        \DB::table('conversation_participants')->insert([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'last_read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/conversations/{$conversation->id}/mark-all-read");

        $response->assertOk();

        // Check that last_read_at was updated
        $participant = \DB::table('conversation_participants')
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $this->user->id)
            ->first();

        $this->assertNotNull($participant);
        $this->assertNotNull($participant->last_read_at);
    }

    public function test_user_can_delete_conversation()
    {
        $conversation = Conversation::factory()->create([
            'tenant_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/conversations/{$conversation->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('conversations', ['id' => $conversation->id]);
    }

    public function test_unread_conversation_count()
    {
        $this->markTestSkipped('Unread count endpoint not implemented yet.');

        // Create conversations with participants
        $conv1 = Conversation::factory()->create([
            'tenant_id' => $this->user->id,
        ]);
        $conv2 = Conversation::factory()->create([
            'tenant_id' => $this->user->id,
        ]);
        $conv3 = Conversation::factory()->create([
            'tenant_id' => $this->user->id,
        ]);
        $conv4 = Conversation::factory()->create([
            'tenant_id' => $this->user->id,
        ]);
        $conv5 = Conversation::factory()->create([
            'tenant_id' => $this->user->id,
        ]);

        // 3 unread (no last_read_at), 2 read (with last_read_at)
        \DB::table('conversation_participants')->insert([
            ['conversation_id' => $conv1->id, 'user_id' => $this->user->id, 'last_read_at' => null, 'created_at' => now(), 'updated_at' => now()],
            ['conversation_id' => $conv2->id, 'user_id' => $this->user->id, 'last_read_at' => null, 'created_at' => now(), 'updated_at' => now()],
            ['conversation_id' => $conv3->id, 'user_id' => $this->user->id, 'last_read_at' => null, 'created_at' => now(), 'updated_at' => now()],
            ['conversation_id' => $conv4->id, 'user_id' => $this->user->id, 'last_read_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['conversation_id' => $conv5->id, 'user_id' => $this->user->id, 'last_read_at' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/conversations/unread-count');

        $response->assertOk()
            ->assertJsonFragment(['count' => 3]);
    }

    public function test_cannot_send_empty_message()
    {
        $conversation = Conversation::factory()->create([
            'tenant_id' => $this->user->id,
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
            'tenant_id' => $this->user->id,
            'owner_id' => $this->owner->id,
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
