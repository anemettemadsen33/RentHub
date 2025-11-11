<?php

namespace App\\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VerificationController extends Controller
{
    /**
     * Upload government ID for verification
     */
    public function uploadGovernmentId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_type' => ['required', 'in:passport,drivers_license,national_id'],
            'id_number' => ['required', 'string', 'max:100'],
            'front_image' => ['required', 'image', 'max:5120'], // 5MB
            'back_image' => ['nullable', 'image', 'max:5120'], // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Store front image
        $frontPath = $request->file('front_image')->store('id-verifications', 'private');

        // Store back image if provided
        $backPath = null;
        if ($request->hasFile('back_image')) {
            $backPath = $request->file('back_image')->store('id-verifications', 'private');
        }

        // Store verification data in user table or create separate verification table
        $user->update([
            'id_type' => $request->id_type,
            'id_number' => $request->id_number,
            'id_front_image' => $frontPath,
            'id_back_image' => $backPath,
            'id_verification_status' => 'pending',
            'id_submitted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'ID submitted for verification. We will review it within 24-48 hours.',
            'data' => [
                'status' => 'pending',
                'submitted_at' => now(),
            ],
        ]);
    }

    /**
     * Get verification status
     */
    public function getStatus(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'email_verified' => $user->email_verified_at !== null,
                'phone_verified' => $user->phone_verified_at !== null,
                'identity_verified' => $user->identity_verified_at !== null,
                'government_id_status' => $user->id_verification_status ?? 'not_submitted',
                'government_id_verified' => $user->government_id_verified_at !== null,
            ],
        ]);
    }

    /**
     * Admin: Approve government ID
     */
    public function approveGovernmentId(Request $request, $userId)
    {
        // Check if user is admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $user = \App\Models\User::findOrFail($userId);

        $user->update([
            'id_verification_status' => 'approved',
            'government_id_verified_at' => now(),
            'identity_verified_at' => now(),
        ]);

        // TODO: Send notification to user

        return response()->json([
            'success' => true,
            'message' => 'Government ID approved successfully',
        ]);
    }

    /**
     * Admin: Reject government ID
     */
    public function rejectGovernmentId(Request $request, $userId)
    {
        // Check if user is admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = \App\Models\User::findOrFail($userId);

        $user->update([
            'id_verification_status' => 'rejected',
            'id_rejection_reason' => $request->reason,
        ]);

        // TODO: Send notification to user with reason

        return response()->json([
            'success' => true,
            'message' => 'Government ID rejected',
        ]);
    }
}

