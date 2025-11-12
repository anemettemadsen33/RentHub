<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Security\AuditLogService;
use App\Services\Security\CCPAService;
use App\Services\Security\GDPRService;
use App\Services\Security\TwoFactorAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
        protected TwoFactorAuthService $twoFactorAuthService,
        protected CCPAService $ccpaService,
        protected GDPRService $gdprService
    ) {}

    /**
     * Get security overview
     */
    public function overview(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'two_factor' => [
                'enabled' => $this->twoFactorAuthService->isEnabled($user),
                'enforced' => $this->twoFactorAuthService->isEnforced($user),
            ],
            'data_protection' => [
                'gdpr_consent' => $user->gdpr_consent ?? false,
                'ccpa_do_not_sell' => $user->ccpa_do_not_sell ?? false,
            ],
            'security_score' => $this->calculateSecurityScore($user),
            'recommendations' => $this->getSecurityRecommendations($user),
        ]);
    }

    /**
     * Get audit logs
     */
    public function auditLogs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_type' => 'sometimes|string',
            'from_date' => 'sometimes|date',
            'to_date' => 'sometimes|date',
        ]);

        $logs = $this->auditLogService->getUserLogs($request->user(), $validated);

        return response()->json($logs);
    }

    /**
     * Calculate security score
     */
    protected function calculateSecurityScore($user): int
    {
        $score = 0;

        // Two-factor authentication (30 points)
        if ($this->twoFactorAuthService->isEnabled($user)) {
            $score += 30;
        }

        // Strong password (20 points)
        if ($user->password_changed_at && $user->password_changed_at->diffInDays() < 90) {
            $score += 20;
        }

        // Email verified (15 points)
        if ($user->email_verified_at) {
            $score += 15;
        }

        // GDPR consent (15 points)
        if ($user->gdpr_consent) {
            $score += 15;
        }

        // No recent failed logins (20 points)
        if ($user->failed_login_attempts == 0) {
            $score += 20;
        }

        return $score;
    }

    /**
     * Get security recommendations
     */
    protected function getSecurityRecommendations($user): array
    {
        $recommendations = [];

        if (! $this->twoFactorAuthService->isEnabled($user)) {
            $recommendations[] = [
                'type' => '2fa',
                'priority' => 'high',
                'message' => 'Enable two-factor authentication for enhanced security',
            ];
        }

        if (! $user->password_changed_at || $user->password_changed_at->diffInDays() > 90) {
            $recommendations[] = [
                'type' => 'password',
                'priority' => 'medium',
                'message' => 'Change your password regularly (recommended every 90 days)',
            ];
        }

        if (! $user->email_verified_at) {
            $recommendations[] = [
                'type' => 'email',
                'priority' => 'high',
                'message' => 'Verify your email address',
            ];
        }

        return $recommendations;
    }
}

