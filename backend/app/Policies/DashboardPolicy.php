<?php

namespace App\Policies;

use App\Models\User;

class DashboardPolicy
{
    /**
     * Determine if the user can access owner dashboard.
     */
    public function viewOwnerDashboard(User $user): bool
    {
        return in_array($user->role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can access tenant dashboard.
     */
    public function viewTenantDashboard(User $user): bool
    {
        return in_array($user->role, ['tenant', 'guest', 'admin']);
    }

    /**
     * Determine if the user can access admin dashboard.
     */
    public function viewAdminDashboard(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if the user can view dashboard statistics.
     */
    public function viewStatistics(User $user): bool
    {
        return true; // All authenticated users can view their own stats
    }

    /**
     * Determine if the user can view analytics.
     */
    public function viewAnalytics(User $user): bool
    {
        return in_array($user->role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can export data.
     */
    public function exportData(User $user): bool
    {
        return in_array($user->role, ['owner', 'admin']);
    }
}
