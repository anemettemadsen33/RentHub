<?php

namespace App\Http\\Controllers\\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingInsurance;
use App\Models\InsuranceClaim;
use App\Models\InsurancePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InsuranceController extends Controller
{
    public function getAvailablePlans(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_total' => 'required|numeric|min:0',
            'nights' => 'required|integer|min:1',
            'type' => 'nullable|in:cancellation,damage,liability,travel,comprehensive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bookingTotal = $request->booking_total;
        $nights = $request->nights;
        $type = $request->type;

        $query = InsurancePlan::active()->orderedByDisplay();

        if ($type) {
            $query->byType($type);
        }

        $plans = $query->get()->filter(function ($plan) use ($bookingTotal, $nights) {
            return $plan->isEligibleForBooking($bookingTotal, $nights);
        })->map(function ($plan) use ($bookingTotal, $nights) {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'type' => $plan->type,
                'description' => $plan->description,
                'premium_amount' => $plan->calculatePremium($bookingTotal, $nights),
                'max_coverage' => $plan->max_coverage,
                'coverage_details' => $plan->coverage_details,
                'exclusions' => $plan->exclusions,
                'is_mandatory' => $plan->is_mandatory,
                'terms_and_conditions' => $plan->terms_and_conditions,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $plans,
            'mandatory_plans' => $plans->where('is_mandatory', true)->values(),
            'optional_plans' => $plans->where('is_mandatory', false)->values(),
        ]);
    }

    public function addToBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'insurance_plan_id' => 'required|exists:insurance_plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $booking = Booking::findOrFail($request->booking_id);
        $plan = InsurancePlan::findOrFail($request->insurance_plan_id);

        if ($booking->user_id !== auth()->id() && ! auth()->user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $nights = $booking->check_in->diffInDays($booking->check_out);
        $bookingTotal = $booking->total_price;

        if (! $plan->isEligibleForBooking($bookingTotal, $nights)) {
            return response()->json([
                'error' => 'This insurance plan is not eligible for this booking',
            ], 422);
        }

        $existingInsurance = BookingInsurance::where('booking_id', $booking->id)
            ->where('insurance_plan_id', $plan->id)
            ->whereIn('status', ['pending', 'active'])
            ->first();

        if ($existingInsurance) {
            return response()->json([
                'error' => 'This insurance plan is already added to this booking',
            ], 422);
        }

        $premiumAmount = $plan->calculatePremium($bookingTotal, $nights);

        $bookingInsurance = BookingInsurance::create([
            'booking_id' => $booking->id,
            'insurance_plan_id' => $plan->id,
            'premium_amount' => $premiumAmount,
            'coverage_amount' => $plan->max_coverage,
            'valid_from' => $booking->check_in,
            'valid_until' => $booking->check_out,
            'coverage_details' => $plan->coverage_details,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Insurance added to booking successfully',
            'data' => $bookingInsurance->load('insurancePlan'),
        ], 201);
    }

    public function getBookingInsurances(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        if ($booking->user_id !== auth()->id() && ! auth()->user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $insurances = BookingInsurance::where('booking_id', $bookingId)
            ->with('insurancePlan', 'claims')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $insurances,
        ]);
    }

    public function activateInsurance(Request $request, $insuranceId)
    {
        $insurance = BookingInsurance::findOrFail($insuranceId);
        $booking = $insurance->booking;

        if ($booking->user_id !== auth()->id() && ! auth()->user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($insurance->activate()) {
            return response()->json([
                'success' => true,
                'message' => 'Insurance activated successfully',
                'data' => $insurance->fresh(),
            ]);
        }

        return response()->json([
            'error' => 'Insurance cannot be activated',
        ], 422);
    }

    public function cancelInsurance(Request $request, $insuranceId)
    {
        $insurance = BookingInsurance::findOrFail($insuranceId);
        $booking = $insurance->booking;

        if ($booking->user_id !== auth()->id() && ! auth()->user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($insurance->cancel()) {
            return response()->json([
                'success' => true,
                'message' => 'Insurance cancelled successfully',
                'data' => $insurance->fresh(),
            ]);
        }

        return response()->json([
            'error' => 'Insurance cannot be cancelled',
        ], 422);
    }

    public function submitClaim(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_insurance_id' => 'required|exists:booking_insurances,id',
            'type' => 'required|in:cancellation,damage,injury,theft,other',
            'description' => 'required|string|min:20',
            'claimed_amount' => 'required|numeric|min:0',
            'incident_date' => 'required|date|before_or_equal:today',
            'supporting_documents' => 'nullable|array',
            'supporting_documents.*' => 'url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $insurance = BookingInsurance::findOrFail($request->booking_insurance_id);

        if ($insurance->booking->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (! $insurance->canBeClaimed()) {
            return response()->json([
                'error' => 'This insurance cannot be claimed',
            ], 422);
        }

        if ($request->claimed_amount > $insurance->coverage_amount) {
            return response()->json([
                'error' => 'Claimed amount exceeds coverage limit',
            ], 422);
        }

        $claim = InsuranceClaim::create([
            'booking_insurance_id' => $insurance->id,
            'user_id' => auth()->id(),
            'type' => $request->type,
            'description' => $request->description,
            'claimed_amount' => $request->claimed_amount,
            'incident_date' => $request->incident_date,
            'supporting_documents' => $request->supporting_documents,
        ]);

        $insurance->update(['status' => 'claimed']);

        return response()->json([
            'success' => true,
            'message' => 'Claim submitted successfully',
            'data' => $claim->load('bookingInsurance'),
        ], 201);
    }

    public function getUserClaims(Request $request)
    {
        $claims = InsuranceClaim::where('user_id', auth()->id())
            ->with(['bookingInsurance.insurancePlan', 'bookingInsurance.booking'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $claims,
        ]);
    }

    public function getClaimDetails(Request $request, $claimId)
    {
        $claim = InsuranceClaim::with([
            'bookingInsurance.insurancePlan',
            'bookingInsurance.booking.property',
            'user',
            'reviewer',
        ])->findOrFail($claimId);

        if ($claim->user_id !== auth()->id() && ! auth()->user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $claim,
        ]);
    }
}

