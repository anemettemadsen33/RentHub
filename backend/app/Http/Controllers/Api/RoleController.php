<?php

namespace App\\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Get available roles
     */
    public function getRoles()
    {
        $roles = [
            [
                'value' => 'guest',
                'label' => 'Guest',
                'description' => 'Can browse properties only',
                'permissions' => [
                    'browse_properties',
                    'view_property_details',
                    'search_properties',
                ],
            ],
            [
                'value' => 'tenant',
                'label' => 'Tenant',
                'description' => 'Can book properties and manage bookings',
                'permissions' => [
                    'browse_properties',
                    'view_property_details',
                    'search_properties',
                    'book_properties',
                    'manage_own_bookings',
                    'write_reviews',
                    'send_messages',
                ],
            ],
            [
                'value' => 'owner',
                'label' => 'Owner',
                'description' => 'Can list and manage properties',
                'permissions' => [
                    'browse_properties',
                    'view_property_details',
                    'list_properties',
                    'manage_own_properties',
                    'manage_bookings',
                    'view_analytics',
                    'send_messages',
                    'respond_to_reviews',
                ],
            ],
            [
                'value' => 'admin',
                'label' => 'Admin',
                'description' => 'Full access to all features',
                'permissions' => [
                    'full_access',
                    'manage_users',
                    'manage_all_properties',
                    'manage_all_bookings',
                    'approve_verifications',
                    'manage_settings',
                    'view_all_analytics',
                    'delete_reviews',
                    'ban_users',
                ],
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }

    /**
     * Get current user role and permissions
     */
    public function getMyRole(Request $request)
    {
        $user = $request->user();

        $roleData = $this->getRoleData($user->role);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'role' => $roleData,
            ],
        ]);
    }

    /**
     * Change user role (Admin only)
     */
    public function changeUserRole(Request $request, $userId)
    {
        // Check if user is admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'role' => ['required', 'in:guest,tenant,owner,admin'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::findOrFail($userId);

        // Prevent changing own role
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot change your own role',
            ], 400);
        }

        $oldRole = $user->role;
        $user->update(['role' => $request->role]);

        return response()->json([
            'success' => true,
            'message' => "User role changed from {$oldRole} to {$request->role}",
            'data' => $user,
        ]);
    }

    /**
     * Check if user has permission
     */
    public function checkPermission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'permission' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $roleData = $this->getRoleData($user->role);

        $hasPermission = in_array($request->permission, $roleData['permissions'])
                        || in_array('full_access', $roleData['permissions']);

        return response()->json([
            'success' => true,
            'data' => [
                'has_permission' => $hasPermission,
                'permission' => $request->permission,
                'user_role' => $user->role,
            ],
        ]);
    }

    /**
     * Get all users with roles (Admin only)
     */
    public function getUsersByRole(Request $request)
    {
        // Check if user is admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        $role = $request->query('role');

        $query = User::query();

        if ($role && in_array($role, ['guest', 'tenant', 'owner', 'admin'])) {
            $query->where('role', $role);
        }

        $users = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Helper: Get role data
     */
    private function getRoleData($role)
    {
        $roles = [
            'guest' => [
                'value' => 'guest',
                'label' => 'Guest',
                'description' => 'Can browse properties only',
                'permissions' => [
                    'browse_properties',
                    'view_property_details',
                    'search_properties',
                ],
            ],
            'tenant' => [
                'value' => 'tenant',
                'label' => 'Tenant',
                'description' => 'Can book properties and manage bookings',
                'permissions' => [
                    'browse_properties',
                    'view_property_details',
                    'search_properties',
                    'book_properties',
                    'manage_own_bookings',
                    'write_reviews',
                    'send_messages',
                ],
            ],
            'owner' => [
                'value' => 'owner',
                'label' => 'Owner',
                'description' => 'Can list and manage properties',
                'permissions' => [
                    'browse_properties',
                    'view_property_details',
                    'list_properties',
                    'manage_own_properties',
                    'manage_bookings',
                    'view_analytics',
                    'send_messages',
                    'respond_to_reviews',
                ],
            ],
            'admin' => [
                'value' => 'admin',
                'label' => 'Admin',
                'description' => 'Full access to all features',
                'permissions' => [
                    'full_access',
                    'manage_users',
                    'manage_all_properties',
                    'manage_all_bookings',
                    'approve_verifications',
                    'manage_settings',
                    'view_all_analytics',
                    'delete_reviews',
                    'ban_users',
                ],
            ],
        ];

        return $roles[$role] ?? $roles['guest'];
    }
}

