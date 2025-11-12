<?php

namespace App\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\CleaningService;
use App\Models\Property;
use App\Notifications\CleaningServiceNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CleaningServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CleaningService::with(['property', 'serviceProvider', 'requestedBy']);

        $user = Auth::user();

        // Filter by user role
        if ($user->role === 'owner') {
            $query->whereHas('property', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif ($user->role === 'tenant') {
            $query->whereHas('booking', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->orWhereHas('longTermRental', function ($q) use ($user) {
                $q->where('tenant_id', $user->id);
            });
        }

        // Filters
        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->has('service_provider_id')) {
            $query->where('service_provider_id', $request->service_provider_id);
        }

        if ($request->has('from_date')) {
            $query->where('scheduled_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('scheduled_date', '<=', $request->to_date);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'scheduled_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $services = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'long_term_rental_id' => 'nullable|exists:long_term_rentals,id',
            'service_provider_id' => 'nullable|exists:service_providers,id',
            'service_type' => 'required|in:regular_cleaning,deep_cleaning,move_in,move_out,post_booking,emergency,custom',
            'description' => 'nullable|string',
            'checklist' => 'nullable|array',
            'special_instructions' => 'nullable|string',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'nullable|date_format:H:i',
            'estimated_duration_hours' => 'required|integer|min:1|max:24',
            'requires_key' => 'boolean',
            'access_instructions' => 'nullable|string',
            'access_code' => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'provider_brings_supplies' => 'boolean',
            'supplies_needed' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['requested_by'] = Auth::id();
        $data['status'] = $data['service_provider_id'] ?? null ? 'confirmed' : 'scheduled';

        $service = CleaningService::create($data);
        $service->load(['property', 'serviceProvider', 'requestedBy']);

        // Send notifications
        if ($service->serviceProvider) {
            // Notify service provider
            // $service->serviceProvider->notify(new CleaningServiceNotification($service, 'new'));
        }

        return response()->json([
            'success' => true,
            'message' => 'Cleaning service scheduled successfully',
            'data' => $service,
        ], 201);
    }

    public function show(CleaningService $cleaningService): JsonResponse
    {
        $cleaningService->load(['property', 'serviceProvider', 'requestedBy', 'booking', 'longTermRental']);

        return response()->json([
            'success' => true,
            'data' => $cleaningService,
        ]);
    }

    public function update(Request $request, CleaningService $cleaningService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_provider_id' => 'nullable|exists:service_providers,id',
            'service_type' => 'sometimes|in:regular_cleaning,deep_cleaning,move_in,move_out,post_booking,emergency,custom',
            'description' => 'nullable|string',
            'checklist' => 'nullable|array',
            'special_instructions' => 'nullable|string',
            'scheduled_date' => 'sometimes|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'estimated_duration_hours' => 'sometimes|integer|min:1|max:24',
            'access_instructions' => 'nullable|string',
            'access_code' => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:scheduled,confirmed,in_progress,completed,cancelled,needs_rescheduling',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $cleaningService->update($validator->validated());
        $cleaningService->load(['property', 'serviceProvider', 'requestedBy']);

        return response()->json([
            'success' => true,
            'message' => 'Cleaning service updated successfully',
            'data' => $cleaningService,
        ]);
    }

    public function destroy(CleaningService $cleaningService): JsonResponse
    {
        if (! $cleaningService->canCancel()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete cleaning service in current status',
            ], 422);
        }

        $cleaningService->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cleaning service deleted successfully',
        ]);
    }

    public function start(CleaningService $cleaningService): JsonResponse
    {
        if ($cleaningService->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Can only start confirmed cleaning services',
            ], 422);
        }

        $cleaningService->markAsStarted();

        return response()->json([
            'success' => true,
            'message' => 'Cleaning service started',
            'data' => $cleaningService,
        ]);
    }

    public function complete(Request $request, CleaningService $cleaningService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'completed_checklist' => 'nullable|array',
            'after_photos' => 'nullable|array',
            'completion_notes' => 'nullable|string',
            'issues_found' => 'nullable|array',
            'actual_cost' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $cleaningService->markAsCompleted($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cleaning service marked as completed',
            'data' => $cleaningService,
        ]);
    }

    public function cancel(Request $request, CleaningService $cleaningService): JsonResponse
    {
        if (! $cleaningService->canCancel()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel cleaning service in current status',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $cleaningService->cancel($request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Cleaning service cancelled',
            'data' => $cleaningService,
        ]);
    }

    public function rate(Request $request, CleaningService $cleaningService): JsonResponse
    {
        if (! $cleaningService->canRate()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot rate this cleaning service',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $cleaningService->rate($request->rating, $request->feedback);

        return response()->json([
            'success' => true,
            'message' => 'Rating submitted successfully',
            'data' => $cleaningService,
        ]);
    }

    public function history(Property $property): JsonResponse
    {
        $services = CleaningService::where('property_id', $property->id)
            ->with(['serviceProvider', 'requestedBy'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }
}

