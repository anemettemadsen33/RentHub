<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\RefreshToken;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class JWTService
{
    protected string $secret;
    protected string $algorithm = 'HS256';
    protected int $accessTokenTtl = 900; // 15 minutes
    protected int $refreshTokenTtl = 2592000; // 30 days

    public function __construct()
    {
        $this->secret = config('app.jwt_secret', config('app.key'));
        $this->accessTokenTtl = config('auth.jwt.access_token_ttl', 900);
        $this->refreshTokenTtl = config('auth.jwt.refresh_token_ttl', 2592000);
    }

    /**
     * Create access token for user
     */
    public function createAccessToken(User $user, array $claims = []): string
    {
        $now = Carbon::now();
        
        $payload = array_merge([
            'iss' => config('app.url'), // Issuer
            'sub' => $user->id, // Subject (user ID)
            'iat' => $now->timestamp, // Issued at
            'exp' => $now->addSeconds($this->accessTokenTtl)->timestamp, // Expiration
            'jti' => Str::uuid()->toString(), // JWT ID
            'type' => 'access',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], $claims);

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    /**
     * Create refresh token for user
     */
    public function createRefreshToken(User $user, ?string $deviceId = null): string
    {
        $token = Str::random(64);
        
        RefreshToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $token),
            'device_id' => $deviceId,
            'expires_at' => now()->addSeconds($this->refreshTokenTtl),
            'last_used_at' => now(),
        ]);

        return $token;
    }

    /**
     * Create token pair (access + refresh)
     */
    public function createTokenPair(User $user, ?string $deviceId = null): array
    {
        return [
            'access_token' => $this->createAccessToken($user),
            'refresh_token' => $this->createRefreshToken($user, $deviceId),
            'token_type' => 'Bearer',
            'expires_in' => $this->accessTokenTtl,
        ];
    }

    /**
     * Validate and decode access token
     */
    public function validateAccessToken(string $token): object
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            
            // Verify token type
            if (($decoded->type ?? null) !== 'access') {
                throw new Exception('Invalid token type');
            }

            return $decoded;
        } catch (Exception $e) {
            throw new Exception('Invalid or expired token: ' . $e->getMessage());
        }
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $hashedToken = hash('sha256', $refreshToken);
        
        $tokenRecord = RefreshToken::where('token', $hashedToken)
            ->where('expires_at', '>', now())
            ->where('revoked', false)
            ->first();

        if (!$tokenRecord) {
            throw new Exception('Invalid or expired refresh token');
        }

        // Update last used
        $tokenRecord->update(['last_used_at' => now()]);

        // Get user
        $user = User::findOrFail($tokenRecord->user_id);

        // Create new access token
        return [
            'access_token' => $this->createAccessToken($user),
            'token_type' => 'Bearer',
            'expires_in' => $this->accessTokenTtl,
        ];
    }

    /**
     * Revoke refresh token
     */
    public function revokeRefreshToken(string $refreshToken): bool
    {
        $hashedToken = hash('sha256', $refreshToken);
        
        return RefreshToken::where('token', $hashedToken)
            ->update(['revoked' => true]) > 0;
    }

    /**
     * Revoke all user's refresh tokens
     */
    public function revokeAllUserTokens(int $userId): int
    {
        return RefreshToken::where('user_id', $userId)
            ->update(['revoked' => true]);
    }

    /**
     * Revoke all user's tokens except current
     */
    public function revokeOtherTokens(int $userId, string $currentToken): int
    {
        $hashedToken = hash('sha256', $currentToken);
        
        return RefreshToken::where('user_id', $userId)
            ->where('token', '!=', $hashedToken)
            ->update(['revoked' => true]);
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens(): int
    {
        return RefreshToken::where('expires_at', '<', now())
            ->orWhere('revoked', true)
            ->delete();
    }

    /**
     * Get user from token
     */
    public function getUserFromToken(string $token): ?User
    {
        try {
            $decoded = $this->validateAccessToken($token);
            return User::find($decoded->sub);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Create token for password reset
     */
    public function createPasswordResetToken(User $user): string
    {
        $now = Carbon::now();
        
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => $now->timestamp,
            'exp' => $now->addHour()->timestamp, // 1 hour expiry
            'jti' => Str::uuid()->toString(),
            'type' => 'password_reset',
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    /**
     * Validate password reset token
     */
    public function validatePasswordResetToken(string $token): object
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            
            if (($decoded->type ?? null) !== 'password_reset') {
                throw new Exception('Invalid token type');
            }

            return $decoded;
        } catch (Exception $e) {
            throw new Exception('Invalid or expired password reset token');
        }
    }
}
