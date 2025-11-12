<?php

namespace App\Http\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\JsonResponse;

class LanguageController extends Controller
{
    public function index(): JsonResponse
    {
        $languages = Language::getActive();

        return response()->json([
            'success' => true,
            'data' => $languages,
        ]);
    }

    public function show(string $code): JsonResponse
    {
        $language = Language::where('code', $code)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $language,
        ]);
    }

    public function getDefault(): JsonResponse
    {
        $language = Language::getDefault();

        if (! $language) {
            return response()->json([
                'success' => false,
                'message' => 'No default language set',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $language,
        ]);
    }
}

