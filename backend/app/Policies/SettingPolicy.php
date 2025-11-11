<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

class SettingPolicy
{
    /**
     * Determine whether the user can view any settings.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the setting.
     */
    public function view(User $user, Setting $setting): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can create settings.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the setting.
     */
    public function update(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the setting.
     */
    public function delete(User $user, Setting $setting): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Additional granular permissions (future extension hooks)
     */
    public function testEmail(User $user): bool
    {
        return $user->hasPermission('settings.test_email');
    }
}
