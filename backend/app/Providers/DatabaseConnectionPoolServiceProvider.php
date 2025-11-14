<?php

namespace App\Providers;

use App\Services\DatabaseConnectionPoolService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;

class DatabaseConnectionPoolServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(DatabaseConnectionPoolService::class, function ($app) {
            return new DatabaseConnectionPoolService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Initialize connection pools on application boot
        if (config('database.pool.supported_drivers.' . config('database.default'), false)) {
            try {
                $poolService = $this->app->make(DatabaseConnectionPoolService::class);
                $poolService->initializePools();
                
                Log::info('Database connection pools initialized successfully');
            } catch (\Exception $e) {
                Log::warning('Failed to initialize database connection pools', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Schedule periodic cleanup of expired connections
        if (config('database.pool.health_check_enabled', true)) {
            $this->scheduleCleanupTasks();
        }
    }

    /**
     * Schedule cleanup and monitoring tasks
     */
    private function scheduleCleanupTasks(): void
    {
        // Clean up expired connections every 5 minutes
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            
            $schedule->call(function () {
                try {
                    $poolService = app(DatabaseConnectionPoolService::class);
                    $poolService->cleanupExpiredConnections();
                    
                    // Log pool statistics if monitoring is enabled
                    if (config('database.pool.monitoring.enabled', true)) {
                        $stats = $poolService->getPoolStats();
                        
                        foreach ($stats as $connection => $connectionStats) {
                            Log::info('Database connection pool statistics', [
                                'connection' => $connection,
                                'stats' => $connectionStats,
                                'timestamp' => now()->toDateTimeString()
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to cleanup database connection pools', [
                        'error' => $e->getMessage()
                    ]);
                }
            })->everyFiveMinutes()->name('db-pool-cleanup');

            // Health check every minute
            $schedule->call(function () {
                try {
                    $poolService = app(DatabaseConnectionPoolService::class);
                    $stats = $poolService->getPoolStats();
                    
                    // Alert if pool is running low on connections
                    foreach ($stats as $connection => $connectionStats) {
                        if ($connectionStats['idle_connections'] < $connectionStats['min_pool_size']) {
                            Log::warning('Database connection pool running low on connections', [
                                'connection' => $connection,
                                'idle_connections' => $connectionStats['idle_connections'],
                                'min_required' => $connectionStats['min_pool_size']
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to perform database connection pool health check', [
                        'error' => $e->getMessage()
                    ]);
                }
            })->everyMinute()->name('db-pool-health-check');
        });
    }
}