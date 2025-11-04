<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InputSanitizationMiddleware
{
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value, $key) {
            if (! in_array($key, $this->except) && is_string($value)) {
                $value = $this->sanitize($value);
            }
        });

        $request->merge($input);

        return $next($request);
    }

    protected function sanitize(string $value): string
    {
        // Remove null bytes
        $value = str_replace(chr(0), '', $value);

        // Strip tags except allowed ones
        $value = strip_tags($value, '<p><br><strong><em><ul><ol><li><a>');

        // Convert special characters
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);

        // Remove any remaining HTML entities
        $value = preg_replace('/&#?[a-z0-9]{2,8};/i', '', $value);

        return trim($value);
    }
}
