<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DatabaseConnectionPoolService
{
    private array $connectionPools = [];
    private array $connectionStats = [];
    private int $maxPoolSize;
    private int $minPoolSize;
    private int $connectionTimeout;
    private int $idleTimeout;
    private string $defaultConnection;

    public function __construct()
    {
        $this->maxPoolSize = config('database.pool.max_connections', 20);
        $this->minPoolSize = config('database.pool.min_connections', 5);
        $this->connectionTimeout = config('database.pool.connection_timeout', 30);
        $this->idleTimeout = config('database.pool.idle_timeout', 300);
        $this->defaultConnection = config('database.default', 'mysql');
    }

    /**
     * Initialize connection pools for all configured connections
     */
    public function initializePools(): void
    {
        $connections = config('database.connections', []);
        
        foreach ($connections as $name => $config) {
            if ($this->supportsPooling($config['driver'] ?? '')) {
                $this->initializePool($name, $config);
            }
        }

        Log::info('Database connection pools initialized', [
            'pools' => array_keys($this->connectionPools),
            'total_connections' => $this->getTotalConnections()
        ]);
    }

    /**
     * Get a connection from the pool with performance monitoring
     */
    public function getConnection(string $connection = null): \Illuminate\Database\Connection
    {
        $connection = $connection ?? $this->defaultConnection;
        $startTime = microtime(true);

        try {
            // Try to get connection from pool first
            if ($this->hasAvailableConnection($connection)) {
                $dbConnection = $this->getPooledConnection($connection);
            } else {
                // Fallback to Laravel's connection manager
                $dbConnection = DB::connection($connection);
            }

            $acquisitionTime = microtime(true) - $startTime;
            $this->recordConnectionStats($connection, $acquisitionTime);

            return $dbConnection;
        } catch (\Exception $e) {
            Log::error('Failed to acquire database connection', [
                'connection' => $connection,
                'error' => $e->getMessage(),
                'time_taken' => microtime(true) - $startTime
            ]);

            throw $e;
        }
    }

    /**
     * Release connection back to pool
     */
    public function releaseConnection(\Illuminate\Database\Connection $connection): void
    {
        $connectionName = $connection->getName();
        
        if (!isset($this->connectionPools[$connectionName])) {
            return;
        }

        // Check if connection is still valid
        if ($this->isConnectionHealthy($connection)) {
            $this->connectionPools[$connectionName][] = [
                'connection' => $connection,
                'released_at' => time(),
                'last_used' => time()
            ];
        } else {
            // Close unhealthy connection
            try {
                $connection->disconnect();
            } catch (\Exception $e) {
                Log::warning('Failed to disconnect unhealthy connection', [
                    'connection' => $connectionName,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Execute query with connection pooling and performance monitoring
     */
    public function executeWithPool(string $query, array $bindings = [], string $connection = null): array
    {
        $connection = $connection ?? $this->defaultConnection;
        $startTime = microtime(true);
        $retryCount = 0;
        $maxRetries = 3;

        while ($retryCount < $maxRetries) {
            try {
                $dbConnection = $this->getConnection($connection);
                $result = $dbConnection->select($query, $bindings);
                
                $this->releaseConnection($dbConnection);
                
                $executionTime = microtime(true) - $startTime;
                
                // Log slow queries
                if ($executionTime > 1.0) {
                    Log::warning('Slow query detected', [
                        'query' => $query,
                        'bindings' => $bindings,
                        'execution_time' => $executionTime,
                        'retry_count' => $retryCount,
                        'connection' => $connection
                    ]);
                }

                return [
                    'success' => true,
                    'data' => $result,
                    'execution_time' => $executionTime,
                    'retry_count' => $retryCount
                ];

            } catch (\Exception $e) {
                $retryCount++;
                
                Log::warning('Query execution failed, retrying', [
                    'query' => $query,
                    'error' => $e->getMessage(),
                    'retry_count' => $retryCount,
                    'connection' => $connection
                ]);

                if ($retryCount >= $maxRetries) {
                    throw $e;
                }

                // Exponential backoff
                usleep(min(pow(2, $retryCount) * 100000, 1000000)); // Max 1 second
            }
        }

        throw new \RuntimeException('Max retries exceeded for query execution');
    }

    /**
     * Get connection pool statistics
     */
    public function getPoolStats(): array
    {
        $stats = [];
        
        foreach ($this->connectionPools as $connection => $pool) {
            $activeConnections = count($pool);
            $idleConnections = 0;
            $expiredConnections = 0;
            $currentTime = time();

            foreach ($pool as $connData) {
                if ($currentTime - $connData['last_used'] > $this->idleTimeout) {
                    $expiredConnections++;
                } else {
                    $idleConnections++;
                }
            }

            $stats[$connection] = [
                'total_connections' => $activeConnections,
                'idle_connections' => $idleConnections,
                'expired_connections' => $expiredConnections,
                'max_pool_size' => $this->maxPoolSize,
                'min_pool_size' => $this->minPoolSize,
                'connection_timeout' => $this->connectionTimeout,
                'idle_timeout' => $this->idleTimeout,
                'avg_acquisition_time' => $this->getAverageAcquisitionTime($connection)
            ];
        }

        return $stats;
    }

    /**
     * Clean up expired connections
     */
    public function cleanupExpiredConnections(): void
    {
        $currentTime = time();
        $cleanedCount = 0;

        foreach ($this->connectionPools as $connection => &$pool) {
            $validConnections = [];

            foreach ($pool as $connData) {
                if ($currentTime - $connData['last_used'] <= $this->idleTimeout) {
                    $validConnections[] = $connData;
                } else {
                    // Close expired connection
                    try {
                        $connData['connection']->disconnect();
                        $cleanedCount++;
                    } catch (\Exception $e) {
                        Log::warning('Failed to close expired connection', [
                            'connection' => $connection,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            $pool = $validConnections;
        }

        if ($cleanedCount > 0) {
            Log::info('Cleaned up expired database connections', [
                'cleaned_count' => $cleanedCount
            ]);
        }
    }

    /**
     * Initialize connection pool for specific connection
     */
    private function initializePool(string $connectionName, array $config): void
    {
        $this->connectionPools[$connectionName] = [];
        
        // Pre-warm pool with minimum connections
        for ($i = 0; $i < $this->minPoolSize; $i++) {
            try {
                $dbConnection = DB::connection($connectionName);
                
                // Test connection
                $dbConnection->getPdo();
                
                $this->connectionPools[$connectionName][] = [
                    'connection' => $dbConnection,
                    'created_at' => time(),
                    'last_used' => time()
                ];
            } catch (\Exception $e) {
                Log::warning('Failed to pre-warm connection pool', [
                    'connection' => $connectionName,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Check if driver supports connection pooling
     */
    private function supportsPooling(string $driver): bool
    {
        return in_array($driver, ['mysql', 'mariadb', 'pgsql']);
    }

    /**
     * Check if available connection exists in pool
     */
    private function hasAvailableConnection(string $connection): bool
    {
        return isset($this->connectionPools[$connection]) && 
               count($this->connectionPools[$connection]) > 0;
    }

    /**
     * Get connection from pool
     */
    private function getPooledConnection(string $connection): \Illuminate\Database\Connection
    {
        $pool = &$this->connectionPools[$connection];
        $connData = array_shift($pool);
        
        // Update last used time
        $connData['last_used'] = time();
        
        return $connData['connection'];
    }

    /**
     * Check if connection is healthy
     */
    private function isConnectionHealthy(\Illuminate\Database\Connection $connection): bool
    {
        try {
            // Test connection with simple query
            $connection->select('SELECT 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Record connection acquisition statistics
     */
    private function recordConnectionStats(string $connection, float $acquisitionTime): void
    {
        if (!isset($this->connectionStats[$connection])) {
            $this->connectionStats[$connection] = [];
        }

        $this->connectionStats[$connection][] = $acquisitionTime;

        // Keep only last 100 measurements
        if (count($this->connectionStats[$connection]) > 100) {
            array_shift($this->connectionStats[$connection]);
        }
    }

    /**
     * Get average connection acquisition time
     */
    private function getAverageAcquisitionTime(string $connection): float
    {
        if (!isset($this->connectionStats[$connection]) || empty($this->connectionStats[$connection])) {
            return 0.0;
        }

        return array_sum($this->connectionStats[$connection]) / count($this->connectionStats[$connection]);
    }

    /**
     * Get total number of pooled connections
     */
    private function getTotalConnections(): int
    {
        $total = 0;
        foreach ($this->connectionPools as $pool) {
            $total += count($pool);
        }
        return $total;
    }
}