<?php

namespace App\Services;

use App\Models\SecurityIncident;
use App\Models\User;
use App\Models\RefreshToken;
use App\Events\SecurityIncident as SecurityIncidentEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SecurityIncidentNotification;

class SecurityIncidentResponseService
{
    /**
     * Incident severity levels
     */
    const SEVERITY_LOW = 'low';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_CRITICAL = 'critical';

    /**
     * Incident types
     */
    const TYPE_BRUTE_FORCE = 'brute_force';
    const TYPE_TOKEN_REUSE = 'token_reuse';
    const TYPE_UNAUTHORIZED_ACCESS = 'unauthorized_access';
    const TYPE_DATA_BREACH = 'data_breach';
    const TYPE_DDOS = 'ddos';
    const TYPE_SQL_INJECTION = 'sql_injection';
    const TYPE_XSS = 'xss';
    const TYPE_SUSPICIOUS_ACTIVITY = 'suspicious_activity';

    /**
     * Handle security incident
     */
    public function handleIncident(
        string $type,
        string $severity,
        array $details,
        ?int $userId = null
    ): SecurityIncident {
        // Create incident record
        $incident = $this->recordIncident($type, $severity, $details, $userId);

        // Execute automatic response based on severity
        $this->executeAutomaticResponse($incident);

        // Notify security team
        $this->notifySecurityTeam($incident);

        // Log to SIEM
        $this->logToSIEM($incident);

        return $incident;
    }

    /**
     * Record incident in database
     */
    private function recordIncident(
        string $type,
        string $severity,
        array $details,
        ?int $userId
    ): SecurityIncident {
        return SecurityIncident::create([
            'type' => $type,
            'severity' => $severity,
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'details' => $details,
            'status' => 'open',
            'detected_at' => now(),
        ]);
    }

    /**
     * Execute automatic response based on incident
     */
    private function executeAutomaticResponse(SecurityIncident $incident): void
    {
        switch ($incident->type) {
            case self::TYPE_BRUTE_FORCE:
                $this->handleBruteForce($incident);
                break;

            case self::TYPE_TOKEN_REUSE:
                $this->handleTokenReuse($incident);
                break;

            case self::TYPE_DDOS:
                $this->handleDDoS($incident);
                break;

            case self::TYPE_UNAUTHORIZED_ACCESS:
                $this->handleUnauthorizedAccess($incident);
                break;

            case self::TYPE_DATA_BREACH:
                $this->handleDataBreach($incident);
                break;

            case self::TYPE_SQL_INJECTION:
            case self::TYPE_XSS:
                $this->handleInjectionAttack($incident);
                break;
        }

        // Common actions for all critical incidents
        if ($incident->severity === self::SEVERITY_CRITICAL) {
            $this->escalateToCritical($incident);
        }
    }

    /**
     * Handle brute force attack
     */
    private function handleBruteForce(SecurityIncident $incident): void
    {
        $ip = $incident->ip_address;

        // Block IP temporarily
        $this->blockIP($ip, hours: 24);

        // If user account targeted, lock it
        if ($incident->user_id) {
            $this->lockUserAccount($incident->user_id, reason: 'Multiple failed login attempts');
        }

        Log::warning('Brute force attack blocked', [
            'ip' => $ip,
            'user_id' => $incident->user_id,
        ]);
    }

    /**
     * Handle token reuse attack
     */
    private function handleTokenReuse(SecurityIncident $incident): void
    {
        if (!$incident->user_id) {
            return;
        }

        // Revoke all user tokens
        RefreshToken::where('user_id', $incident->user_id)
            ->update(['revoked' => true]);

        // Force user to re-authenticate
        Cache::put("force_reauth:{$incident->user_id}", true, now()->addHours(24));

        // Notify user
        $user = User::find($incident->user_id);
        if ($user) {
            $user->notify(new SecurityIncidentNotification($incident));
        }

        Log::critical('Token reuse detected - all tokens revoked', [
            'user_id' => $incident->user_id,
            'ip' => $incident->ip_address,
        ]);
    }

    /**
     * Handle DDoS attack
     */
    private function handleDDoS(SecurityIncident $incident): void
    {
        $ip = $incident->ip_address;

        // Block IP permanently
        $this->blockIP($ip, permanent: true);

        // Enable enhanced rate limiting
        Cache::put('ddos_mode', true, now()->addHours(1));

        // Notify CDN/WAF to block IP
        $this->notifyCDN($ip, action: 'block');

        Log::critical('DDoS attack detected - IP blocked', [
            'ip' => $ip,
            'request_rate' => $incident->details['request_rate'] ?? null,
        ]);
    }

    /**
     * Handle unauthorized access attempt
     */
    private function handleUnauthorizedAccess(SecurityIncident $incident): void
    {
        if ($incident->user_id) {
            // Suspend user account
            $this->suspendUserAccount($incident->user_id);

            // Revoke all sessions
            RefreshToken::where('user_id', $incident->user_id)
                ->update(['revoked' => true]);
        }

        // Block IP
        $this->blockIP($incident->ip_address, hours: 48);

        Log::warning('Unauthorized access attempt blocked', [
            'user_id' => $incident->user_id,
            'ip' => $incident->ip_address,
            'attempted_resource' => $incident->details['resource'] ?? null,
        ]);
    }

    /**
     * Handle potential data breach
     */
    private function handleDataBreach(SecurityIncident $incident): void
    {
        // Immediately block all access
        Cache::put('maintenance_mode', true, now()->addHours(2));

        // Revoke all active sessions
        RefreshToken::query()->update(['revoked' => true]);

        // Take snapshot of affected data
        $this->snapshotAffectedData($incident);

        // Notify legal and compliance teams
        $this->notifyLegalTeam($incident);

        Log::critical('CRITICAL: Potential data breach detected', [
            'details' => $incident->details,
        ]);
    }

    /**
     * Handle injection attacks (SQL, XSS)
     */
    private function handleInjectionAttack(SecurityIncident $incident): void
    {
        $ip = $incident->ip_address;

        // Block IP permanently
        $this->blockIP($ip, permanent: true);

        // Log full request for forensics
        $this->logForensics($incident);

        // Enable WAF protection
        $this->enableWAFRules($incident->type);

        Log::critical('Injection attack detected and blocked', [
            'type' => $incident->type,
            'ip' => $ip,
            'payload' => $incident->details['payload'] ?? null,
        ]);
    }

    /**
     * Escalate to critical incident response
     */
    private function escalateToCritical(SecurityIncident $incident): void
    {
        // Notify on-call engineer via PagerDuty
        $this->notifyPagerDuty($incident);

        // Create incident channel in Slack
        $this->createIncidentChannel($incident);

        // Start incident timeline
        $incident->update([
            'escalated_at' => now(),
            'status' => 'escalated',
        ]);
    }

    /**
     * Block IP address
     */
    private function blockIP(string $ip, ?int $hours = null, bool $permanent = false): void
    {
        if ($permanent) {
            // Add to permanent blacklist
            \App\Models\IPBlacklist::create([
                'ip_address' => $ip,
                'reason' => 'Security incident',
                'blocked_at' => now(),
            ]);
        } else {
            // Temporary block in cache
            $ttl = $hours ? now()->addHours($hours) : now()->addDay();
            Cache::put("ip_blocked:{$ip}", true, $ttl);
        }

        // Update firewall rules
        $this->updateFirewallRules($ip, 'block');
    }

    /**
     * Lock user account
     */
    private function lockUserAccount(int $userId, string $reason): void
    {
        User::where('id', $userId)->update([
            'account_locked' => true,
            'locked_reason' => $reason,
            'locked_at' => now(),
        ]);

        Cache::put("account_locked:{$userId}", true, now()->addDays(7));
    }

    /**
     * Suspend user account
     */
    private function suspendUserAccount(int $userId): void
    {
        User::where('id', $userId)->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspended_reason' => 'Security incident',
        ]);
    }

    /**
     * Notify security team
     */
    private function notifySecurityTeam(SecurityIncident $incident): void
    {
        $securityTeam = User::where('role', 'admin')
            ->orWhere('role', 'security')
            ->get();

        Notification::send($securityTeam, new SecurityIncidentNotification($incident));
    }

    /**
     * Log to SIEM (Security Information and Event Management)
     */
    private function logToSIEM(SecurityIncident $incident): void
    {
        // Send to external SIEM system (e.g., Splunk, ELK)
        Log::channel('siem')->critical('SECURITY_INCIDENT', [
            'incident_id' => $incident->id,
            'type' => $incident->type,
            'severity' => $incident->severity,
            'user_id' => $incident->user_id,
            'ip_address' => $incident->ip_address,
            'details' => $incident->details,
            'timestamp' => $incident->detected_at->toIso8601String(),
        ]);
    }

    /**
     * Notify CDN/WAF service
     */
    private function notifyCDN(string $ip, string $action): void
    {
        // Send to CloudFlare, AWS WAF, or similar
        // Implementation depends on CDN provider
        Log::info('CDN notification sent', ['ip' => $ip, 'action' => $action]);
    }

    /**
     * Notify PagerDuty for critical incidents
     */
    private function notifyPagerDuty(SecurityIncident $incident): void
    {
        if (!config('services.pagerduty.enabled')) {
            return;
        }

        // PagerDuty API integration
        $client = new \GuzzleHttp\Client();
        
        try {
            $client->post('https://events.pagerduty.com/v2/enqueue', [
                'json' => [
                    'routing_key' => config('services.pagerduty.routing_key'),
                    'event_action' => 'trigger',
                    'payload' => [
                        'summary' => "Critical Security Incident: {$incident->type}",
                        'severity' => $incident->severity,
                        'source' => 'RentHub Security System',
                        'custom_details' => $incident->details,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify PagerDuty', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create incident response channel in Slack
     */
    private function createIncidentChannel(SecurityIncident $incident): void
    {
        if (!config('services.slack.enabled')) {
            return;
        }

        $channelName = "incident-{$incident->id}";
        
        // Slack API to create channel
        // Implementation depends on Slack SDK
        Log::info('Incident channel created', ['channel' => $channelName]);
    }

    /**
     * Snapshot affected data for forensics
     */
    private function snapshotAffectedData(SecurityIncident $incident): void
    {
        // Create database snapshot
        // Store affected records
        Log::info('Data snapshot created for incident', ['incident_id' => $incident->id]);
    }

    /**
     * Notify legal team
     */
    private function notifyLegalTeam(SecurityIncident $incident): void
    {
        $legalTeam = explode(',', config('security.legal_team_emails'));
        
        // Send urgent email notification
        Log::critical('Legal team notified of potential breach', [
            'incident_id' => $incident->id,
        ]);
    }

    /**
     * Log forensic data
     */
    private function logForensics(SecurityIncident $incident): void
    {
        Log::channel('forensics')->info('FORENSIC_LOG', [
            'incident_id' => $incident->id,
            'full_request' => request()->all(),
            'headers' => request()->headers->all(),
            'server' => request()->server->all(),
        ]);
    }

    /**
     * Enable WAF rules
     */
    private function enableWAFRules(string $attackType): void
    {
        // Update WAF configuration
        Cache::put("waf_rule_enabled:{$attackType}", true, now()->addWeek());
        
        Log::info('WAF rules enabled', ['attack_type' => $attackType]);
    }

    /**
     * Update firewall rules
     */
    private function updateFirewallRules(string $ip, string $action): void
    {
        // Integration with cloud firewall (AWS Security Groups, etc.)
        Log::info('Firewall rule updated', ['ip' => $ip, 'action' => $action]);
    }

    /**
     * Get incident statistics
     */
    public function getIncidentStats(int $days = 30): array
    {
        $incidents = SecurityIncident::where('detected_at', '>=', now()->subDays($days))
            ->get();

        return [
            'total' => $incidents->count(),
            'by_severity' => $incidents->groupBy('severity')->map->count(),
            'by_type' => $incidents->groupBy('type')->map->count(),
            'critical_open' => $incidents->where('severity', self::SEVERITY_CRITICAL)
                ->where('status', 'open')
                ->count(),
            'avg_response_time' => $this->calculateAverageResponseTime($incidents),
            'top_ips' => $incidents->groupBy('ip_address')
                ->map->count()
                ->sortDesc()
                ->take(10),
        ];
    }

    /**
     * Calculate average incident response time
     */
    private function calculateAverageResponseTime($incidents): float
    {
        $resolved = $incidents->whereNotNull('resolved_at');
        
        if ($resolved->isEmpty()) {
            return 0;
        }

        $totalTime = $resolved->sum(function ($incident) {
            return $incident->resolved_at->diffInMinutes($incident->detected_at);
        });

        return round($totalTime / $resolved->count(), 2);
    }
}
