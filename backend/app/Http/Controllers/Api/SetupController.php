<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SetupController extends Controller
{
    /**
     * Create admin user (ONLY for initial setup)
     * TODO: REMOVE THIS ENDPOINT AFTER SETUP!
     */
    public function createAdminUser(Request $request)
    {
        // Security check - only allow if no admin exists
        $adminExists = User::where('is_admin', true)->exists();
        
        if ($adminExists) {
            return response()->json([
                'success' => false,
                'message' => 'Admin user already exists. This endpoint is disabled for security.',
            ], 403);
        }

        try {
            $admin = User::firstOrCreate(
                ['email' => 'admin@renthub.com'],
                [
                    'name' => 'Admin',
                    'password' => Hash::make('admin123'),
                    'is_admin' => true,
                    'email_verified_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Admin user created successfully',
                'admin' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                ],
                'credentials' => [
                    'email' => 'admin@renthub.com',
                    'password' => 'admin123',
                ],
                'login_url' => url('/admin/login'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create admin user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
