<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CircuitBreakerService
{
    private array $config;
    private string $serviceName;

    public function __construct(string $serviceName, array $config = [])
    {
        $this->serviceName = $serviceName;
        $this->config = array_merge([
            'failure_threshold' => 5,
            'recovery_timeout' => 60,
            'success_threshold' => 3,
            'half_open_max_calls' => 10,
            'timeout' => 30,
            'retry_delay' => 1,
            'max_retry_attempts' => 3
        ], $config);
    }

    /**
     * Execute a function with circuit breaker protection
     */
    public function execute(callable $callback, string $operation = 'default')
    {
        $circuitState = $this->getCircuitState();
        $operationKey = "{$this->serviceName}:{$operation}";

        Log::debug("Circuit breaker executing", [
            'service' => $this->serviceName,
            'operation' => $operation,
            'state' => $circuitState
        ]);

        switch ($circuitState) {
            case 'CLOSED':
                return $this->executeInClosedState($callback, $operationKey);
                
            case 'OPEN':
                return $this->executeInOpenState($operationKey);
                
            case 'HALF_OPEN':
                return $this->executeInHalfOpenState($callback, $operationKey);
                
            default:
                throw new \RuntimeException("Unknown circuit state: {$circuitState}");
        }
    }

    /**
     * Execute in CLOSED state (normal operation)
     */
    private function executeInClosedState(callable $callback, string $operationKey)
    {
        try {
            $startTime = microtime(true);
            $result = $callback();
            $executionTime = microtime(true) - $startTime;

            $this->recordSuccess($operationKey, $executionTime);
            
            Log::debug("Circuit breaker success", [
                'service' => $this->serviceName,
                'operation' => $operationKey,
                'execution_time' => $executionTime
            ]);

            return $result;

        } catch (\Exception $e) {
            $this->recordFailure($operationKey, $e);
            
            Log::warning("Circuit breaker failure", [
                'service' => $this->serviceName,
                'operation' => $operationKey,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Execute in OPEN state (circuit is open, service unavailable)
     */
    private function executeInOpenState(string $operationKey)
    {
        $lastFailureTime = $this->getLastFailureTime($operationKey);
        $recoveryTimeout = $this->config['recovery_timeout'];
        
        if (time() - $lastFailureTime >= $recoveryTimeout) {
            // Transition to HALF_OPEN state
            $this->setCircuitState('HALF_OPEN');
            $this->resetHalfOpenCallCount($operationKey);
            
            Log::info("Circuit breaker transitioning to HALF_OPEN", [
                'service' => $this->serviceName,
                'operation' => $operationKey
            ]);
            
            return $this->executeInHalfOpenState(function() {
                throw new \RuntimeException("Circuit breaker is half-open");
            }, $operationKey);
        }

        $failureCount = $this->getFailureCount($operationKey);
        
        Log::warning("Circuit breaker is OPEN", [
            'service' => $this->serviceName,
            'operation' => $operationKey,
            'failure_count' => $failureCount,
            'last_failure_time' => $lastFailureTime
        ]);

        throw new \RuntimeException("Circuit breaker is OPEN for service: {$this->serviceName}");
    }

    /**
     * Execute in HALF_OPEN state (testing if service has recovered)
     */
    private function executeInHalfOpenState(callable $callback, string $operationKey)
    {
        $halfOpenCalls = $this->getHalfOpenCallCount($operationKey);
        $maxCalls = $this->config['half_open_max_calls'];

        if ($halfOpenCalls >= $maxCalls) {
            // Too many calls in half-open state, revert to OPEN
            $this->setCircuitState('OPEN');
            
            Log::warning("Circuit breaker reverting to OPEN from HALF_OPEN", [
                'service' => $this->serviceName,
                'operation' => $operationKey,
                'half_open_calls' => $halfOpenCalls
            ]);
            
            throw new \RuntimeException("Circuit breaker is OPEN for service: {$this->serviceName}");
        }

        $this->incrementHalfOpenCallCount($operationKey);

        try {
            $startTime = microtime(true);
            $result = $callback();
            $executionTime = microtime(true) - $startTime;

            $this->recordSuccess($operationKey, $executionTime);
            $successCount = $this->getHalfOpenSuccessCount($operationKey);

            Log::debug("Circuit breaker half-open success", [
                'service' => $this->serviceName,
                'operation' => $operationKey,
                'success_count' => $successCount,
                'half_open_calls' => $halfOpenCalls
            ]);

            if ($successCount >= $this->config['success_threshold']) {
                // Service has recovered, transition to CLOSED
                $this->setCircuitState('CLOSED');
                $this->resetCircuitMetrics($operationKey);
                
                Log::info("Circuit breaker transitioning to CLOSED", [
                    'service' => $this->serviceName,
                    'operation' => $operationKey,
                    'success_count' => $successCount
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            $this->recordFailure($operationKey, $e);
            
            Log::warning("Circuit breaker half-open failure", [
                'service' => $this->serviceName,
                'operation' => $operationKey,
                'error' => $e->getMessage()
            ]);

            // Service still failing, revert to OPEN
            $this->setCircuitState('OPEN');
            throw $e;
        }
    }

    /**
     * Record a successful operation
     */
    private function recordSuccess(string $operationKey, float $executionTime): void
    {
        $key = "circuit_breaker:success:{$operationKey}";
        Cache::increment($key);
        Cache::put("circuit_breaker:last_success_time:{$operationKey}", time(), 3600);
        Cache::put("circuit_breaker:last_execution_time:{$operationKey}", $executionTime, 3600);
        
        // Reset failure count on success
        Cache::forget("circuit_breaker:failure_count:{$operationKey}");
    }

    /**
     * Record a failed operation
     */
    private function recordFailure(string $operationKey, \Exception $exception): void
    {
        $failureKey = "circuit_breaker:failure_count:{$operationKey}";
        $failureCount = Cache::increment($failureKey);
        Cache::put("circuit_breaker:last_failure_time:{$operationKey}", time(), 3600);
        Cache::put("circuit_breaker:last_error:{$operationKey}", $exception->getMessage(), 3600);

        // Check if we should open the circuit
        if ($failureCount >= $this->config['failure_threshold']) {
            $this->setCircuitState('OPEN');
            
            Log::error("Circuit breaker opened due to failures", [
                'service' => $this->serviceName,
                'operation' => $operationKey,
                'failure_count' => $failureCount,
                'threshold' => $this->config['failure_threshold']
            ]);
        }
    }

    /**
     * Get current circuit state
     */
    private function getCircuitState(): string
    {
        return Cache::get("circuit_breaker:state:{$this->serviceName}", 'CLOSED');
    }

    /**
     * Set circuit state
     */
    private function setCircuitState(string $state): void
    {
        Cache::put("circuit_breaker:state:{$this->serviceName}", $state, 3600);
        Cache::put("circuit_breaker:state_change_time:{$this->serviceName}", time(), 3600);
    }

    /**
     * Get failure count for an operation
     */
    private function getFailureCount(string $operationKey): int
    {
        return Cache::get("circuit_breaker:failure_count:{$operationKey}", 0);
    }

    /**
     * Get last failure time
     */
    private function getLastFailureTime(string $operationKey): int
    {
        return Cache::get("circuit_breaker:last_failure_time:{$operationKey}", 0);
    }

    /**
     * Get half-open call count
     */
    private function getHalfOpenCallCount(string $operationKey): int
    {
        return Cache::get("circuit_breaker:half_open_calls:{$operationKey}", 0);
    }

    /**
     * Increment half-open call count
     */
    private function incrementHalfOpenCallCount(string $operationKey): void
    {
        Cache::increment("circuit_breaker:half_open_calls:{$operationKey}");
    }

    /**
     * Reset half-open call count
     */
    private function resetHalfOpenCallCount(string $operationKey): void
    {
        Cache::forget("circuit_breaker:half_open_calls:{$operationKey}");
    }

    /**
     * Get half-open success count
     */
    private function getHalfOpenSuccessCount(string $operationKey): int
    {
        return Cache::get("circuit_breaker:half_open_success:{$operationKey}", 0);
    }

    /**
     * Reset circuit metrics
     */
    private function resetCircuitMetrics(string $operationKey): void
    {
        Cache::forget("circuit_breaker:failure_count:{$operationKey}");
        Cache::forget("circuit_breaker:half_open_calls:{$operationKey}");
        Cache::forget("circuit_breaker:half_open_success:{$operationKey}");
    }

    /**
     * Get circuit breaker statistics
     */
    public function getStats(): array
    {
        $state = $this->getCircuitState();
        $stateChangeTime = Cache::get("circuit_breaker:state_change_time:{$this->serviceName}", 0);
        
        $operations = [];
        $keys = Cache::getRedis()->keys("circuit_breaker:*:{$this->serviceName}:*");
        
        foreach ($keys as $key) {
            if (preg_match('/circuit_breaker:(\w+):' . preg_quote($this->serviceName) . ':(.+)/', $key, $matches)) {
                $metric = $matches[1];
                $operation = $matches[2];
                
                if (!isset($operations[$operation])) {
                    $operations[$operation] = [];
                }
                
                $operations[$operation][$metric] = Cache::get($key);
            }
        }

        return [
            'service' => $this->serviceName,
            'state' => $state,
            'state_change_time' => $stateChangeTime ? date('Y-m-d H:i:s', $stateChangeTime) : null,
            'config' => $this->config,
            'operations' => $operations
        ];
    }

    /**
     * Manually reset circuit breaker
     */
    public function reset(): void
    {
        $this->setCircuitState('CLOSED');
        
        // Clear all metrics for this service
        $keys = Cache::getRedis()->keys("circuit_breaker:*:{$this->serviceName}:*");
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        Log::info("Circuit breaker manually reset", [
            'service' => $this->serviceName
        ]);
    }
}