<?php

namespace App\Http\\Controllers\\Api\Security;

use App\Http\Controllers\Controller;
use App\Services\Security\SecurityAuditService;
use App\Services\Security\VulnerabilityScanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecurityAuditController extends Controller
{
    public function __construct(
        private SecurityAuditService $auditService,
        private VulnerabilityScanner $scanner
    ) {}

    public function getUserAuditTrail(Request $request): JsonResponse
    {
        $request->validate(['days' => 'nullable|integer|min:1|max:365']);

        $trail = $this->auditService->getUserAuditTrail(
            $request->user(),
            $request->days ?? 30
        );

        return response()->json(['data' => $trail]);
    }

    public function getSecurityIncidents(Request $request): JsonResponse
    {
        $request->validate(['hours' => 'nullable|integer|min:1|max:720']);

        $incidents = $this->auditService->getRecentIncidents($request->hours ?? 24);

        return response()->json(['data' => $incidents]);
    }

    public function runVulnerabilityScan(Request $request): JsonResponse
    {
        $this->authorize('run-security-scans');

        $results = $this->scanner->runScan();

        return response()->json(['data' => $results]);
    }

    public function getSecurityReport(Request $request): JsonResponse
    {
        $this->authorize('view-security-reports');

        $report = $this->scanner->generateReport();

        return response()->json(['report' => $report]);
    }
}

