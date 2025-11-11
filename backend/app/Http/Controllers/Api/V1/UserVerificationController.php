<?php

namespace App\\Http\\Controllers\\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserVerification;
use App\Models\VerificationDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserVerificationController extends Controller
{
    /**
     * Get user's verification status
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $verification = $user->verification ?? UserVerification::create(['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'data' => [
                'verification' => $verification,
                'documents' => $verification->documents()->get(),
            ],
        ]);
    }

    /**
     * Submit ID verification
     */
    public function submitIdVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'id_document_type' => ['required', Rule::in(['passport', 'driving_license', 'national_id'])],
            'id_document_number' => 'required|string|max:50',
            'id_front_image' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'id_back_image' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'selfie_image' => 'required|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        $verification = $user->verification ?? UserVerification::create(['user_id' => $user->id]);

        $frontPath = $request->file('id_front_image')->store('verifications/id-documents', 'public');
        $verification->id_front_image = $frontPath;

        if ($request->hasFile('id_back_image')) {
            $backPath = $request->file('id_back_image')->store('verifications/id-documents', 'public');
            $verification->id_back_image = $backPath;
        }

        $selfiePath = $request->file('selfie_image')->store('verifications/selfies', 'public');
        $verification->selfie_image = $selfiePath;

        $verification->id_document_type = $validated['id_document_type'];
        $verification->id_document_number = $validated['id_document_number'];
        $verification->id_verification_status = 'under_review';
        $verification->save();

        return response()->json([
            'success' => true,
            'message' => 'ID verification submitted successfully',
            'data' => $verification,
        ]);
    }

    /**
     * Verify phone number - send code
     */
    public function sendPhoneVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
        ]);

        $verification = $user->verification ?? UserVerification::create(['user_id' => $user->id]);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $verification->phone_number = $validated['phone_number'];
        $verification->phone_verification_code = $code;
        $verification->phone_verification_code_sent_at = now();
        $verification->save();

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent to phone',
            'code' => config('app.debug') ? $code : null,
        ]);
    }

    /**
     * Verify phone number - confirm code
     */
    public function verifyPhone(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $verification = $user->verification;

        if (! $verification || ! $verification->phone_verification_code) {
            return response()->json([
                'success' => false,
                'message' => 'No verification code found. Please request a new code.',
            ], 400);
        }

        if ($verification->phone_verification_code_sent_at->diffInMinutes(now()) > 10) {
            return response()->json([
                'success' => false,
                'message' => 'Verification code expired. Please request a new code.',
            ], 400);
        }

        if ($verification->phone_verification_code !== $validated['code']) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code.',
            ], 400);
        }

        $verification->phone_verification_status = 'verified';
        $verification->phone_verified_at = now();
        $verification->phone_verification_code = null;
        $verification->updateOverallStatus();

        return response()->json([
            'success' => true,
            'message' => 'Phone number verified successfully',
            'data' => $verification,
        ]);
    }

    /**
     * Submit address verification
     */
    public function submitAddressVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'address' => 'required|string|max:500',
            'address_proof_document' => ['required', Rule::in(['utility_bill', 'bank_statement', 'rental_contract', 'other'])],
            'address_proof_image' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $verification = $user->verification ?? UserVerification::create(['user_id' => $user->id]);

        $proofPath = $request->file('address_proof_image')->store('verifications/address-proofs', 'public');

        $verification->address = $validated['address'];
        $verification->address_proof_document = $validated['address_proof_document'];
        $verification->address_proof_image = $proofPath;
        $verification->address_verification_status = 'under_review';
        $verification->save();

        return response()->json([
            'success' => true,
            'message' => 'Address verification submitted successfully',
            'data' => $verification,
        ]);
    }

    /**
     * Request background check
     */
    public function requestBackgroundCheck(Request $request): JsonResponse
    {
        $user = $request->user();
        $verification = $user->verification;

        if (! $verification || ! $verification->canRequestBackgroundCheck()) {
            return response()->json([
                'success' => false,
                'message' => 'You must complete ID verification first',
            ], 400);
        }

        $verification->background_check_status = 'pending';
        $verification->save();

        return response()->json([
            'success' => true,
            'message' => 'Background check requested successfully',
            'data' => $verification,
        ]);
    }

    /**
     * Upload additional document
     */
    public function uploadDocument(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'document_type' => ['required', Rule::in([
                'id_card', 'passport', 'driving_license', 'selfie',
                'address_proof', 'bank_statement', 'other',
            ])],
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'metadata' => 'nullable|array',
        ]);

        $verification = $user->verification ?? UserVerification::create(['user_id' => $user->id]);

        $file = $request->file('file');
        $path = $file->store('verifications/documents', 'public');

        $document = VerificationDocument::create([
            'verifiable_type' => UserVerification::class,
            'verifiable_id' => $verification->id,
            'document_type' => $validated['document_type'],
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => $user->id,
            'metadata' => $validated['metadata'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully',
            'data' => $document,
        ]);
    }

    /**
     * Delete document
     */
    public function destroy(Request $request, $documentId): JsonResponse
    {
        $user = $request->user();
        $document = VerificationDocument::findOrFail($documentId);

        if ($document->uploaded_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($document->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete reviewed documents',
            ], 400);
        }

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully',
        ]);
    }
}

