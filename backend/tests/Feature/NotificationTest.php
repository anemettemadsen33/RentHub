<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\NotificationPreference;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->owner = User::factory()->create(['role' => 'owner']);
        Notification::fake();
    }

    public function test_user_can_view_notification_preferences()
    {
        NotificationPreference::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/notification-preferences');

        $response->assertOk()
            ->assertJsonStructure(['email_enabled', 'sms_enabled', 'push_enabled']);
    }

    public function test_user_can_update_notification_preferences()
    {
        $preference = NotificationPreference::factory()->create([
            'user_id' => $this->user->id,
            'email_enabled' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/v1/notification-preferences', [
                'email_enabled' => false,
                'sms_enabled' => true,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $this->user->id,
            'email_enabled' => false,
            'sms_enabled' => true,
        ]);
    }

    public function test_notification_sent_on_booking_creation()
    {
        $property = Property::factory()->create(['owner_id' => $this->owner->id]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => now()->addDays(7)->format('Y-m-d'),
                'check_out' => now()->addDays(10)->format('Y-m-d'),
                'guests' => 2,
            ]);

        // Owner should receive notification
        Notification::assertSentTo($this->owner, function ($notification) {
            return $notification instanceof \App\Notifications\NewBookingNotification;
        });
    }

    public function test_notification_sent_on_booking_confirmation()
    {
        $property = Property::factory()->create(['owner_id' => $this->owner->id]);
        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/confirm");

        // Guest should receive notification
        Notification::assertSentTo($this->user, function ($notification) {
            return $notification instanceof \App\Notifications\BookingConfirmedNotification;
        });
    }

    public function test_user_can_view_notifications()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/notifications');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_user_can_mark_notification_as_read()
    {
        // Create a notification for the user
        $this->user->notify(new \App\Notifications\TestNotification);
        $notification = $this->user->notifications()->first();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertOk();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read()
    {
        // Create multiple notifications
        $this->user->notify(new \App\Notifications\TestNotification);
        $this->user->notify(new \App\Notifications\TestNotification);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/notifications/read-all');

        $response->assertOk();

        $this->assertEquals(0, $this->user->unreadNotifications()->count());
    }

    public function test_user_can_delete_notification()
    {
        $this->user->notify(new \App\Notifications\TestNotification);
        $notification = $this->user->notifications()->first();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/notifications/{$notification->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_unread_notification_count()
    {
        $this->user->notify(new \App\Notifications\TestNotification);
        $this->user->notify(new \App\Notifications\TestNotification);
        $this->user->notify(new \App\Notifications\TestNotification);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/notifications/unread-count');

        $response->assertOk()
            ->assertJsonFragment(['count' => 3]);
    }

    public function test_notification_preference_created_on_user_registration()
    {
        $newUser = User::factory()->create();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $newUser->id,
            'email_enabled' => true,
        ]);
    }

    public function test_notification_sent_on_payment_received()
    {
        $property = Property::factory()->create(['owner_id' => $this->owner->id]);
        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'user_id' => $this->user->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/payments', [
                'booking_id' => $booking->id,
                'amount' => 300,
                'payment_method' => 'bank_transfer',
            ]);

        // Owner should receive notification
        Notification::assertSentTo($this->owner, function ($notification) {
            return $notification instanceof \App\Notifications\PaymentReceivedNotification;
        });
    }

    public function test_notification_categories_can_be_toggled()
    {
        $preference = NotificationPreference::factory()->create([
            'user_id' => $this->user->id,
            'booking_updates' => true,
            'payment_updates' => true,
            'message_updates' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/v1/notification-preferences', [
                'booking_updates' => false,
                'payment_updates' => true,
                'message_updates' => false,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $this->user->id,
            'booking_updates' => false,
            'message_updates' => false,
        ]);
    }
}
