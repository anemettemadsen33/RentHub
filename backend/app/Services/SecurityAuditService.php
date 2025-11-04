<?php

namespace App\Services;

use App\Models\SecurityAuditLog;
use Illuminate\Support\Facades\Auth;

class SecurityAuditService
{
    /**
     * Log security event
     */
    public function logEvent(string $event, string $level = 'info', array $context = []): void
    {
        SecurityAuditLog::create([
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'event_type' => $event,
            'level' => $level,
            'description' => $context['description'] ?? null,
            'metadata' => json_encode($context),
            'created_at' => now(),
        ]);
    }

    /**
     * Log authentication attempt
     */
    public function logAuthAttempt(string $email, bool $successful): void
    {
        $this->logEvent('auth.attempt', $successful ? 'info' : 'warning', [
            'email' => $email,
            'successful' => $successful,
            'description' => $successful ? 'Successful login' : 'Failed login attempt',
        ]);
    }

    /**
     * Log permission denied
     */
    public function logPermissionDenied(string $action, string $resource): void
    {
        $this->logEvent('permission.denied', 'warning', [
            'action' => $action,
            'resource' => $resource,
            'description' => "Permission denied for {$action} on {$resource}",
        ]);
    }

    /**
     * Log data access
     */
    public function logDataAccess(string $model, int $id, string $action): void
    {
        $this->logEvent('data.access', 'info', [
            'model' => $model,
            'model_id' => $id,
            'action' => $action,
            'description' => "User accessed {$model} #{$id} for {$action}",
        ]);
    }

    /**
     * Log suspicious activity
     */
    public function logSuspiciousActivity(string $description, array $context = []): void
    {
        $this->logEvent('security.suspicious', 'critical', array_merge([
            'description' => $description,
        ], $context));

        // Send alert to security team
        $this->sendSecurityAlert($description, $context);
    }

    /**
     * Log intrusion attempt
     */
    public function logIntrusionAttempt(string $type, array $details): void
    {
        $this->logEvent('security.intrusion', 'critical', [
            'type' => $type,
            'details' => $details,
            'description' => "Intrusion attempt detected: {$type}",
        ]);

        // Immediate notification
        $this->sendSecurityAlert("Intrusion attempt: {$type}", $details);
    }

    /**
     * Get audit logs for user
     */
    public function getUserAuditLogs(int $userId, int $limit = 100)
    {
        return SecurityAuditLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs by event type
     */
    public function getAuditLogsByEvent(string $eventType, int $limit = 100)
    {
        return SecurityAuditLog::where('event_type', $eventType)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get security incidents (warnings and critical)
     */
    public function getSecurityIncidents(int $limit = 50)
    {
        return SecurityAuditLog::whereIn('level', ['warning', 'critical'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Send security alert
     */
    protected function sendSecurityAlert(string $message, array $context): void
    {
        \Log::critical('Security Alert', [
            'message' => $message,
            'context' => $context,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => Auth::id(),
        ]);

        // Send email notification to security team
        // \Notification::route('mail', config('app.security_email'))
        //     ->notify(new SecurityAlertNotification($message, $context));
    }
}
