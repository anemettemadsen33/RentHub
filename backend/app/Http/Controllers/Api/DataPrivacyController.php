<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Security\AuditLogService;
use App\Services\Security\CCPAService;
use App\Services\Security\GDPRService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DataPrivacyController extends Controller
{
    public function __construct(
        protected CCPAService $ccpaService,
        protected GDPRService $gdprService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Give GDPR consent
     */
    public function giveGdprConsent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'consent_text' => 'required|string',
            'purposes' => 'required|array',
        ]);

        $user = $request->user();

        $consent = $this->gdprService->recordConsent($user, [
            'purposes' => $validated['purposes'],
            'consent_text' => $validated['consent_text'],
        ]);

        $user->update([
            'gdpr_consent' => true,
            'gdpr_consent_date' => now(),
        ]);

        $this->auditLogService->logSecurityEvent('gdpr_consent_given', true);

        return response()->json([
            'message' => 'GDPR consent recorded successfully',
            'consent' => $consent,
        ]);
    }

    /**
     * Withdraw GDPR consent
     */
    public function withdrawGdprConsent(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->update([
            'gdpr_consent' => false,
        ]);

        $this->auditLogService->logSecurityEvent('gdpr_consent_withdrawn', true);

        return response()->json([
            'message' => 'GDPR consent withdrawn successfully',
        ]);
    }

    /**
     * CCPA opt-out of data sale
     */
    public function ccpaOptOut(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($this->ccpaService->optOutOfDataSale($user)) {
            return response()->json([
                'message' => 'Successfully opted out of data sale',
            ]);
        }

        return response()->json(['error' => 'Failed to opt out'], 500);
    }

    /**
     * Request data disclosure (CCPA)
     */
    public function requestDataDisclosure(Request $request): JsonResponse
    {
        $user = $request->user();

        $disclosure = $this->ccpaService->requestDataDisclosure($user);

        $this->auditLogService->logSecurityEvent('data_disclosure_requested', true);

        return response()->json($disclosure);
    }

    /**
     * Export user data
     */
    public function exportData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'format' => 'sometimes|in:json,csv,pdf',
        ]);

        $user = $request->user();
        $format = $validated['format'] ?? 'json';

        $data = $this->gdprService->exportUserData($user, $format);

        $this->auditLogService->logSecurityEvent('data_export_requested', true, [
            'format' => $format,
        ]);

        // Store the export file
        $filename = "data-export-{$user->id}-".now()->format('Y-m-d-His').".{$format}";

        if ($format === 'json') {
            $content = json_encode($data, JSON_PRETTY_PRINT);
        } else {
            $content = $this->convertToCsv($data);
        }

        Storage::disk('private')->put("exports/{$filename}", $content);

        return response()->json([
            'message' => 'Data export completed successfully',
            'download_url' => route('api.privacy.download-export', ['filename' => $filename]),
            'expires_at' => now()->addDays(7),
        ]);
    }

    /**
     * Request data deletion (Right to be forgotten)
     */
    public function requestDataDeletion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'categories' => 'sometimes|array',
            'reason' => 'sometimes|string',
        ]);

        $user = $request->user();

        $result = $this->gdprService->requestDataDeletion($user, $validated);

        if ($result['success']) {
            $this->auditLogService->logSecurityEvent('data_deletion_requested', true, [
                'categories' => $validated['categories'] ?? ['all'],
            ]);
        }

        return response()->json($result);
    }

    /**
     * Cancel data deletion request
     */
    public function cancelDataDeletion(Request $request): JsonResponse
    {
        $user = $request->user();

        $cancelled = $user->dataDeletionRequests()
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        if ($cancelled) {
            $this->auditLogService->logSecurityEvent('data_deletion_cancelled', true);

            return response()->json([
                'message' => 'Data deletion request cancelled successfully',
            ]);
        }

        return response()->json(['error' => 'No pending deletion request found'], 404);
    }

    /**
     * Get privacy settings
     */
    public function getPrivacySettings(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'gdpr' => [
                'consent_given' => $user->gdpr_consent ?? false,
                'consent_date' => $user->gdpr_consent_date,
            ],
            'ccpa' => [
                'do_not_sell' => $user->ccpa_do_not_sell ?? false,
                'opt_out_date' => $user->ccpa_opt_out_date,
            ],
            'data_retention' => [
                'policy_days' => config('security.gdpr.data_retention_days'),
                'account_created' => $user->created_at,
            ],
            'pending_requests' => [
                'export' => $user->dataExportRequests()->where('status', 'pending')->count(),
                'deletion' => $user->dataDeletionRequests()->where('status', 'pending')->count(),
            ],
        ]);
    }

    /**
     * Download data export
     */
    public function downloadExport(string $filename): mixed
    {
        $user = auth()->user();
        $path = "exports/{$filename}";

        // Verify the file belongs to the user
        if (! str_contains($filename, "data-export-{$user->id}-")) {
            abort(403, 'Unauthorized access');
        }

        if (! Storage::disk('private')->exists($path)) {
            abort(404, 'Export file not found or expired');
        }

        return Storage::disk('private')->download($path);
    }

    /**
     * Convert data to CSV format
     */
    protected function convertToCsv(array $data): string
    {
        $csv = '';

        foreach ($data as $section => $records) {
            $csv .= strtoupper($section)."\n\n";

            if (is_array($records) && ! empty($records)) {
                if (isset($records[0]) && is_array($records[0])) {
                    // Array of records
                    $headers = array_keys($records[0]);
                    $csv .= implode(',', $headers)."\n";

                    foreach ($records as $record) {
                        $csv .= implode(',', array_values($record))."\n";
                    }
                } else {
                    // Single record
                    foreach ($records as $key => $value) {
                        $csv .= "{$key},".(is_array($value) ? json_encode($value) : $value)."\n";
                    }
                }
            }

            $csv .= "\n";
        }

        return $csv;
    }
}

