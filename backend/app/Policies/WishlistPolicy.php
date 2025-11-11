<?php

namespace App\Policies;

use App\Models\Wishlist;
use App\Models\User;

class WishlistPolicy
{
    /**
     * Determine if the user can view any wishlists.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the wishlist.
     */
    public function view(User $user, Wishlist $wishlist): bool
    {
        // Users can view their own wishlists or shared wishlists
        return $user->id === $wishlist->user_id || $wishlist->is_public;
    }

    /**
     * Determine if the user can create wishlists.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the wishlist.
     */
    public function update(User $user, Wishlist $wishlist): bool
    {
        return $user->id === $wishlist->user_id;
    }

    /**
     * Determine if the user can delete the wishlist.
     */
    public function delete(User $user, Wishlist $wishlist): bool
    {
        // Cannot delete default wishlist
        return $user->id === $wishlist->user_id && ! $wishlist->is_default;
    }

    /**
     * Determine if the user can add properties to the wishlist.
     */
    public function addProperty(User $user, Wishlist $wishlist): bool
    {
        return $user->id === $wishlist->user_id;
    }

    /**
     * Determine if the user can remove properties from the wishlist.
     */
    public function removeProperty(User $user, Wishlist $wishlist): bool
    {
        return $user->id === $wishlist->user_id;
    }

    /**
     * Determine if the user can share the wishlist.
     */
    public function share(User $user, Wishlist $wishlist): bool
    {
        return $user->id === $wishlist->user_id;
    }
}
