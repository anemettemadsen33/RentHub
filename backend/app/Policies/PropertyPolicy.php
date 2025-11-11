<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    /**
     * Determine if the user can view the property.
     */
    public function view(?User $user, Property $property): bool
    {
        // Published properties are viewable by everyone
        if ($property->status === 'published') {
            return true;
        }

        // Owners and admins can view any status
        if ($user && ($user->hasRole('admin') || $property->user_id === $user->id || $property->owner_id === $user->id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the given property can be deleted by the user.
     */
    public function delete(User $user, Property $property): bool
    {
        // Owners can delete their own properties; admins can delete any
        return $user->hasRole('admin') || $property->user_id === $user->id || $property->owner_id === $user->id;
    }

    /**
     * Determine if the user can update the property.
     */
    public function update(User $user, Property $property): bool
    {
        // Owners can update their own properties; admins can update any
        return $user->hasRole('admin') || $property->user_id === $user->id || $property->owner_id === $user->id;
    }
}
