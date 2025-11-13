<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyVerification;
use App\Models\VerificationDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PropertyVerificationController extends Controller
{
    /**
     * Get property verification status
     */
    public function show(Request $request, $propertyId): JsonResponse
    {
        $user = $request->user();
        $property = Property::findOrFail($propertyId);

        if ($property->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $verification = $property->verification ?? PropertyVerification::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'verification' => $verification,
                'documents' => $verification->documents()->get(),
            ],
        ]);
    }

    /**
     * Submit ownership verification
     */
    public function submitOwnership(Request $request, $propertyId): JsonResponse
    {
        $user = $request->user();
        $property = Property::findOrFail($propertyId);

        if ($property->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'ownership_document_type' => ['required', Rule::in(['deed', 'lease_agreement', 'rental_contract'])],
            'documents' => 'required|array|min:1',
            'documents.*' => 'file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $verification = $property->verification ?? PropertyVerification::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
        ]);

        $documentPaths = [];
        foreach ($request->file('documents') as $file) {
            $path = $file->store('verifications/ownership-documents', 'public');
            $documentPaths[] = $path;

            VerificationDocument::create([
                'verifiable_type' => PropertyVerification::class,
                'verifiable_id' => $verification->id,
                'document_type' => 'ownership_deed',
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => $user->id,
                'status' => 'pending',
            ]);
        }

        $verification->ownership_document_type = $validated['ownership_document_type'];
        $verification->ownership_documents = $documentPaths;
        $verification->ownership_status = 'under_review';
        $verification->save();

        return response()->json([
            'success' => true,
            'message' => 'Ownership documents submitted successfully',
            'data' => $verification,
        ]);
    }

    /**
     * Submit legal documents (business license, safety certificate, insurance)
     */
    public function submitLegalDocuments(Request $request, $propertyId): JsonResponse
    {
        $user = $request->user();
        $property = Property::findOrFail($propertyId);

        if ($property->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'has_business_license' => 'boolean',
            'business_license_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'has_safety_certificate' => 'boolean',
            'safety_certificate_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'has_insurance' => 'boolean',
            'insurance_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'insurance_expiry_date' => 'nullable|date|after:today',
        ]);

        $verification = $property->verification ?? PropertyVerification::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
        ]);

        if ($request->has('has_business_license')) {
            $verification->has_business_license = $validated['has_business_license'];
            if ($request->hasFile('business_license_document')) {
                $path = $request->file('business_license_document')->store('verifications/licenses', 'public');
                $verification->business_license_document = $path;

                VerificationDocument::create([
                    'verifiable_type' => PropertyVerification::class,
                    'verifiable_id' => $verification->id,
                    'document_type' => 'business_license',
                    'file_path' => $path,
                    'file_name' => $request->file('business_license_document')->getClientOriginalName(),
                    'file_type' => $request->file('business_license_document')->getMimeType(),
                    'file_size' => $request->file('business_license_document')->getSize(),
                    'uploaded_by' => $user->id,
                    'status' => 'pending',
                ]);
            }
        }

        if ($request->has('has_safety_certificate')) {
            $verification->has_safety_certificate = $validated['has_safety_certificate'];
            if ($request->hasFile('safety_certificate_document')) {
                $path = $request->file('safety_certificate_document')->store('verifications/certificates', 'public');
                $verification->safety_certificate_document = $path;

                VerificationDocument::create([
                    'verifiable_type' => PropertyVerification::class,
                    'verifiable_id' => $verification->id,
                    'document_type' => 'safety_certificate',
                    'file_path' => $path,
                    'file_name' => $request->file('safety_certificate_document')->getClientOriginalName(),
                    'file_type' => $request->file('safety_certificate_document')->getMimeType(),
                    'file_size' => $request->file('safety_certificate_document')->getSize(),
                    'uploaded_by' => $user->id,
                    'status' => 'pending',
                ]);
            }
        }

        if ($request->has('has_insurance')) {
            $verification->has_insurance = $validated['has_insurance'];
            if ($request->hasFile('insurance_document')) {
                $path = $request->file('insurance_document')->store('verifications/insurance', 'public');
                $verification->insurance_document = $path;

                if (isset($validated['insurance_expiry_date'])) {
                    $verification->insurance_expiry_date = $validated['insurance_expiry_date'];
                }

                VerificationDocument::create([
                    'verifiable_type' => PropertyVerification::class,
                    'verifiable_id' => $verification->id,
                    'document_type' => 'insurance_document',
                    'file_path' => $path,
                    'file_name' => $request->file('insurance_document')->getClientOriginalName(),
                    'file_type' => $request->file('insurance_document')->getMimeType(),
                    'file_size' => $request->file('insurance_document')->getSize(),
                    'uploaded_by' => $user->id,
                    'metadata' => ['expiry_date' => $validated['insurance_expiry_date'] ?? null],
                    'status' => 'pending',
                ]);
            }
        }

        $verification->save();
        $verification->updateOverallStatus();

        return response()->json([
            'success' => true,
            'message' => 'Legal documents submitted successfully',
            'data' => $verification,
        ]);
    }

    /**
     * Request property inspection
     */
    public function requestInspection(Request $request, $propertyId): JsonResponse
    {
        $user = $request->user();
        $property = Property::findOrFail($propertyId);

        if ($property->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $verification = $property->verification ?? PropertyVerification::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
        ]);

        if (! $verification->canScheduleInspection()) {
            return response()->json([
                'success' => false,
                'message' => 'Property inspection cannot be requested at this time',
            ], 400);
        }

        $verification->inspection_status = 'pending';
        $verification->save();

        return response()->json([
            'success' => true,
            'message' => 'Property inspection requested successfully',
            'data' => $verification,
        ]);
    }

    /**
     * Get all verifications for user's properties
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $verifications = PropertyVerification::where('user_id', $user->id)
            ->with(['property', 'documents'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $verifications,
        ]);
    }
}
