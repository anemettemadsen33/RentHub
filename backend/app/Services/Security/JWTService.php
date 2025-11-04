<?php

namespace App\Services\Security;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService
{
    private string $secretKey;

    private string $algorithm = 'HS256';

    private int $accessTokenLifetime = 3600; // 1 hour

    private int $refreshTokenLifetime = 2592000; // 30 days

    public function __construct()
    {
        $this->secretKey = config('app.key');
    }

    /**
     * Generate JWT token
     */
    public function generateToken(User $user, bool $remember = false): array
    {
        $accessToken = $this->createAccessToken($user);
        $refreshToken = $this->createRefreshToken($user);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => $this->accessTokenLifetime,
        ];
    }

    /**
     * Create access token
     */
    private function createAccessToken(User $user): string
    {
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => Carbon::now()->timestamp,
            'exp' => Carbon::now()->addSeconds($this->accessTokenLifetime)->timestamp,
            'nbf' => Carbon::now()->timestamp,
            'jti' => uniqid(),
            'type' => 'access',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    /**
     * Create refresh token
     */
    private function createRefreshToken(User $user): string
    {
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => Carbon::now()->timestamp,
            'exp' => Carbon::now()->addSeconds($this->refreshTokenLifetime)->timestamp,
            'jti' => uniqid(),
            'type' => 'refresh',
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    /**
     * Verify and decode token
     */
    public function verifyToken(string $token): object
    {
        try {
            return JWT::decode($token, new Key($this->secretKey, $this->algorithm));
        } catch (Exception $e) {
            throw new Exception('Invalid token: '.$e->getMessage());
        }
    }

    /**
     * Refresh access token
     */
    public function refreshToken(string $refreshToken): array
    {
        $decoded = $this->verifyToken($refreshToken);

        if ($decoded->type !== 'refresh') {
            throw new Exception('Invalid token type');
        }

        $user = User::find($decoded->sub);

        if (! $user) {
            throw new Exception('User not found');
        }

        return $this->generateToken($user);
    }

    /**
     * Invalidate token (add to blacklist)
     */
    public function invalidateToken(string $token): bool
    {
        $decoded = $this->verifyToken($token);
        $expiresIn = $decoded->exp - time();

        if ($expiresIn > 0) {
            cache()->put(
                "jwt:blacklist:{$decoded->jti}",
                true,
                $expiresIn
            );
        }

        return true;
    }

    /**
     * Check if token is blacklisted
     */
    public function isBlacklisted(string $token): bool
    {
        try {
            $decoded = $this->verifyToken($token);

            return cache()->has("jwt:blacklist:{$decoded->jti}");
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * Get user from token
     */
    public function getUserFromToken(string $token): ?User
    {
        try {
            $decoded = $this->verifyToken($token);

            if ($this->isBlacklisted($token)) {
                return null;
            }

            return User::find($decoded->sub);
        } catch (Exception $e) {
            return null;
        }
    }
}
