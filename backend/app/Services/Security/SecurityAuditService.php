<?php

namespace App\Services\Security;

use App\Models\SecurityAuditLog;
use App\Models\SecurityIncident;
use App\Models\User;
use App\Notifications\SecurityAlertNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SecurityAuditService
{
    /**
     * Log authentication event
     */
    public function logAuthentication(User $user, string $event, bool $successful, array $metadata = []): void
    {
        $this->log('authentication', $event, $user->id, $successful, array_merge($metadata, [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]));

        if (! $successful) {
            $this->checkFailedLoginThreshold($user);
        }
    }

    /**
     * Log authorization failure
     */
    public function logAuthorizationFailure(User $user, string $resource, string $action, array $metadata = []): void
    {
        $this->log('authorization', 'access_denied', $user->id, false, array_merge($metadata, [
            'resource' => $resource,
            'action' => $action,
            'ip' => request()->ip(),
        ]));

        $this->checkUnauthorizedAccessThreshold($user);
    }

    /**
     * Log data access
     */
    public function logDataAccess(User $user, string $resource, string $action, array $metadata = []): void
    {
        if (! config('security.audit.log_data_access', true)) {
            return;
        }

        $this->log('data_access', $action, $user->id, true, array_merge($metadata, [
            'resource' => $resource,
            'ip' => request()->ip(),
        ]));
    }

    /**
     * Log data modification
     */
    public function logDataModification(User $user, string $resource, string $action, array $before = [], array $after = []): void
    {
        if (! config('security.audit.log_data_modifications', true)) {
            return;
        }

        $this->log('data_modification', $action, $user->id, true, [
            'resource' => $resource,
            'before' => $before,
            'after' => $after,
            'ip' => request()->ip(),
        ]);
    }

    /**
     * Log admin action
     */
    public function logAdminAction(User $admin, string $action, array $metadata = []): void
    {
        if (! config('security.audit.log_admin_actions', true)) {
            return;
        }

        $this->log('admin_action', $action, $admin->id, true, array_merge($metadata, [
            'ip' => request()->ip(),
            'admin_role' => $admin->role,
        ]));
    }

    /**
     * Create security incident
     */
    public function createIncident(string $type, string $severity, string $description, array $metadata = []): SecurityIncident
    {
        $incident = SecurityIncident::create([
            'type' => $type,
            'severity' => $severity,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => 'open',
            'detected_at' => now(),
        ]);

        $this->notifySecurityTeam($incident);

        return $incident;
    }

    /**
     * Check failed login threshold
     */
    private function checkFailedLoginThreshold(User $user): void
    {
        $threshold = config('security.monitoring.thresholds.failed_logins', 5);
        $window = 15; // minutes

        $count = SecurityAuditLog::where('user_id', $user->id)
            ->where('category', 'authentication')
            ->where('successful', false)
            ->where('created_at', '>', now()->subMinutes($window))
            ->count();

        if ($count >= $threshold) {
            $this->createIncident(
                'suspicious_login_attempts',
                'high',
                "Multiple failed login attempts detected for user {$user->id}",
                ['user_id' => $user->id, 'attempt_count' => $count]
            );

            // Lock account temporarily
            $user->update(['locked_until' => now()->addHour()]);
        }
    }

    /**
     * Check unauthorized access threshold
     */
    private function checkUnauthorizedAccessThreshold(User $user): void
    {
        $threshold = config('security.monitoring.thresholds.unauthorized_access_attempts', 3);
        $window = 10; // minutes

        $count = SecurityAuditLog::where('user_id', $user->id)
            ->where('category', 'authorization')
            ->where('successful', false)
            ->where('created_at', '>', now()->subMinutes($window))
            ->count();

        if ($count >= $threshold) {
            $this->createIncident(
                'unauthorized_access_attempts',
                'high',
                "Multiple unauthorized access attempts by user {$user->id}",
                ['user_id' => $user->id, 'attempt_count' => $count]
            );
        }
    }

    /**
     * Notify security team
     */
    private function notifySecurityTeam(SecurityIncident $incident): void
    {
        if (! config('security.monitoring.alert_on_suspicious_activity', true)) {
            return;
        }

        $channels = config('security.monitoring.alert_channels', ['email']);

        // Get security team users
        $securityTeam = User::whereHas('roles', function ($query) {
            $query->where('name', 'security_admin');
        })->get();

        foreach ($securityTeam as $admin) {
            Notification::send($admin, new SecurityAlertNotification($incident));
        }

        Log::channel('security')->critical('Security Incident Created', [
            'incident_id' => $incident->id,
            'type' => $incident->type,
            'severity' => $incident->severity,
        ]);
    }

    /**
     * Base log method
     */
    private function log(string $category, string $event, ?int $userId, bool $successful, array $metadata): void
    {
        SecurityAuditLog::create([
            'category' => $category,
            'event' => $event,
            'user_id' => $userId,
            'successful' => $successful,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }

    /**
     * Get user audit trail
     */
    public function getUserAuditTrail(User $user, int $days = 30): array
    {
        return SecurityAuditLog::where('user_id', $user->id)
            ->where('created_at', '>', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get recent security incidents
     */
    public function getRecentIncidents(int $hours = 24): array
    {
        return SecurityIncident::where('detected_at', '>', now()->subHours($hours))
            ->orderBy('detected_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Clean old audit logs
     */
    public function cleanOldLogs(): int
    {
        $retentionDays = config('security.audit.retention_days', 365);

        return SecurityAuditLog::where('created_at', '<', now()->subDays($retentionDays))
            ->delete();
    }
}
