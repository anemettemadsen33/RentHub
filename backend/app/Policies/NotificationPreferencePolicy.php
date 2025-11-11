<?php

namespace App\Policies;

use App\Models\NotificationPreference;
use App\Models\User;

class NotificationPreferencePolicy
{
    /**
     * Determine if the user can view their notification preferences.
     */
    public function view(User $user, NotificationPreference $preference): bool
    {
        return $user->id === $preference->user_id;
    }

    /**
     * Determine if the user can update their notification preferences.
     */
    public function update(User $user, NotificationPreference $preference): bool
    {
        return $user->id === $preference->user_id;
    }

    /**
     * Determine if the user can create notification preferences.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can delete notification preferences.
     */
    public function delete(User $user, NotificationPreference $preference): bool
    {
        return $user->id === $preference->user_id;
    }
}
