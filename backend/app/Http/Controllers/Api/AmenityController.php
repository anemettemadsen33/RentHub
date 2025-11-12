<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AmenityController extends Controller
{
    /**
     * Get all amenities (cached for 24 hours)
     */
    public function index(): JsonResponse
    {
        $amenities = Cache::tags(['amenities'])->remember('all_amenities', 86400, function () {
            return Amenity::orderBy('name')->get();
        });

        return response()->json([
            'success' => true,
            'data' => $amenities,
        ]);
    }

    /**
     * Get a single amenity (cached for 24 hours)
     */
    public function show(int $id): JsonResponse
    {
        $amenity = Cache::tags(['amenities'])->remember("amenity_{$id}", 86400, function () use ($id) {
            return Amenity::findOrFail($id);
        });

        return response()->json([
            'success' => true,
            'data' => $amenity,
        ]);
    }
}

