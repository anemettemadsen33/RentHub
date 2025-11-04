<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AdvancedRBACMiddleware
{
    /**
     * Permission hierarchy and inheritance
     */
    private array $permissionHierarchy = [
        'admin' => ['*'], // Full access
        'property_manager' => [
            'properties.*',
            'bookings.*',
            'reviews.read',
            'reviews.respond',
            'analytics.read',
            'messages.*',
        ],
        'owner' => [
            'properties.read',
            'properties.update',
            'bookings.read',
            'bookings.cancel',
            'reviews.read',
            'analytics.read',
        ],
        'guest' => [
            'properties.read',
            'bookings.create',
            'bookings.read.own',
            'reviews.create',
            'messages.send',
        ],
        'support' => [
            'bookings.read',
            'messages.*',
            'users.read',
            'analytics.read',
        ],
    ];

    /**
     * Resource-based permissions
     */
    private array $resourcePermissions = [
        'properties' => [
            'create' => ['admin', 'property_manager', 'owner'],
            'read' => ['*'],
            'update' => ['admin', 'property_manager', 'owner:own'],
            'delete' => ['admin', 'property_manager'],
            'publish' => ['admin', 'property_manager'],
        ],
        'bookings' => [
            'create' => ['guest', 'admin'],
            'read' => ['*:own', 'admin', 'property_manager', 'support'],
            'update' => ['admin', 'property_manager'],
            'cancel' => ['guest:own', 'admin', 'property_manager', 'owner:property_owner'],
            'approve' => ['admin', 'property_manager', 'owner:property_owner'],
        ],
        'users' => [
            'create' => ['admin'],
            'read' => ['admin', 'support'],
            'update' => ['admin', '*:own'],
            'delete' => ['admin'],
            'impersonate' => ['admin'],
        ],
    ];

    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Check if user has any of the required permissions
        $hasPermission = false;

        foreach ($permissions as $permission) {
            if ($this->checkPermission($user, $permission, $request)) {
                $hasPermission = true;
                break;
            }
        }

        if (! $hasPermission) {
            Log::warning('RBAC: Access denied', [
                'user_id' => $user->id,
                'role' => $user->role,
                'required_permissions' => $permissions,
                'route' => $request->route()->getName(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Insufficient permissions',
                'required_permissions' => $permissions,
            ], 403);
        }

        // Log successful permission check
        Log::info('RBAC: Access granted', [
            'user_id' => $user->id,
            'role' => $user->role,
            'permission' => $permissions,
            'route' => $request->route()->getName(),
        ]);

        return $next($request);
    }

    /**
     * Check if user has permission
     */
    private function checkPermission($user, string $permission, Request $request): bool
    {
        // Cache key for user permissions
        $cacheKey = "rbac:user:{$user->id}:permissions";

        // Get cached permissions or compute
        $userPermissions = Cache::remember($cacheKey, 300, function () use ($user) {
            return $this->computeUserPermissions($user);
        });

        // Check for wildcard permission
        if (in_array('*', $userPermissions)) {
            return true;
        }

        // Check direct permission match
        if (in_array($permission, $userPermissions)) {
            return true;
        }

        // Check pattern matching (e.g., properties.* matches properties.read)
        foreach ($userPermissions as $userPerm) {
            if ($this->matchesPattern($permission, $userPerm)) {
                return true;
            }
        }

        // Check resource-based permissions with ownership
        if (strpos($permission, ':own') !== false) {
            return $this->checkOwnership($user, $permission, $request);
        }

        return false;
    }

    /**
     * Compute all permissions for a user based on their role
     */
    private function computeUserPermissions($user): array
    {
        $role = $user->role ?? 'guest';
        $permissions = $this->permissionHierarchy[$role] ?? [];

        // Add custom permissions from database
        if (method_exists($user, 'customPermissions')) {
            $permissions = array_merge($permissions, $user->customPermissions()->pluck('name')->toArray());
        }

        return $permissions;
    }

    /**
     * Check if permission matches pattern (wildcard support)
     */
    private function matchesPattern(string $permission, string $pattern): bool
    {
        // Convert pattern to regex
        $regex = str_replace('*', '.*', $pattern);
        $regex = '/^'.$regex.'$/';

        return preg_match($regex, $permission) === 1;
    }

    /**
     * Check resource ownership
     */
    private function checkOwnership($user, string $permission, Request $request): bool
    {
        // Extract resource from permission (e.g., bookings.read:own -> bookings)
        [$resource] = explode('.', $permission);

        // Get resource ID from route parameters
        $resourceId = $request->route($resource) ?? $request->route('id');

        if (! $resourceId) {
            return false;
        }

        // Check ownership based on resource type
        return match ($resource) {
            'properties' => $this->checkPropertyOwnership($user, $resourceId),
            'bookings' => $this->checkBookingOwnership($user, $resourceId),
            'reviews' => $this->checkReviewOwnership($user, $resourceId),
            'messages' => $this->checkMessageOwnership($user, $resourceId),
            default => false,
        };
    }

    private function checkPropertyOwnership($user, $propertyId): bool
    {
        return \App\Models\Property::where('id', $propertyId)
            ->where('owner_id', $user->id)
            ->exists();
    }

    private function checkBookingOwnership($user, $bookingId): bool
    {
        $booking = \App\Models\Booking::find($bookingId);

        return $booking && (
            $booking->user_id === $user->id ||
            $booking->property->owner_id === $user->id
        );
    }

    private function checkReviewOwnership($user, $reviewId): bool
    {
        return \App\Models\Review::where('id', $reviewId)
            ->where('user_id', $user->id)
            ->exists();
    }

    private function checkMessageOwnership($user, $messageId): bool
    {
        $message = \App\Models\Message::find($messageId);

        return $message && (
            $message->sender_id === $user->id ||
            $message->recipient_id === $user->id
        );
    }

    /**
     * Clear cached permissions for a user
     */
    public static function clearUserPermissions(int $userId): void
    {
        Cache::forget("rbac:user:{$userId}:permissions");
    }
}
