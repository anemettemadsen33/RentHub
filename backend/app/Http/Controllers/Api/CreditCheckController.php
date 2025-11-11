<?php

namespace App\\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\CreditCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CreditCheckController extends Controller
{
    public function index(Request $request)
    {
        $query = CreditCheck::with(['screening.user', 'user', 'requester']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('credit_rating')) {
            $query->where('credit_rating', $request->credit_rating);
        }

        $creditChecks = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json($creditChecks);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_screening_id' => 'required|exists:guest_screenings,id',
            'user_id' => 'required|exists:users,id',
            'requested_by' => 'required|exists:users,id',
            'provider' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $creditCheck = CreditCheck::create([
            'guest_screening_id' => $request->guest_screening_id,
            'user_id' => $request->user_id,
            'requested_by' => $request->requested_by,
            'provider' => $request->provider ?? 'manual',
            'status' => 'pending',
            'requested_at' => now(),
            'expires_at' => now()->addDays(90),
        ]);

        return response()->json([
            'message' => 'Credit check initiated successfully',
            'credit_check' => $creditCheck->load(['screening', 'user', 'requester']),
        ], 201);
    }

    public function show($id)
    {
        $creditCheck = CreditCheck::with(['screening.user', 'user', 'requester'])
            ->findOrFail($id);

        return response()->json($creditCheck);
    }

    public function update(Request $request, $id)
    {
        $creditCheck = CreditCheck::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:pending,completed,failed,expired',
            'credit_score' => 'nullable|integer|min:300|max:850',
            'credit_rating' => 'nullable|in:excellent,good,fair,poor,very_poor',
            'passed' => 'nullable|boolean',
            'report_data' => 'nullable|array',
            'total_accounts' => 'nullable|integer',
            'open_accounts' => 'nullable|integer',
            'total_debt' => 'nullable|numeric',
            'available_credit' => 'nullable|numeric',
            'credit_utilization' => 'nullable|numeric',
            'on_time_payments' => 'nullable|integer',
            'late_payments' => 'nullable|integer',
            'missed_payments' => 'nullable|integer',
            'defaults' => 'nullable|integer',
            'bankruptcies' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $creditCheck->fill($request->except(['guest_screening_id', 'user_id', 'requested_by']));

        if ($request->has('credit_score') && ! $request->has('credit_rating')) {
            $creditCheck->credit_rating = $creditCheck->calculateCreditRating();
        }

        if ($request->status === 'completed') {
            $creditCheck->completed_at = now();

            $screening = $creditCheck->screening;
            $screening->credit_check_completed = true;
            $screening->credit_check_completed_at = now();
            $screening->credit_score = $creditCheck->credit_score;
            $screening->credit_rating = $creditCheck->credit_rating;
            $screening->screening_score = $screening->calculateScreeningScore();
            $screening->risk_level = $screening->determineRiskLevel();
            $screening->save();
        }

        $creditCheck->save();

        return response()->json([
            'message' => 'Credit check updated successfully',
            'credit_check' => $creditCheck->load(['screening', 'user', 'requester']),
        ]);
    }

    public function destroy($id)
    {
        $creditCheck = CreditCheck::findOrFail($id);
        $creditCheck->delete();

        return response()->json([
            'message' => 'Credit check deleted successfully',
        ]);
    }

    public function simulateCheck(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'credit_score' => 'required|integer|min:300|max:850',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $creditCheck = CreditCheck::findOrFail($id);

        $score = $request->credit_score;
        $rating = $creditCheck->calculateCreditRating();

        $simulatedData = [
            'credit_score' => $score,
            'max_score' => 850,
            'credit_rating' => $rating,
            'total_accounts' => rand(5, 15),
            'open_accounts' => rand(3, 10),
            'total_debt' => rand(5000, 50000),
            'available_credit' => rand(10000, 100000),
            'credit_utilization' => rand(10, 60),
            'on_time_payments' => rand(80, 100),
            'late_payments' => rand(0, 5),
            'missed_payments' => rand(0, 3),
            'defaults' => rand(0, 2),
            'bankruptcies' => 0,
            'status' => 'completed',
            'passed' => $score >= 650,
            'completed_at' => now(),
            'report_data' => [
                'simulated' => true,
                'timestamp' => now()->toISOString(),
            ],
        ];

        $creditCheck->update($simulatedData);

        $screening = $creditCheck->screening;
        $screening->credit_check_completed = true;
        $screening->credit_check_completed_at = now();
        $screening->credit_score = $score;
        $screening->credit_rating = $rating;
        $screening->screening_score = $screening->calculateScreeningScore();
        $screening->risk_level = $screening->determineRiskLevel();
        $screening->save();

        return response()->json([
            'message' => 'Credit check simulated successfully',
            'credit_check' => $creditCheck->load(['screening', 'user']),
        ]);
    }

    public function getUserCreditChecks($userId)
    {
        $creditChecks = CreditCheck::with(['screening'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return response()->json($creditChecks);
    }

    public function getLatestCreditCheck($userId)
    {
        $creditCheck = CreditCheck::with(['screening'])
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->latest()
            ->first();

        if (! $creditCheck) {
            return response()->json(['message' => 'No credit check found'], 404);
        }

        return response()->json($creditCheck);
    }
}

