<?php

namespace App\Services\Security;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class RolePermissionService
{
    /**
     * Define role hierarchy.
     */
    private array $roleHierarchy = [
        'super_admin' => 100,
        'admin' => 80,
        'property_manager' => 60,
        'owner' => 50,
        'guest' => 10,
    ];

    /**
     * Define permissions for each role.
     */
    private array $rolePermissions = [
        'super_admin' => ['*'], // All permissions
        'admin' => [
            'properties.*',
            'bookings.*',
            'users.view',
            'users.edit',
            'payments.*',
            'reports.*',
            'settings.view',
            'settings.edit',
        ],
        'property_manager' => [
            'properties.view',
            'properties.edit',
            'bookings.view',
            'bookings.manage',
            'calendar.view',
            'calendar.edit',
            'guests.view',
            'messages.*',
            'maintenance.*',
        ],
        'owner' => [
            'properties.view',
            'properties.create',
            'properties.edit.own',
            'bookings.view.own',
            'calendar.view.own',
            'reports.view.own',
            'payments.view.own',
        ],
        'guest' => [
            'properties.view',
            'properties.search',
            'bookings.create',
            'bookings.view.own',
            'messages.send',
            'reviews.create',
            'profile.edit.own',
        ],
    ];

    /**
     * Check if user has permission.
     */
    public function hasPermission(User $user, string $permission): bool
    {
        $cacheKey = "user_permissions:{$user->id}";

        $permissions = Cache::remember($cacheKey, 3600, function () use ($user) {
            return $this->getUserPermissions($user);
        });

        // Super admin has all permissions
        if (in_array('*', $permissions)) {
            return true;
        }

        // Check exact permission
        if (in_array($permission, $permissions)) {
            return true;
        }

        // Check wildcard permissions
        $parts = explode('.', $permission);
        for ($i = count($parts) - 1; $i > 0; $i--) {
            $wildcard = implode('.', array_slice($parts, 0, $i)).'.*';
            if (in_array($wildcard, $permissions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has any of the permissions.
     */
    public function hasAnyPermission(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($user, $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all permissions.
     */
    public function hasAllPermissions(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! $this->hasPermission($user, $permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all user permissions.
     */
    public function getUserPermissions(User $user): array
    {
        $role = $user->role ?? 'guest';

        return $this->rolePermissions[$role] ?? [];
    }

    /**
     * Check if user has role.
     */
    public function hasRole(User $user, string $role): bool
    {
        return $user->role === $role;
    }

    /**
     * Check if user has any of the roles.
     */
    public function hasAnyRole(User $user, array $roles): bool
    {
        return in_array($user->role, $roles);
    }

    /**
     * Check if user's role is higher or equal to specified role.
     */
    public function hasRoleOrHigher(User $user, string $role): bool
    {
        $userLevel = $this->roleHierarchy[$user->role] ?? 0;
        $requiredLevel = $this->roleHierarchy[$role] ?? 0;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Check if user can perform action on resource.
     */
    public function canAccessResource(User $user, string $resource, $ownerId = null): bool
    {
        $permission = "{$resource}.view";

        // Check full access
        if ($this->hasPermission($user, $permission)) {
            return true;
        }

        // Check own resource access
        if ($ownerId && $this->hasPermission($user, "{$permission}.own")) {
            return $user->id === $ownerId;
        }

        return false;
    }

    /**
     * Clear user permissions cache.
     */
    public function clearUserCache(User $user): void
    {
        Cache::forget("user_permissions:{$user->id}");
    }

    /**
     * Grant temporary permission.
     */
    public function grantTemporaryPermission(User $user, string $permission, int $duration = 3600): void
    {
        $cacheKey = "temp_permission:{$user->id}:{$permission}";
        Cache::put($cacheKey, true, $duration);
    }

    /**
     * Check temporary permission.
     */
    public function hasTemporaryPermission(User $user, string $permission): bool
    {
        $cacheKey = "temp_permission:{$user->id}:{$permission}";

        return Cache::has($cacheKey);
    }
}
