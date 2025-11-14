<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Http\Request;
use Exception;

class ProductionHealthController extends Controller
{
    /**
     * Comprehensive health check for production environment
     */
    public function health(Request $request)
    {
        $checks = [];
        $overall_status = 'healthy';
        $start_time = microtime(true);

        try {
            // Database connectivity check
            $checks['database'] = $this->checkDatabase();
            
            // Cache connectivity check
            $checks['cache'] = $this->checkCache();
            
            // Queue connectivity check
            $checks['queue'] = $this->checkQueue();
            
            // Storage check
            $checks['storage'] = $this->checkStorage();
            
            // External services check
            $checks['external_services'] = $this->checkExternalServices();
            
            // Memory usage check
            $checks['memory'] = $this->checkMemory();
            
            // Disk space check
            $checks['disk_space'] = $this->checkDiskSpace();
            
            // Application-specific checks
            $checks['application'] = $this->checkApplication();

            // Determine overall status
            foreach ($checks as $check => $result) {
                if ($result['status'] === 'error') {
                    $overall_status = 'error';
                    break;
                } elseif ($result['status'] === 'warning' && $overall_status === 'healthy') {
                    $overall_status = 'warning';
                }
            }

        } catch (Exception $e) {
            $overall_status = 'error';
            $checks['exception'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
            
            Log::error('Health check failed with exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        $response_time = round((microtime(true) - $start_time) * 1000, 2);

        return response()->json([
            'status' => $overall_status,
            'timestamp' => now()->toDateTimeString(),
            'response_time_ms' => $response_time,
            'environment' => app()->environment(),
            'version' => config('app.version', '1.0.0'),
            'checks' => $checks
        ], $overall_status === 'error' ? 503 : 200);
    }

    /**
     * Detailed database health check
     */
    private function checkDatabase(): array
    {
        try {
            $start_time = microtime(true);
            
            // Test basic connectivity
            DB::select('SELECT 1');
            
            // Test connection pool if available
            $pool_stats = null;
            if (app()->bound('App\Services\DatabaseConnectionPoolService')) {
                $poolService = app('App\Services\DatabaseConnectionPoolService');
                $pool_stats = $poolService->getPoolStats();
            }
            
            // Check for slow queries
            $slow_queries = $this->checkSlowQueries();
            
            // Check table locks
            $locks = $this->checkTableLocks();
            
            $response_time = round((microtime(true) - $start_time) * 1000, 2);

            $status = 'healthy';
            if ($response_time > 1000) $status = 'warning';
            if ($response_time > 3000) $status = 'error';
            if ($slow_queries > 5) $status = 'warning';

            return [
                'status' => $status,
                'response_time_ms' => $response_time,
                'slow_queries' => $slow_queries,
                'table_locks' => $locks,
                'connection_pool' => $pool_stats,
                'driver' => config('database.default')
            ];

        } catch (Exception $e) {
            Log::error('Database health check failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Cache health check
     */
    private function checkCache(): array
    {
        try {
            $start_time = microtime(true);
            
            // Test Redis connectivity
            $test_key = 'health_check_' . uniqid();
            Cache::put($test_key, 'test', 60);
            $value = Cache::get($test_key);
            Cache::forget($test_key);
            
            if ($value !== 'test') {
                throw new Exception('Cache read/write test failed');
            }
            
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            
            // Get Redis info if available
            $redis_info = null;
            try {
                $redis = Cache::getRedis();
                $redis_info = $redis->info();
            } catch (Exception $e) {
                // Redis info not available
            }

            return [
                'status' => 'healthy',
                'response_time_ms' => $response_time,
                'driver' => config('cache.default'),
                'redis_info' => $redis_info ? $this->sanitizeRedisInfo($redis_info) : null
            ];

        } catch (Exception $e) {
            Log::error('Cache health check failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Cache connection failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Queue health check
     */
    private function checkQueue(): array
    {
        try {
            $connection = config('queue.default');
            $queue_size = 0;
            $failed_jobs = 0;
            
            // Try to get queue statistics
            try {
                if ($connection === 'redis') {
                    $redis = Cache::getRedis();
                    $queue_size = $redis->llen('queues:default');
                    $failed_jobs = $redis->llen('queues:default:failed');
                }
            } catch (Exception $e) {
                // Queue stats not available
            }

            $status = 'healthy';
            if ($queue_size > 100) $status = 'warning';
            if ($queue_size > 500) $status = 'error';
            if ($failed_jobs > 10) $status = 'warning';

            return [
                'status' => $status,
                'connection' => $connection,
                'queue_size' => $queue_size,
                'failed_jobs' => $failed_jobs,
                'workers' => $this->checkQueueWorkers()
            ];

        } catch (Exception $e) {
            Log::error('Queue health check failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Queue connection failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Storage health check
     */
    private function checkStorage(): array
    {
        try {
            $disk = config('filesystems.default');
            $test_file = 'health_check_' . uniqid() . '.txt';
            $test_content = 'health check test';
            
            // Test write
            \Storage::put($test_file, $test_content);
            
            // Test read
            $content = \Storage::get($test_file);
            
            // Test delete
            \Storage::delete($test_file);
            
            if ($content !== $test_content) {
                throw new Exception('Storage read/write test failed');
            }

            $free_space = disk_free_space(storage_path());
            $total_space = disk_total_space(storage_path());
            $used_percentage = round((($total_space - $free_space) / $total_space) * 100, 2);

            $status = 'healthy';
            if ($used_percentage > 85) $status = 'warning';
            if ($used_percentage > 95) $status = 'error';

            return [
                'status' => $status,
                'disk' => $disk,
                'disk_usage_percentage' => $used_percentage,
                'free_space_gb' => round($free_space / 1073741824, 2),
                'total_space_gb' => round($total_space / 1073741824, 2)
            ];

        } catch (Exception $e) {
            Log::error('Storage health check failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Storage operation failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * External services health check
     */
    private function checkExternalServices(): array
    {
        $services = [];
        
        // Check email service
        $services['email'] = $this->checkEmailService();
        
        // Check PDF service
        $services['pdf'] = $this->checkPdfService();
        
        // Check circuit breakers
        $services['circuit_breakers'] = $this->checkCircuitBreakers();

        $failed_services = array_filter($services, function($service) {
            return $service['status'] === 'error';
        });

        $status = count($failed_services) > 0 ? 'warning' : 'healthy';

        return [
            'status' => $status,
            'services' => $services,
            'failed_services_count' => count($failed_services)
        ];
    }

    /**
     * Memory usage check
     */
    private function checkMemory(): array
    {
        $memory_usage = memory_get_usage(true);
        $memory_peak = memory_get_peak_usage(true);
        $memory_limit = $this->getMemoryLimit();
        
        $usage_percentage = round(($memory_usage / $memory_limit) * 100, 2);
        $peak_percentage = round(($memory_peak / $memory_limit) * 100, 2);

        $status = 'healthy';
        if ($usage_percentage > 80) $status = 'warning';
        if ($usage_percentage > 95) $status = 'error';

        return [
            'status' => $status,
            'current_usage_mb' => round($memory_usage / 1048576, 2),
            'peak_usage_mb' => round($memory_peak / 1048576, 2),
            'memory_limit_mb' => round($memory_limit / 1048576, 2),
            'usage_percentage' => $usage_percentage,
            'peak_percentage' => $peak_percentage
        ];
    }

    /**
     * Disk space check
     */
    private function checkDiskSpace(): array
    {
        $free_space = disk_free_space('/');
        $total_space = disk_total_space('/');
        $used_percentage = round((($total_space - $free_space) / $total_space) * 100, 2);

        $status = 'healthy';
        if ($used_percentage > 85) $status = 'warning';
        if ($used_percentage > 95) $status = 'error';

        return [
            'status' => $status,
            'free_space_gb' => round($free_space / 1073741824, 2),
            'total_space_gb' => round($total_space / 1073741824, 2),
            'used_percentage' => $used_percentage
        ];
    }

    /**
     * Application-specific health checks
     */
    private function checkApplication(): array
    {
        $checks = [];

        // Check Laravel configuration
        $checks['config_cached'] = [
            'status' => app()->configurationIsCached() ? 'healthy' : 'warning',
            'message' => app()->configurationIsCached() ? 'Configuration is cached' : 'Configuration not cached'
        ];

        // Check routes caching
        $checks['routes_cached'] = [
            'status' => app()->routesAreCached() ? 'healthy' : 'warning',
            'message' => app()->routesAreCached() ? 'Routes are cached' : 'Routes not cached'
        ];

        // Check if in maintenance mode
        $checks['maintenance_mode'] = [
            'status' => app()->isDownForMaintenance() ? 'warning' : 'healthy',
            'message' => app()->isDownForMaintenance() ? 'Application is in maintenance mode' : 'Application is running normally'
        ];

        // Check important directories
        $checks['directories'] = $this->checkDirectories();

        // Check PHP configuration
        $checks['php_config'] = $this->checkPhpConfiguration();

        $failed_checks = array_filter($checks, function($check) {
            return $check['status'] === 'error';
        });

        $status = count($failed_checks) > 0 ? 'warning' : 'healthy';

        return [
            'status' => $status,
            'checks' => $checks
        ];
    }

    /**
     * Helper method to check slow queries
     */
    private function checkSlowQueries(): int
    {
        try {
            // This would need to be implemented based on your logging system
            // For now, return 0
            return 0;
        } catch (Exception $e) {
            return -1;
        }
    }

    /**
     * Helper method to check table locks
     */
    private function checkTableLocks(): array
    {
        try {
            // This would need to be implemented based on your database
            // For now, return empty array
            return [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Check email service
     */
    private function checkEmailService(): array
    {
        try {
            // Check if email configuration is set
            $mail_driver = config('mail.default');
            $mail_host = config('mail.mailers.smtp.host');
            
            if (empty($mail_driver) || empty($mail_host)) {
                return [
                    'status' => 'warning',
                    'message' => 'Email configuration incomplete'
                ];
            }

            return [
                'status' => 'healthy',
                'driver' => $mail_driver,
                'host' => $mail_host
            ];

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Email service check failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check PDF service
     */
    private function checkPdfService(): array
    {
        try {
            // Check if PDF service dependencies are available
            $pdf_enabled = class_exists('Dompdf\Dompdf');
            
            if (!$pdf_enabled) {
                return [
                    'status' => 'error',
                    'message' => 'PDF service (Dompdf) not available'
                ];
            }

            return [
                'status' => 'healthy',
                'service' => 'Dompdf',
                'enabled' => true
            ];

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'PDF service check failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check circuit breakers
     */
    private function checkCircuitBreakers(): array
    {
        try {
            $circuit_breakers = [];
            
            if (app()->bound('circuit_breaker.email')) {
                $circuit_breakers['email'] = app('circuit_breaker.email')->getStats();
            }
            
            if (app()->bound('circuit_breaker.pdf')) {
                $circuit_breakers['pdf'] = app('circuit_breaker.pdf')->getStats();
            }

            $open_circuits = array_filter($circuit_breakers, function($cb) {
                return isset($cb['state']) && $cb['state'] === 'OPEN';
            });

            $status = count($open_circuits) > 0 ? 'warning' : 'healthy';

            return [
                'status' => $status,
                'circuit_breakers' => $circuit_breakers,
                'open_circuits' => count($open_circuits)
            ];

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Circuit breaker check failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check queue workers
     */
    private function checkQueueWorkers(): array
    {
        try {
            // This is a simplified check - in production you'd want more sophisticated monitoring
            return [
                'status' => 'healthy',
                'message' => 'Queue worker status not implemented'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Queue worker check failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check important directories
     */
    private function checkDirectories(): array
    {
        $directories = [
            'storage' => storage_path(),
            'storage/logs' => storage_path('logs'),
            'storage/app' => storage_path('app'),
            'storage/framework' => storage_path('framework'),
            'bootstrap/cache' => bootstrap_path('cache')
        ];

        $results = [];
        foreach ($directories as $name => $path) {
            if (!is_dir($path)) {
                $results[$name] = [
                    'status' => 'error',
                    'message' => "Directory does not exist: {$path}"
                ];
            } elseif (!is_writable($path)) {
                $results[$name] = [
                    'status' => 'error',
                    'message' => "Directory is not writable: {$path}"
                ];
            } else {
                $results[$name] = [
                    'status' => 'healthy',
                    'message' => 'Directory is writable'
                ];
            }
        }

        return $results;
    }

    /**
     * Check PHP configuration
     */
    private function checkPhpConfiguration(): array
    {
        $checks = [];

        // Memory limit
        $memory_limit = ini_get('memory_limit');
        $checks['memory_limit'] = [
            'status' => 'healthy',
            'value' => $memory_limit,
            'message' => "Memory limit: {$memory_limit}"
        ];

        // Max execution time
        $max_execution_time = ini_get('max_execution_time');
        $checks['max_execution_time'] = [
            'status' => $max_execution_time >= 30 ? 'healthy' : 'warning',
            'value' => $max_execution_time,
            'message' => "Max execution time: {$max_execution_time}s"
        ];

        // OPcache
        $opcache_enabled = function_exists('opcache_get_status') && opcache_get_status(false) !== false;
        $checks['opcache'] = [
            'status' => $opcache_enabled ? 'healthy' : 'warning',
            'enabled' => $opcache_enabled,
            'message' => $opcache_enabled ? 'OPcache is enabled' : 'OPcache is not enabled'
        ];

        return $checks;
    }

    /**
     * Get memory limit in bytes
     */
    private function getMemoryLimit(): int
    {
        $limit = ini_get('memory_limit');
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }
        
        $unit = strtolower(substr($limit, -1));
        $value = (int) $limit;
        
        switch ($unit) {
            case 'g':
                $value *= 1024;
                // no break
            case 'm':
                $value *= 1024;
                // no break
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }

    /**
     * Sanitize Redis info for output
     */
    private function sanitizeRedisInfo(array $info): array
    {
        $sensitive_keys = ['password', 'auth', 'secret', 'key'];
        $sanitized = [];
        
        foreach ($info as $key => $value) {
            $is_sensitive = false;
            foreach ($sensitive_keys as $sensitive) {
                if (stripos($key, $sensitive) !== false) {
                    $is_sensitive = true;
                    break;
                }
            }
            
            $sanitized[$key] = $is_sensitive ? '***' : $value;
        }
        
        return $sanitized;
    }

    /**
     * Get production logs
     */
    public function logs(Request $request)
    {
        $level = $request->get('level', 'error');
        $lines = $request->get('lines', 50);
        $service = $request->get('service');

        try {
            $log_file = storage_path('logs/laravel.log');
            
            if (!file_exists($log_file)) {
                return response()->json([
                    'error' => 'Log file not found'
                ], 404);
            }

            $logs = $this->parseLogFile($log_file, $level, $lines, $service);

            return response()->json([
                'logs' => $logs,
                'file' => $log_file,
                'lines_requested' => $lines,
                'lines_returned' => count($logs),
                'level' => $level,
                'service' => $service
            ]);

        } catch (Exception $e) {
            Log::error('Failed to retrieve logs', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Failed to retrieve logs',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse log file
     */
    private function parseLogFile(string $file, string $level, int $lines, ?string $service = null): array
    {
        $content = file_get_contents($file);
        $lines_array = explode("\n", $content);
        $logs = [];
        $count = 0;

        // Process lines in reverse order (newest first)
        for ($i = count($lines_array) - 1; $i >= 0 && $count < $lines; $i--) {
            $line = $lines_array[$i];
            
            if (empty(trim($line))) continue;
            
            // Check if line matches the requested level
            if (stripos($line, ".{$level}.") === false) continue;
            
            // Check if line matches the requested service
            if ($service && stripos($line, $service) === false) continue;
            
            $parsed_line = $this->parseLogLine($line);
            if ($parsed_line) {
                $logs[] = $parsed_line;
                $count++;
            }
        }

        return $logs;
    }

    /**
     * Parse individual log line
     */
    private function parseLogLine(string $line): ?array
    {
        // Laravel log format: [YYYY-MM-DD HH:MM:SS] environment.LOG_LEVEL: message {"context":...} {"extra":...}
        $pattern = '/\[(?<timestamp>[^\]]+)\]\s+(?<environment>[^\.]+)\.(?<level>[^:]+):\s+(?<message>.*)/';
        
        if (preg_match($pattern, $line, $matches)) {
            return [
                'timestamp' => $matches['timestamp'],
                'environment' => $matches['environment'],
                'level' => $matches['level'],
                'message' => trim($matches['message']),
                'raw' => $line
            ];
        }
        
        return null;
    }
}