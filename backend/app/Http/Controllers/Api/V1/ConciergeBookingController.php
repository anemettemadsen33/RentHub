<?php

namespace App\Http\\Controllers\\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ConciergeBooking;
use App\Models\ConciergeService;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConciergeBookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ConciergeBooking::with(['conciergeService', 'property', 'booking'])
            ->where('user_id', $request->user()->id);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('upcoming') && $request->upcoming) {
            $query->upcoming();
        }

        $bookings = $query->orderBy('service_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'concierge_service_id' => 'required|exists:concierge_services,id',
            'property_id' => 'nullable|exists:properties,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'service_date' => 'required|date|after:today',
            'service_time' => 'required|date_format:H:i',
            'guests_count' => 'nullable|integer|min:1',
            'special_requests' => 'nullable|string|max:1000',
            'contact_details' => 'required|array',
            'contact_details.name' => 'required|string',
            'contact_details.phone' => 'required|string',
            'contact_details.email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $service = ConciergeService::findOrFail($request->concierge_service_id);

        // Check advance booking requirement
        $serviceDateTime = Carbon::parse($request->service_date.' '.$request->service_time);
        $hoursDifference = now()->diffInHours($serviceDateTime);

        if ($hoursDifference < $service->advance_booking_hours) {
            return response()->json([
                'success' => false,
                'message' => "This service requires booking at least {$service->advance_booking_hours} hours in advance.",
            ], 422);
        }

        // Check guest capacity
        if ($service->max_guests && $request->guests_count > $service->max_guests) {
            return response()->json([
                'success' => false,
                'message' => "This service can accommodate a maximum of {$service->max_guests} guests.",
            ], 422);
        }

        // Calculate pricing
        $basePrice = $service->base_price;
        $extrasPrice = 0;

        // TODO: Calculate extras based on pricing_extras in service
        // For now, just use base price

        $totalPrice = $basePrice + $extrasPrice;

        $booking = ConciergeBooking::create([
            'user_id' => $request->user()->id,
            'property_id' => $request->property_id,
            'booking_id' => $request->booking_id,
            'concierge_service_id' => $service->id,
            'service_date' => $request->service_date,
            'service_time' => $serviceDateTime,
            'guests_count' => $request->guests_count ?? 1,
            'special_requests' => $request->special_requests,
            'base_price' => $basePrice,
            'extras_price' => $extrasPrice,
            'total_price' => $totalPrice,
            'currency' => 'RON', // Default currency
            'status' => 'pending',
            'payment_status' => 'pending',
            'contact_details' => $request->contact_details,
        ]);

        $booking->load(['conciergeService', 'property', 'booking']);

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'data' => $booking,
        ], 201);
    }

    public function show(Request $request, ConciergeBooking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $booking->load(['conciergeService.serviceProvider', 'property', 'booking', 'payment']);

        return response()->json([
            'success' => true,
            'data' => $booking,
        ]);
    }

    public function update(Request $request, ConciergeBooking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (! in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'This booking cannot be updated',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'service_date' => 'sometimes|date|after:today',
            'service_time' => 'sometimes|date_format:H:i',
            'guests_count' => 'sometimes|integer|min:1',
            'special_requests' => 'sometimes|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $booking->update($request->only([
            'service_date',
            'service_time',
            'guests_count',
            'special_requests',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'data' => $booking,
        ]);
    }

    public function cancel(Request $request, ConciergeBooking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (! in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'This booking cannot be cancelled',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $booking->cancel($request->reason);

        // Process refund if payment was made
        $payment = Payment::where('concierge_booking_id', $booking->id)
            ->where('status', 'completed')
            ->first();

        if ($payment) {
            $payment->notes = trim(($payment->notes ? ($payment->notes."\n") : '').'Refund reason: Concierge booking cancelled - '.$request->reason);
            $payment->save();
            $payment->markAsRefunded();
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'data' => $booking->fresh(),
            'refunded' => $payment ? true : false,
        ]);
    }

    public function addReview(Request $request, ConciergeBooking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($booking->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'You can only review completed services',
            ], 422);
        }

        if ($booking->reviewed_at) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this service',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $booking->addReview($request->rating, $request->review);

        return response()->json([
            'success' => true,
            'message' => 'Review added successfully',
            'data' => $booking,
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $stats = [
            'total_bookings' => ConciergeBooking::where('user_id', $userId)->count(),
            'upcoming_bookings' => ConciergeBooking::where('user_id', $userId)->upcoming()->count(),
            'completed_bookings' => ConciergeBooking::where('user_id', $userId)->completed()->count(),
            'total_spent' => ConciergeBooking::where('user_id', $userId)
                ->where('payment_status', 'completed')
                ->sum('total_price'),
            'favorite_service' => ConciergeBooking::where('user_id', $userId)
                ->select('concierge_service_id')
                ->selectRaw('COUNT(*) as booking_count')
                ->groupBy('concierge_service_id')
                ->orderByDesc('booking_count')
                ->with('conciergeService')
                ->first()
                ?->conciergeService,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}

