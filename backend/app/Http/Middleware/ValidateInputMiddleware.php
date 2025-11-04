<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateInputMiddleware
{
    /**
     * Dangerous patterns that should be blocked
     */
    protected array $dangerousPatterns = [
        // SQL Injection patterns
        '/(\bUNION\b.*\bSELECT\b)/i',
        '/(\bSELECT\b.*\bFROM\b)/i',
        '/(\bINSERT\b.*\bINTO\b)/i',
        '/(\bUPDATE\b.*\bSET\b)/i',
        '/(\bDELETE\b.*\bFROM\b)/i',
        '/(\bDROP\b.*\bTABLE\b)/i',

        // XSS patterns
        '/<script[^>]*>.*?<\/script>/is',
        '/javascript:/i',
        '/on\w+\s*=/i',

        // Path traversal
        '/\.\.\//',
        '/\.\.\\\\/',

        // Command injection
        '/[;&|`$]/',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Sanitize all input data
        $this->sanitizeInput($request);

        // Check for dangerous patterns
        if ($this->containsDangerousContent($request->all())) {
            return response()->json([
                'message' => 'Invalid input detected.',
                'error' => 'SECURITY_VALIDATION_FAILED',
            ], 400);
        }

        return $next($request);
    }

    protected function sanitizeInput(Request $request): void
    {
        $input = $request->all();
        $sanitized = $this->recursiveSanitize($input);
        $request->merge($sanitized);
    }

    protected function recursiveSanitize(mixed $data): mixed
    {
        if (is_array($data)) {
            return array_map([$this, 'recursiveSanitize'], $data);
        }

        if (is_string($data)) {
            // Remove null bytes
            $data = str_replace("\0", '', $data);

            // Trim whitespace
            $data = trim($data);

            // Convert special HTML characters
            return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $data;
    }

    protected function containsDangerousContent(array $data): bool
    {
        foreach ($data as $value) {
            if (is_array($value)) {
                if ($this->containsDangerousContent($value)) {
                    return true;
                }
            } elseif (is_string($value)) {
                foreach ($this->dangerousPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
