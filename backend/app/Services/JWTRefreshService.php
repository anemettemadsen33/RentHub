<?php

namespace App\Services;

use App\Models\User;
use App\Models\RefreshToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class JWTRefreshService
{
    /**
     * Token rotation strategy
     */
    public function rotateRefreshToken(string $oldRefreshToken): array
    {
        $tokenRecord = RefreshToken::where('token', hash('sha256', $oldRefreshToken))
            ->where('expires_at', '>', now())
            ->where('revoked', false)
            ->first();

        if (!$tokenRecord) {
            throw new \Exception('Invalid or expired refresh token');
        }

        // Check for token reuse (potential attack)
        if ($tokenRecord->last_used_at && $tokenRecord->last_used_at->diffInSeconds(now()) < 5) {
            $this->handleTokenReuse($tokenRecord);
            throw new \Exception('Token reuse detected');
        }

        // Mark old token as used
        $tokenRecord->update([
            'last_used_at' => now(),
        ]);

        $user = $tokenRecord->user;

        // Generate new tokens
        $accessToken = $this->generateAccessToken($user);
        $newRefreshToken = $this->generateRefreshToken($user, $tokenRecord);

        // Revoke old refresh token after successful rotation
        $tokenRecord->update(['revoked' => true]);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $newRefreshToken,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl', 3600),
        ];
    }

    /**
     * Generate access token with custom claims
     */
    private function generateAccessToken(User $user): string
    {
        $payload = [
            'sub' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'permissions' => $this->getUserPermissions($user),
            'iat' => time(),
            'exp' => time() + config('jwt.ttl', 3600),
            'jti' => Str::uuid()->toString(),
            'device_id' => request()->header('X-Device-ID'),
            'ip' => request()->ip(),
        ];

        return $this->encodeJWT($payload);
    }

    /**
     * Generate refresh token
     */
    private function generateRefreshToken(User $user, ?RefreshToken $previousToken = null): string
    {
        $token = Str::random(64);
        $hashedToken = hash('sha256', $token);

        RefreshToken::create([
            'user_id' => $user->id,
            'token' => $hashedToken,
            'expires_at' => Carbon::now()->addDays(30),
            'device_id' => request()->header('X-Device-ID'),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
            'parent_token_id' => $previousToken?->id,
        ]);

        return $token;
    }

    /**
     * Handle token reuse attack
     */
    private function handleTokenReuse(RefreshToken $tokenRecord): void
    {
        // Revoke entire token family (all tokens in the rotation chain)
        $this->revokeTokenFamily($tokenRecord);

        // Log security incident
        \Log::critical('JWT: Token reuse detected', [
            'user_id' => $tokenRecord->user_id,
            'token_id' => $tokenRecord->id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Trigger security alert
        event(new \App\Events\SecurityIncident([
            'type' => 'token_reuse',
            'severity' => 'critical',
            'user_id' => $tokenRecord->user_id,
            'details' => [
                'token_id' => $tokenRecord->id,
                'ip' => request()->ip(),
            ],
        ]));

        // Optional: Force logout user
        $this->forceLogoutUser($tokenRecord->user_id);
    }

    /**
     * Revoke token family (all tokens in rotation chain)
     */
    private function revokeTokenFamily(RefreshToken $token): void
    {
        // Find root token
        $rootToken = $token;
        while ($rootToken->parent_token_id) {
            $rootToken = RefreshToken::find($rootToken->parent_token_id);
        }

        // Revoke all descendants
        $this->revokeTokenDescendants($rootToken->id);
    }

    /**
     * Recursively revoke all descendant tokens
     */
    private function revokeTokenDescendants(int $tokenId): void
    {
        RefreshToken::where('id', $tokenId)
            ->orWhere('parent_token_id', $tokenId)
            ->update(['revoked' => true]);

        $children = RefreshToken::where('parent_token_id', $tokenId)->pluck('id');
        
        foreach ($children as $childId) {
            $this->revokeTokenDescendants($childId);
        }
    }

    /**
     * Force logout user across all devices
     */
    private function forceLogoutUser(int $userId): void
    {
        RefreshToken::where('user_id', $userId)->update(['revoked' => true]);
        
        // Clear user sessions
        \Cache::forget("user_sessions:{$userId}");
    }

    /**
     * Get user permissions for token
     */
    private function getUserPermissions(User $user): array
    {
        $cacheKey = "user_permissions:{$user->id}";
        
        return \Cache::remember($cacheKey, 300, function () use ($user) {
            return $user->getAllPermissions()->pluck('name')->toArray();
        });
    }

    /**
     * Encode JWT (simplified - use tymon/jwt-auth in production)
     */
    private function encodeJWT(array $payload): string
    {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', "$header.$payload", config('jwt.secret'));
        
        return "$header.$payload." . base64_encode($signature);
    }

    /**
     * Validate and decode JWT
     */
    public function decodeJWT(string $token): ?array
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;
        
        // Verify signature
        $validSignature = base64_encode(
            hash_hmac('sha256', "$header.$payload", config('jwt.secret'))
        );

        if ($signature !== $validSignature) {
            return null;
        }

        $payload = json_decode(base64_decode($payload), true);

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    /**
     * Revoke all tokens for a user
     */
    public function revokeAllUserTokens(int $userId): void
    {
        RefreshToken::where('user_id', $userId)->update(['revoked' => true]);
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens(): int
    {
        return RefreshToken::where('expires_at', '<', now())
            ->orWhere('revoked', true)
            ->where('created_at', '<', now()->subDays(90))
            ->delete();
    }
}
