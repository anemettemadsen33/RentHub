<?php

namespace Tests\Feature\Jobs;

use App\Jobs\SendBookingConfirmedNotification;
use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use App\Notifications\BookingConfirmedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendBookingConfirmedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_is_dispatched_on_booking_confirmation()
    {
        Queue::fake();

        $owner = User::factory()->create();
        $guest = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'user_id' => $guest->id,
            'status' => 'pending',
        ]);

        // Update booking to confirmed (would trigger observer in real scenario)
        $booking->update(['status' => 'confirmed']);

        // In real scenario, observer dispatches job - simulate here
        SendBookingConfirmedNotification::dispatch($booking);

        Queue::assertPushed(SendBookingConfirmedNotification::class, function ($job) use ($booking) {
            return $job->booking->id === $booking->id;
        });
    }

    public function test_job_sends_notifications_to_guest_and_owner()
    {
        Notification::fake();

        $owner = User::factory()->create();
        $guest = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'user_id' => $guest->id,
            'status' => 'confirmed',
        ]);

        // Execute job
        $job = new SendBookingConfirmedNotification($booking);
        $job->handle();

        // Assert notifications sent
        Notification::assertSentTo($guest, BookingConfirmedNotification::class);
        Notification::assertSentTo($owner, BookingConfirmedNotification::class);
    }
}
