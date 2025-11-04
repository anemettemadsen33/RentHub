<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuditLogService
{
    public function log(string $action, string $entity, $entityId = null, array $data = [], string $level = 'info'): void
    {
        $user = Auth::user();
        $request = request();

        AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'data' => $this->sanitizeData($data),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'level' => $level,
            'created_at' => now(),
        ]);
    }

    public function logSecurityEvent(string $event, array $data = []): void
    {
        $this->log($event, 'security', null, $data, 'warning');
    }

    public function logAuthentication(string $action, $userId = null, bool $success = true): void
    {
        $this->log($action, 'authentication', $userId, [
            'success' => $success,
            'timestamp' => now()->toIso8601String(),
        ], $success ? 'info' : 'warning');
    }

    public function logDataAccess(string $entity, $entityId, string $action = 'view'): void
    {
        $this->log($action, $entity, $entityId, [], 'info');
    }

    public function logDataModification(string $entity, $entityId, array $changes): void
    {
        $this->log('update', $entity, $entityId, [
            'changes' => $this->sanitizeData($changes),
        ], 'info');
    }

    protected function sanitizeData(array $data): array
    {
        $sensitiveKeys = [
            'password', 'token', 'secret', 'api_key', 'credit_card',
            'ssn', 'passport', 'bank_account'
        ];

        $sanitized = [];

        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
