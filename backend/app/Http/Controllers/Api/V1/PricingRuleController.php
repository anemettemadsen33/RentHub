<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PricingRule;
use App\Models\Property;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PricingRuleController extends Controller
{
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Get all pricing rules for a property
     */
    public function index(Request $request, $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        // Check authorization
        if ($request->user()->id !== $property->user_id && ! $request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $rules = $property->pricingRules()
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rules,
        ]);
    }

    /**
     * Create a new pricing rule
     */
    public function store(Request $request, $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        // Check authorization
        if ($request->user()->id !== $property->user_id && ! $request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:seasonal,weekend,holiday,demand,last_minute,early_bird,weekly,monthly',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|between:0,6',
            'adjustment_type' => 'required|in:percentage,fixed',
            'adjustment_value' => 'required|numeric',
            'min_nights' => 'nullable|integer|min:1',
            'max_nights' => 'nullable|integer|min:1',
            'advance_booking_days' => 'nullable|integer|min:1',
            'last_minute_days' => 'nullable|integer|min:1',
            'priority' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $rule = $property->pricingRules()->create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pricing rule created successfully',
            'data' => $rule,
        ], 201);
    }

    /**
     * Get a specific pricing rule
     */
    public function show(Request $request, $propertyId, $ruleId)
    {
        $property = Property::findOrFail($propertyId);
        $rule = PricingRule::where('property_id', $property->id)->findOrFail($ruleId);

        // Check authorization
        if ($request->user()->id !== $property->user_id && ! $request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $rule,
        ]);
    }

    /**
     * Update a pricing rule
     */
    public function update(Request $request, $propertyId, $ruleId)
    {
        $property = Property::findOrFail($propertyId);
        $rule = PricingRule::where('property_id', $property->id)->findOrFail($ruleId);

        // Check authorization
        if ($request->user()->id !== $property->user_id && ! $request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|in:seasonal,weekend,holiday,demand,last_minute,early_bird,weekly,monthly',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|between:0,6',
            'adjustment_type' => 'sometimes|in:percentage,fixed',
            'adjustment_value' => 'sometimes|numeric',
            'min_nights' => 'nullable|integer|min:1',
            'max_nights' => 'nullable|integer|min:1',
            'advance_booking_days' => 'nullable|integer|min:1',
            'last_minute_days' => 'nullable|integer|min:1',
            'priority' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $rule->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pricing rule updated successfully',
            'data' => $rule->fresh(),
        ]);
    }

    /**
     * Delete a pricing rule
     */
    public function destroy(Request $request, $propertyId, $ruleId)
    {
        $property = Property::findOrFail($propertyId);
        $rule = PricingRule::where('property_id', $property->id)->findOrFail($ruleId);

        // Check authorization
        if ($request->user()->id !== $property->user_id && ! $request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $rule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pricing rule deleted successfully',
        ]);
    }

    /**
     * Calculate price for specific dates
     */
    public function calculatePrice(Request $request, $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        $validator = Validator::make($request->all(), [
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);

        $priceBreakdown = $this->pricingService->calculateTotalPrice($property, $checkIn, $checkOut);

        return response()->json([
            'success' => true,
            'data' => $priceBreakdown,
        ]);
    }

    /**
     * Get pricing calendar
     */
    public function calendar(Request $request, $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $calendar = $this->pricingService->getPricingCalendar($property, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $calendar,
        ]);
    }

    /**
     * Toggle rule active status
     */
    public function toggle(Request $request, $propertyId, $ruleId)
    {
        $property = Property::findOrFail($propertyId);
        $rule = PricingRule::where('property_id', $property->id)->findOrFail($ruleId);

        // Check authorization
        if ($request->user()->id !== $property->user_id && ! $request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $rule->update(['is_active' => ! $rule->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Rule status updated',
            'data' => $rule->fresh(),
        ]);
    }
}

