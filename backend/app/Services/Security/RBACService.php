<?php

namespace App\Services\Security;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Cache;

class RBACService
{
    /**
     * Assign role to user
     */
    public function assignRole(User $user, string|Role $role): bool
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }

        $user->roles()->syncWithoutDetaching($role->id);
        $this->clearUserCache($user);
        
        return true;
    }

    /**
     * Remove role from user
     */
    public function removeRole(User $user, string|Role $role): bool
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }

        $user->roles()->detach($role->id);
        $this->clearUserCache($user);
        
        return true;
    }

    /**
     * Assign permission to role
     */
    public function assignPermissionToRole(Role $role, string|Permission $permission): bool
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->firstOrFail();
        }

        $role->permissions()->syncWithoutDetaching($permission->id);
        $this->clearRoleCache($role);
        
        return true;
    }

    /**
     * Check if user has role
     */
    public function hasRole(User $user, string|array $roles): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        return $user->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Check if user has permission
     */
    public function hasPermission(User $user, string $permission): bool
    {
        return Cache::remember(
            "user:{$user->id}:permission:{$permission}",
            3600,
            function () use ($user, $permission) {
                // Check direct permissions
                if ($user->permissions()->where('name', $permission)->exists()) {
                    return true;
                }

                // Check role permissions
                return $user->roles()
                    ->whereHas('permissions', function ($query) use ($permission) {
                        $query->where('name', $permission);
                    })
                    ->exists();
            }
        );
    }

    /**
     * Check if user has any of the permissions
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
     * Check if user has all permissions
     */
    public function hasAllPermissions(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($user, $permission)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get user permissions
     */
    public function getUserPermissions(User $user): array
    {
        return Cache::remember(
            "user:{$user->id}:all_permissions",
            3600,
            function () use ($user) {
                $directPermissions = $user->permissions()->pluck('name')->toArray();
                
                $rolePermissions = $user->roles()
                    ->with('permissions')
                    ->get()
                    ->pluck('permissions')
                    ->flatten()
                    ->pluck('name')
                    ->unique()
                    ->toArray();

                return array_unique(array_merge($directPermissions, $rolePermissions));
            }
        );
    }

    /**
     * Get user roles
     */
    public function getUserRoles(User $user): array
    {
        return Cache::remember(
            "user:{$user->id}:roles",
            3600,
            function () use ($user) {
                return $user->roles()->pluck('name')->toArray();
            }
        );
    }

    /**
     * Create role with permissions
     */
    public function createRole(string $name, array $permissions = [], ?string $description = null): Role
    {
        $role = Role::create([
            'name' => $name,
            'description' => $description,
        ]);

        if (!empty($permissions)) {
            $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
            $role->permissions()->attach($permissionIds);
        }

        return $role;
    }

    /**
     * Create permission
     */
    public function createPermission(string $name, ?string $description = null, ?string $category = null): Permission
    {
        return Permission::create([
            'name' => $name,
            'description' => $description,
            'category' => $category,
        ]);
    }

    /**
     * Clear user cache
     */
    private function clearUserCache(User $user): void
    {
        Cache::forget("user:{$user->id}:roles");
        Cache::forget("user:{$user->id}:all_permissions");
        
        // Clear individual permission caches
        $permissions = Permission::pluck('name');
        foreach ($permissions as $permission) {
            Cache::forget("user:{$user->id}:permission:{$permission}");
        }
    }

    /**
     * Clear role cache
     */
    private function clearRoleCache(Role $role): void
    {
        // Clear cache for all users with this role
        $users = $role->users;
        foreach ($users as $user) {
            $this->clearUserCache($user);
        }
    }
}
