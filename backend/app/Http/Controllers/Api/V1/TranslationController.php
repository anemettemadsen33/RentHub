<?php

namespace App\Http\\Controllers\\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function __construct(
        protected TranslationService $translationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $locale = $request->input('locale', config('app.locale'));
        $group = $request->input('group');

        $translations = $this->translationService->getAllTranslations($locale, $group);

        return response()->json([
            'success' => true,
            'locale' => $locale,
            'translations' => $translations,
        ]);
    }

    public function show(Request $request, string $key): JsonResponse
    {
        $locale = $request->input('locale', config('app.locale'));
        $group = $request->input('group', 'common');

        $translation = $this->translationService->getTranslation($key, $locale, $group);

        return response()->json([
            'success' => true,
            'key' => $key,
            'value' => $translation,
        ]);
    }

    public function languages(): JsonResponse
    {
        $languages = $this->translationService->getSupportedLanguages();

        return response()->json([
            'success' => true,
            'languages' => $languages,
        ]);
    }

    public function detectLanguage(Request $request): JsonResponse
    {
        $acceptLanguage = $request->header('Accept-Language', '');
        $detected = $this->translationService->detectLanguage($acceptLanguage);

        return response()->json([
            'success' => true,
            'detected_language' => $detected,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'locale' => 'required|string|max:10',
            'group' => 'required|string|max:255',
            'key' => 'required|string|max:255',
            'value' => 'required|string',
        ]);

        $translation = $this->translationService->setTranslation(
            $request->locale,
            $request->group,
            $request->key,
            $request->value
        );

        return response()->json([
            'success' => true,
            'message' => 'Translation created successfully',
            'translation' => $translation,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'value' => 'required|string',
        ]);

        $translation = \App\Models\Translation::findOrFail($id);
        $translation->update(['value' => $request->value]);

        return response()->json([
            'success' => true,
            'message' => 'Translation updated successfully',
            'translation' => $translation,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'locale' => 'required|string|max:10',
            'group' => 'required|string|max:255',
            'key' => 'required|string|max:255',
        ]);

        $deleted = $this->translationService->deleteTranslation(
            $request->locale,
            $request->group,
            $request->key
        );

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'Translation deleted successfully' : 'Translation not found',
        ], $deleted ? 200 : 404);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'locale' => 'required|string|max:10',
            'group' => 'required|string|max:255',
            'translations' => 'required|array',
        ]);

        $count = $this->translationService->importTranslations(
            $request->locale,
            $request->translations,
            $request->group
        );

        return response()->json([
            'success' => true,
            'message' => "{$count} translations imported successfully",
            'count' => $count,
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        $locale = $request->input('locale', config('app.locale'));
        $group = $request->input('group');

        $translations = $this->translationService->exportTranslations($locale, $group);

        return response()->json([
            'success' => true,
            'locale' => $locale,
            'group' => $group,
            'translations' => $translations,
        ]);
    }
}

