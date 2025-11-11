<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip maintenance mode check for admin routes
        if ($request->is('admin/*') || $request->is('api/admin/*')) {
            return $next($request);
        }

        // Check if maintenance mode is enabled
        if (setting('maintenance_mode') === '1') {
            // Allow API health checks
            if ($request->is('api/health')) {
                return $next($request);
            }

            // Return maintenance response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Aplicația este în modul de mentenanță. Vă rugăm să reveniți mai târziu.',
                    'maintenance_mode' => true,
                ], 503);
            }

            abort(503, 'Aplicația este în modul de mentenanță.');
        }

        return $next($request);
    }
}
