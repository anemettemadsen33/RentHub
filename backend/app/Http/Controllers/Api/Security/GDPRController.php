<?php

namespace App\Http\Controllers\\Api\Security;

use App\Http\Controllers\Controller;
use App\Services\Security\GDPRService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GDPRController extends Controller
{
    public function __construct(
        private GDPRService $gdprService
    ) {}

    public function exportData(Request $request): JsonResponse
    {
        $request->validate(['format' => 'nullable|in:json,csv,pdf']);

        try {
            $url = $this->gdprService->exportUserData(
                $request->user(),
                $request->format ?? 'json'
            );

            return response()->json([
                'message' => 'Data export initiated',
                'download_url' => $url,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to export data', 'error' => $e->getMessage()], 500);
        }
    }

    public function requestDeletion(Request $request): JsonResponse
    {
        $request->validate(['immediate' => 'nullable|boolean']);
        $immediate = $request->immediate === true && $request->user()->role === 'admin';

        $this->gdprService->deleteUserData($request->user(), ! $immediate);

        $message = $immediate
            ? 'Your data has been deleted immediately.'
            : 'Your data deletion has been scheduled. You have 30 days to cancel.';

        return response()->json(['message' => $message]);
    }

    public function cancelDeletion(Request $request): JsonResponse
    {
        $user = $request->user();

        \App\Models\GDPRRequest::where('user_id', $user->id)
            ->where('type', 'deletion')
            ->where('status', 'pending')
            ->delete();

        $user->update(['deletion_scheduled_at' => null, 'status' => 'active']);

        return response()->json(['message' => 'Data deletion request cancelled successfully']);
    }

    public function getConsents(Request $request): JsonResponse
    {
        $consents = $this->gdprService->getUserConsents($request->user());

        return response()->json(['data' => $consents]);
    }

    public function grantConsent(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:marketing,analytics,third_party_sharing,cookies',
            'details' => 'nullable|array',
        ]);

        $consent = $this->gdprService->recordConsent(
            $request->user(),
            $request->type,
            $request->details ?? []
        );

        return response()->json(['message' => 'Consent recorded successfully', 'data' => $consent]);
    }

    public function revokeConsent(Request $request): JsonResponse
    {
        $request->validate(['type' => 'required|string']);
        $this->gdprService->revokeConsent($request->user(), $request->type);

        return response()->json(['message' => 'Consent revoked successfully']);
    }
}

