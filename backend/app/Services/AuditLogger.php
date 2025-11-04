<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Log security-sensitive actions
     */
    public function log(string $action, string $entity, array $details = [], string $severity = 'info'): void
    {
        $request = request();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => $entity,
            'entity_id' => $details['entity_id'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'details' => $this->sanitizeDetails($details),
            'severity' => $severity,
            'session_id' => session()->getId(),
            'request_id' => $request->header('X-Request-ID') ?? uniqid('req_', true),
            'created_at' => now(),
        ]);

        // Log to external security monitoring if critical
        if (in_array($severity, ['critical', 'alert', 'emergency'])) {
            $this->notifySecurityTeam($action, $entity, $details, $severity);
        }
    }

    /**
     * Log authentication events
     */
    public function logAuth(string $event, ?int $userId = null, bool $success = true): void
    {
        $this->log(
            "auth.{$event}",
            'User',
            [
                'entity_id' => $userId,
                'success' => $success,
                'timestamp' => now()->toIso8601String(),
            ],
            $success ? 'info' : 'warning'
        );
    }

    /**
     * Log data access events
     */
    public function logDataAccess(string $entity, int $entityId, string $accessType = 'read'): void
    {
        $this->log(
            "data_access.{$accessType}",
            $entity,
            [
                'entity_id' => $entityId,
                'access_type' => $accessType,
            ]
        );
    }

    /**
     * Log security incidents
     */
    public function logSecurityIncident(string $incidentType, array $details): void
    {
        $this->log(
            "security.{$incidentType}",
            'Security',
            $details,
            'critical'
        );
    }

    /**
     * Log GDPR-related actions
     */
    public function logGDPR(string $action, int $userId, array $details = []): void
    {
        $this->log(
            "gdpr.{$action}",
            'User',
            array_merge($details, ['entity_id' => $userId]),
            'info'
        );
    }

    /**
     * Sanitize sensitive data before logging
     */
    protected function sanitizeDetails(array $details): array
    {
        $sensitiveKeys = ['password', 'token', 'secret', 'api_key', 'ssn', 'credit_card'];

        foreach ($details as $key => $value) {
            if (is_string($key) && $this->isSensitiveKey($key, $sensitiveKeys)) {
                $details[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $details[$key] = $this->sanitizeDetails($value);
            }
        }

        return $details;
    }

    /**
     * Check if key contains sensitive information
     */
    protected function isSensitiveKey(string $key, array $sensitiveKeys): bool
    {
        $lowercaseKey = strtolower($key);

        foreach ($sensitiveKeys as $sensitiveKey) {
            if (str_contains($lowercaseKey, $sensitiveKey)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Notify security team of critical events
     */
    protected function notifySecurityTeam(string $action, string $entity, array $details, string $severity): void
    {
        // Integration with security monitoring tools (e.g., Splunk, DataDog)
        // This is a placeholder for actual implementation
        \Log::channel('security')->critical("Security Alert: {$action}", [
            'entity' => $entity,
            'severity' => $severity,
            'details' => $details,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
