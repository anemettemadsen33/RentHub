<?php

namespace App\Policies;

use App\Models\SavedSearch;
use App\Models\User;

class SavedSearchPolicy
{
    /**
     * Determine if the user can view any saved searches.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own saved searches
    }

    /**
     * Determine if the user can view the saved search.
     */
    public function view(User $user, SavedSearch $savedSearch): bool
    {
        return $user->id === $savedSearch->user_id;
    }

    /**
     * Determine if the user can create saved searches.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the saved search.
     */
    public function update(User $user, SavedSearch $savedSearch): bool
    {
        return $user->id === $savedSearch->user_id;
    }

    /**
     * Determine if the user can delete the saved search.
     */
    public function delete(User $user, SavedSearch $savedSearch): bool
    {
        return $user->id === $savedSearch->user_id;
    }

    /**
     * Determine if the user can execute the saved search.
     */
    public function execute(User $user, SavedSearch $savedSearch): bool
    {
        return $user->id === $savedSearch->user_id;
    }

    /**
     * Determine if the user can toggle alerts for the saved search.
     */
    public function toggleAlerts(User $user, SavedSearch $savedSearch): bool
    {
        return $user->id === $savedSearch->user_id;
    }
}
