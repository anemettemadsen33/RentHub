<?php

namespace App\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MaintenanceRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceRequest::with(['longTermRental', 'property', 'tenant', 'assignedTo', 'serviceProvider']);

        if ($request->has('long_term_rental_id')) {
            $query->where('long_term_rental_id', $request->long_term_rental_id);
        }

        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->boolean('urgent')) {
            $query->urgent();
        }

        if ($request->boolean('open')) {
            $query->open();
        }

        $perPage = $request->get('per_page', 15);
        $requests = $query->latest()->paginate($perPage);

        return response()->json($requests);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'long_term_rental_id' => 'required|exists:long_term_rentals,id',
            'property_id' => 'required|exists:properties,id',
            'tenant_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:plumbing,electrical,hvac,appliance,structural,pest_control,cleaning,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'preferred_date' => 'nullable|date',
            'requires_access' => 'boolean',
            'access_instructions' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('maintenance-requests', 'public');
                $photos[] = $path;
            }
        }

        $maintenanceRequest = MaintenanceRequest::create([
            'long_term_rental_id' => $request->long_term_rental_id,
            'property_id' => $request->property_id,
            'tenant_id' => $request->tenant_id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority,
            'preferred_date' => $request->get('preferred_date'),
            'requires_access' => $request->boolean('requires_access', true),
            'access_instructions' => $request->get('access_instructions'),
            'photos' => $photos,
            'status' => 'submitted',
        ]);

        $maintenanceRequest->load(['property', 'tenant']);

        return response()->json([
            'message' => 'Maintenance request created successfully',
            'request' => $maintenanceRequest,
        ], 201);
    }

    public function show($id)
    {
        $maintenanceRequest = MaintenanceRequest::with([
            'longTermRental',
            'property',
            'tenant',
            'assignedTo',
            'serviceProvider',
        ])->findOrFail($id);

        return response()->json($maintenanceRequest);
    }

    public function update(Request $request, $id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:submitted,acknowledged,scheduled,in_progress,completed,cancelled',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'scheduled_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_cost' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $maintenanceRequest->update($request->only([
            'status',
            'priority',
            'scheduled_date',
            'assigned_to',
            'estimated_cost',
        ]));

        return response()->json([
            'message' => 'Maintenance request updated successfully',
            'request' => $maintenanceRequest,
        ]);
    }

    public function complete(Request $request, $id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'resolution_notes' => 'required|string',
            'actual_cost' => 'nullable|numeric|min:0',
            'completion_photos' => 'nullable|array',
            'completion_photos.*' => 'image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $completionPhotos = [];
        if ($request->hasFile('completion_photos')) {
            foreach ($request->file('completion_photos') as $photo) {
                $path = $photo->store('maintenance-requests/completed', 'public');
                $completionPhotos[] = $path;
            }
        }

        $maintenanceRequest->markAsCompleted(
            $request->resolution_notes,
            $completionPhotos
        );

        if ($request->has('actual_cost')) {
            $maintenanceRequest->update(['actual_cost' => $request->actual_cost]);
        }

        return response()->json([
            'message' => 'Maintenance request completed successfully',
            'request' => $maintenanceRequest,
        ]);
    }

    public function assign(Request $request, $id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|exists:users,id',
            'service_provider_id' => 'nullable|exists:service_providers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $maintenanceRequest->assign($request->assigned_to);

        if ($request->has('service_provider_id')) {
            $maintenanceRequest->update(['service_provider_id' => $request->service_provider_id]);
        }

        return response()->json([
            'message' => 'Maintenance request assigned successfully',
            'request' => $maintenanceRequest->fresh(['assignedTo', 'serviceProvider']),
        ]);
    }

    public function assignServiceProvider(Request $request, $id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'service_provider_id' => 'required|exists:service_providers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $maintenanceRequest->update([
            'service_provider_id' => $request->service_provider_id,
            'status' => 'acknowledged',
        ]);

        return response()->json([
            'message' => 'Service provider assigned successfully',
            'request' => $maintenanceRequest->fresh('serviceProvider'),
        ]);
    }

    public function destroy($id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);

        if ($maintenanceRequest->status === 'in_progress') {
            return response()->json([
                'error' => 'Cannot delete maintenance request in progress',
            ], 422);
        }

        $maintenanceRequest->delete();

        return response()->json([
            'message' => 'Maintenance request deleted successfully',
        ]);
    }
}

