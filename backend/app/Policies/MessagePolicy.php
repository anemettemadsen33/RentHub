<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Message $message): bool
    {
        $conversation = $message->conversation;

        return $user->id === $conversation->tenant_id
            || $user->id === $conversation->owner_id
            || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('send_messages');
    }

    public function update(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id
            && $message->created_at->diffInMinutes(now()) <= 15;
    }

    public function delete(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id || $user->isAdmin();
    }

    public function restore(User $user, Message $message): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Message $message): bool
    {
        return $user->isAdmin();
    }

    public function markAsRead(User $user, Message $message): bool
    {
        $conversation = $message->conversation;
        return $user->id === $conversation->tenant_id
            || $user->id === $conversation->owner_id
            || $user->isAdmin();
    }
}
