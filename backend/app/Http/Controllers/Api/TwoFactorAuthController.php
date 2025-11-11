<?php

namespace App\\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Services\Security\AuditLogService;
use App\Services\Security\TwoFactorAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TwoFactorAuthController extends Controller
{
    public function __construct(
        protected TwoFactorAuthService $twoFactorAuthService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Enable 2FA
     */
    public function enable(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'method' => 'required|in:totp,sms,email',
            'password' => 'required|string',
        ]);

        $user = $request->user();

        // Verify password
        if (! Hash::check($validated['password'], $user->password)) {
            return response()->json(['error' => 'Invalid password'], 401);
        }

        $result = $this->twoFactorAuthService->enable($user, $validated['method']);

        $this->auditLogService->logSecurityEvent('2fa_enabled', true, [
            'method' => $validated['method'],
        ]);

        return response()->json([
            'message' => '2FA enabled successfully',
            'data' => $result,
        ]);
    }

    /**
     * Verify 2FA setup
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();

        if ($this->twoFactorAuthService->verify($user, $validated['code'])) {
            // Activate 2FA
            $user->twoFactorAuth()->update(['enabled' => true]);

            $this->auditLogService->logSecurityEvent('2fa_verified', true);

            return response()->json([
                'message' => '2FA verified and activated successfully',
            ]);
        }

        $this->auditLogService->logSecurityEvent('2fa_verification_failed', false, [
            'code' => substr($validated['code'], 0, 2).'****',
        ]);

        return response()->json(['error' => 'Invalid code'], 400);
    }

    /**
     * Send 2FA code
     */
    public function sendCode(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($this->twoFactorAuthService->sendCode($user)) {
            return response()->json([
                'message' => '2FA code sent successfully',
            ]);
        }

        return response()->json(['error' => 'Failed to send code'], 500);
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        // Verify password
        if (! Hash::check($validated['password'], $user->password)) {
            return response()->json(['error' => 'Invalid password'], 401);
        }

        if ($this->twoFactorAuthService->disable($user)) {
            $this->auditLogService->logSecurityEvent('2fa_disabled', true);

            return response()->json([
                'message' => '2FA disabled successfully',
            ]);
        }

        return response()->json(['error' => 'Failed to disable 2FA'], 500);
    }

    /**
     * Regenerate backup codes
     */
    public function regenerateBackupCodes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        // Verify password
        if (! Hash::check($validated['password'], $user->password)) {
            return response()->json(['error' => 'Invalid password'], 401);
        }

        $codes = $this->twoFactorAuthService->generateBackupCodes($user);

        $this->auditLogService->logSecurityEvent('backup_codes_regenerated', true);

        return response()->json([
            'message' => 'Backup codes regenerated successfully',
            'backup_codes' => $codes,
        ]);
    }

    /**
     * Get 2FA status
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $twoFactor = $user->twoFactorAuth;

        return response()->json([
            'enabled' => $twoFactor ? $twoFactor->enabled : false,
            'method' => $twoFactor?->method,
            'enforced' => $this->twoFactorAuthService->isEnforced($user),
            'last_used_at' => $twoFactor?->last_used_at,
        ]);
    }
}

