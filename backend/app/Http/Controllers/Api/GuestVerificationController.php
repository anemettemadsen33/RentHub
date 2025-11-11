<?php

namespace App\\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\GuestReference;
use App\Models\GuestVerification;
use App\Models\VerificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GuestVerificationController extends Controller
{
    /**
     * Get guest verification status
     */
    public function show(Request $request)
    {
        $verification = GuestVerification::with(['guestReferences', 'verificationLogs'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $verification) {
            return response()->json([
                'status' => 'not_started',
                'message' => 'No verification started yet',
            ]);
        }

        return response()->json([
            'verification' => $verification,
            'can_book' => $verification->canBook(),
            'is_fully_verified' => $verification->isFullyVerified(),
        ]);
    }

    /**
     * Submit identity verification documents
     */
    public function submitIdentity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_type' => 'required|in:passport,drivers_license,id_card,national_id',
            'document_number' => 'required|string|max:50',
            'document_front' => 'required|image|max:10240', // 10MB
            'document_back' => 'nullable|image|max:10240',
            'selfie_photo' => 'required|image|max:10240',
            'document_expiry_date' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        // Get or create verification record
        $verification = GuestVerification::firstOrCreate(
            ['user_id' => $user->id],
            ['identity_status' => 'pending']
        );

        // Store documents
        $documentFront = $request->file('document_front')->store('verifications/identity', 'public');
        $documentBack = $request->hasFile('document_back')
            ? $request->file('document_back')->store('verifications/identity', 'public')
            : null;
        $selfie = $request->file('selfie_photo')->store('verifications/selfie', 'public');

        $verification->update([
            'document_type' => $request->document_type,
            'document_number' => $request->document_number,
            'document_front' => $documentFront,
            'document_back' => $documentBack,
            'selfie_photo' => $selfie,
            'document_expiry_date' => $request->document_expiry_date,
            'identity_status' => 'pending',
        ]);

        // Log the action
        VerificationLog::log(
            $verification->id,
            'identity',
            'submitted',
            'Identity documents submitted for verification'
        );

        return response()->json([
            'message' => 'Identity documents submitted successfully',
            'verification' => $verification,
        ]);
    }

    /**
     * Add a reference
     */
    public function addReference(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference_name' => 'required|string|max:255',
            'reference_email' => 'required|email|max:255',
            'reference_phone' => 'nullable|string|max:20',
            'reference_type' => 'required|in:previous_landlord,employer,personal,other',
            'relationship' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        $verification = GuestVerification::firstOrCreate(
            ['user_id' => $user->id]
        );

        // Check reference limit
        if ($verification->guestReferences()->count() >= 5) {
            return response()->json([
                'message' => 'Maximum 5 references allowed',
            ], 422);
        }

        $allowedRelationships = [
            'previous_landlord', 'employer', 'colleague', 'friend', 'family', 'other',
        ];
        // Map incoming descriptive relationship text to enum; fallback to reference_type or 'other'
        $relationshipEnum = $request->reference_type; // primary source
        if ($request->filled('relationship')) {
            $candidate = strtolower(str_replace(' ', '_', $request->relationship));
            if (in_array($candidate, $allowedRelationships, true)) {
                $relationshipEnum = $candidate;
            }
        }

        if (! in_array($relationshipEnum, $allowedRelationships, true)) {
            $relationshipEnum = 'other';
        }

        $reference = $verification->guestReferences()->create([
            'user_id' => $user->id,
            'reference_name' => $request->reference_name,
            'reference_email' => $request->reference_email,
            'reference_phone' => $request->reference_phone,
            'reference_type' => $request->reference_type,
            'relationship' => $relationshipEnum,
            'relationship_description' => $request->relationship, // store original descriptive text
            'status' => 'pending',
        ]);

        // Send verification request to reference
        // TODO: Implement email notification
        $reference->sendVerificationRequest();

        VerificationLog::log(
            $verification->id,
            'reference',
            'submitted',
            "Reference added: {$request->reference_name}"
        );

        return response()->json([
            'message' => 'Reference added successfully',
            'reference' => $reference,
        ]);
    }

    /**
     * Request credit check
     */
    public function requestCreditCheck(Request $request)
    {
        $user = $request->user();

        $verification = GuestVerification::firstOrCreate(
            ['user_id' => $user->id]
        );

        if ($verification->credit_check_enabled && $verification->credit_status !== 'not_requested') {
            return response()->json([
                'message' => 'Credit check already requested',
            ], 422);
        }

        $verification->update([
            'credit_check_enabled' => true,
            'credit_status' => 'pending',
        ]);

        // TODO: Integrate with credit check API (Experian, Equifax, etc.)

        VerificationLog::log(
            $verification->id,
            'credit',
            'submitted',
            'Credit check requested'
        );

        return response()->json([
            'message' => 'Credit check requested successfully',
            'verification' => $verification,
        ]);
    }

    /**
     * Get verification statistics
     */
    public function statistics(Request $request)
    {
        $verification = GuestVerification::where('user_id', $request->user()->id)->first();

        if (! $verification) {
            return response()->json([
                'trust_score' => 0,
                'completed_bookings' => 0,
                'positive_reviews' => 0,
                'verification_level' => 'none',
            ]);
        }

        $verificationLevel = 'basic';
        if ($verification->isFullyVerified()) {
            $verificationLevel = 'full';
        } elseif ($verification->identity_status === 'verified') {
            $verificationLevel = 'verified';
        }

        return response()->json([
            'trust_score' => $verification->trust_score,
            'completed_bookings' => $verification->completed_bookings,
            'cancelled_bookings' => $verification->cancelled_bookings,
            'positive_reviews' => $verification->positive_reviews,
            'negative_reviews' => $verification->negative_reviews,
            'verification_level' => $verificationLevel,
            'identity_verified' => $verification->identity_status === 'verified',
            'background_clear' => $verification->background_status === 'clear',
            'credit_approved' => $verification->credit_status === 'approved',
            'references_count' => $verification->guestReferences()->verified()->count(),
        ]);
    }

    /**
     * Verify reference (public endpoint with token)
     */
    public function verifyReference(Request $request, string $token)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reference = GuestReference::where('verification_token', $token)->first();

        if (! $reference) {
            return response()->json(['message' => 'Invalid verification token'], 404);
        }

        if ($reference->status === 'verified') {
            return response()->json(['message' => 'Reference already verified'], 422);
        }

        $reference->verify($request->rating, $request->comments);

        VerificationLog::log(
            $reference->guest_verification_id,
            'reference',
            'approved',
            "Reference verified by {$reference->reference_name} with rating {$request->rating}"
        );

        return response()->json([
            'message' => 'Thank you for verifying the reference',
            'reference' => $reference,
        ]);
    }
}

