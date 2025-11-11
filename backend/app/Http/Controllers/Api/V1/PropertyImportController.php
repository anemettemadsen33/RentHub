<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PropertyImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PropertyImportController extends Controller
{
    public function __construct(
        private PropertyImportService $importService
    ) {}

    /**
     * Import property from external platform
     *
     * @group Property Management
     *
     * @authenticated
     *
     * @bodyParam platform string required Platform name (booking, airbnb, vrbo). Example: booking
     * @bodyParam url string required Property URL from the platform. Example: https://www.booking.com/hotel/ro/property-name.html
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Property imported successfully",
     *   "data": {
     *     "id": 123,
     *     "title": "Beautiful Apartment in Bucharest",
     *     "platform": "booking",
     *     "status": "draft"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "platform": ["The platform field is required."],
     *     "url": ["The url field is required."]
     *   }
     * }
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'platform' => ['required', 'string', 'in:booking,airbnb,vrbo'],
            'url' => ['required', 'url', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->importService->importProperty(
            platform: $request->input('platform'),
            url: $request->input('url'),
            user: $request->user()
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Get import statistics for authenticated user
     *
     * @group Property Management
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "total_imported": 15,
     *     "by_platform": {
     *       "booking": 5,
     *       "airbnb": 7,
     *       "vrbo": 3
     *     },
     *     "recent_imports": []
     *   }
     * }
     */
    public function stats(Request $request): JsonResponse
    {
        $stats = $this->importService->getImportStats($request->user());

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Validate import URL before importing
     *
     * @group Property Management
     *
     * @authenticated
     *
     * @bodyParam platform string required Platform name. Example: booking
     * @bodyParam url string required Property URL. Example: https://www.booking.com/hotel/ro/property-name.html
     *
     * @response 200 {
     *   "success": true,
     *   "valid": true,
     *   "message": "URL is valid for booking platform"
     * }
     * @response 200 {
     *   "success": false,
     *   "valid": false,
     *   "message": "Invalid booking URL format"
     * }
     */
    public function validateUrl(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'platform' => ['required', 'string', 'in:booking,airbnb,vrbo'],
            'url' => ['required', 'url'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Invalid parameters',
                'errors' => $validator->errors(),
            ], 422);
        }

        $patterns = [
            'booking' => '/^https?:\/\/(www\.)?booking\.com\/.+/',
            'airbnb' => '/^https?:\/\/(www\.)?airbnb\.com\/rooms\/.+/',
            'vrbo' => '/^https?:\/\/(www\.)?vrbo\.com\/.+/',
        ];

        $platform = $request->input('platform');
        $url = $request->input('url');
        $isValid = preg_match($patterns[$platform], $url);

        return response()->json([
            'success' => true,
            'valid' => (bool) $isValid,
            'message' => $isValid
                ? "URL is valid for {$platform} platform"
                : "Invalid {$platform} URL format",
        ]);
    }
}
