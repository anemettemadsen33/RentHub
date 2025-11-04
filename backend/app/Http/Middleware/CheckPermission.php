<?php

namespace App\Http\Middleware;

use App\Services\Auth\RBACService;
use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    protected RBACService $rbacService;

    public function __construct(RBACService $rbacService)
    {
        $this->rbacService = $rbacService;
    }

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if user has any of the required permissions
        if (!$this->rbacService->hasAnyPermission($user, $permissions)) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You do not have the required permissions',
            ], 403);
        }

        return $next($request);
    }
}
