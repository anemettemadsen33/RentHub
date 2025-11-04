<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceProviderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ServiceProvider::query();

        // Filters
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->active();
        }

        if ($request->has('verified')) {
            $query->verified();
        }

        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        if ($request->has('min_rating')) {
            $query->where('average_rating', '>=', $request->min_rating);
        }

        if ($request->has('service_type')) {
            if ($request->service_type === 'cleaning') {
                $query->cleaning();
            } elseif ($request->service_type === 'maintenance') {
                $query->maintenance();
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'average_rating');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $providers = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $providers,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'type' => 'required|in:cleaning,maintenance,both',
            'email' => 'required|email|unique:service_providers,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20',
            'services_offered' => 'nullable|array',
            'maintenance_specialties' => 'nullable|array',
            'hourly_rate' => 'nullable|numeric|min:0',
            'base_rate' => 'nullable|numeric|min:0',
            'pricing_type' => 'required|in:hourly,per_service,per_square_foot,custom',
            'working_hours' => 'nullable|array',
            'bio' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $provider = ServiceProvider::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Service provider created successfully',
            'data' => $provider,
        ], 201);
    }

    public function show(ServiceProvider $serviceProvider): JsonResponse
    {
        $serviceProvider->load([
            'cleaningServices' => function ($query) {
                $query->latest()->limit(10);
            },
            'maintenanceRequests' => function ($query) {
                $query->latest()->limit(10);
            },
        ]);

        return response()->json([
            'success' => true,
            'data' => $serviceProvider,
        ]);
    }

    public function update(Request $request, ServiceProvider $serviceProvider): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'type' => 'sometimes|in:cleaning,maintenance,both',
            'email' => 'sometimes|email|unique:service_providers,email,'.$serviceProvider->id,
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string|max:100',
            'zip_code' => 'sometimes|string|max:20',
            'services_offered' => 'nullable|array',
            'maintenance_specialties' => 'nullable|array',
            'hourly_rate' => 'nullable|numeric|min:0',
            'base_rate' => 'nullable|numeric|min:0',
            'pricing_type' => 'sometimes|in:hourly,per_service,per_square_foot,custom',
            'working_hours' => 'nullable|array',
            'status' => 'sometimes|in:active,inactive,suspended,pending_verification',
            'bio' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $serviceProvider->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Service provider updated successfully',
            'data' => $serviceProvider,
        ]);
    }

    public function destroy(ServiceProvider $serviceProvider): JsonResponse
    {
        $serviceProvider->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service provider deleted successfully',
        ]);
    }

    public function verify(ServiceProvider $serviceProvider): JsonResponse
    {
        $serviceProvider->update([
            'verified' => true,
            'verified_at' => now(),
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service provider verified successfully',
            'data' => $serviceProvider,
        ]);
    }

    public function checkAvailability(Request $request, ServiceProvider $serviceProvider): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $isAvailable = $serviceProvider->isAvailable($request->date, $request->time);

        return response()->json([
            'success' => true,
            'data' => [
                'available' => $isAvailable,
                'provider' => $serviceProvider->only(['id', 'name', 'company_name']),
                'requested_date' => $request->date,
                'requested_time' => $request->time,
            ],
        ]);
    }

    public function stats(ServiceProvider $serviceProvider): JsonResponse
    {
        $stats = [
            'total_jobs' => $serviceProvider->total_jobs,
            'completed_jobs' => $serviceProvider->completed_jobs,
            'cancelled_jobs' => $serviceProvider->cancelled_jobs,
            'completion_rate' => $serviceProvider->total_jobs > 0
                ? round(($serviceProvider->completed_jobs / $serviceProvider->total_jobs) * 100, 2)
                : 0,
            'average_rating' => $serviceProvider->average_rating,
            'response_time_hours' => $serviceProvider->response_time_hours,
            'cleaning_services_count' => $serviceProvider->cleaningServices()->count(),
            'maintenance_requests_count' => $serviceProvider->maintenanceRequests()->count(),
            'recent_ratings' => $serviceProvider->cleaningServices()
                ->whereNotNull('rating')
                ->latest()
                ->limit(5)
                ->get(['rating', 'feedback', 'rated_at']),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
