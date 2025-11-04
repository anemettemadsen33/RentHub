<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Carbon\Carbon;

class JWTService
{
    private string $secretKey;
    private string $algorithm = 'HS256';
    private int $accessTokenTTL = 3600; // 1 hour
    private int $refreshTokenTTL = 2592000; // 30 days
    
    public function __construct()
    {
        $this->secretKey = config('app.jwt_secret') ?? config('app.key');
    }
    
    /**
     * Generate JWT access token
     */
    public function generateAccessToken(User $user, array $claims = []): string
    {
        $payload = array_merge([
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + $this->accessTokenTTL,
            'nbf' => time(),
            'jti' => uniqid('', true),
            'email' => $user->email,
            'roles' => $user->roles->pluck('name')->toArray(),
            'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        ], $claims);
        
        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }
    
    /**
     * Generate JWT refresh token
     */
    public function generateRefreshToken(User $user): string
    {
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + $this->refreshTokenTTL,
            'type' => 'refresh',
            'jti' => uniqid('', true),
        ];
        
        $token = JWT::encode($payload, $this->secretKey, $this->algorithm);
        
        // Store refresh token in database for revocation capability
        $user->refreshTokens()->create([
            'token' => hash('sha256', $token),
            'expires_at' => Carbon::now()->addSeconds($this->refreshTokenTTL),
        ]);
        
        return $token;
    }
    
    /**
     * Validate and decode JWT token
     */
    public function validateToken(string $token): ?object
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            
            // Check if token is blacklisted
            if ($this->isTokenBlacklisted($token)) {
                return null;
            }
            
            return $decoded;
        } catch (\Exception $e) {
            \Log::warning('JWT validation failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Refresh access token using refresh token
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $decoded = $this->validateToken($refreshToken);
        
        if (!$decoded || ($decoded->type ?? null) !== 'refresh') {
            throw new \Exception('Invalid refresh token');
        }
        
        $user = User::find($decoded->sub);
        
        if (!$user) {
            throw new \Exception('User not found');
        }
        
        // Verify refresh token exists in database
        $hashedToken = hash('sha256', $refreshToken);
        $tokenRecord = $user->refreshTokens()
            ->where('token', $hashedToken)
            ->where('expires_at', '>', now())
            ->first();
            
        if (!$tokenRecord) {
            throw new \Exception('Refresh token not found or expired');
        }
        
        // Generate new tokens
        $newAccessToken = $this->generateAccessToken($user);
        $newRefreshToken = $this->generateRefreshToken($user);
        
        // Revoke old refresh token
        $tokenRecord->delete();
        
        return [
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'token_type' => 'Bearer',
            'expires_in' => $this->accessTokenTTL,
        ];
    }
    
    /**
     * Revoke token (add to blacklist)
     */
    public function revokeToken(string $token): bool
    {
        $decoded = $this->validateToken($token);
        
        if (!$decoded) {
            return false;
        }
        
        $exp = $decoded->exp ?? time();
        $ttl = max(0, $exp - time());
        
        cache()->put(
            "jwt:blacklist:" . hash('sha256', $token),
            true,
            $ttl
        );
        
        return true;
    }
    
    /**
     * Check if token is blacklisted
     */
    private function isTokenBlacklisted(string $token): bool
    {
        return cache()->has("jwt:blacklist:" . hash('sha256', $token));
    }
    
    /**
     * Get user from token
     */
    public function getUserFromToken(string $token): ?User
    {
        $decoded = $this->validateToken($token);
        
        if (!$decoded) {
            return null;
        }
        
        return User::find($decoded->sub);
    }
    
    /**
     * Verify token signature
     */
    public function verifySignature(string $token): bool
    {
        try {
            JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
