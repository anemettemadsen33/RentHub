<?php

namespace Tests\Feature\Api;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MessageApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    #[Test]
    public function participant_can_send_message_in_conversation()
    {
        $tenant = User::factory()->create();
        $owner = User::factory()->create();
        $tenant->assignRole('tenant');
        $owner->assignRole('owner');

        $property = Property::factory()->create(['user_id' => $owner->id]);

        $conversation = Conversation::factory()->create([
            'property_id' => $property->id,
            'tenant_id' => $tenant->id,
            'owner_id' => $owner->id,
        ]);

        $this->actingAs($tenant, 'sanctum');

        $response = $this->postJson("/api/v1/conversations/{$conversation->id}/messages", [
            'message' => 'Hello, I have a question about the property.',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $tenant->id,
            'message' => 'Hello, I have a question about the property.',
        ]);
    }

    #[Test]
    public function non_participant_cannot_send_message_in_conversation()
    {
        $tenant = User::factory()->create();
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $tenant->assignRole('tenant');
        $owner->assignRole('owner');
        $stranger->assignRole('tenant');

        $property = Property::factory()->create(['user_id' => $owner->id]);

        $conversation = Conversation::factory()->create([
            'property_id' => $property->id,
            'tenant_id' => $tenant->id,
            'owner_id' => $owner->id,
        ]);

        $this->actingAs($stranger, 'sanctum');

        $response = $this->postJson("/api/v1/conversations/{$conversation->id}/messages", [
            'message' => 'This should fail.',
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function participant_can_list_messages_in_conversation()
    {
        $tenant = User::factory()->create();
        $owner = User::factory()->create();
        $tenant->assignRole('tenant');
        $owner->assignRole('owner');

        $property = Property::factory()->create(['user_id' => $owner->id]);

        $conversation = Conversation::factory()->create([
            'property_id' => $property->id,
            'tenant_id' => $tenant->id,
            'owner_id' => $owner->id,
        ]);

        $message1 = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $tenant->id,
            'message' => 'First message',
        ]);

        $message2 = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $owner->id,
            'message' => 'Second message',
        ]);

        $this->actingAs($tenant, 'sanctum');

        $response = $this->getJson("/api/v1/conversations/{$conversation->id}/messages");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [],
            'meta',
        ]);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['message' => 'First message']);
        $response->assertJsonFragment(['message' => 'Second message']);
    }

    #[Test]
    public function non_participant_cannot_list_messages_in_conversation()
    {
        $tenant = User::factory()->create();
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $tenant->assignRole('tenant');
        $owner->assignRole('owner');
        $stranger->assignRole('tenant');

        $property = Property::factory()->create(['user_id' => $owner->id]);

        $conversation = Conversation::factory()->create([
            'property_id' => $property->id,
            'tenant_id' => $tenant->id,
            'owner_id' => $owner->id,
        ]);

        $this->actingAs($stranger, 'sanctum');

        $response = $this->getJson("/api/v1/conversations/{$conversation->id}/messages");

        $response->assertStatus(404);
    }

    #[Test]
    public function sender_can_update_own_message_within_time_limit()
    {
        $tenant = User::factory()->create();
        $owner = User::factory()->create();
        $tenant->assignRole('tenant');
        $owner->assignRole('owner');

        $property = Property::factory()->create(['user_id' => $owner->id]);

        $conversation = Conversation::factory()->create([
            'property_id' => $property->id,
            'tenant_id' => $tenant->id,
            'owner_id' => $owner->id,
        ]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $tenant->id,
            'message' => 'Original message',
        ]);

        $this->actingAs($tenant, 'sanctum');

        $response = $this->patchJson("/api/v1/messages/{$message->id}", [
            'message' => 'Updated message',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'message' => 'Updated message',
        ]);
    }

    #[Test]
    public function user_cannot_update_others_message()
    {
        $tenant = User::factory()->create();
        $owner = User::factory()->create();
        $tenant->assignRole('tenant');
        $owner->assignRole('owner');

        $property = Property::factory()->create(['user_id' => $owner->id]);

        $conversation = Conversation::factory()->create([
            'property_id' => $property->id,
            'tenant_id' => $tenant->id,
            'owner_id' => $owner->id,
        ]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $owner->id,
            'message' => 'Owner message',
        ]);

        $this->actingAs($tenant, 'sanctum');

        $response = $this->patchJson("/api/v1/messages/{$message->id}", [
            'message' => 'Trying to update',
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function sender_can_delete_own_message()
    {
        $tenant = User::factory()->create();
        $owner = User::factory()->create();
        $tenant->assignRole('tenant');
        $owner->assignRole('owner');

        $property = Property::factory()->create(['user_id' => $owner->id]);

        $conversation = Conversation::factory()->create([
            'property_id' => $property->id,
            'tenant_id' => $tenant->id,
            'owner_id' => $owner->id,
        ]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $tenant->id,
            'message' => 'Message to delete',
        ]);

        $this->actingAs($tenant, 'sanctum');

        $response = $this->deleteJson("/api/v1/messages/{$message->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('messages', [
            'id' => $message->id,
        ]);
    }

    /** @test */
    public function user_cannot_delete_others_message()
    {
        $tenant = User::factory()->create();
        $owner = User::factory()->create();
        $tenant->assignRole('tenant');
        $owner->assignRole('owner');

        $property = Property::factory()->create(['user_id' => $owner->id]);

        $conversation = Conversation::factory()->create([
            'property_id' => $property->id,
            'tenant_id' => $tenant->id,
            'owner_id' => $owner->id,
        ]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $owner->id,
            'message' => 'Owner message',
        ]);

        $this->actingAs($tenant, 'sanctum');

        $response = $this->deleteJson("/api/v1/messages/{$message->id}");

        $response->assertStatus(404);
    }

    /** @test */
    public function participant_can_mark_message_as_read()
    {
        $tenant = User::factory()->create();
        $owner = User::factory()->create();
        $tenant->assignRole('tenant');
        $owner->assignRole('owner');

        $property = Property::factory()->create(['user_id' => $owner->id]);

        $conversation = Conversation::factory()->create([
            'property_id' => $property->id,
            'tenant_id' => $tenant->id,
            'owner_id' => $owner->id,
        ]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $owner->id,
            'message' => 'New message',
            'read_at' => null,
        ]);

        $this->actingAs($tenant, 'sanctum');

        $response = $this->postJson("/api/v1/messages/{$message->id}/read");

        $response->assertStatus(200);
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
        ]);

        $message->refresh();
        $this->assertNotNull($message->read_at);
    }

    /** @test */
    public function it_validates_message_content_is_required()
    {
        $tenant = User::factory()->create();
        $owner = User::factory()->create();
        $tenant->assignRole('tenant');
        $owner->assignRole('owner');

        $property = Property::factory()->create(['user_id' => $owner->id]);

        $conversation = Conversation::factory()->create([
            'property_id' => $property->id,
            'tenant_id' => $tenant->id,
            'owner_id' => $owner->id,
        ]);

        $this->actingAs($tenant, 'sanctum');

        $response = $this->postJson("/api/v1/conversations/{$conversation->id}/messages", [
            'message' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message']);
    }
}
