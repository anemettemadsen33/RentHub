<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InputValidationMiddleware
{
    private array $config;
    
    public function __construct()
    {
        $this->config = config('security.input_validation');
    }
    
    public function handle(Request $request, Closure $next)
    {
        if (!$this->config['enabled']) {
            return $next($request);
        }
        
        $this->validateInput($request);
        
        if ($this->config['sanitize_strings']) {
            $this->sanitizeInput($request);
        }
        
        return $next($request);
    }
    
    private function validateInput(Request $request): void
    {
        $maxLength = $this->config['max_input_length'];
        
        foreach ($request->all() as $key => $value) {
            if (is_string($value) && strlen($value) > $maxLength) {
                abort(400, "Input field '{$key}' exceeds maximum length");
            }
            
            // Check for common attack patterns
            if ($this->containsMaliciousPattern($value)) {
                \Log::warning('Malicious input detected', [
                    'field' => $key,
                    'ip' => $request->ip(),
                    'user' => $request->user()?->id,
                ]);
                abort(400, 'Invalid input detected');
            }
        }
    }
    
    private function sanitizeInput(Request $request): void
    {
        $sanitized = [];
        
        foreach ($request->all() as $key => $value) {
            $sanitized[$key] = $this->sanitizeValue($value);
        }
        
        $request->merge($sanitized);
    }
    
    private function sanitizeValue($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'sanitizeValue'], $value);
        }
        
        if (!is_string($value)) {
            return $value;
        }
        
        // Remove null bytes
        $value = str_replace("\0", '', $value);
        
        // Strip tags if configured
        if ($this->config['strip_tags']) {
            $value = strip_tags($value);
        }
        
        // Trim whitespace
        $value = trim($value);
        
        return $value;
    }
    
    private function containsMaliciousPattern($value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        
        $patterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe/i',
            '/eval\s*\(/i',
            '/base64_decode/i',
            '/system\s*\(/i',
            '/exec\s*\(/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }
        
        return false;
    }
}
