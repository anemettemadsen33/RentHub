<?php

namespace Tests\Feature\Api;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    protected User $guest;
    protected User $host;
    protected Property $property;
    protected Conversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->guest = User::factory()->create(['role' => 'guest']);
        $this->host = User::factory()->create(['role' => 'host']);
        
        $this->property = Property::factory()->create([
            'user_id' => $this->host->id,
        ]);
        
        $this->conversation = Conversation::factory()->create([
            'property_id' => $this->property->id,
            'tenant_id' => $this->guest->id,
            'owner_id' => $this->host->id,
        ]);
    }

    public function test_can_send_message_in_conversation(): void
    {
        $response = $this->actingAs($this->guest, 'sanctum')
            ->postJson("/api/v1/conversations/{$this->conversation->id}/messages", [
                'message' => 'Hello, I have a question about the property.',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Message sent successfully',
            ])
            ->assertJsonStructure([
                'data' => ['id', 'message', 'sender'],
            ]);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->guest->id,
            'message' => 'Hello, I have a question about the property.',
        ]);
    }

    public function test_can_send_message_with_attachments(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('document.jpg');

        $response = $this->actingAs($this->guest, 'sanctum')
            ->postJson("/api/v1/conversations/{$this->conversation->id}/messages", [
                'message' => 'Here is the document.',
                'attachments' => [$file],
            ]);

        $response->assertStatus(201);

        $message = Message::latest()->first();
        $this->assertNotNull($message->attachments);
        $this->assertIsArray($message->attachments);
        $this->assertCount(1, $message->attachments);
        
        Storage::disk('public')->assertExists($message->attachments[0]['path']);
    }

    public function test_validates_attachment_limits(): void
    {
        Storage::fake('public');

        $files = [
            UploadedFile::fake()->image('file1.jpg'),
            UploadedFile::fake()->image('file2.jpg'),
            UploadedFile::fake()->image('file3.jpg'),
            UploadedFile::fake()->image('file4.jpg'),
            UploadedFile::fake()->image('file5.jpg'),
            UploadedFile::fake()->image('file6.jpg'), // 6th file (max is 5)
        ];

        $response = $this->actingAs($this->guest, 'sanctum')
            ->postJson("/api/v1/conversations/{$this->conversation->id}/messages", [
                'message' => 'Too many files',
                'attachments' => $files,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['attachments']);
    }

    public function test_validates_message_max_length(): void
    {
        $longMessage = str_repeat('a', 5001); // Exceeds 5000 char limit

        $response = $this->actingAs($this->guest, 'sanctum')
            ->postJson("/api/v1/conversations/{$this->conversation->id}/messages", [
                'message' => $longMessage,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function test_cannot_send_message_to_conversation_not_participant(): void
    {
        $otherUser = User::factory()->create(['role' => 'guest']);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->postJson("/api/v1/conversations/{$this->conversation->id}/messages", [
                'message' => 'Trying to send',
            ]);

        $response->assertStatus(404);
    }

    public function test_can_retrieve_messages_from_conversation(): void
    {
        Message::factory()->count(5)->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->guest->id,
        ]);

        $response = $this->actingAs($this->guest, 'sanctum')
            ->getJson("/api/v1/conversations/{$this->conversation->id}/messages");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'message', 'sender', 'created_at'],
                ],
                'meta' => ['current_page', 'last_page', 'total'],
            ]);

        $this->assertCount(5, $response->json('data'));
    }

    public function test_can_mark_message_as_read(): void
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->host->id,
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->guest, 'sanctum')
            ->postJson("/api/v1/messages/{$message->id}/read");

        $response->assertStatus(200);

        $message->refresh();
        $this->assertNotNull($message->read_at);
    }

    public function test_cannot_mark_message_read_twice(): void
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->host->id,
        ]);

        $message->markAsRead();

        $response = $this->actingAs($this->guest, 'sanctum')
            ->postJson("/api/v1/messages/{$message->id}/read");

        $response->assertStatus(200);
        
        // Should still have same read_at timestamp
        $originalReadAt = $message->fresh()->read_at;
        $this->assertEquals($originalReadAt->format('Y-m-d H:i:s'), $message->fresh()->read_at->format('Y-m-d H:i:s'));
    }

    public function test_can_update_own_message(): void
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->guest->id,
            'message' => 'Original message',
        ]);

        $response = $this->actingAs($this->guest, 'sanctum')
            ->putJson("/api/v1/messages/{$message->id}", [
                'message' => 'Updated message',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'message' => 'Updated message',
        ]);
    }

    public function test_cannot_update_other_user_message(): void
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->host->id,
        ]);

        $response = $this->actingAs($this->guest, 'sanctum')
            ->putJson("/api/v1/messages/{$message->id}", [
                'message' => 'Trying to edit',
            ]);

        $response->assertStatus(403);
    }

    public function test_can_soft_delete_own_message(): void
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->guest->id,
        ]);

        $response = $this->actingAs($this->guest, 'sanctum')
            ->deleteJson("/api/v1/messages/{$message->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('messages', [
            'id' => $message->id,
        ]);
    }

    public function test_cannot_delete_other_user_message(): void
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->host->id,
        ]);

        $response = $this->actingAs($this->guest, 'sanctum')
            ->deleteJson("/api/v1/messages/{$message->id}");

        $response->assertStatus(403);
    }

    public function test_conversation_last_message_updates_after_send(): void
    {
        $this->actingAs($this->guest, 'sanctum')
            ->postJson("/api/v1/conversations/{$this->conversation->id}/messages", [
                'message' => 'New message',
            ]);

        $this->conversation->refresh();
        
        $this->assertNotNull($this->conversation->last_message_at);
        $this->assertTrue($this->conversation->last_message_at->isToday());
    }

    public function test_can_get_unread_message_count(): void
    {
        Message::factory()->count(3)->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->host->id,
            'read_at' => null,
        ]);

        Message::factory()->count(2)->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->host->id,
            'read_at' => now(),
        ]);

        $response = $this->actingAs($this->guest, 'sanctum')
            ->getJson("/api/v1/conversations/{$this->conversation->id}/unread-count");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'unread_count' => 3,
            ]);
    }

    public function test_system_messages_marked_correctly(): void
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->guest->id,
            'is_system_message' => true,
            'message' => 'Booking confirmed',
        ]);

        $this->assertTrue($message->is_system_message);
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'is_system_message' => true,
        ]);
    }

    public function test_messages_paginated_correctly(): void
    {
        Message::factory()->count(60)->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->guest->id,
        ]);

        $response = $this->actingAs($this->guest, 'sanctum')
            ->getJson("/api/v1/conversations/{$this->conversation->id}/messages?per_page=20");

        $response->assertStatus(200);
        
        $this->assertCount(20, $response->json('data'));
        $this->assertEquals(3, $response->json('meta.last_page'));
        $this->assertEquals(60, $response->json('meta.total'));
    }
}
