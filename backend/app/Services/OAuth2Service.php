<?php

namespace App\Services;

use App\Models\User;
use App\Models\OAuthClient;
use App\Models\OAuthAccessToken;
use App\Models\OAuthRefreshToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class OAuth2Service
{
    /**
     * Generate authorization code
     */
    public function generateAuthorizationCode(User $user, OAuthClient $client, array $scopes = []): string
    {
        $code = Str::random(64);
        
        cache()->put(
            "oauth:auth_code:{$code}",
            [
                'user_id' => $user->id,
                'client_id' => $client->id,
                'scopes' => $scopes,
                'redirect_uri' => request()->input('redirect_uri'),
                'expires_at' => now()->addMinutes(10),
            ],
            600
        );
        
        return $code;
    }
    
    /**
     * Exchange authorization code for access token
     */
    public function exchangeAuthorizationCode(
        string $code,
        string $clientId,
        string $clientSecret,
        string $redirectUri
    ): array {
        $data = cache()->get("oauth:auth_code:{$code}");
        
        if (!$data || $data['expires_at']->isPast()) {
            throw new \Exception('Invalid or expired authorization code');
        }
        
        $client = OAuthClient::where('client_id', $clientId)->first();
        
        if (!$client || !Hash::check($clientSecret, $client->client_secret)) {
            throw new \Exception('Invalid client credentials');
        }
        
        if ($data['redirect_uri'] !== $redirectUri) {
            throw new \Exception('Redirect URI mismatch');
        }
        
        cache()->forget("oauth:auth_code:{$code}");
        
        return $this->issueTokens($data['user_id'], $client, $data['scopes']);
    }
    
    /**
     * Issue access and refresh tokens
     */
    public function issueTokens(int $userId, OAuthClient $client, array $scopes = []): array
    {
        $accessToken = Str::random(80);
        $refreshToken = Str::random(80);
        
        OAuthAccessToken::create([
            'user_id' => $userId,
            'client_id' => $client->id,
            'token' => hash('sha256', $accessToken),
            'scopes' => $scopes,
            'expires_at' => now()->addHours(1),
        ]);
        
        OAuthRefreshToken::create([
            'user_id' => $userId,
            'client_id' => $client->id,
            'token' => hash('sha256', $refreshToken),
            'expires_at' => now()->addDays(30),
        ]);
        
        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => implode(' ', $scopes),
        ];
    }
    
    /**
     * Refresh access token
     */
    public function refreshAccessToken(string $refreshToken, string $clientId, string $clientSecret): array
    {
        $client = OAuthClient::where('client_id', $clientId)->first();
        
        if (!$client || !Hash::check($clientSecret, $client->client_secret)) {
            throw new \Exception('Invalid client credentials');
        }
        
        $token = OAuthRefreshToken::where('token', hash('sha256', $refreshToken))
            ->where('client_id', $client->id)
            ->where('expires_at', '>', now())
            ->first();
            
        if (!$token) {
            throw new \Exception('Invalid or expired refresh token');
        }
        
        // Revoke old access tokens
        OAuthAccessToken::where('user_id', $token->user_id)
            ->where('client_id', $client->id)
            ->delete();
        
        return $this->issueTokens($token->user_id, $client, $token->scopes ?? []);
    }
    
    /**
     * Validate access token
     */
    public function validateAccessToken(string $token): ?array
    {
        $accessToken = OAuthAccessToken::where('token', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->first();
            
        if (!$accessToken) {
            return null;
        }
        
        return [
            'user_id' => $accessToken->user_id,
            'client_id' => $accessToken->client_id,
            'scopes' => $accessToken->scopes ?? [],
        ];
    }
    
    /**
     * Revoke token
     */
    public function revokeToken(string $token): bool
    {
        $hashedToken = hash('sha256', $token);
        
        $deleted = OAuthAccessToken::where('token', $hashedToken)->delete();
        $deleted += OAuthRefreshToken::where('token', $hashedToken)->delete();
        
        return $deleted > 0;
    }
    
    /**
     * Introspect token
     */
    public function introspectToken(string $token): array
    {
        $hashedToken = hash('sha256', $token);
        
        $accessToken = OAuthAccessToken::where('token', $hashedToken)->first();
        
        if (!$accessToken) {
            return ['active' => false];
        }
        
        $active = $accessToken->expires_at->isFuture();
        
        return [
            'active' => $active,
            'scope' => implode(' ', $accessToken->scopes ?? []),
            'client_id' => $accessToken->client->client_id ?? null,
            'username' => $accessToken->user->email ?? null,
            'exp' => $accessToken->expires_at->timestamp,
        ];
    }
}
