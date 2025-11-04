<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiVersionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $config = config('api.versioning');

        if (! $config['enabled']) {
            return $next($request);
        }

        $version = $this->resolveVersion($request);

        if (! in_array($version, $config['supported_versions'])) {
            return response()->json([
                'error' => 'Unsupported API version',
                'supported_versions' => $config['supported_versions'],
            ], 400);
        }

        $request->attributes->set('api_version', $version);

        return $next($request);
    }

    protected function resolveVersion(Request $request): string
    {
        $config = config('api.versioning');

        // Check header
        if ($request->hasHeader($config['header'])) {
            return $request->header($config['header']);
        }

        // Check query param
        if ($request->has($config['query_param'])) {
            return $request->query($config['query_param']);
        }

        // Check URL path
        $segments = $request->segments();
        if (isset($segments[1]) && in_array($segments[1], $config['supported_versions'])) {
            return $segments[1];
        }

        return $config['default'];
    }
}
