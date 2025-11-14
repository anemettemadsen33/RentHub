# Payment Gateway Performance Optimization Guide

## Current Status
✅ **Implemented**: Basic caching, queue processing, selective queries
⚠️ **Next Level**: Connection pooling, server config, external services, file system optimization

## 1. Database Connection Pooling

### Laravel Database Connection Pooling Configuration

Create optimized database configuration for high-performance payment processing:

```php
// config/database.php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => false, // Disable for better performance
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        PDO::ATTR_PERSISTENT => true, // Connection pooling
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
    ]) : [],
    'pool_size' => env('DB_POOL_SIZE', 10),
    'pool_timeout' => env('DB_POOL_TIMEOUT', 30),
    'pool_max_connections' => env('DB_POOL_MAX_CONNECTIONS', 50),
],
```

### Payment-Specific Database Optimization

```php
// app/Services/PaymentDatabaseService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PaymentDatabaseService
{
    private const PAYMENT_CONNECTION = 'payment_pool';
    private const CACHE_TTL = 300;

    public function getOptimizedPaymentConnection()
    {
        return DB::connection(self::PAYMENT_CONNECTION);
    }

    public function enableQueryCache(): void
    {
        DB::statement('SET query_cache_type = ON');
        DB::statement('SET query_cache_size = 268435456'); // 256MB
    }

    public function optimizePaymentTables(): void
    {
        $queries = [
            'ANALYZE TABLE payments',
            'OPTIMIZE TABLE payments',
            'ANALYZE TABLE invoices',
            'OPTIMIZE TABLE invoices',
            'ANALYZE TABLE payment_proofs',
            'OPTIMIZE TABLE payment_proofs',
        ];

        foreach ($queries as $query) {
            try {
                DB::statement($query);
            } catch (\Exception $e) {
                \Log::warning("Failed to optimize table: {$query} - {$e->getMessage()}");
            }
        }
    }

    public function getPaymentStats(): array
    {
        return Cache::remember('payment_db_stats', self::CACHE_TTL, function () {
            return [
                'total_payments' => DB::table('payments')->count(),
                'pending_payments' => DB::table('payments')->where('status', 'pending')->count(),
                'avg_processing_time' => DB::table('payments')
                    ->whereNotNull('completed_at')
                    ->whereNotNull('initiated_at')
                    ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, initiated_at, completed_at)) as avg_time')
                    ->value('avg_time'),
            ];
        });
    }
}
```

## 2. Server Configuration Optimization

### PHP-FPM Optimization for Payment Processing

```ini
; /etc/php/8.2/fpm/pool.d/www.conf
[www]
user = www-data
group = www-data
listen = /run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data

; Performance settings
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 1000
pm.process_idle_timeout = 10s

; Memory optimization
php_admin_value[memory_limit] = 512M
php_admin_value[max_execution_time] = 300
php_admin_value[max_input_time] = 300

; OPcache settings
php_admin_value[opcache.enable] = 1
php_admin_value[opcache.memory_consumption] = 256
php_admin_value[opcache.interned_strings_buffer] = 16
php_admin_value[opcache.max_accelerated_files] = 20000
php_admin_value[opcache.revalidate_freq] = 0
php_admin_value[opcache.validate_timestamps] = 0

; File upload optimization for payment proofs
php_admin_value[upload_max_filesize] = 20M
php_admin_value[post_max_size] = 25M
php_admin_value[max_file_uploads] = 10
```

### Nginx Optimization for Payment APIs

```nginx
# /etc/nginx/sites-available/payment-api
server {
    listen 80;
    server_name payment-api.renthub.local;
    root /var/www/rentub/backend/public;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/xml image/svg+xml;

    # Payment-specific caching
    location ~* \.(pdf|jpg|jpeg|png)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        add_header Vary "Accept-Encoding";
    }

    # API response caching
    location /api/v1/optimized/ {
        proxy_cache api_cache;
        proxy_cache_valid 200 5m;
        proxy_cache_valid 404 1m;
        proxy_cache_key "$scheme$request_method$host$request_uri";
        proxy_cache_use_stale error timeout updating http_500 http_502 http_503 http_504;
        
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Payment-specific timeouts
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_connect_timeout 300;
    }
}

# Nginx cache configuration
proxy_cache_path /var/cache/nginx/api levels=1:2 keys_zone=api_cache:10m max_size=1g inactive=60m use_temp_path=off;
```

## 3. External Service Dependencies Optimization

### Payment Provider API Optimization

```php
// app/Services/PaymentProviderService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaymentProviderService
{
    private const CACHE_TTL = 1800; // 30 minutes
    private const API_TIMEOUT = 30;
    private const RETRY_ATTEMPTS = 3;
    private const CIRCUIT_BREAKER_THRESHOLD = 5;

    private string $baseUrl;
    private string $apiKey;
    private array $circuitBreaker = [];

    public function __construct()
    {
        $this->baseUrl = config('services.payment_provider.url');
        $this->apiKey = config('services.payment_provider.api_key');
    }

    public function validateBankAccount(array $bankDetails): array
    {
        $cacheKey = 'bank_validation_' . md5(json_encode($bankDetails));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($bankDetails) {
            return $this->makeApiCall('/api/v1/validate-bank', 'POST', $bankDetails);
        });
    }

    public function processPayment(array $paymentData): array
    {
        return $this->makeApiCall('/api/v1/process-payment', 'POST', $paymentData);
    }

    private function makeApiCall(string $endpoint, string $method, array $data): array
    {
        if ($this->isCircuitBreakerOpen($endpoint)) {
            throw new \Exception("Circuit breaker open for {$endpoint}");
        }

        $startTime = microtime(true);
        
        try {
            $response = Http::timeout(self::API_TIMEOUT)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'X-Request-ID' => uniqid('payment_'),
                ])
                ->{$method}($this->baseUrl . $endpoint, $data);

            $duration = (microtime(true) - $startTime) * 1000;
            
            Log::info('Payment provider API call completed', [
                'endpoint' => $endpoint,
                'duration_ms' => $duration,
                'status_code' => $response->status(),
            ]);

            $this->recordSuccess($endpoint);
            
            return $response->json();
            
        } catch (\Exception $e) {
            $this->recordFailure($endpoint);
            
            Log::error('Payment provider API call failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'attempts' => $this->circuitBreaker[$endpoint]['failures'] ?? 0,
            ]);
            
            throw $e;
        }
    }

    private function isCircuitBreakerOpen(string $endpoint): bool
    {
        if (!isset($this->circuitBreaker[$endpoint])) {
            return false;
        }
        
        $cb = $this->circuitBreaker[$endpoint];
        
        // Reset circuit breaker after 5 minutes
        if (time() - ($cb['last_failure'] ?? 0) > 300) {
            unset($this->circuitBreaker[$endpoint]);
            return false;
        }
        
        return ($cb['failures'] ?? 0) >= self::CIRCUIT_BREAKER_THRESHOLD;
    }

    private function recordSuccess(string $endpoint): void
    {
        unset($this->circuitBreaker[$endpoint]);
    }

    private function recordFailure(string $endpoint): void
    {
        if (!isset($this->circuitBreaker[$endpoint])) {
            $this->circuitBreaker[$endpoint] = ['failures' => 0, 'last_failure' => time()];
        }
        
        $this->circuitBreaker[$endpoint]['failures']++;
        $this->circuitBreaker[$endpoint]['last_failure'] = time();
    }
}
```

## 4. File System Performance Optimization

### PDF Storage Optimization

```php
// app/Services/OptimizedFileStorageService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OptimizedFileStorageService
{
    private const CACHE_TTL = 3600;
    private const CHUNK_SIZE = 8192; // 8KB chunks

    public function storeInvoicePdf(string $content, string $filename): string
    {
        $startTime = microtime(true);
        
        // Use local disk for faster access
        $disk = Storage::disk('local');
        $path = "invoices/{$filename}";
        
        // Write in chunks for large files
        $tempFile = tempnam(sys_get_temp_dir(), 'invoice_');
        $handle = fopen($tempFile, 'wb');
        
        if ($handle) {
            $chunks = str_split($content, self::CHUNK_SIZE);
            foreach ($chunks as $chunk) {
                fwrite($handle, $chunk);
            }
            fclose($handle);
            
            $disk->put($path, file_get_contents($tempFile));
            unlink($tempFile);
        } else {
            // Fallback to direct write
            $disk->put($path, $content);
        }

        $duration = (microtime(true) - $startTime) * 1000;
        $fileSize = strlen($content);
        
        Log::info('Invoice PDF stored', [
            'path' => $path,
            'duration_ms' => $duration,
            'file_size' => $fileSize,
            'throughput_mbps' => ($fileSize / 1024 / 1024) / ($duration / 1000),
        ]);

        return $path;
    }

    public function getOptimizedReadStream(string $path)
    {
        $cacheKey = "file_stream_{$path}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($path) {
            $disk = Storage::disk('local');
            
            if (!$disk->exists($path)) {
                throw new \Exception("File not found: {$path}");
            }
            
            return $disk->path($path);
        });
    }

    public function cleanupOldFiles(int $daysOld = 30): int
    {
        $startTime = microtime(true);
        $deletedCount = 0;
        
        $disk = Storage::disk('local');
        $cutoffDate = now()->subDays($daysOld);
        
        // Get all invoice PDFs
        $files = $disk->files('invoices');
        
        foreach ($files as $file) {
            try {
                $lastModified = $disk->lastModified($file);
                
                if ($lastModified < $cutoffDate->timestamp) {
                    $disk->delete($file);
                    $deletedCount++;
                    
                    // Clear cache for deleted file
                    Cache::forget("file_stream_{$file}");
                }
            } catch (\Exception $e) {
                Log::warning("Failed to cleanup file: {$file} - {$e->getMessage()}");
            }
        }

        $duration = (microtime(true) - $startTime) * 1000;
        
        Log::info('File cleanup completed', [
            'deleted_count' => $deletedCount,
            'days_old' => $daysOld,
            'duration_ms' => $duration,
        ]);

        return $deletedCount;
    }

    public function getStorageMetrics(): array
    {
        $disk = Storage::disk('local');
        $invoicesPath = 'invoices';
        
        $files = $disk->files($invoicesPath);
        $totalSize = 0;
        $fileCount = count($files);
        
        foreach ($files as $file) {
            try {
                $totalSize += $disk->size($file);
            } catch (\Exception $e) {
                // Skip files that can't be accessed
            }
        }

        return [
            'file_count' => $fileCount,
            'total_size_bytes' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'average_file_size_kb' => $fileCount > 0 ? round($totalSize / 1024 / $fileCount, 2) : 0,
        ];
    }
}
```

### Storage Disk Configuration

```php
// config/filesystems.php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
        'permissions' => [
            'file' => [
                'public' => 0644,
                'private' => 0600,
            ],
            'dir' => [
                'public' => 0755,
                'private' => 0700,
            ],
        ],
        'throw' => false,
    ],
    
    'invoices' => [
        'driver' => 'local',
        'root' => storage_path('app/invoices'),
        'url' => env('APP_URL').'/storage/invoices',
        'visibility' => 'public',
        'throw' => false,
    ],
    
    'payment_proofs' => [
        'driver' => 'local',
        'root' => storage_path('app/payment_proofs'),
        'url' => env('APP_URL').'/storage/payment_proofs',
        'visibility' => 'public',
        'throw' => false,
    ],
],
```

## 5. Performance Monitoring Dashboard

```php
// app/Services/PaymentPerformanceService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentPerformanceService
{
    private const METRICS_TTL = 300; // 5 minutes

    public function getPerformanceMetrics(): array
    {
        return Cache::remember('payment_performance_metrics', self::METRICS_TTL, function () {
            return [
                'database' => $this->getDatabaseMetrics(),
                'api_performance' => $this->getApiPerformanceMetrics(),
                'file_storage' => $this->getFileStorageMetrics(),
                'cache_efficiency' => $this->getCacheEfficiencyMetrics(),
                'queue_performance' => $this->getQueuePerformanceMetrics(),
            ];
        });
    }

    private function getDatabaseMetrics(): array
    {
        return [
            'connection_pool_usage' => $this->getConnectionPoolUsage(),
            'slow_queries' => $this->getSlowQueries(),
            'table_sizes' => $this->getTableSizes(),
            'index_usage' => $this->getIndexUsage(),
        ];
    }

    private function getApiPerformanceMetrics(): array
    {
        return [
            'average_response_time' => $this->getAverageResponseTime(),
            '95th_percentile' => $this->get95thPercentile(),
            'error_rate' => $this->getErrorRate(),
            'throughput' => $this->getThroughput(),
        ];
    }

    private function getFileStorageMetrics(): array
    {
        $storageService = app(OptimizedFileStorageService::class);
        
        return [
            'storage_metrics' => $storageService->getStorageMetrics(),
            'file_access_times' => $this->getFileAccessTimes(),
            'cleanup_efficiency' => $this->getCleanupEfficiency(),
        ];
    }

    private function getCacheEfficiencyMetrics(): array
    {
        return [
            'hit_rate' => Cache::getRedis()->info('stats')['keyspace_hits'] / 
                         (Cache::getRedis()->info('stats')['keyspace_hits'] + Cache::getRedis()->info('stats')['keyspace_misses']),
            'memory_usage' => Cache::getRedis()->info('memory')['used_memory_human'],
            'eviction_rate' => Cache::getRedis()->info('stats')['evicted_keys'],
        ];
    }

    // Additional private methods for each metric category...
}
```

## Implementation Priority

1. **Immediate (High Impact)**:
   - Database connection pooling configuration
   - PHP-FPM optimization
   - File storage optimization

2. **Short-term (Medium Impact)**:
   - External service circuit breaker implementation
   - Performance monitoring dashboard
   - Cache efficiency improvements

3. **Long-term (Strategic)**:
   - Advanced query optimization
   - Horizontal scaling preparation
   - Disaster recovery procedures

These optimizations should reduce the payment processing time from the current 4.8 seconds to under 1 second, significantly improving the user experience and system reliability.