<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GuestReference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GuestReferenceController extends Controller
{
    public function index(Request $request)
    {
        $query = GuestReference::with(['screening.user', 'user']);

        if ($request->has('guest_screening_id')) {
            $query->where('guest_screening_id', $request->guest_screening_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('responded_only')) {
            $query->where('responded', true);
        }

        $references = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json($references);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_screening_id' => 'required|exists:guest_screenings,id',
            'user_id' => 'required|exists:users,id',
            'reference_name' => 'required|string|max:255',
            'reference_email' => 'required|email',
            'reference_phone' => 'nullable|string',
            'relationship' => 'required|in:previous_landlord,employer,colleague,friend,family,other',
            'relationship_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reference = GuestReference::create($request->all());

        $screening = $reference->screening;
        $screening->increment('references_count');

        return response()->json([
            'message' => 'Guest reference added successfully',
            'reference' => $reference->load(['screening', 'user']),
        ], 201);
    }

    public function show($id)
    {
        $reference = GuestReference::with(['screening.user', 'user'])
            ->findOrFail($id);

        return response()->json($reference);
    }

    public function update(Request $request, $id)
    {
        $reference = GuestReference::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'reference_name' => 'sometimes|string|max:255',
            'reference_email' => 'sometimes|email',
            'reference_phone' => 'nullable|string',
            'relationship' => 'sometimes|in:previous_landlord,employer,colleague,friend,family,other',
            'status' => 'sometimes|in:pending,contacted,verified,failed,expired',
            'verification_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reference->update($request->only([
            'reference_name',
            'reference_email',
            'reference_phone',
            'relationship',
            'relationship_description',
            'status',
            'verification_notes',
        ]));

        return response()->json([
            'message' => 'Guest reference updated successfully',
            'reference' => $reference->load(['screening', 'user']),
        ]);
    }

    public function destroy($id)
    {
        $reference = GuestReference::findOrFail($id);
        $screening = $reference->screening;

        $reference->delete();

        $screening->decrement('references_count');
        if ($reference->status === 'verified') {
            $screening->decrement('references_verified');
        }

        return response()->json([
            'message' => 'Guest reference deleted successfully',
        ]);
    }

    public function sendRequest($id)
    {
        $reference = GuestReference::findOrFail($id);

        $reference->sendVerificationRequest();

        return response()->json([
            'message' => 'Verification request sent successfully',
            'reference' => $reference,
        ]);
    }

    public function submitResponse(Request $request, $code)
    {
        $reference = GuestReference::where('verification_code', $code)->firstOrFail();

        if ($reference->isExpired()) {
            return response()->json(['message' => 'This reference request has expired'], 410);
        }

        if ($reference->responded) {
            return response()->json(['message' => 'Response already submitted'], 409);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string',
            'would_rent_again' => 'required|boolean',
            'reliable_tenant' => 'required|boolean',
            'damages_caused' => 'required|boolean',
            'payment_issues' => 'required|boolean',
            'strengths' => 'nullable|string',
            'concerns' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reference->submitResponse($request->all());

        return response()->json([
            'message' => 'Reference response submitted successfully',
            'reference' => $reference,
        ]);
    }

    public function getByCode($code)
    {
        $reference = GuestReference::with(['screening.user'])
            ->where('verification_code', $code)
            ->firstOrFail();

        if ($reference->isExpired()) {
            return response()->json(['message' => 'This reference request has expired'], 410);
        }

        return response()->json([
            'reference' => $reference,
            'guest_name' => $reference->user->name,
            'expired' => false,
            'responded' => $reference->responded,
        ]);
    }

    public function getScreeningReferences($screeningId)
    {
        $references = GuestReference::where('guest_screening_id', $screeningId)
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total' => $references->count(),
            'pending' => $references->where('status', 'pending')->count(),
            'contacted' => $references->where('status', 'contacted')->count(),
            'verified' => $references->where('status', 'verified')->count(),
            'responded' => $references->where('responded', true)->count(),
            'average_rating' => $references->where('responded', true)->avg('rating'),
        ];

        return response()->json([
            'references' => $references,
            'statistics' => $stats,
        ]);
    }

    public function resendRequest($id)
    {
        $reference = GuestReference::findOrFail($id);

        if ($reference->responded) {
            return response()->json(['message' => 'Reference already responded'], 409);
        }

        if ($reference->isExpired()) {
            $reference->expires_at = now()->addDays(14);
            $reference->save();
        }

        $reference->sendVerificationRequest();

        return response()->json([
            'message' => 'Verification request resent successfully',
            'reference' => $reference,
        ]);
    }

    public function markAsVerified(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string',
            'verification_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reference = GuestReference::findOrFail($id);

        $reference->update([
            'status' => 'verified',
            'responded' => true,
            'responded_at' => now(),
            'rating' => $request->rating,
            'comments' => $request->comments,
            'verification_notes' => $request->verification_notes,
        ]);

        $reference->screening->increment('references_verified');
        $reference->screening->screening_score = $reference->screening->calculateScreeningScore();
        $reference->screening->risk_level = $reference->screening->determineRiskLevel();
        $reference->screening->save();

        return response()->json([
            'message' => 'Reference marked as verified',
            'reference' => $reference,
        ]);
    }
}
