<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Conversation $conversation): bool
    {
        return $user->id === $conversation->tenant_id 
            || $user->id === $conversation->owner_id
            || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('send_messages');
    }

    public function update(User $user, Conversation $conversation): bool
    {
        return ($user->id === $conversation->tenant_id 
            || $user->id === $conversation->owner_id)
            && !$conversation->is_archived;
    }

    public function delete(User $user, Conversation $conversation): bool
    {
        return $user->id === $conversation->tenant_id 
            || $user->id === $conversation->owner_id
            || $user->isAdmin();
    }

    public function restore(User $user, Conversation $conversation): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Conversation $conversation): bool
    {
        return $user->isAdmin();
    }

    public function archive(User $user, Conversation $conversation): bool
    {
        return $user->id === $conversation->tenant_id 
            || $user->id === $conversation->owner_id;
    }
}
