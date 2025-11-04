<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class XssProtection
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                $value = $this->sanitize($value);
            }
        });

        $request->merge($input);

        return $next($request);
    }

    /**
     * Sanitize input to prevent XSS
     */
    protected function sanitize(string $value): string
    {
        // Remove null bytes
        $value = str_replace(chr(0), '', $value);

        // Strip tags except allowed ones
        $allowedTags = '<p><br><strong><em><ul><ol><li><a>';
        $value = strip_tags($value, $allowedTags);

        // Encode special characters
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $value;
    }
}
