<?php

namespace App\\Http\\Controllers\\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FraudAlert;
use App\Services\AI\FraudDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FraudDetectionController extends Controller
{
    public function __construct(
        private FraudDetectionService $fraudDetectionService
    ) {}

    /**
     * Analyze user for suspicious activity (Admin only)
     */
    public function analyzeUser(int $userId): JsonResponse
    {
        if (! auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $alert = $this->fraudDetectionService->analyzeUserBehavior($userId);

        return response()->json([
            'success' => true,
            'data' => [
                'alert_created' => $alert !== null,
                'alert' => $alert,
            ],
        ]);
    }

    /**
     * Analyze property listing (Admin only)
     */
    public function analyzeProperty(int $propertyId): JsonResponse
    {
        if (! auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $alert = $this->fraudDetectionService->analyzePropertyListing($propertyId);

        return response()->json([
            'success' => true,
            'data' => [
                'alert_created' => $alert !== null,
                'alert' => $alert,
            ],
        ]);
    }

    /**
     * Analyze payment (Admin only)
     */
    public function analyzePayment(int $paymentId): JsonResponse
    {
        if (! auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $alert = $this->fraudDetectionService->analyzePayment($paymentId);

        return response()->json([
            'success' => true,
            'data' => [
                'alert_created' => $alert !== null,
                'alert' => $alert,
            ],
        ]);
    }

    /**
     * Analyze review (Admin only)
     */
    public function analyzeReview(int $reviewId): JsonResponse
    {
        if (! auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $alert = $this->fraudDetectionService->analyzeReview($reviewId);

        return response()->json([
            'success' => true,
            'data' => [
                'alert_created' => $alert !== null,
                'alert' => $alert,
            ],
        ]);
    }

    /**
     * Analyze booking (Admin only)
     */
    public function analyzeBooking(int $bookingId): JsonResponse
    {
        if (! auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $alert = $this->fraudDetectionService->analyzeBooking($bookingId);

        return response()->json([
            'success' => true,
            'data' => [
                'alert_created' => $alert !== null,
                'alert' => $alert,
            ],
        ]);
    }

    /**
     * Get all fraud alerts (Admin only)
     */
    public function getAlerts(Request $request): JsonResponse
    {
        if (! auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $query = FraudAlert::with(['user', 'property', 'booking', 'payment'])
            ->orderByDesc('fraud_score')
            ->orderByDesc('created_at');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by severity
        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('alert_type', $request->type);
        }

        $alerts = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $alerts,
        ]);
    }

    /**
     * Get fraud alert details (Admin only)
     */
    public function getAlertDetails(int $alertId): JsonResponse
    {
        if (! auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $alert = FraudAlert::with(['user', 'property', 'booking', 'payment', 'reviewer'])
            ->findOrFail($alertId);

        return response()->json([
            'success' => true,
            'data' => $alert,
        ]);
    }

    /**
     * Resolve fraud alert (Admin only)
     */
    public function resolveAlert(Request $request, int $alertId): JsonResponse
    {
        if (! auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'resolution_notes' => 'required|string',
            'action_type' => 'nullable|string|in:account_suspended,property_removed,payment_blocked,no_action',
        ]);

        $alert = FraudAlert::findOrFail($alertId);

        $alert->resolve(
            auth()->id(),
            $request->resolution_notes,
            $request->action_type
        );

        return response()->json([
            'success' => true,
            'message' => 'Alert resolved successfully',
            'data' => $alert,
        ]);
    }

    /**
     * Mark alert as false positive (Admin only)
     */
    public function markFalsePositive(Request $request, int $alertId): JsonResponse
    {
        if (! auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'notes' => 'required|string',
        ]);

        $alert = FraudAlert::findOrFail($alertId);
        $alert->markFalsePositive(auth()->id(), $request->notes);

        return response()->json([
            'success' => true,
            'message' => 'Marked as false positive',
            'data' => $alert,
        ]);
    }

    /**
     * Get fraud statistics (Admin only)
     */
    public function getStatistics(Request $request): JsonResponse
    {
        if (! auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $stats = [
            'total_alerts' => FraudAlert::count(),
            'pending_alerts' => FraudAlert::where('status', 'pending')->count(),
            'resolved_alerts' => FraudAlert::where('status', 'resolved')->count(),
            'false_positives' => FraudAlert::where('status', 'false_positive')->count(),
            'by_severity' => FraudAlert::selectRaw('severity, COUNT(*) as count')
                ->groupBy('severity')
                ->pluck('count', 'severity'),
            'by_type' => FraudAlert::selectRaw('alert_type, COUNT(*) as count')
                ->groupBy('alert_type')
                ->pluck('count', 'alert_type'),
            'avg_fraud_score' => round(FraudAlert::avg('fraud_score'), 2),
            'actions_taken' => FraudAlert::where('action_taken', true)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}

