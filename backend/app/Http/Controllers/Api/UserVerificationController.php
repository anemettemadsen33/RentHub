<?php

namespace App\Http\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\UserVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserVerificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $verifications = UserVerification::with(['user', 'reviewer'])
                ->when($request->status, function ($query, $status) {
                    return $query->where('overall_status', $status);
                })
                ->when($request->id_status, function ($query, $status) {
                    return $query->where('id_verification_status', $status);
                })
                ->latest()
                ->paginate($request->per_page ?? 15);
        } else {
            $verifications = UserVerification::where('user_id', $user->id)
                ->with(['reviewer'])
                ->get();
        }

        return response()->json($verifications);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $verification = UserVerification::with(['user', 'reviewer'])->findOrFail($id);

        if (! $user->isAdmin() && $verification->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($verification);
    }

    public function getMyVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        $verification = UserVerification::firstOrCreate(
            ['user_id' => $user->id],
            [
                'email_verification_status' => $user->email_verified_at ? 'verified' : 'pending',
                'email_verified_at' => $user->email_verified_at,
            ]
        );

        return response()->json($verification);
    }

    public function submitIdVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_document_type' => 'required|in:passport,driving_license,national_id',
            'id_document_number' => 'required|string|max:100',
            'id_front_image' => 'required|image|max:10240',
            'id_back_image' => 'nullable|image|max:10240',
            'selfie_image' => 'required|image|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $verification = UserVerification::firstOrCreate(['user_id' => $user->id]);

        // Upload images
        $idFrontPath = $request->file('id_front_image')->store('verifications/id/'.$user->id, 'public');
        $selfiePath = $request->file('selfie_image')->store('verifications/selfie/'.$user->id, 'public');

        $idBackPath = null;
        if ($request->hasFile('id_back_image')) {
            $idBackPath = $request->file('id_back_image')->store('verifications/id/'.$user->id, 'public');
        }

        // Update verification
        $verification->update([
            'id_document_type' => $request->id_document_type,
            'id_document_number' => $request->id_document_number,
            'id_front_image' => $idFrontPath,
            'id_back_image' => $idBackPath,
            'selfie_image' => $selfiePath,
            'id_verification_status' => 'under_review',
        ]);

        $verification->updateOverallStatus();

        return response()->json([
            'message' => 'ID verification submitted successfully',
            'verification' => $verification,
        ], 200);
    }

    public function submitPhoneVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $verification = UserVerification::firstOrCreate(['user_id' => $user->id]);

        // Generate verification code
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        $verification->update([
            'phone_number' => $request->phone_number,
            'phone_verification_code' => $code,
            'phone_verification_code_sent_at' => now(),
            'phone_verification_status' => 'pending',
        ]);

        // TODO: Send SMS with code using Twilio or similar service
        // For now, we'll return the code in development mode
        $response = ['message' => 'Verification code sent to your phone'];

        if (config('app.debug')) {
            $response['code'] = $code;
        }

        return response()->json($response, 200);
    }

    public function verifyPhoneCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $verification = UserVerification::where('user_id', $user->id)->firstOrFail();

        if ($verification->phone_verification_code !== $request->code) {
            return response()->json(['message' => 'Invalid verification code'], 422);
        }

        // Check if code is not expired (valid for 10 minutes)
        if ($verification->phone_verification_code_sent_at->diffInMinutes(now()) > 10) {
            return response()->json(['message' => 'Verification code expired'], 422);
        }

        $verification->update([
            'phone_verification_status' => 'verified',
            'phone_verified_at' => now(),
            'phone_verification_code' => null,
        ]);

        $verification->updateOverallStatus();

        return response()->json([
            'message' => 'Phone verified successfully',
            'verification' => $verification,
        ], 200);
    }

    public function submitAddressVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string|max:500',
            'address_proof_document' => 'required|in:utility_bill,bank_statement,rental_agreement,tax_document',
            'address_proof_image' => 'required|image|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $verification = UserVerification::firstOrCreate(['user_id' => $user->id]);

        // Upload document
        $addressProofPath = $request->file('address_proof_image')->store('verifications/address/'.$user->id, 'public');

        $verification->update([
            'address' => $request->address,
            'address_proof_document' => $request->address_proof_document,
            'address_proof_image' => $addressProofPath,
            'address_verification_status' => 'under_review',
        ]);

        $verification->updateOverallStatus();

        return response()->json([
            'message' => 'Address verification submitted successfully',
            'verification' => $verification,
        ], 200);
    }

    public function requestBackgroundCheck(Request $request): JsonResponse
    {
        $user = $request->user();
        $verification = UserVerification::where('user_id', $user->id)->firstOrFail();

        if (! $verification->canRequestBackgroundCheck()) {
            return response()->json([
                'message' => 'ID verification must be approved before requesting background check',
            ], 422);
        }

        $verification->update([
            'background_check_status' => 'pending',
            'background_check_provider' => 'system',
            'background_check_reference' => 'BG-'.Str::random(10),
        ]);

        return response()->json([
            'message' => 'Background check requested successfully',
            'verification' => $verification,
        ], 200);
    }

    // Admin endpoints
    public function approveId(Request $request, $id): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $verification = UserVerification::findOrFail($id);

        $verification->update([
            'id_verification_status' => 'approved',
            'id_verified_at' => now(),
            'id_rejection_reason' => null,
            'reviewed_by' => $request->user()->id,
        ]);

        $verification->updateOverallStatus();

        return response()->json([
            'message' => 'ID verification approved',
            'verification' => $verification,
        ]);
    }

    public function rejectId(Request $request, $id): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $verification = UserVerification::findOrFail($id);

        $verification->update([
            'id_verification_status' => 'rejected',
            'id_rejection_reason' => $request->reason,
            'reviewed_by' => $request->user()->id,
        ]);

        $verification->updateOverallStatus();

        return response()->json([
            'message' => 'ID verification rejected',
            'verification' => $verification,
        ]);
    }

    public function approveAddress(Request $request, $id): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $verification = UserVerification::findOrFail($id);

        $verification->update([
            'address_verification_status' => 'approved',
            'address_verified_at' => now(),
            'address_rejection_reason' => null,
            'reviewed_by' => $request->user()->id,
        ]);

        $verification->updateOverallStatus();

        return response()->json([
            'message' => 'Address verification approved',
            'verification' => $verification,
        ]);
    }

    public function rejectAddress(Request $request, $id): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $verification = UserVerification::findOrFail($id);

        $verification->update([
            'address_verification_status' => 'rejected',
            'address_rejection_reason' => $request->reason,
            'reviewed_by' => $request->user()->id,
        ]);

        $verification->updateOverallStatus();

        return response()->json([
            'message' => 'Address verification rejected',
            'verification' => $verification,
        ]);
    }

    public function completeBackgroundCheck(Request $request, $id): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:completed,failed',
            'result' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $verification = UserVerification::findOrFail($id);

        $verification->update([
            'background_check_status' => $request->status,
            'background_check_result' => $request->result,
            'background_check_completed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ]);

        $verification->updateOverallStatus();

        return response()->json([
            'message' => 'Background check completed',
            'verification' => $verification,
        ]);
    }

    public function getStatistics(Request $request): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $stats = [
            'total_verifications' => UserVerification::count(),
            'fully_verified' => UserVerification::where('overall_status', 'fully_verified')->count(),
            'partially_verified' => UserVerification::where('overall_status', 'partially_verified')->count(),
            'unverified' => UserVerification::where('overall_status', 'unverified')->count(),
            'pending_id_review' => UserVerification::where('id_verification_status', 'under_review')->count(),
            'pending_address_review' => UserVerification::where('address_verification_status', 'under_review')->count(),
            'background_checks_pending' => UserVerification::where('background_check_status', 'pending')->count(),
        ];

        return response()->json($stats);
    }
}

