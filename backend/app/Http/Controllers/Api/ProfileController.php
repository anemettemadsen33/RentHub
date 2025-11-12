<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Get profile completion status
     */
    public function getCompletionStatus(Request $request)
    {
        $user = $request->user();

        $completed = [];
        $pending = [];

        // Basic Info (Step 1)
        if ($user->name && $user->email) {
            $completed[] = 'basic_info';
        } else {
            $pending[] = 'basic_info';
        }

        // Contact Info (Step 2)
        if ($user->phone) {
            $completed[] = 'contact_info';
        } else {
            $pending[] = 'contact_info';
        }

        // Profile Details (Step 3)
        if ($user->bio && $user->avatar) {
            $completed[] = 'profile_details';
        } else {
            $pending[] = 'profile_details';
        }

        // Verification (Step 4)
        if ($user->email_verified_at) {
            $completed[] = 'email_verification';
        } else {
            $pending[] = 'email_verification';
        }

        if ($user->phone_verified_at) {
            $completed[] = 'phone_verification';
        } else {
            $pending[] = 'phone_verification';
        }

        $totalSteps = count($completed) + count($pending);
        $percentage = $totalSteps > 0 ? (count($completed) / $totalSteps) * 100 : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'completed' => $completed,
                'pending' => $pending,
                'percentage' => round($percentage, 2),
                'is_complete' => empty($pending),
            ],
        ]);
    }

    /**
     * Update basic info (Step 1)
     */
    public function updateBasicInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $request->user()->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Basic info updated successfully',
            'data' => $request->user(),
        ]);
    }

    /**
     * Update contact info (Step 2)
     */
    public function updateContactInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $request->user()->update([
            'phone' => $request->phone,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contact info updated successfully',
            'data' => $request->user(),
        ]);
    }

    /**
     * Update profile details (Step 3)
     */
    public function updateProfileDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'max:2048'], // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = [
            'bio' => $request->bio,
        ];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($request->user()->avatar) {
                Storage::disk('public')->delete($request->user()->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        $request->user()->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile details updated successfully',
            'data' => $request->user(),
        ]);
    }

    /**
     * Complete profile wizard
     */
    public function completeWizard(Request $request)
    {
        $user = $request->user();

        // Check if all steps are completed
        $status = $this->getCompletionStatus($request)->getData()->data;

        if (! $status->is_complete) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete all steps before finishing',
                'data' => $status,
            ], 400);
        }

        // Mark profile as completed
        $user->update([
            'profile_completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile completed successfully!',
            'data' => $user,
        ]);
    }

    /**
     * Get user profile
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'avatar_url' => $user->avatar ? asset('storage/'.$user->avatar) : null,
                'is_email_verified' => $user->email_verified_at !== null,
                'is_phone_verified' => $user->phone_verified_at !== null,
                'has_2fa_enabled' => $user->two_factor_enabled,
            ],
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female,other'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'zip_code' => ['nullable', 'string', 'max:20'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $request->user()->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $request->user(),
        ]);
    }

    /**
     * Upload profile picture
     */
    public function uploadAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Delete old avatar
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Avatar uploaded successfully',
            'data' => [
                'avatar' => $path,
                'avatar_url' => asset('storage/'.$path),
            ],
        ]);
    }

    /**
     * Delete profile picture
     */
    public function deleteAvatar(Request $request)
    {
        $user = $request->user();

        if (! $user->avatar) {
            return response()->json([
                'success' => false,
                'message' => 'No avatar to delete',
            ], 400);
        }

        Storage::disk('public')->delete($user->avatar);
        $user->update(['avatar' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Avatar deleted successfully',
        ]);
    }

    /**
     * Get user settings
     */
    public function getSettings(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'language' => $user->language ?? 'en',
                'currency' => $user->currency ?? 'USD',
                'timezone' => $user->timezone ?? 'UTC',
                'notifications_email' => $user->notifications_email ?? true,
                'notifications_sms' => $user->notifications_sms ?? false,
                'notifications_push' => $user->notifications_push ?? true,
                'two_factor_enabled' => $user->two_factor_enabled ?? false,
            ],
        ]);
    }

    /**
     * Update user settings
     */
    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => ['nullable', 'string', 'max:10'],
            'currency' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'notifications_email' => ['nullable', 'boolean'],
            'notifications_sms' => ['nullable', 'boolean'],
            'notifications_push' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Store settings in JSON column or separate table
        $settings = $user->settings ?? [];
        $settings = array_merge($settings, $validator->validated());

        $user->update(['settings' => $settings]);

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'data' => $settings,
        ]);
    }

    /**
     * Update privacy settings
     */
    public function updatePrivacySettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_visibility' => ['nullable', 'in:public,private,friends'],
            'show_email' => ['nullable', 'boolean'],
            'show_phone' => ['nullable', 'boolean'],
            'show_address' => ['nullable', 'boolean'],
            'allow_messages' => ['nullable', 'boolean'],
            'allow_reviews' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Store privacy settings
        $privacy = $user->privacy_settings ?? [];
        $privacy = array_merge($privacy, $validator->validated());

        $user->update(['privacy_settings' => $privacy]);

        return response()->json([
            'success' => true,
            'message' => 'Privacy settings updated successfully',
            'data' => $privacy,
        ]);
    }

    /**
     * Get user verification status
     */
    public function getVerificationStatus(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'email_verified' => $user->email_verified_at !== null,
                'phone_verified' => $user->phone_verified_at !== null,
                'identity_verified' => $user->identity_verified_at !== null,
                'verification_badges' => [
                    'email' => $user->email_verified_at !== null,
                    'phone' => $user->phone_verified_at !== null,
                    'identity' => $user->identity_verified_at !== null,
                    'government_id' => $user->government_id_verified_at !== null,
                ],
            ],
        ]);
    }
}

