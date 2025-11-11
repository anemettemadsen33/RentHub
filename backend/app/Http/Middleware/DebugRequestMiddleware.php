<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugRequestMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Log EVERY incoming request before any processing
        Log::info('[DEBUG] Incoming request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'origin' => $request->header('Origin'),
            'referer' => $request->header('Referer'),
            'headers' => $request->headers->all(),
        ]);

        $response = $next($request);

        Log::info('[DEBUG] Outgoing response', [
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
            'cors_headers' => [
                'Access-Control-Allow-Origin' => $response->headers->get('Access-Control-Allow-Origin'),
                'Access-Control-Allow-Methods' => $response->headers->get('Access-Control-Allow-Methods'),
                'Access-Control-Allow-Headers' => $response->headers->get('Access-Control-Allow-Headers'),
                'Access-Control-Allow-Credentials' => $response->headers->get('Access-Control-Allow-Credentials'),
            ],
        ]);

        return $response;
    }
}
