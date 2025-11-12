<?php

namespace App\Http\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\LongTermRental;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LongTermRentalController extends Controller
{
    public function index(Request $request)
    {
        $query = LongTermRental::with(['property', 'tenant', 'owner']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->has('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        if ($request->boolean('expiring_soon')) {
            $days = $request->get('days', 30);
            $query->expiringSoon($days);
        }

        if ($request->has('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        $perPage = $request->get('per_page', 15);
        $rentals = $query->latest()->paginate($perPage);

        return response()->json($rentals);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'tenant_id' => 'required|exists:users,id',
            'start_date' => 'required|date|after:today',
            'duration_months' => 'required|integer|min:1|max:120',
            'rental_type' => 'required|in:monthly,quarterly,yearly',
            'monthly_rent' => 'required|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
            'payment_frequency' => 'required|in:monthly,quarterly,yearly',
            'payment_day_of_month' => 'nullable|integer|min:1|max:28',
            'utilities_included' => 'nullable|array',
            'utilities_paid_by_tenant' => 'nullable|array',
            'utilities_estimate' => 'nullable|numeric|min:0',
            'maintenance_included' => 'boolean',
            'maintenance_terms' => 'nullable|string',
            'auto_renewable' => 'boolean',
            'renewal_notice_days' => 'nullable|integer|min:7|max:90',
            'special_terms' => 'nullable|string',
            'house_rules' => 'nullable|array',
            'pets_allowed' => 'boolean',
            'smoking_allowed' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $property = Property::findOrFail($request->property_id);

        $startDate = Carbon::parse($request->start_date);
        $endDate = $startDate->copy()->addMonths($request->duration_months);

        $totalRent = $request->monthly_rent * $request->duration_months;

        $rental = LongTermRental::create([
            'property_id' => $property->id,
            'tenant_id' => $request->tenant_id,
            'owner_id' => $property->user_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_months' => $request->duration_months,
            'rental_type' => $request->rental_type,
            'monthly_rent' => $request->monthly_rent,
            'security_deposit' => $request->security_deposit,
            'total_rent' => $totalRent,
            'payment_frequency' => $request->payment_frequency,
            'payment_day_of_month' => $request->get('payment_day_of_month', 1),
            'utilities_included' => $request->get('utilities_included', []),
            'utilities_paid_by_tenant' => $request->get('utilities_paid_by_tenant', []),
            'utilities_estimate' => $request->get('utilities_estimate'),
            'maintenance_included' => $request->boolean('maintenance_included'),
            'maintenance_terms' => $request->get('maintenance_terms'),
            'status' => 'draft',
            'auto_renewable' => $request->boolean('auto_renewable', false),
            'renewal_notice_days' => $request->get('renewal_notice_days', 30),
            'special_terms' => $request->get('special_terms'),
            'house_rules' => $request->get('house_rules', []),
            'pets_allowed' => $request->boolean('pets_allowed', false),
            'smoking_allowed' => $request->boolean('smoking_allowed', false),
        ]);

        $rental->load(['property', 'tenant', 'owner']);

        return response()->json([
            'message' => 'Long-term rental created successfully',
            'rental' => $rental,
        ], 201);
    }

    public function show($id)
    {
        $rental = LongTermRental::with([
            'property',
            'tenant',
            'owner',
            'rentPayments' => function ($query) {
                $query->orderBy('due_date');
            },
            'maintenanceRequests' => function ($query) {
                $query->latest();
            },
        ])->findOrFail($id);

        return response()->json($rental);
    }

    public function update(Request $request, $id)
    {
        $rental = LongTermRental::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'monthly_rent' => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'payment_day_of_month' => 'nullable|integer|min:1|max:28',
            'utilities_included' => 'nullable|array',
            'utilities_paid_by_tenant' => 'nullable|array',
            'utilities_estimate' => 'nullable|numeric|min:0',
            'maintenance_included' => 'nullable|boolean',
            'maintenance_terms' => 'nullable|string',
            'special_terms' => 'nullable|string',
            'house_rules' => 'nullable|array',
            'status' => 'nullable|in:draft,pending_approval,active,completed,cancelled,terminated',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $rental->update($request->only([
            'monthly_rent',
            'security_deposit',
            'payment_day_of_month',
            'utilities_included',
            'utilities_paid_by_tenant',
            'utilities_estimate',
            'maintenance_included',
            'maintenance_terms',
            'special_terms',
            'house_rules',
            'status',
        ]));

        $rental->load(['property', 'tenant', 'owner']);

        return response()->json([
            'message' => 'Long-term rental updated successfully',
            'rental' => $rental,
        ]);
    }

    public function activate(Request $request, $id)
    {
        $rental = LongTermRental::findOrFail($id);

        if ($rental->status !== 'draft' && $rental->status !== 'pending_approval') {
            return response()->json(['error' => 'Only draft or pending rentals can be activated'], 422);
        }

        $rental->update([
            'status' => 'active',
            'lease_signed_at' => now(),
        ]);

        $rental->generatePaymentSchedule();

        return response()->json([
            'message' => 'Rental activated successfully',
            'rental' => $rental->load(['property', 'tenant', 'owner', 'rentPayments']),
        ]);
    }

    public function requestRenewal(Request $request, $id)
    {
        $rental = LongTermRental::findOrFail($id);

        if (! $rental->canRequestRenewal()) {
            return response()->json([
                'error' => 'Renewal cannot be requested at this time',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'duration_months' => 'required|integer|min:1|max:120',
            'monthly_rent' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $rental->update([
            'renewal_requested_at' => now(),
            'renewal_status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Renewal request submitted successfully',
            'rental' => $rental,
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $rental = LongTermRental::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $rental->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->reason,
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Rental cancelled successfully',
            'rental' => $rental,
        ]);
    }

    public function statistics(Request $request)
    {
        $query = LongTermRental::query();

        if ($request->has('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        $stats = [
            'total' => $query->count(),
            'active' => $query->where('status', 'active')->count(),
            'pending' => $query->where('status', 'pending_approval')->count(),
            'completed' => $query->where('status', 'completed')->count(),
            'expiring_30_days' => $query->expiringSoon(30)->count(),
            'total_monthly_revenue' => $query->where('status', 'active')->sum('monthly_rent'),
            'total_deposits_held' => $query->where('deposit_status', 'held')->sum('security_deposit'),
        ];

        return response()->json($stats);
    }

    public function destroy($id)
    {
        $rental = LongTermRental::findOrFail($id);

        if ($rental->status === 'active') {
            return response()->json([
                'error' => 'Cannot delete active rental. Please cancel it first.',
            ], 422);
        }

        $rental->delete();

        return response()->json([
            'message' => 'Long-term rental deleted successfully',
        ]);
    }
}

