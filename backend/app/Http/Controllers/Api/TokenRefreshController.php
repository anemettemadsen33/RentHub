<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RefreshTokenRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Token Refresh Controller
 * 
 * Handles token refresh operations for authenticated users
 * with comprehensive logging and security measures.
 */
class TokenRefreshController extends Controller
{
    /**
     * Refresh the current authentication token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            // Validate rate limiting
            if ($this->isRateLimited($request)) {
                return $this->rateLimitedResponse($request);
            }

            // Get current user
            $user = $request->user();
            if (!$user) {
                return $this->unauthenticatedResponse();
            }

            // Get current token
            $currentToken = $user->currentAccessToken();
            if (!$currentToken) {
                return response()->json([
                    'error' => 'No active token',
                    'message' => 'No active authentication token found',
                ], 400);
            }

            // Log token refresh attempt
            Log::info('Token refresh attempt', [
                'user_id' => $user->id,
                'old_token_id' => $currentToken->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Revoke current token
            $currentToken->delete();

            // Create new token
            $newToken = $user->createToken('auth_token');
            $plainTextToken = $newToken->plainTextToken;
            $tokenModel = $newToken->accessToken;

            // Cache token info for session management
            Cache::put(
                "user_token:{$user->id}:{$tokenModel->id}",
                [
                    'created_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
                86400 // 24 hours
            );

            // Log successful refresh
            Log::info('Token refreshed successfully', [
                'user_id' => $user->id,
                'new_token_id' => $tokenModel->id,
                'expires_at' => $tokenModel->expires_at?->toDateTimeString(),
            ]);

            return response()->json([
                'message' => 'Token refreshed successfully',
                'access_token' => $plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $tokenModel->expires_at?->toDateTimeString(),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at?->toDateTimeString(),
                ],
                'timestamp' => now()->toDateTimeString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Token refresh failed', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Token refresh failed',
                'message' => 'Unable to refresh authentication token',
            ], 500);
        }
    }

    /**
     * Get all active tokens for the authenticated user
     */
    public function tokens(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->unauthenticatedResponse();
        }

        $tokens = $user->tokens()
            ->where('expires_at', '>', now())
            ->orWhereNull('expires_at')
            ->orderBy('last_used_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at?->toDateTimeString(),
                    'created_at' => $token->created_at->toDateTimeString(),
                    'expires_at' => $token->expires_at?->toDateTimeString(),
                    'is_current' => $token->id === optional($token->user->currentAccessToken())->id,
                ];
            });

        return response()->json([
            'tokens' => $tokens,
            'total_active' => $tokens->count(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Revoke a specific token
     */
    public function revoke(Request $request, string $tokenId): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->unauthenticatedResponse();
        }

        $token = $user->tokens()->find($tokenId);
        if (!$token) {
            return response()->json([
                'error' => 'Token not found',
                'message' => 'The specified token does not exist or does not belong to you',
            ], 404);
        }

        // Prevent revoking current token through this endpoint
        if ($token->id === optional($user->currentAccessToken())->id) {
            return response()->json([
                'error' => 'Cannot revoke current token',
                'message' => 'Use the refresh endpoint to revoke and create a new token',
            ], 400);
        }

        Log::info('Token revoked', [
            'user_id' => $user->id,
            'token_id' => $token->id,
            'token_name' => $token->name,
            'ip' => $request->ip(),
        ]);

        $token->delete();

        return response()->json([
            'message' => 'Token revoked successfully',
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Revoke all tokens except current one
     */
    public function revokeAll(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->unauthenticatedResponse();
        }

        $currentToken = $user->currentAccessToken();
        $revokedCount = $user->tokens()
            ->where('id', '!=', optional($currentToken)->id)
            ->count();

        Log::info('Revoking all tokens except current', [
            'user_id' => $user->id,
            'current_token_id' => optional($currentToken)->id,
            'tokens_to_revoke' => $revokedCount,
            'ip' => $request->ip(),
        ]);

        $user->tokens()
            ->where('id', '!=', optional($currentToken)->id)
            ->delete();

        return response()->json([
            'message' => 'All other tokens revoked successfully',
            'revoked_count' => $revokedCount,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Check if request is rate limited
     */
    protected function isRateLimited(Request $request): bool
    {
        $key = "token_refresh:{$request->ip()}";
        return RateLimiter::tooManyAttempts($key, 10); // 10 attempts per minute
    }

    /**
     * Return rate limited response
     */
    protected function rateLimitedResponse(Request $request): JsonResponse
    {
        $key = "token_refresh:{$request->ip()}";
        $retryAfter = RateLimiter::availableIn($key);

        Log::warning('Token refresh rate limited', [
            'ip' => $request->ip(),
            'retry_after' => $retryAfter,
        ]);

        return response()->json([
            'error' => 'Too Many Requests',
            'message' => 'Token refresh rate limit exceeded',
            'retry_after' => $retryAfter,
            'available_in' => $retryAfter . ' seconds',
        ], 429)->header('Retry-After', $retryAfter);
    }

    /**
     * Return unauthenticated response
     */
    protected function unauthenticatedResponse(): JsonResponse
    {
        return response()->json([
            'error' => 'Unauthenticated',
            'message' => 'Authentication required',
        ], 401);
    }
}