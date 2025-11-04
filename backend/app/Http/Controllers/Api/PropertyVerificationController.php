<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PropertyVerification;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PropertyVerificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->isAdmin()) {
            $verifications = PropertyVerification::with(['property', 'user', 'reviewer'])
                ->when($request->status, function ($query, $status) {
                    return $query->where('overall_status', $status);
                })
                ->when($request->ownership_status, function ($query, $status) {
                    return $query->where('ownership_status', $status);
                })
                ->latest()
                ->paginate($request->per_page ?? 15);
        } else {
            $verifications = PropertyVerification::where('user_id', $user->id)
                ->with(['property', 'reviewer'])
                ->latest()
                ->paginate($request->per_page ?? 15);
        }

        return response()->json($verifications);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $verification = PropertyVerification::with(['property', 'user', 'reviewer'])->findOrFail($id);
        
        if (!$user->isAdmin() && $verification->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($verification);
    }

    public function getPropertyVerification(Request $request, $propertyId): JsonResponse
    {
        $user = $request->user();
        $property = Property::findOrFail($propertyId);
        
        if (!$user->isAdmin() && $property->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $verification = PropertyVerification::firstOrCreate(
            ['property_id' => $propertyId],
            ['user_id' => $property->user_id]
        );

        return response()->json($verification);
    }

    public function submitOwnershipDocuments(Request $request, $propertyId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ownership_document_type' => 'required|in:deed,lease_agreement,rental_contract,title_certificate',
            'ownership_documents' => 'required|array',
            'ownership_documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $property = Property::findOrFail($propertyId);
        
        if ($property->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $verification = PropertyVerification::firstOrCreate(
            ['property_id' => $propertyId],
            ['user_id' => $user->id]
        );

        // Upload documents
        $documents = [];
        foreach ($request->file('ownership_documents') as $file) {
            $path = $file->store('verifications/ownership/' . $propertyId, 'public');
            $documents[] = $path;
        }

        $verification->update([
            'ownership_document_type' => $request->ownership_document_type,
            'ownership_documents' => $documents,
            'ownership_status' => 'under_review',
        ]);

        $verification->updateOverallStatus();

        return response()->json([
            'message' => 'Ownership documents submitted successfully',
            'verification' => $verification
        ], 200);
    }

    public function submitLegalDocuments(Request $request, $propertyId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'has_business_license' => 'required|boolean',
            'business_license_document' => 'required_if:has_business_license,true|nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'has_safety_certificate' => 'required|boolean',
            'safety_certificate_document' => 'required_if:has_safety_certificate,true|nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'has_insurance' => 'required|boolean',
            'insurance_document' => 'required_if:has_insurance,true|nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'insurance_expiry_date' => 'required_if:has_insurance,true|nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $property = Property::findOrFail($propertyId);
        
        if ($property->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $verification = PropertyVerification::firstOrCreate(
            ['property_id' => $propertyId],
            ['user_id' => $user->id]
        );

        $updateData = [
            'has_business_license' => $request->has_business_license,
            'has_safety_certificate' => $request->has_safety_certificate,
            'has_insurance' => $request->has_insurance,
        ];

        if ($request->has_business_license && $request->hasFile('business_license_document')) {
            $updateData['business_license_document'] = $request->file('business_license_document')
                ->store('verifications/business-license/' . $propertyId, 'public');
        }

        if ($request->has_safety_certificate && $request->hasFile('safety_certificate_document')) {
            $updateData['safety_certificate_document'] = $request->file('safety_certificate_document')
                ->store('verifications/safety-certificate/' . $propertyId, 'public');
        }

        if ($request->has_insurance) {
            if ($request->hasFile('insurance_document')) {
                $updateData['insurance_document'] = $request->file('insurance_document')
                    ->store('verifications/insurance/' . $propertyId, 'public');
            }
            $updateData['insurance_expiry_date'] = $request->insurance_expiry_date;
        }

        $verification->update($updateData);
        $verification->updateOverallStatus();

        return response()->json([
            'message' => 'Legal documents submitted successfully',
            'verification' => $verification
        ], 200);
    }

    public function requestInspection(Request $request, $propertyId): JsonResponse
    {
        $user = $request->user();
        $property = Property::findOrFail($propertyId);
        
        if ($property->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $verification = PropertyVerification::firstOrCreate(
            ['property_id' => $propertyId],
            ['user_id' => $user->id]
        );

        if ($verification->ownership_status !== 'approved') {
            return response()->json([
                'message' => 'Ownership must be verified before requesting inspection'
            ], 422);
        }

        $verification->update([
            'inspection_status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Inspection requested successfully',
            'verification' => $verification
        ], 200);
    }

    // Admin endpoints
    public function approveOwnership(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $verification = PropertyVerification::findOrFail($id);
        
        $verification->update([
            'ownership_status' => 'approved',
            'ownership_verified_at' => now(),
            'ownership_rejection_reason' => null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $verification->updateOverallStatus();

        return response()->json([
            'message' => 'Ownership verification approved',
            'verification' => $verification
        ]);
    }

    public function rejectOwnership(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $verification = PropertyVerification::findOrFail($id);
        
        $verification->update([
            'ownership_status' => 'rejected',
            'ownership_rejection_reason' => $request->reason,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $verification->updateOverallStatus();

        return response()->json([
            'message' => 'Ownership verification rejected',
            'verification' => $verification
        ]);
    }

    public function approvePhotos(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $verification = PropertyVerification::findOrFail($id);
        
        $verification->update([
            'photos_status' => 'approved',
            'photos_verified_at' => now(),
            'photos_rejection_reason' => null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $verification->updateOverallStatus();

        return response()->json([
            'message' => 'Photos approved',
            'verification' => $verification
        ]);
    }

    public function rejectPhotos(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $verification = PropertyVerification::findOrFail($id);
        
        $verification->update([
            'photos_status' => 'rejected',
            'photos_rejection_reason' => $request->reason,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $verification->updateOverallStatus();

        return response()->json([
            'message' => 'Photos rejected',
            'verification' => $verification
        ]);
    }

    public function approveDetails(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $verification = PropertyVerification::findOrFail($id);
        
        $verification->update([
            'details_status' => 'approved',
            'details_verified_at' => now(),
            'details_to_correct' => null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $verification->updateOverallStatus();

        return response()->json([
            'message' => 'Property details approved',
            'verification' => $verification
        ]);
    }

    public function rejectDetails(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'details_to_correct' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $verification = PropertyVerification::findOrFail($id);
        
        $verification->update([
            'details_status' => 'rejected',
            'details_to_correct' => $request->details_to_correct,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $verification->updateOverallStatus();

        return response()->json([
            'message' => 'Property details need corrections',
            'verification' => $verification
        ]);
    }

    public function scheduleInspection(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'inspection_scheduled_at' => 'required|date|after:now',
            'inspector_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $verification = PropertyVerification::findOrFail($id);
        
        $verification->update([
            'inspection_status' => 'scheduled',
            'inspection_scheduled_at' => $request->inspection_scheduled_at,
            'inspector_id' => $request->inspector_id,
            'reviewed_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Inspection scheduled successfully',
            'verification' => $verification
        ]);
    }

    public function completeInspection(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'inspection_score' => 'required|integer|min:0|max:100',
            'inspection_notes' => 'nullable|string',
            'inspection_report' => 'nullable|array',
            'status' => 'required|in:completed,failed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $verification = PropertyVerification::findOrFail($id);
        
        $verification->update([
            'inspection_status' => $request->status,
            'inspection_completed_at' => now(),
            'inspection_score' => $request->inspection_score,
            'inspection_notes' => $request->inspection_notes,
            'inspection_report' => $request->inspection_report,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $verification->updateOverallStatus();

        return response()->json([
            'message' => 'Inspection completed',
            'verification' => $verification
        ]);
    }

    public function grantVerifiedBadge(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $verification = PropertyVerification::findOrFail($id);
        
        if ($verification->overall_status !== 'verified') {
            return response()->json([
                'message' => 'Property must be fully verified before granting badge'
            ], 422);
        }
        
        $verification->update([
            'has_verified_badge' => true,
            'last_verified_at' => now(),
            'next_verification_due' => now()->addYear(),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Verified badge granted',
            'verification' => $verification
        ]);
    }

    public function revokeVerifiedBadge(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $verification = PropertyVerification::findOrFail($id);
        
        $verification->update([
            'has_verified_badge' => false,
            'admin_notes' => $request->reason,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Verified badge revoked',
            'verification' => $verification
        ]);
    }

    public function getStatistics(Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $stats = [
            'total_verifications' => PropertyVerification::count(),
            'verified' => PropertyVerification::where('overall_status', 'verified')->count(),
            'under_review' => PropertyVerification::where('overall_status', 'under_review')->count(),
            'unverified' => PropertyVerification::where('overall_status', 'unverified')->count(),
            'with_badge' => PropertyVerification::where('has_verified_badge', true)->count(),
            'pending_ownership_review' => PropertyVerification::where('ownership_status', 'under_review')->count(),
            'pending_inspections' => PropertyVerification::where('inspection_status', 'pending')->count(),
            'scheduled_inspections' => PropertyVerification::where('inspection_status', 'scheduled')->count(),
        ];

        return response()->json($stats);
    }
}
