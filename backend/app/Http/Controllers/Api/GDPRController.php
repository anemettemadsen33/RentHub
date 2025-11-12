<?php

namespace App\Http\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Services\Security\AnonymizationService;
use App\Services\Security\GDPRService;
use Illuminate\Http\Request;

class GDPRController extends Controller
{
    public function __construct(
        protected GDPRService $gdprService,
        protected AnonymizationService $anonymizationService
    ) {}

    /**
     * Get user's data (Right to Access)
     */
    public function getUserData(Request $request)
    {
        $user = auth()->user();

        $this->gdprService->recordDataAccess($user, 'view', 'User requested data access');

        return response()->json([
            'personal_info' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'created_at' => $user->created_at,
            ],
            'consent_status' => $this->gdprService->getConsentStatus($user),
            'data_locations' => [
                'properties' => $user->properties()->count(),
                'bookings' => $user->bookings()->count(),
                'reviews' => $user->reviews()->count(),
                'messages' => $user->messages()->count(),
            ],
        ]);
    }

    /**
     * Export user data (Right to Data Portability)
     */
    public function exportData(Request $request)
    {
        $user = auth()->user();

        $this->gdprService->recordDataAccess($user, 'export', 'User requested data export');

        try {
            $zipPath = $this->gdprService->exportUserDataZip($user);

            return response()->download($zipPath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to export data: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update consent preferences
     */
    public function updateConsent(Request $request)
    {
        $validated = $request->validate([
            'terms' => 'sometimes|boolean',
            'privacy' => 'sometimes|boolean',
            'marketing' => 'sometimes|boolean',
            'data_processing' => 'sometimes|boolean',
        ]);

        $user = auth()->user();

        $this->gdprService->updateConsent($user, $validated);

        return response()->json([
            'message' => 'Consent preferences updated',
            'consent_status' => $this->gdprService->getConsentStatus($user),
        ]);
    }

    /**
     * Request account deletion (Right to be Forgotten)
     */
    public function requestDeletion(Request $request)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
            'confirm' => 'required|boolean|accepted',
        ]);

        $user = auth()->user();

        // Check if user has active bookings or obligations
        $activeBookings = $user->bookings()
            ->where('status', 'confirmed')
            ->where('end_date', '>', now())
            ->count();

        if ($activeBookings > 0) {
            return response()->json([
                'error' => 'Cannot delete account with active bookings. Please complete or cancel them first.',
                'active_bookings' => $activeBookings,
            ], 400);
        }

        $this->gdprService->recordDataAccess(
            $user,
            'delete_request',
            $validated['reason'] ?? 'User requested account deletion'
        );

        // Schedule deletion (grace period)
        $user->update([
            'deletion_requested_at' => now(),
            'deletion_reason' => $validated['reason'] ?? null,
        ]);

        return response()->json([
            'message' => 'Account deletion scheduled. You have 30 days to cancel this request.',
            'deletion_date' => now()->addDays(30)->toDateString(),
        ]);
    }

    /**
     * Cancel deletion request
     */
    public function cancelDeletion(Request $request)
    {
        $user = auth()->user();

        if (! $user->deletion_requested_at) {
            return response()->json([
                'error' => 'No deletion request found',
            ], 400);
        }

        $user->update([
            'deletion_requested_at' => null,
            'deletion_reason' => null,
        ]);

        return response()->json([
            'message' => 'Deletion request cancelled',
        ]);
    }

    /**
     * Get consent history
     */
    public function consentHistory(Request $request)
    {
        $user = auth()->user();

        return response()->json([
            'consent_status' => $this->gdprService->getConsentStatus($user),
            'history' => $user->consentLogs()
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get(),
        ]);
    }

    /**
     * Get data access logs
     */
    public function accessLogs(Request $request)
    {
        $user = auth()->user();

        $logs = \DB::table('data_access_logs')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return response()->json([
            'access_logs' => $logs->map(function ($log) {
                return [
                    'type' => $log->access_type,
                    'date' => $log->created_at,
                    'ip' => $this->anonymizationService->anonymizeIP($log->ip_address),
                ];
            }),
        ]);
    }

    /**
     * Get retention policies
     */
    public function retentionPolicies(Request $request)
    {
        return response()->json([
            'policies' => [
                'user_profile' => $this->gdprService->getRetentionPolicy('user_profile'),
                'bookings' => $this->gdprService->getRetentionPolicy('bookings'),
                'payments' => $this->gdprService->getRetentionPolicy('payments'),
                'messages' => $this->gdprService->getRetentionPolicy('messages'),
                'activity_logs' => $this->gdprService->getRetentionPolicy('activity_logs'),
            ],
        ]);
    }

    /**
     * Download privacy policy
     */
    public function privacyPolicy(Request $request)
    {
        return response()->json([
            'version' => '1.0',
            'last_updated' => '2024-11-03',
            'url' => url('/privacy-policy'),
            'content' => 'Privacy policy content here...',
        ]);
    }

    /**
     * Download terms of service
     */
    public function termsOfService(Request $request)
    {
        return response()->json([
            'version' => '1.0',
            'last_updated' => '2024-11-03',
            'url' => url('/terms-of-service'),
            'content' => 'Terms of service content here...',
        ]);
    }
}

