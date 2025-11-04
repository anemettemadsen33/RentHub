<?php

namespace App\Services\Security;

use App\Models\OAuthAccessToken;
use App\Models\OAuthClient;
use App\Models\OAuthRefreshToken;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OAuth2Service
{
    private const ACCESS_TOKEN_LIFETIME = 3600; // 1 hour

    private const REFRESH_TOKEN_LIFETIME = 2592000; // 30 days

    private const AUTHORIZATION_CODE_LIFETIME = 600; // 10 minutes

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
                'created_at' => now(),
            ],
            self::AUTHORIZATION_CODE_LIFETIME
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
        $authData = cache()->get("oauth:auth_code:{$code}");

        if (! $authData) {
            throw new Exception('Invalid or expired authorization code');
        }

        $client = OAuthClient::where('client_id', $clientId)->first();

        if (! $client || ! Hash::check($clientSecret, $client->client_secret)) {
            throw new Exception('Invalid client credentials');
        }

        if ($client->redirect_uri !== $redirectUri) {
            throw new Exception('Invalid redirect URI');
        }

        cache()->forget("oauth:auth_code:{$code}");

        return $this->issueTokens(
            User::find($authData['user_id']),
            $client,
            $authData['scopes']
        );
    }

    /**
     * Issue access and refresh tokens
     */
    public function issueTokens(User $user, OAuthClient $client, array $scopes = []): array
    {
        $accessToken = $this->createAccessToken($user, $client, $scopes);
        $refreshToken = $this->createRefreshToken($user, $client, $scopes);

        return [
            'access_token' => $accessToken->token,
            'refresh_token' => $refreshToken->token,
            'token_type' => 'Bearer',
            'expires_in' => self::ACCESS_TOKEN_LIFETIME,
            'scope' => implode(' ', $scopes),
        ];
    }

    /**
     * Create access token
     */
    private function createAccessToken(User $user, OAuthClient $client, array $scopes): OAuthAccessToken
    {
        return OAuthAccessToken::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'token' => Str::random(64),
            'scopes' => $scopes,
            'expires_at' => Carbon::now()->addSeconds(self::ACCESS_TOKEN_LIFETIME),
        ]);
    }

    /**
     * Create refresh token
     */
    private function createRefreshToken(User $user, OAuthClient $client, array $scopes): OAuthRefreshToken
    {
        return OAuthRefreshToken::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'token' => Str::random(64),
            'scopes' => $scopes,
            'expires_at' => Carbon::now()->addSeconds(self::REFRESH_TOKEN_LIFETIME),
        ]);
    }

    /**
     * Refresh access token
     */
    public function refreshAccessToken(string $refreshToken, string $clientId, string $clientSecret): array
    {
        $token = OAuthRefreshToken::where('token', $refreshToken)
            ->where('expires_at', '>', now())
            ->first();

        if (! $token) {
            throw new Exception('Invalid or expired refresh token');
        }

        $client = OAuthClient::where('client_id', $clientId)->first();

        if (! $client || ! Hash::check($clientSecret, $client->client_secret)) {
            throw new Exception('Invalid client credentials');
        }

        if ($token->client_id !== $client->id) {
            throw new Exception('Token does not belong to this client');
        }

        // Revoke old access tokens
        OAuthAccessToken::where('user_id', $token->user_id)
            ->where('client_id', $client->id)
            ->delete();

        return $this->issueTokens($token->user, $client, $token->scopes);
    }

    /**
     * Revoke token
     */
    public function revokeToken(string $token): bool
    {
        OAuthAccessToken::where('token', $token)->delete();
        OAuthRefreshToken::where('token', $token)->delete();

        return true;
    }

    /**
     * Validate access token
     */
    public function validateAccessToken(string $token): ?OAuthAccessToken
    {
        return OAuthAccessToken::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Introspect token
     */
    public function introspectToken(string $token): array
    {
        $accessToken = $this->validateAccessToken($token);

        if (! $accessToken) {
            return ['active' => false];
        }

        return [
            'active' => true,
            'scope' => implode(' ', $accessToken->scopes),
            'client_id' => $accessToken->client->client_id,
            'user_id' => $accessToken->user_id,
            'exp' => $accessToken->expires_at->timestamp,
        ];
    }
}
