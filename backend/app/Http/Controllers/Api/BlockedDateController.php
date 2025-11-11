<?php

namespace App\\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\BlockedDate;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BlockedDateController extends Controller
{
    /**
     * Get blocked dates for a property
     */
    public function index(Property $property)
    {
        $blockedDates = BlockedDate::where('property_id', $property->id)
            ->orderBy('start_date', 'asc')
            ->get();

        return response()->json([
            'data' => $blockedDates,
        ]);
    }

    /**
     * Create a new blocked date
     */
    public function store(Request $request, Property $property)
    {
        // Check ownership
        if ($property->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $blockedDate = BlockedDate::create([
            'property_id' => $property->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
        ]);

        return response()->json([
            'data' => $blockedDate,
            'message' => 'Dates blocked successfully',
        ], 201);
    }

    /**
     * Delete a blocked date
     */
    public function destroy(BlockedDate $blockedDate)
    {
        // Check ownership
        $property = $blockedDate->property;
        if ($property->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $blockedDate->forceDelete();

        return response()->json([
            'message' => 'Blocked date removed successfully',
        ]);
    }
}

