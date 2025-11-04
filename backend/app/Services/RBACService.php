<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class RBACService
{
    /**
     * Check if user has role
     */
    public function hasRole(User $user, string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return $user->roles()
            ->whereIn('name', $roles)
            ->exists();
    }

    /**
     * Check if user has permission
     */
    public function hasPermission(User $user, string|array $permissions): bool
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];

        // Check direct permissions
        $hasDirectPermission = $user->permissions()
            ->whereIn('name', $permissions)
            ->exists();

        if ($hasDirectPermission) {
            return true;
        }

        // Check permissions through roles
        return $user->roles()
            ->whereHas('permissions', function ($query) use ($permissions) {
                $query->whereIn('name', $permissions);
            })
            ->exists();
    }

    /**
     * Check if user has any permission
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
            if (! $this->hasPermission($user, $permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Assign role to user
     */
    public function assignRole(User $user, string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }

        if (! $user->roles()->where('role_id', $role->id)->exists()) {
            $user->roles()->attach($role->id);
            $this->clearUserCache($user);
        }
    }

    /**
     * Remove role from user
     */
    public function removeRole(User $user, string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }

        $user->roles()->detach($role->id);
        $this->clearUserCache($user);
    }

    /**
     * Assign permission to user
     */
    public function givePermissionTo(User $user, string|Permission $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->firstOrFail();
        }

        if (! $user->permissions()->where('permission_id', $permission->id)->exists()) {
            $user->permissions()->attach($permission->id);
            $this->clearUserCache($user);
        }
    }

    /**
     * Revoke permission from user
     */
    public function revokePermissionFrom(User $user, string|Permission $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->firstOrFail();
        }

        $user->permissions()->detach($permission->id);
        $this->clearUserCache($user);
    }

    /**
     * Get all user permissions (including role permissions)
     */
    public function getUserPermissions(User $user): array
    {
        return Cache::remember("user:{$user->id}:permissions", 3600, function () use ($user) {
            $directPermissions = $user->permissions->pluck('name')->toArray();

            $rolePermissions = $user->roles()
                ->with('permissions')
                ->get()
                ->pluck('permissions')
                ->flatten()
                ->pluck('name')
                ->toArray();

            return array_unique(array_merge($directPermissions, $rolePermissions));
        });
    }

    /**
     * Get all user roles
     */
    public function getUserRoles(User $user): array
    {
        return Cache::remember("user:{$user->id}:roles", 3600, function () use ($user) {
            return $user->roles->pluck('name')->toArray();
        });
    }

    /**
     * Create role
     */
    public function createRole(string $name, ?string $description = null, array $permissions = []): Role
    {
        $role = Role::create([
            'name' => $name,
            'description' => $description,
        ]);

        if (! empty($permissions)) {
            $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
            $role->permissions()->attach($permissionIds);
        }

        return $role;
    }

    /**
     * Create permission
     */
    public function createPermission(string $name, ?string $description = null, ?string $group = null): Permission
    {
        return Permission::create([
            'name' => $name,
            'description' => $description,
            'group' => $group,
        ]);
    }

    /**
     * Sync user roles
     */
    public function syncRoles(User $user, array $roles): void
    {
        $roleIds = Role::whereIn('name', $roles)->pluck('id');
        $user->roles()->sync($roleIds);
        $this->clearUserCache($user);
    }

    /**
     * Sync user permissions
     */
    public function syncPermissions(User $user, array $permissions): void
    {
        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
        $user->permissions()->sync($permissionIds);
        $this->clearUserCache($user);
    }

    /**
     * Clear user cache
     */
    private function clearUserCache(User $user): void
    {
        Cache::forget("user:{$user->id}:permissions");
        Cache::forget("user:{$user->id}:roles");
    }

    /**
     * Check resource ownership
     */
    public function ownsResource(User $user, $resource): bool
    {
        if (method_exists($resource, 'user')) {
            return $resource->user()->is($user);
        }

        if (method_exists($resource, 'owner')) {
            return $resource->owner()->is($user);
        }

        if (property_exists($resource, 'user_id')) {
            return $resource->user_id === $user->id;
        }

        return false;
    }

    /**
     * Check if user can perform action on resource
     */
    public function can(User $user, string $action, $resource = null): bool
    {
        // Admin can do anything
        if ($this->hasRole($user, 'admin')) {
            return true;
        }

        // Check permission
        if (! $this->hasPermission($user, $action)) {
            return false;
        }

        // If resource provided, check ownership
        if ($resource && ! $this->ownsResource($user, $resource)) {
            // Check if user has permission to manage others' resources
            return $this->hasPermission($user, "{$action}.others");
        }

        return true;
    }
}
