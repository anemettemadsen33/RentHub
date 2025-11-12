<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PerformanceController extends Controller
{
    /**
     * Store Web Vitals metrics
     */
    public function storeWebVitals(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'metric' => 'required|string|in:FCP,LCP,FID,CLS,TTFB,INP',
            'value' => 'required|numeric',
            'rating' => 'required|string|in:good,needs-improvement,poor',
            'url' => 'required|string',
            'userAgent' => 'nullable|string',
        ]);

        // Store metrics for analytics
        $key = 'web_vitals:'.date('Y-m-d');
        $metrics = Cache::get($key, []);

        $metrics[] = [
            'timestamp' => now()->toIso8601String(),
            'metric' => $validated['metric'],
            'value' => $validated['value'],
            'rating' => $validated['rating'],
            'url' => $validated['url'],
            'userAgent' => $validated['userAgent'] ?? null,
        ];

        // Keep last 1000 entries
        if (count($metrics) > 1000) {
            $metrics = array_slice($metrics, -1000);
        }

        Cache::put($key, $metrics, now()->addDays(7));

        // Log poor performance
        if ($validated['rating'] === 'poor') {
            Log::warning('Poor Web Vital detected', $validated);
        }

        return response()->json(['success' => true]);
    }

    public function storePwaEvent(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => 'required|string',
            'payload' => 'nullable|array',
            'timestamp' => 'required|date',
            'user' => 'nullable|array',
        ]);

        // Per-type rate limiting based on clientId extracted from payload.
        $clientId = $data['payload']['clientId'] ?? null;

        // Support batched events sent as a single envelope
        if (($data['type'] ?? '') === 'batch' && isset($data['payload']['events']) && is_array($data['payload']['events'])) {
            $events = $data['payload']['events'];
            $stored = 0;
            $limited = 0;
            foreach ($events as $event) {
                // Derive clientId from child event if present, else fallback to envelope clientId
                $evtClientId = $event['payload']['clientId'] ?? $clientId;
                $evtType = $event['type'] ?? 'unknown';
                if ($evtClientId) {
                    $cur = 0;
                    $cap = 0;
                    $allowed = $this->allowClientEvent($evtClientId, (string) $evtType, $cur, $cap);
                    if (! $allowed) {
                        $limited++;

                        continue; // skip storing limited event
                    }
                }
                $this->storeAnalyticsEventSafely($event);
                $stored++;
            }
            Log::info('PWA Batch Events stored', ['countStored' => $stored, 'countLimited' => $limited]);

            return response()->json(['status' => 'batch_logged', 'countStored' => $stored, 'countLimited' => $limited]);
        }

        // Single event fallback
        if ($clientId) {
            $cur = 0;
            $cap = 0;
            $allowed = $this->allowClientEvent($clientId, (string) ($data['type'] ?? 'unknown'), $cur, $cap);
            if (! $allowed) {
                return response()->json(['status' => 'rate_limited'], 429);
            }
        }
        $this->storeAnalyticsEventSafely($data);
        Log::info('PWA Event', $data);

        return response()->json(['status' => 'logged']);
    }

    /**
     * Store a single analytics event in cache (structured storage)
     */
    private function storeAnalyticsEventSafely(array $event): void
    {
        try {
            $dateKey = 'analytics_events:'.date('Y-m-d');
            $existing = Cache::get($dateKey, []);

            // Normalize shape
            $normalized = [
                'type' => $event['type'] ?? 'unknown',
                'payload' => $event['payload'] ?? [],
                'timestamp' => $event['timestamp'] ?? now()->toIso8601String(),
                'user' => $event['user'] ?? null,
            ];

            $existing[] = $normalized;
            // Keep last 1000 entries per day
            if (count($existing) > 1000) {
                $existing = array_slice($existing, -1000);
            }
            Cache::put($dateKey, $existing, now()->addDays(7));

            // Persist in DB (best-effort)
            AnalyticsEvent::create([
                'type' => $normalized['type'],
                'payload' => $normalized['payload'],
                'event_timestamp' => $normalized['timestamp'],
                'user_id' => $normalized['user']['id'] ?? null,
                'user_role' => $normalized['user']['role'] ?? null,
                'client_id' => $normalized['payload']['clientId'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to store analytics event', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Allow up to X events per clientId per minute (basic Redis counter).
     */
    private function allowClientEvent(string $clientId, string $eventType, ?int &$current = null, ?int &$limit = null): bool
    {
        try {
            // Normalize type bucket
            $typeNorm = strtolower(trim($eventType));
            $isPageview = in_array($typeNorm, ['pageview', 'page_view', 'page-view'], true);

            // Limits with sensible fallbacks
            $defaultLimit = (int) env('ANALYTICS_EVENTS_RATE_LIMIT_DEFAULT', (int) env('ANALYTICS_EVENTS_RATE_LIMIT', 120));
            $pageviewLimit = (int) env('ANALYTICS_EVENTS_RATE_LIMIT_PAGEVIEW', 60);
            $limit = $isPageview ? $pageviewLimit : $defaultLimit;

            $bucket = $isPageview ? 'pageview' : 'default';
            $key = 'analytics_rl:'.date('YmdHi').':'.$clientId.':'.$bucket; // per-type per-minute

            $current = Cache::increment($key, 1);
            if ($current === 1) {
                // Set expiry ~65 seconds (cover the minute + drift)
                Cache::put($key, $current, now()->addSeconds(65));
            }

            return $current <= $limit;
        } catch (\Throwable $e) {
            // Fail open if rate limiter storage fails
            Log::warning('Rate limiter failure', ['clientId' => $clientId, 'error' => $e->getMessage()]);

            return true;
        }
    }

    /**
     * Admin: Return current rate limiter usage for a clientId (per-type buckets)
     */
    public function getRateLimiterUsage(Request $request): JsonResponse
    {
        $clientId = $request->query('clientId');
        if (! $clientId) {
            return response()->json(['error' => 'clientId is required'], 422);
        }

        $minute = date('YmdHi');
        $defaultLimit = (int) env('ANALYTICS_EVENTS_RATE_LIMIT_DEFAULT', (int) env('ANALYTICS_EVENTS_RATE_LIMIT', 120));
        $pageviewLimit = (int) env('ANALYTICS_EVENTS_RATE_LIMIT_PAGEVIEW', 60);

        $pageviewKey = 'analytics_rl:'.$minute.':'.$clientId.':pageview';
        $defaultKey = 'analytics_rl:'.$minute.':'.$clientId.':default';

        $pageviewCount = (int) (Cache::get($pageviewKey, 0));
        $defaultCount = (int) (Cache::get($defaultKey, 0));

        return response()->json([
            'clientId' => $clientId,
            'minuteWindow' => $minute,
            'buckets' => [
                'pageview' => [
                    'count' => $pageviewCount,
                    'limit' => $pageviewLimit,
                    'remaining' => max(0, $pageviewLimit - $pageviewCount),
                    'key' => $pageviewKey,
                ],
                'default' => [
                    'count' => $defaultCount,
                    'limit' => $defaultLimit,
                    'remaining' => max(0, $defaultLimit - $defaultCount),
                    'key' => $defaultKey,
                ],
            ],
        ]);
    }

    /**
     * Get Web Vitals summary
     */
    public function getWebVitalsSummary(Request $request): JsonResponse
    {
        $days = $request->input('days', 7);
        $summary = [];

        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            $key = 'web_vitals:'.$date;
            $metrics = Cache::get($key, []);

            if (! empty($metrics)) {
                $summary[$date] = $this->calculateMetricsSummary($metrics);
            }
        }

        return response()->json($summary);
    }

    /**
     * Get performance recommendations
     */
    public function getRecommendations(): JsonResponse
    {
        $key = 'web_vitals:'.date('Y-m-d');
        $metrics = Cache::get($key, []);

        if (empty($metrics)) {
            return response()->json(['recommendations' => []]);
        }

        $recommendations = [];
        $summary = $this->calculateMetricsSummary($metrics);

        // LCP recommendations
        if (isset($summary['LCP']) && $summary['LCP']['avgValue'] > 2500) {
            $recommendations[] = [
                'metric' => 'LCP',
                'severity' => $summary['LCP']['avgValue'] > 4000 ? 'high' : 'medium',
                'message' => 'Largest Contentful Paint is slow. Consider optimizing images and critical resources.',
                'tips' => [
                    'Optimize and compress images',
                    'Use lazy loading for below-the-fold content',
                    'Minimize CSS and JavaScript',
                    'Use a CDN for static assets',
                ],
            ];
        }

        // FID recommendations
        if (isset($summary['FID']) && $summary['FID']['avgValue'] > 100) {
            $recommendations[] = [
                'metric' => 'FID',
                'severity' => $summary['FID']['avgValue'] > 300 ? 'high' : 'medium',
                'message' => 'First Input Delay is high. Reduce JavaScript execution time.',
                'tips' => [
                    'Break up long JavaScript tasks',
                    'Defer non-critical JavaScript',
                    'Use code splitting',
                    'Minimize third-party scripts',
                ],
            ];
        }

        // CLS recommendations
        if (isset($summary['CLS']) && $summary['CLS']['avgValue'] > 0.1) {
            $recommendations[] = [
                'metric' => 'CLS',
                'severity' => $summary['CLS']['avgValue'] > 0.25 ? 'high' : 'medium',
                'message' => 'Cumulative Layout Shift is high. Reserve space for dynamic content.',
                'tips' => [
                    'Set explicit dimensions for images and videos',
                    'Reserve space for ads and embeds',
                    'Avoid inserting content above existing content',
                    'Use CSS transforms for animations',
                ],
            ];
        }

        return response()->json(['recommendations' => $recommendations]);
    }

    /**
     * Get performance budget status
     */
    public function getBudgetStatus(): JsonResponse
    {
        $budget = [
            'js' => 300, // KB
            'css' => 100,
            'images' => 500,
            'fonts' => 100,
            'total' => 1000,
        ];

        // This would be calculated from actual resource sizes
        $current = [
            'js' => 250,
            'css' => 80,
            'images' => 420,
            'fonts' => 85,
            'total' => 835,
        ];

        $status = [];
        foreach ($budget as $resource => $limit) {
            $usage = $current[$resource];
            $percentage = ($usage / $limit) * 100;

            $status[$resource] = [
                'limit' => $limit,
                'current' => $usage,
                'percentage' => round($percentage, 2),
                'status' => $percentage > 100 ? 'over' : ($percentage > 90 ? 'warning' : 'good'),
            ];
        }

        return response()->json($status);
    }

    /**
     * Calculate metrics summary
     */
    private function calculateMetricsSummary(array $metrics): array
    {
        $grouped = [];

        foreach ($metrics as $entry) {
            $metricName = $entry['metric'];
            if (! isset($grouped[$metricName])) {
                $grouped[$metricName] = [
                    'values' => [],
                    'ratings' => ['good' => 0, 'needs-improvement' => 0, 'poor' => 0],
                ];
            }

            $grouped[$metricName]['values'][] = $entry['value'];
            $grouped[$metricName]['ratings'][$entry['rating']]++;
        }

        $summary = [];
        foreach ($grouped as $metricName => $data) {
            $values = $data['values'];
            $total = count($values);

            $summary[$metricName] = [
                'avgValue' => array_sum($values) / $total,
                'minValue' => min($values),
                'maxValue' => max($values),
                'p75Value' => $this->percentile($values, 75),
                'p95Value' => $this->percentile($values, 95),
                'totalSamples' => $total,
                'ratings' => $data['ratings'],
                'goodPercentage' => round(($data['ratings']['good'] / $total) * 100, 2),
            ];
        }

        return $summary;
    }

    /**
     * Calculate percentile
     */
    private function percentile(array $values, int $percentile): float
    {
        sort($values);
        $index = ($percentile / 100) * (count($values) - 1);
        $lower = floor($index);
        $upper = ceil($index);
        $weight = $index - $lower;

        return $values[$lower] * (1 - $weight) + $values[$upper] * $weight;
    }

    /**
     * Clear old metrics
     */
    public function clearOldMetrics(): JsonResponse
    {
        $cleared = 0;
        $cutoffDate = now()->subDays(30)->format('Y-m-d');

        for ($i = 30; $i < 90; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            $key = 'web_vitals:'.$date;

            if (Cache::has($key)) {
                Cache::forget($key);
                $cleared++;
            }
        }

        return response()->json([
            'message' => 'Old metrics cleared',
            'count' => $cleared,
        ]);
    }

    /**
     * Return analytics events stored in cache (admin usage)
     */
    public function getAnalyticsEvents(Request $request): JsonResponse
    {
        $date = $request->input('date');
        $days = (int) $request->input('days', 1);
        $limit = (int) $request->input('limit', 200);
        $type = $request->input('type');

        $events = [];
        if ($date) {
            $key = 'analytics_events:'.$date;
            $chunk = Cache::get($key, []);
            $events = array_merge($events, $chunk);
        } else {
            for ($i = 0; $i < max(1, $days); $i++) {
                $d = now()->subDays($i)->format('Y-m-d');
                $key = 'analytics_events:'.$d;
                $chunk = Cache::get($key, []);
                if (! empty($chunk)) {
                    $events = array_merge($events, $chunk);
                }
                if (count($events) >= $limit) {
                    break;
                }
            }
        }

        if ($type) {
            $events = array_values(array_filter($events, function ($e) use ($type) {
                return ($e['type'] ?? '') === $type;
            }));
        }

        // Trim to limit
        if (count($events) > $limit) {
            $events = array_slice($events, -1 * $limit);
        }

        return response()->json(['events' => $events]);
    }

    /**
     * Export events as CSV
     */
    public function exportAnalyticsEvents(Request $request)
    {
        $days = (int) $request->input('days', 1);
        $type = $request->input('type');
        $query = AnalyticsEvent::query()->orderBy('event_timestamp', 'desc');
        if ($type) {
            $query->where('type', $type);
        }
        $from = now()->subDays($days - 1)->startOfDay();
        $query->where('event_timestamp', '>=', $from);
        $events = $query->limit(5000)->get();

        $lines = [];
        $lines[] = 'id,type,event_timestamp,user_id,user_role,client_id,payload';
        foreach ($events as $ev) {
            $lines[] = implode(',', [
                $ev->id,
                $ev->type,
                $ev->event_timestamp,
                $ev->user_id ?? '',
                $ev->user_role ?? '',
                $ev->client_id ?? '',
                '"'.str_replace('"', '""', json_encode($ev->payload)).'"',
            ]);
        }
        $csv = implode("\n", $lines);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="analytics_events.csv"',
        ]);
    }

    /**
     * Summary aggregation (counts per type per day)
     */
    public function analyticsSummary(Request $request): JsonResponse
    {
        $days = (int) $request->input('days', 7);
        $from = now()->subDays($days - 1)->startOfDay();
        $raw = AnalyticsEvent::query()
            ->selectRaw('type, DATE(event_timestamp) as day, count(*) as cnt')
            ->where('event_timestamp', '>=', $from)
            ->groupBy('type', 'day')
            ->orderBy('day', 'asc')
            ->get();

        $result = [];
        foreach ($raw as $row) {
            $day = $row->day;
            if (! isset($result[$day])) {
                $result[$day] = [];
            }
            $result[$day][$row->type] = (int) $row->cnt;
        }

        return response()->json(['summary' => $result]);
    }
}

