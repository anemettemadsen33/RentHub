<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SqlInjectionProtection
{
    /**
     * SQL injection patterns to detect
     */
    protected array $sqlPatterns = [
        '/(\bunion\b.*\bselect\b)/i',
        '/(\bselect\b.*\bfrom\b)/i',
        '/(\binsert\b.*\binto\b)/i',
        '/(\bupdate\b.*\bset\b)/i',
        '/(\bdelete\b.*\bfrom\b)/i',
        '/(\bdrop\b.*\btable\b)/i',
        '/(\bexec\b|\bexecute\b)/i',
        '/(\bor\b.*=.*)/i',
        '/(\band\b.*=.*)/i',
        '/(--|;|\/\*|\*\/)/i',
        '/(\bxp_\w+)/i',
        '/(\bsp_\w+)/i',
    ];

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = array_merge(
            $request->query->all(),
            $request->request->all(),
            $request->route()->parameters() ?? []
        );

        foreach ($input as $key => $value) {
            if (is_string($value) && $this->detectSqlInjection($value)) {
                abort(403, 'Suspicious input detected');
            }
        }

        return $next($request);
    }

    /**
     * Detect SQL injection patterns
     */
    protected function detectSqlInjection(string $input): bool
    {
        foreach ($this->sqlPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }
}
