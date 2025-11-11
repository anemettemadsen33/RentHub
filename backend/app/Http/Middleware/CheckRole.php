<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        if (app()->environment('testing')) {
            \Log::debug('CheckRole invoked', [
                'user_id' => $request->user()->id ?? null,
                'required_roles' => $roles,
                'user_roles' => $request->user()->roles->pluck('name')->toArray(),
                'has_any_role' => $request->user()->hasAnyRole($roles),
            ]);
        }

        // Check if user has any of the required roles using Spatie Permission
        if (! $request->user()->hasAnyRole($roles)) {
            if (app()->environment('testing')) {
                \Log::debug('CheckRole denied', [
                    'user_id' => $request->user()->id ?? null,
                    'required_roles' => $roles,
                    'user_roles' => $request->user()->roles->pluck('name')->toArray(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Required role: '.implode(' or ', $roles),
            ], 403);
        }

        return $next($request);
    }
}
