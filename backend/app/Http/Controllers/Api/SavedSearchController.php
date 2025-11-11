<?php

namespace App\\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\SavedSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SavedSearchController extends Controller
{
    /**
     * Get all saved searches for authenticated user
     */
    public function index(Request $request)
    {
        $searches = Auth::user()->savedSearches()
            ->when($request->is_active !== null, function ($query) use ($request) {
                $query->where('is_active', $request->boolean('is_active'));
            })
            ->orderBy('last_searched_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $searches,
        ]);
    }

    /**
     * Create a new saved search
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius_km' => 'nullable|integer|min:1|max:100',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'min_bedrooms' => 'nullable|integer|min:0',
            'max_bedrooms' => 'nullable|integer|min:0',
            'min_bathrooms' => 'nullable|integer|min:0',
            'max_bathrooms' => 'nullable|integer|min:0',
            'min_guests' => 'nullable|integer|min:1',
            'property_type' => 'nullable|string|max:50',
            'amenities' => 'nullable|array',
            'amenities.*' => 'integer|exists:amenities,id',
            'check_in' => 'nullable|date|after:today',
            'check_out' => 'nullable|date|after:check_in',
            'enable_alerts' => 'boolean',
            'alert_frequency' => ['nullable', Rule::in(['instant', 'daily', 'weekly'])],
            'criteria' => 'nullable|array', // Additional custom criteria
            'filters' => 'nullable|array',
        ]);

        $validated['user_id'] = Auth::id();

        $savedSearch = SavedSearch::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Saved search created successfully',
            'data' => $savedSearch,
        ], 201);
    }

    /**
     * Get a specific saved search
     */
    public function show($id)
    {
        $savedSearch = SavedSearch::findOrFail($id);
        $this->authorize('view', $savedSearch);

        return response()->json([
            'success' => true,
            'data' => $savedSearch,
        ]);
    }

    /**
     * Update a saved search
     */
    public function update(Request $request, $id)
    {
        $savedSearch = SavedSearch::findOrFail($id);
        $this->authorize('update', $savedSearch);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius_km' => 'nullable|integer|min:1|max:100',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'min_bedrooms' => 'nullable|integer|min:0',
            'max_bedrooms' => 'nullable|integer|min:0',
            'min_bathrooms' => 'nullable|integer|min:0',
            'max_bathrooms' => 'nullable|integer|min:0',
            'min_guests' => 'nullable|integer|min:1',
            'property_type' => 'nullable|string|max:50',
            'amenities' => 'nullable|array',
            'amenities.*' => 'integer|exists:amenities,id',
            'check_in' => 'nullable|date',
            'check_out' => 'nullable|date|after:check_in',
            'enable_alerts' => 'boolean',
            'notify' => 'boolean',
            'alert_frequency' => ['nullable', Rule::in(['instant', 'daily', 'weekly'])],
            'is_active' => 'boolean',
            'criteria' => 'nullable|array',
            'filters' => 'nullable|array',
        ]);

        $savedSearch->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Saved search updated successfully',
            'data' => $savedSearch->fresh(),
        ]);
    }

    /**
     * Delete a saved search
     */
    public function destroy($id)
    {
        $savedSearch = SavedSearch::findOrFail($id);
        $this->authorize('delete', $savedSearch);

        $savedSearch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Saved search deleted successfully',
        ]);
    }

    /**
     * Execute a saved search and return matching properties
     */
    public function execute($id)
    {
        $savedSearch = SavedSearch::findOrFail($id);
        $this->authorize('execute', $savedSearch);

        $properties = $savedSearch->executeSearch();

        // Legacy tests expect 'data' to be an array of matched properties only
        return response()->json([
            'success' => true,
            'data' => $properties,
        ]);
    }

    /**
     * Check for new listings matching this saved search
     */
    public function checkNewListings($id)
    {
        $savedSearch = SavedSearch::findOrFail($id);
        $this->authorize('view', $savedSearch);

        $newProperties = $savedSearch->checkNewListings();

        return response()->json([
            'success' => true,
            'data' => [
                'saved_search' => $savedSearch,
                'new_properties' => $newProperties,
                'count' => $newProperties->count(),
                'since' => $savedSearch->last_alert_sent_at ?? $savedSearch->created_at,
            ],
        ]);
    }

    /**
     * Toggle alerts for a saved search
     */
    public function toggleAlerts($id)
    {
        $savedSearch = SavedSearch::findOrFail($id);
        $this->authorize('toggleAlerts', $savedSearch);

        $savedSearch->update([
            'enable_alerts' => ! $savedSearch->enable_alerts,
        ]);

        return response()->json([
            'success' => true,
            'message' => $savedSearch->enable_alerts
                ? 'Alerts enabled successfully'
                : 'Alerts disabled successfully',
            'data' => $savedSearch,
        ]);
    }

    /**
     * Get statistics for user's saved searches
     */
    public function statistics()
    {
        $user = Auth::user();

        $stats = [
            'total_searches' => $user->savedSearches()->count(),
            'active_searches' => $user->savedSearches()->where('is_active', true)->count(),
            'with_alerts' => $user->savedSearches()->where('enable_alerts', true)->count(),
            'most_used' => $user->savedSearches()
                ->orderBy('search_count', 'desc')
                ->limit(5)
                ->get(),
            'recent' => $user->savedSearches()
                ->orderBy('last_searched_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}

