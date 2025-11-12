<?php

namespace App\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\GuestScreening;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GuestScreeningController extends Controller
{
    public function index(Request $request)
    {
        $query = GuestScreening::with(['user', 'booking', 'reviewer', 'documents', 'creditCheck', 'references']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $screenings = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json($screenings);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'booking_id' => 'nullable|exists:bookings,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $screening = GuestScreening::create([
            'user_id' => $request->user_id,
            'booking_id' => $request->booking_id,
            'status' => 'pending',
            'risk_level' => 'unknown',
            'expires_at' => now()->addDays(30),
        ]);

        $user = User::find($request->user_id);
        if ($user->email_verified_at) {
            $screening->email_verified = true;
            $screening->email_verified_at = $user->email_verified_at;
        }
        if ($user->phone_verified_at ?? false) {
            $screening->phone_verified = true;
            $screening->phone_verified_at = $user->phone_verified_at;
        }
        $screening->save();

        return response()->json([
            'message' => 'Guest screening initiated successfully',
            'screening' => $screening->load(['user', 'documents', 'creditCheck', 'references']),
        ], 201);
    }

    public function show($id)
    {
        $screening = GuestScreening::with(['user', 'booking', 'reviewer', 'documents', 'creditCheck', 'references'])
            ->findOrFail($id);

        return response()->json($screening);
    }

    public function update(Request $request, $id)
    {
        $screening = GuestScreening::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:pending,in_progress,approved,rejected,expired',
            'risk_level' => 'sometimes|in:low,medium,high,unknown',
            'admin_notes' => 'nullable|string',
            'rejection_reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $screening->update($request->only([
            'status',
            'risk_level',
            'admin_notes',
            'rejection_reason',
        ]));

        if ($request->status === 'approved') {
            $screening->completed_at = now();
        }

        $screening->save();

        return response()->json([
            'message' => 'Guest screening updated successfully',
            'screening' => $screening->load(['user', 'documents', 'creditCheck', 'references']),
        ]);
    }

    public function destroy($id)
    {
        $screening = GuestScreening::findOrFail($id);
        $screening->delete();

        return response()->json([
            'message' => 'Guest screening deleted successfully',
        ]);
    }

    public function verifyIdentity(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'method' => 'required|in:passport,id_card,drivers_license',
            'verified_by' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $screening = GuestScreening::findOrFail($id);
        $screening->update([
            'identity_verified' => true,
            'identity_verified_at' => now(),
            'identity_verification_method' => $request->method,
            'reviewed_by' => $request->verified_by,
            'status' => 'in_progress',
        ]);

        $screening->screening_score = $screening->calculateScreeningScore();
        $screening->risk_level = $screening->determineRiskLevel();
        $screening->save();

        return response()->json([
            'message' => 'Identity verified successfully',
            'screening' => $screening,
        ]);
    }

    public function verifyPhone(Request $request, $id)
    {
        $screening = GuestScreening::findOrFail($id);
        $screening->update([
            'phone_verified' => true,
            'phone_verified_at' => now(),
            'status' => 'in_progress',
        ]);

        $screening->screening_score = $screening->calculateScreeningScore();
        $screening->risk_level = $screening->determineRiskLevel();
        $screening->save();

        return response()->json([
            'message' => 'Phone verified successfully',
            'screening' => $screening,
        ]);
    }

    public function calculateScore($id)
    {
        $screening = GuestScreening::findOrFail($id);
        $score = $screening->calculateScreeningScore();
        $riskLevel = $screening->determineRiskLevel();

        $screening->update([
            'screening_score' => $score,
            'risk_level' => $riskLevel,
        ]);

        return response()->json([
            'screening_id' => $screening->id,
            'score' => $score,
            'risk_level' => $riskLevel,
            'screening' => $screening->load(['user', 'documents', 'creditCheck', 'references']),
        ]);
    }

    public function statistics(Request $request)
    {
        $stats = [
            'total_screenings' => GuestScreening::count(),
            'pending' => GuestScreening::where('status', 'pending')->count(),
            'in_progress' => GuestScreening::where('status', 'in_progress')->count(),
            'approved' => GuestScreening::where('status', 'approved')->count(),
            'rejected' => GuestScreening::where('status', 'rejected')->count(),
            'by_risk_level' => [
                'low' => GuestScreening::where('risk_level', 'low')->count(),
                'medium' => GuestScreening::where('risk_level', 'medium')->count(),
                'high' => GuestScreening::where('risk_level', 'high')->count(),
            ],
            'avg_screening_score' => GuestScreening::whereNotNull('screening_score')->avg('screening_score'),
            'identity_verified' => GuestScreening::where('identity_verified', true)->count(),
            'phone_verified' => GuestScreening::where('phone_verified', true)->count(),
            'email_verified' => GuestScreening::where('email_verified', true)->count(),
            'credit_checks_completed' => GuestScreening::where('credit_check_completed', true)->count(),
        ];

        return response()->json($stats);
    }

    public function getUserScreenings($userId)
    {
        $screenings = GuestScreening::with(['booking', 'documents', 'creditCheck', 'references'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return response()->json($screenings);
    }

    public function getLatestScreening($userId)
    {
        $screening = GuestScreening::with(['user', 'booking', 'documents', 'creditCheck', 'references'])
            ->where('user_id', $userId)
            ->active()
            ->latest()
            ->first();

        if (! $screening) {
            return response()->json(['message' => 'No active screening found'], 404);
        }

        return response()->json($screening);
    }
}

