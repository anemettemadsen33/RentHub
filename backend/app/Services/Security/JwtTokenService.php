<?php

namespace App\Services\Security;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class JwtTokenService
{
    private string $secret;
    private string $algorithm = 'HS256';
    private int $accessTokenExpiry = 900; // 15 minutes
    private int $refreshTokenExpiry = 604800; // 7 days

    public function __construct()
    {
        $this->secret = config('app.key');
    }

    /**
     * Generate access token.
     */
    public function generateAccessToken(User $user): string
    {
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + $this->accessTokenExpiry,
            'type' => 'access',
            'email' => $user->email,
            'role' => $user->role,
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    /**
     * Generate refresh token.
     */
    public function generateRefreshToken(User $user): string
    {
        $jti = bin2hex(random_bytes(16));

        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + $this->refreshTokenExpiry,
            'type' => 'refresh',
            'jti' => $jti,
        ];

        $token = JWT::encode($payload, $this->secret, $this->algorithm);

        // Store refresh token in cache
        Cache::put(
            "refresh_token:{$jti}",
            [
                'user_id' => $user->id,
                'created_at' => now(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
            $this->refreshTokenExpiry
        );

        return $token;
    }

    /**
     * Verify and decode token.
     */
    public function verifyToken(string $token): ?object
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));

            // Check if token is revoked
            if ($decoded->type === 'refresh' && isset($decoded->jti)) {
                if (!Cache::has("refresh_token:{$decoded->jti}")) {
                    return null;
                }
            }

            return $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Refresh access token using refresh token.
     */
    public function refreshAccessToken(string $refreshToken): ?array
    {
        $decoded = $this->verifyToken($refreshToken);

        if (!$decoded || $decoded->type !== 'refresh') {
            return null;
        }

        $user = User::find($decoded->sub);

        if (!$user) {
            return null;
        }

        return [
            'access_token' => $this->generateAccessToken($user),
            'token_type' => 'Bearer',
            'expires_in' => $this->accessTokenExpiry,
        ];
    }

    /**
     * Revoke refresh token.
     */
    public function revokeRefreshToken(string $token): bool
    {
        $decoded = $this->verifyToken($token);

        if (!$decoded || $decoded->type !== 'refresh' || !isset($decoded->jti)) {
            return false;
        }

        Cache::forget("refresh_token:{$decoded->jti}");

        return true;
    }

    /**
     * Revoke all user's refresh tokens.
     */
    public function revokeAllUserTokens(User $user): void
    {
        // This would require storing all JTIs per user
        // For simplicity, we'll mark them as revoked
        Cache::put("user_tokens_revoked:{$user->id}", true, $this->refreshTokenExpiry);
    }

    /**
     * Generate API key.
     */
    public function generateApiKey(User $user, string $name, ?Carbon $expiresAt = null): array
    {
        $key = 'rh_' . bin2hex(random_bytes(32));
        $hashedKey = hash('sha256', $key);

        $data = [
            'user_id' => $user->id,
            'name' => $name,
            'created_at' => now(),
            'expires_at' => $expiresAt,
            'last_used_at' => null,
        ];

        Cache::put("api_key:{$hashedKey}", $data, $expiresAt ?? now()->addYears(10));

        return [
            'key' => $key,
            'name' => $name,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Verify API key.
     */
    public function verifyApiKey(string $key): ?array
    {
        $hashedKey = hash('sha256', $key);
        $data = Cache::get("api_key:{$hashedKey}");

        if (!$data) {
            return null;
        }

        if ($data['expires_at'] && Carbon::parse($data['expires_at'])->isPast()) {
            Cache::forget("api_key:{$hashedKey}");
            return null;
        }

        // Update last used timestamp
        $data['last_used_at'] = now();
        Cache::put("api_key:{$hashedKey}", $data, $data['expires_at'] ?? now()->addYears(10));

        return $data;
    }
}
