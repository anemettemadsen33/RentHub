<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Security\InputValidationService;
use Symfony\Component\HttpFoundation\Response;

class XssProtectionMiddleware
{
    public function __construct(
        protected InputValidationService $validationService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('security.app_security.xss_protection.enabled', true)) {
            return $next($request);
        }

        // Sanitize input data
        $this->sanitizeInput($request);

        $response = $next($request);

        // Add XSS protection headers
        if (config('security.app_security.xss_protection.content_security_policy', true)) {
            $csp = config('security.headers.Content-Security-Policy');
            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }

    /**
     * Sanitize request input
     */
    protected function sanitizeInput(Request $request): void
    {
        if (!config('security.app_security.xss_protection.sanitize_output', true)) {
            return;
        }

        $input = $request->all();
        $sanitized = $this->sanitizeArray($input);
        $request->merge($sanitized);
    }

    /**
     * Sanitize array recursively
     */
    protected function sanitizeArray(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                // Check for malicious content
                if ($this->validationService->containsMaliciousContent($value)) {
                    $sanitized[$key] = '';
                } else {
                    $sanitized[$key] = $this->validationService->preventXss($value);
                }
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
