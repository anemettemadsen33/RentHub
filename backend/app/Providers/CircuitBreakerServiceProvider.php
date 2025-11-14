<?php

namespace App\Providers;

use App\Services\CircuitBreakerService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class CircuitBreakerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register circuit breaker for external services
        $this->registerCircuitBreakers();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Log circuit breaker initialization
        Log::info('Circuit breaker services initialized');
    }

    /**
     * Register circuit breaker services
     */
    private function registerCircuitBreakers(): void
    {
        // Email Service Circuit Breaker
        $this->app->singleton('circuit_breaker.email', function ($app) {
            return new CircuitBreakerService('email', [
                'failure_threshold' => 3,
                'recovery_timeout' => 60,
                'success_threshold' => 2,
                'half_open_max_calls' => 5,
                'timeout' => 30,
                'retry_delay' => 2,
                'max_retry_attempts' => 2
            ]);
        });

        // PDF Service Circuit Breaker
        $this->app->singleton('circuit_breaker.pdf', function ($app) {
            return new CircuitBreakerService('pdf', [
                'failure_threshold' => 5,
                'recovery_timeout' => 120,
                'success_threshold' => 3,
                'half_open_max_calls' => 10,
                'timeout' => 60,
                'retry_delay' => 3,
                'max_retry_attempts' => 3
            ]);
        });

        // External API Circuit Breaker
        $this->app->singleton('circuit_breaker.external_api', function ($app) {
            return new CircuitBreakerService('external_api', [
                'failure_threshold' => 5,
                'recovery_timeout' => 180,
                'success_threshold' => 3,
                'half_open_max_calls' => 15,
                'timeout' => 45,
                'retry_delay' => 5,
                'max_retry_attempts' => 3
            ]);
        });

        // Payment Gateway Circuit Breaker
        $this->app->singleton('circuit_breaker.payment_gateway', function ($app) {
            return new CircuitBreakerService('payment_gateway', [
                'failure_threshold' => 2,
                'recovery_timeout' => 300,
                'success_threshold' => 2,
                'half_open_max_calls' => 3,
                'timeout' => 30,
                'retry_delay' => 10,
                'max_retry_attempts' => 1
            ]);
        });

        // Database Circuit Breaker (for external database connections)
        $this->app->singleton('circuit_breaker.database', function ($app) {
            return new CircuitBreakerService('database', [
                'failure_threshold' => 10,
                'recovery_timeout' => 30,
                'success_threshold' => 5,
                'half_open_max_calls' => 20,
                'timeout' => 15,
                'retry_delay' => 1,
                'max_retry_attempts' => 5
            ]);
        });

        // File Storage Circuit Breaker
        $this->app->singleton('circuit_breaker.file_storage', function ($app) {
            return new CircuitBreakerService('file_storage', [
                'failure_threshold' => 5,
                'recovery_timeout' => 60,
                'success_threshold' => 3,
                'half_open_max_calls' => 10,
                'timeout' => 30,
                'retry_delay' => 2,
                'max_retry_attempts' => 3
            ]);
        });
    }
}