<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SecurityAuditService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecurityAuditController extends Controller
{
    protected SecurityAuditService $auditService;

    public function __construct(SecurityAuditService $auditService)
    {
        $this->auditService = $auditService;
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin');
    }

    /**
     * Get audit logs
     *
     * @OA\Get(
     *     path="/api/security/audit-logs",
     *     summary="Get security audit logs",
     *     tags={"Security"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="start_date", in="query", description="Start date", schema={"type":"string", "format":"date"}),
     *     @OA\Parameter(name="end_date", in="query", description="End date", schema={"type":"string", "format":"date"}),
     *
     *     @OA\Response(response=200, description="Audit logs retrieved")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subDays(7);
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        $report = $this->auditService->getAuditReport($startDate, $endDate);

        return response()->json($report);
    }

    /**
     * Detect anomalies
     *
     * @OA\Get(
     *     path="/api/security/anomalies",
     *     summary="Detect security anomalies",
     *     tags={"Security"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="Anomalies detected")
     * )
     */
    public function detectAnomalies(): JsonResponse
    {
        $anomalies = $this->auditService->detectAnomalies();

        return response()->json([
            'anomalies' => $anomalies,
            'total_anomalies' => count($anomalies),
            'checked_at' => Carbon::now(),
        ]);
    }

    /**
     * Log security event manually
     *
     * @OA\Post(
     *     path="/api/security/log",
     *     summary="Log security event",
     *     tags={"Security"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="event", type="string"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="severity", type="string", enum={"info","warning","critical"})
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Event logged")
     * )
     */
    public function logEvent(Request $request): JsonResponse
    {
        $request->validate([
            'event' => 'required|string',
            'data' => 'nullable|array',
            'severity' => 'required|in:info,warning,critical',
        ]);

        $this->auditService->log(
            $request->event,
            $request->data ?? [],
            $request->user()->id,
            $request->severity
        );

        return response()->json([
            'message' => 'Security event logged successfully',
        ], 201);
    }

    /**
     * Cleanup old logs
     *
     * @OA\Delete(
     *     path="/api/security/cleanup",
     *     summary="Cleanup old audit logs",
     *     tags={"Security"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="days", in="query", description="Days to keep", schema={"type":"integer"}),
     *
     *     @OA\Response(response=200, description="Logs cleaned up")
     * )
     */
    public function cleanup(Request $request): JsonResponse
    {
        $days = $request->days ?? 90;
        $deleted = $this->auditService->cleanupOldLogs($days);

        return response()->json([
            'message' => "Cleaned up audit logs older than {$days} days",
            'deleted_count' => $deleted,
        ]);
    }
}

