<?php

namespace App\\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\JsonResponse;

class AmenityController extends Controller
{
    /**
     * Get all amenities
     */
    public function index(): JsonResponse
    {
        $amenities = Amenity::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $amenities,
        ]);
    }

    /**
     * Get a single amenity
     */
    public function show(int $id): JsonResponse
    {
        $amenity = Amenity::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $amenity,
        ]);
    }
}

