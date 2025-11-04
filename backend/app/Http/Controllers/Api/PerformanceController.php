<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
}
