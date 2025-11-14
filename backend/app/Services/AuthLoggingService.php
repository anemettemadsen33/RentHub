<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Centralized Authentication Logging Service
 * 
 * Provides comprehensive logging for all authentication events with:
 * - Structured logging with context
 * - Real-time monitoring and alerting
 * - Audit trail for compliance
 * - Performance metrics
 * - Security incident detection
 */
class AuthLoggingService
{
    /**
     * Log authentication attempt
     */
    public function logAuthAttempt(array $credentials, bool $success, ?string $error = null): void
    {
        $context = [
            'event' => 'auth_attempt',
            'success' => $success,
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'timestamp' => now()->toDateTimeString(),
            'request_id' => $this->getRequestId(),
        ];

        // Add credential identifier (without sensitive data)
        if (isset($credentials['email'])) {
            $context['identifier'] = $this->maskEmail($credentials['email']);
        }

        if ($error) {
            $context['error'] = $error;
        }

        if ($success && Auth::check()) {
            $context['user_id'] = Auth::id();
            $context['user_email'] = Auth::user()->email;
        }

        // Log with appropriate level
        if ($success) {
            Log::info('Authentication successful', $context);
        } else {
            Log::warning('Authentication failed', $context);
            
            // Track failed attempts for rate limiting
            $this->trackFailedAttempt(Request::ip(), $credentials);
        }

        // Store in cache for real-time monitoring
        $this->cacheAuthEvent($context);
    }

    /**
     * Log token refresh
     */
    public function logTokenRefresh(bool $success, ?string $oldTokenId = null, ?string $newTokenId = null, ?string $error = null): void
    {
        $context = [
            'event' => 'token_refresh',
            'success' => $success,
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'timestamp' => now()->toDateTimeString(),
            'request_id' => $this->getRequestId(),
        ];

        if (Auth::check()) {
            $context['user_id'] = Auth::id();
            $context['user_email'] = Auth::user()->email;
        }

        if ($oldTokenId) {
            $context['old_token_id'] = $oldTokenId;
        }

        if ($newTokenId) {
            $context['new_token_id'] = $newTokenId;
        }

        if ($error) {
            $context['error'] = $error;
        }

        if ($success) {
            Log::info('Token refresh successful', $context);
        } else {
            Log::warning('Token refresh failed', $context);
        }

        $this->cacheAuthEvent($context);
    }

    /**
     * Log logout
     */
    public function logLogout(?string $tokenId = null): void
    {
        $context = [
            'event' => 'logout',
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'timestamp' => now()->toDateTimeString(),
            'request_id' => $this->getRequestId(),
        ];

        if (Auth::check()) {
            $context['user_id'] = Auth::id();
            $context['user_email'] = Auth::user()->email;
        }

        if ($tokenId) {
            $context['token_id'] = $tokenId;
        }

        Log::info('User logged out', $context);
        $this->cacheAuthEvent($context);
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $event, array $data = []): void
    {
        $context = array_merge([
            'event' => $event,
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'timestamp' => now()->toDateTimeString(),
            'request_id' => $this->getRequestId(),
        ], $data);

        if (Auth::check()) {
            $context['user_id'] = Auth::id();
            $context['user_email'] = Auth::user()->email;
        }

        Log::warning('Security event: ' . $event, $context);
        $this->cacheAuthEvent($context);

        // Alert for critical security events
        if (in_array($event, ['rate_limit_exceeded', 'suspicious_login', 'multiple_failed_attempts'])) {
            $this->sendSecurityAlert($context);
        }
    }

    /**
     * Log authorization attempt
     */
    public function logAuthorizationAttempt(string $action, bool $granted, ?string $resource = null, ?string $error = null): void
    {
        $context = [
            'event' => 'authorization',
            'action' => $action,
            'granted' => $granted,
            'resource' => $resource,
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'timestamp' => now()->toDateTimeString(),
            'request_id' => $this->getRequestId(),
        ];

        if (Auth::check()) {
            $context['user_id'] = Auth::id();
            $context['user_email'] = Auth::user()->email;
            $context['user_role'] = Auth::user()->role ?? 'unknown';
        }

        if ($error) {
            $context['error'] = $error;
        }

        if ($granted) {
            Log::info('Authorization granted', $context);
        } else {
            Log::warning('Authorization denied', $context);
            
            // Track denied authorizations for security monitoring
            $this->trackDeniedAuthorization($context);
        }

        $this->cacheAuthEvent($context);
    }

    /**
     * Get authentication statistics
     */
    public function getAuthStatistics(string $period = '1h'): array
    {
        $cacheKey = "auth_stats:{$period}";
        
        return Cache::remember($cacheKey, 300, function () use ($period) {
            $now = now();
            $startTime = match ($period) {
                '1h' => $now->copy()->subHour(),
                '24h' => $now->copy()->subDay(),
                '7d' => $now->copy()->subWeek(),
                '30d' => $now->copy()->subMonth(),
                default => $now->copy()->subHour(),
            };

            $events = $this->getAuthEventsFromCache($startTime);

            return [
                'period' => $period,
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $now->toDateTimeString(),
                'total_attempts' => count($events),
                'successful_logins' => collect($events)->where('event', 'auth_attempt')->where('success', true)->count(),
                'failed_logins' => collect($events)->where('event', 'auth_attempt')->where('success', false)->count(),
                'token_refreshes' => collect($events)->where('event', 'token_refresh')->where('success', true)->count(),
                'logouts' => collect($events)->where('event', 'logout')->count(),
                'security_events' => collect($events)->whereIn('event', ['rate_limit_exceeded', 'suspicious_login', 'multiple_failed_attempts'])->count(),
                'top_ips' => collect($events)->pluck('ip')->countBy()->take(10)->toArray(),
                'unique_users' => collect($events)->where('user_id', '!=', null)->pluck('user_id')->unique()->count(),
            ];
        });
    }

    /**
     * Check for suspicious activity
     */
    public function checkSuspiciousActivity(string $ip, ?int $userId = null): array
    {
        $warnings = [];

        // Check failed attempts from IP
        $failedAttempts = $this->getFailedAttemptsFromIp($ip);
        if ($failedAttempts > 5) {
            $warnings[] = 'Multiple failed login attempts from IP';
        }

        // Check rapid authentication attempts
        $recentAttempts = $this->getRecentAuthAttempts($ip, 5); // Last 5 minutes
        if ($recentAttempts > 10) {
            $warnings[] = 'Rapid authentication attempts detected';
        }

        // Check login from different locations
        if ($userId) {
            $locationChanges = $this->getLocationChanges($userId);
            if ($locationChanges > 3) {
                $warnings[] = 'Login from multiple locations detected';
            }
        }

        // Check for suspicious patterns
        $patterns = $this->detectSuspiciousPatterns($ip);
        if (!empty($patterns)) {
            $warnings = array_merge($warnings, $patterns);
        }

        return [
            'ip' => $ip,
            'user_id' => $userId,
            'warnings' => $warnings,
            'risk_level' => $this->calculateRiskLevel($warnings),
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    /**
     * Helper: Get unique request ID
     */
    protected function getRequestId(): string
    {
        return Request::header('X-Request-ID') ?? uniqid('req_');
    }

    /**
     * Helper: Mask email address
     */
    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $username = $parts[0];
        $domain = $parts[1];

        $maskedUsername = strlen($username) > 2 
            ? substr($username, 0, 2) . str_repeat('*', strlen($username) - 2)
            : str_repeat('*', strlen($username));

        return $maskedUsername . '@' . $domain;
    }

    /**
     * Helper: Cache auth event
     */
    protected function cacheAuthEvent(array $event): void
    {
        $key = 'auth_events:' . now()->format('Y-m-d-H');
        $events = Cache::get($key, []);
        $events[] = $event;
        
        // Keep only last 1000 events per hour
        if (count($events) > 1000) {
            array_shift($events);
        }
        
        Cache::put($key, $events, 3600); // 1 hour
    }

    /**
     * Helper: Track failed attempts
     */
    protected function trackFailedAttempt(string $ip, array $credentials): void
    {
        $key = "failed_attempts:{$ip}";
        $attempts = Cache::get($key, 0);
        Cache::put($key, $attempts + 1, 3600); // 1 hour

        // Track by identifier if available
        if (isset($credentials['email'])) {
            $identifierKey = "failed_attempts:" . md5($credentials['email']);
            $identifierAttempts = Cache::get($identifierKey, 0);
            Cache::put($identifierKey, $identifierAttempts + 1, 3600);
        }
    }

    /**
     * Helper: Get failed attempts from IP
     */
    protected function getFailedAttemptsFromIp(string $ip): int
    {
        return Cache::get("failed_attempts:{$ip}", 0);
    }

    /**
     * Helper: Get recent auth attempts
     */
    protected function getRecentAuthAttempts(string $ip, int $minutes): int
    {
        $key = "recent_attempts:{$ip}";
        return Cache::get($key, 0);
    }

    /**
     * Helper: Get auth events from cache
     */
    protected function getAuthEventsFromCache($startTime): array
    {
        $events = [];
        $currentHour = now()->format('Y-m-d-H');
        $startHour = $startTime->format('Y-m-d-H');

        // Collect events from relevant hours
        $hour = $startTime->copy();
        while ($hour <= now()) {
            $key = 'auth_events:' . $hour->format('Y-m-d-H');
            $hourEvents = Cache::get($key, []);
            
            foreach ($hourEvents as $event) {
                if (strtotime($event['timestamp']) >= $startTime->timestamp) {
                    $events[] = $event;
                }
            }
            
            $hour->addHour();
        }

        return $events;
    }

    /**
     * Helper: Track denied authorizations
     */
    protected function trackDeniedAuthorization(array $context): void
    {
        $key = 'denied_authorizations:' . now()->format('Y-m-d-H');
        $denials = Cache::get($key, []);
        $denials[] = $context;
        
        // Keep only last 100 denials per hour
        if (count($denials) > 100) {
            array_shift($denials);
        }
        
        Cache::put($key, $denials, 3600);
    }

    /**
     * Helper: Send security alert
     */
    protected function sendSecurityAlert(array $context): void
    {
        // This would integrate with your notification system
        // For now, just log with higher priority
        Log::alert('SECURITY ALERT: ' . $context['event'], $context);
    }

    /**
     * Helper: Get location changes
     */
    protected function getLocationChanges(int $userId): int
    {
        // Simplified implementation - would integrate with IP geolocation service
        $key = "location_changes:{$userId}";
        return Cache::get($key, 0);
    }

    /**
     * Helper: Detect suspicious patterns
     */
    protected function detectSuspiciousPatterns(string $ip): array
    {
        $patterns = [];

        // Check for brute force patterns
        $recentEvents = $this->getAuthEventsFromCache(now()->subMinutes(10));
        $ipEvents = collect($recentEvents)->where('ip', $ip);
        
        if ($ipEvents->where('event', 'auth_attempt')->where('success', false)->count() > 5) {
            $patterns[] = 'Potential brute force attack';
        }

        return $patterns;
    }

    /**
     * Helper: Calculate risk level
     */
    protected function calculateRiskLevel(array $warnings): string
    {
        $count = count($warnings);
        
        if ($count === 0) {
            return 'low';
        } elseif ($count <= 2) {
            return 'medium';
        } else {
            return 'high';
        }
    }
}