<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Security\InputValidationService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SqlInjectionProtectionMiddleware
{
    public function __construct(
        protected InputValidationService $validationService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('security.app_security.sql_injection.enabled', true)) {
            return $next($request);
        }

        if (!config('security.app_security.sql_injection.validate_input', true)) {
            return $next($request);
        }

        // Check all input for SQL injection patterns
        if ($this->containsSqlInjection($request->all())) {
            Log::warning('SQL injection attempt detected', [
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
                'route' => $request->path(),
                'input' => $request->all(),
            ]);

            return response()->json([
                'error' => 'Invalid input detected',
            ], 400);
        }

        return $next($request);
    }

    /**
     * Check if input contains SQL injection patterns
     */
    protected function containsSqlInjection($input): bool
    {
        if (is_array($input)) {
            foreach ($input as $value) {
                if ($this->containsSqlInjection($value)) {
                    return true;
                }
            }
            return false;
        }

        if (!is_string($input)) {
            return false;
        }

        return !$this->validationService->validateSqlInput($input);
    }
}
