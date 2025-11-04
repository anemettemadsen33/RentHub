<?php

namespace App\Services\Security;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * Log authentication event
     */
    public function logAuthentication(User $user, string $event, bool $success, array $metadata = []): void
    {
        if (!config('security.audit.log_authentication', true)) {
            return;
        }

        $this->createLog([
            'user_id' => $user->id,
            'event_type' => 'authentication',
            'event_name' => $event,
            'success' => $success,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => array_merge($metadata, [
                'method' => $event,
                'timestamp' => now()->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Log authorization failure
     */
    public function logAuthorizationFailure(User $user, string $resource, string $action, array $metadata = []): void
    {
        if (!config('security.audit.log_authorization_failures', true)) {
            return;
        }

        $this->createLog([
            'user_id' => $user->id,
            'event_type' => 'authorization_failure',
            'event_name' => "Failed to {$action} {$resource}",
            'success' => false,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => array_merge($metadata, [
                'resource' => $resource,
                'action' => $action,
                'role' => $user->role,
            ]),
        ]);

        Log::warning('Authorization failure', [
            'user_id' => $user->id,
            'resource' => $resource,
            'action' => $action,
        ]);
    }

    /**
     * Log data access
     */
    public function logDataAccess(User $user, string $model, $modelId, string $action = 'view', array $metadata = []): void
    {
        if (!config('security.audit.log_data_access', true)) {
            return;
        }

        $this->createLog([
            'user_id' => $user->id,
            'event_type' => 'data_access',
            'event_name' => "{$action} {$model}",
            'success' => true,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => array_merge($metadata, [
                'model' => $model,
                'model_id' => $modelId,
                'action' => $action,
            ]),
        ]);
    }

    /**
     * Log data modification
     */
    public function logDataModification(
        User $user,
        string $model,
        $modelId,
        string $action,
        array $oldValues = [],
        array $newValues = []
    ): void {
        if (!config('security.audit.log_data_modifications', true)) {
            return;
        }

        $this->createLog([
            'user_id' => $user->id,
            'event_type' => 'data_modification',
            'event_name' => "{$action} {$model}",
            'success' => true,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => [
                'model' => $model,
                'model_id' => $modelId,
                'action' => $action,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'changes' => $this->getChanges($oldValues, $newValues),
            ],
        ]);
    }

    /**
     * Log admin action
     */
    public function logAdminAction(User $admin, string $action, array $metadata = []): void
    {
        if (!config('security.audit.log_admin_actions', true)) {
            return;
        }

        if ($admin->role !== 'admin') {
            return;
        }

        $this->createLog([
            'user_id' => $admin->id,
            'event_type' => 'admin_action',
            'event_name' => $action,
            'success' => true,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => array_merge($metadata, [
                'action' => $action,
                'admin_name' => $admin->name,
            ]),
        ]);

        Log::info('Admin action', [
            'admin_id' => $admin->id,
            'action' => $action,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $event, bool $success, array $metadata = []): void
    {
        $this->createLog([
            'user_id' => auth()->id(),
            'event_type' => 'security',
            'event_name' => $event,
            'success' => $success,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);

        if (!$success) {
            Log::warning('Security event', [
                'event' => $event,
                'metadata' => $metadata,
            ]);
        }
    }

    /**
     * Log suspicious activity
     */
    public function logSuspiciousActivity(string $activity, int $severity, array $metadata = []): void
    {
        $this->createLog([
            'user_id' => auth()->id(),
            'event_type' => 'suspicious_activity',
            'event_name' => $activity,
            'success' => false,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => array_merge($metadata, [
                'severity' => $severity,
            ]),
        ]);

        Log::alert('Suspicious activity detected', [
            'activity' => $activity,
            'severity' => $severity,
            'ip' => request()->ip(),
            'metadata' => $metadata,
        ]);

        // Trigger alerts if monitoring is enabled
        if (config('security.monitoring.alert_on_suspicious_activity', true)) {
            $this->triggerSecurityAlert($activity, $severity, $metadata);
        }
    }

    /**
     * Create audit log entry
     */
    protected function createLog(array $data): void
    {
        try {
            AuditLog::create($data);
        } catch (\Exception $e) {
            Log::error('Failed to create audit log', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }

    /**
     * Get changes between old and new values
     */
    protected function getChanges(array $oldValues, array $newValues): array
    {
        $changes = [];

        foreach ($newValues as $key => $newValue) {
            $oldValue = $oldValues[$key] ?? null;

            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    /**
     * Trigger security alert
     */
    protected function triggerSecurityAlert(string $activity, int $severity, array $metadata): void
    {
        $channels = config('security.monitoring.alert_channels', ['email']);

        foreach ($channels as $channel) {
            try {
                switch ($channel) {
                    case 'email':
                        $this->sendEmailAlert($activity, $severity, $metadata);
                        break;
                    case 'slack':
                        $this->sendSlackAlert($activity, $severity, $metadata);
                        break;
                    case 'sms':
                        $this->sendSmsAlert($activity, $severity, $metadata);
                        break;
                }
            } catch (\Exception $e) {
                Log::error("Failed to send {$channel} alert", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function sendEmailAlert(string $activity, int $severity, array $metadata): void
    {
        // Implementation for email alerts
    }

    protected function sendSlackAlert(string $activity, int $severity, array $metadata): void
    {
        // Implementation for Slack alerts
    }

    protected function sendSmsAlert(string $activity, int $severity, array $metadata): void
    {
        // Implementation for SMS alerts
    }

    /**
     * Get audit logs for user
     */
    public function getUserLogs(User $user, array $filters = [])
    {
        $query = AuditLog::where('user_id', $user->id);

        if (isset($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (isset($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(50);
    }

    /**
     * Cleanup old audit logs
     */
    public function cleanupOldLogs(): int
    {
        $retentionDays = config('security.audit.retention_days', 365);
        $cutoffDate = now()->subDays($retentionDays);

        return AuditLog::where('created_at', '<', $cutoffDate)->delete();
    }
}
