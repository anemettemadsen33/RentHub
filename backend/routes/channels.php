<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// User's private notification channel
Broadcast::channel('user.{userId}', function (User $user, int $userId) {
    return (int) $user->id === (int) $userId;
});

// Property updates channel (owner only)
Broadcast::channel('property.{propertyId}', function (User $user, int $propertyId) {
    return \App\Models\Property::where('id', $propertyId)
        ->where('owner_id', $user->id)
        ->exists();
});

// Conversation/messaging channel (participants only)
Broadcast::channel('conversation.{conversationId}', function (User $user, int $conversationId) {
    $conversation = \App\Models\Conversation::find($conversationId);

    if (! $conversation) {
        return false;
    }

    return $conversation->participants()
        ->where('user_id', $user->id)
        ->exists();
});

// Booking updates channel (property owner or guest)
Broadcast::channel('booking.{bookingId}', function (User $user, int $bookingId) {
    $booking = \App\Models\Booking::find($bookingId);

    if (! $booking) {
        return false;
    }

    return $booking->guest_id === $user->id ||
           $booking->property->owner_id === $user->id;
});

// Chat presence channel
Broadcast::channel('chat.{roomId}', function (User $user, string $roomId) {
    // Return user info for presence
    return [
        'id' => $user->id,
        'name' => $user->name,
        'avatar' => $user->avatar_url ?? null,
    ];
});

// Property viewing presence (who's currently viewing a property)
Broadcast::channel('property.viewing.{propertyId}', function (User $user, int $propertyId) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'type' => $user->role,
    ];
});

