<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\OAuthProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

class OAuthService
{
    protected array $providers = [
        'google' => [
            'authorize_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'user_url' => 'https://www.googleapis.com/oauth2/v2/userinfo',
        ],
        'facebook' => [
            'authorize_url' => 'https://www.facebook.com/v12.0/dialog/oauth',
            'token_url' => 'https://graph.facebook.com/v12.0/oauth/access_token',
            'user_url' => 'https://graph.facebook.com/me',
        ],
        'github' => [
            'authorize_url' => 'https://github.com/login/oauth/authorize',
            'token_url' => 'https://github.com/login/oauth/access_token',
            'user_url' => 'https://api.github.com/user',
        ],
    ];

    /**
     * Get authorization URL for OAuth provider
     */
    public function getAuthorizationUrl(string $provider, string $redirectUri): string
    {
        if (!isset($this->providers[$provider])) {
            throw new Exception("Provider {$provider} not supported");
        }

        $config = config("services.{$provider}");
        $state = Str::random(40);

        session(['oauth_state' => $state]);

        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $redirectUri,
            'scope' => $config['scope'] ?? 'email profile',
            'response_type' => 'code',
            'state' => $state,
        ];

        return $this->providers[$provider]['authorize_url'] . '?' . http_build_query($params);
    }

    /**
     * Handle OAuth callback and authenticate user
     */
    public function handleCallback(string $provider, string $code, string $state): User
    {
        // Verify state
        if ($state !== session('oauth_state')) {
            throw new Exception('Invalid OAuth state');
        }

        // Exchange code for access token
        $accessToken = $this->getAccessToken($provider, $code);

        // Get user info from provider
        $providerUser = $this->getUserFromProvider($provider, $accessToken);

        // Find or create user
        return $this->findOrCreateUser($provider, $providerUser, $accessToken);
    }

    /**
     * Get access token from provider
     */
    protected function getAccessToken(string $provider, string $code): string
    {
        $config = config("services.{$provider}");
        
        $response = Http::asForm()->post($this->providers[$provider]['token_url'], [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'code' => $code,
            'redirect_uri' => $config['redirect_uri'],
            'grant_type' => 'authorization_code',
        ]);

        if (!$response->successful()) {
            throw new Exception('Failed to obtain access token');
        }

        return $response->json('access_token');
    }

    /**
     * Get user information from provider
     */
    protected function getUserFromProvider(string $provider, string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->get($this->providers[$provider]['user_url']);

        if (!$response->successful()) {
            throw new Exception('Failed to get user information');
        }

        return $this->normalizeUserData($provider, $response->json());
    }

    /**
     * Normalize user data from different providers
     */
    protected function normalizeUserData(string $provider, array $data): array
    {
        return match($provider) {
            'google' => [
                'provider_id' => $data['id'],
                'email' => $data['email'],
                'name' => $data['name'],
                'avatar' => $data['picture'] ?? null,
                'email_verified' => $data['email_verified'] ?? false,
            ],
            'facebook' => [
                'provider_id' => $data['id'],
                'email' => $data['email'] ?? null,
                'name' => $data['name'],
                'avatar' => $data['picture']['data']['url'] ?? null,
                'email_verified' => false,
            ],
            'github' => [
                'provider_id' => $data['id'],
                'email' => $data['email'],
                'name' => $data['name'] ?? $data['login'],
                'avatar' => $data['avatar_url'] ?? null,
                'email_verified' => true,
            ],
            default => throw new Exception("Unknown provider: {$provider}"),
        };
    }

    /**
     * Find or create user from OAuth data
     */
    protected function findOrCreateUser(string $provider, array $providerUser, string $accessToken): User
    {
        // Check if OAuth provider exists
        $oauthProvider = OAuthProvider::where('provider', $provider)
            ->where('provider_id', $providerUser['provider_id'])
            ->first();

        if ($oauthProvider) {
            // Update access token
            $oauthProvider->update([
                'access_token' => $accessToken,
                'last_login_at' => now(),
            ]);
            
            return $oauthProvider->user;
        }

        // Check if user exists by email
        $user = User::where('email', $providerUser['email'])->first();

        if (!$user) {
            // Create new user
            $user = User::create([
                'name' => $providerUser['name'],
                'email' => $providerUser['email'],
                'email_verified_at' => $providerUser['email_verified'] ? now() : null,
                'avatar' => $providerUser['avatar'],
                'password' => bcrypt(Str::random(32)), // Random password
            ]);
        }

        // Create OAuth provider record
        OAuthProvider::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $providerUser['provider_id'],
            'access_token' => $accessToken,
            'last_login_at' => now(),
        ]);

        return $user;
    }

    /**
     * Revoke OAuth provider
     */
    public function revokeProvider(User $user, string $provider): bool
    {
        $oauthProvider = OAuthProvider::where('user_id', $user->id)
            ->where('provider', $provider)
            ->first();

        if (!$oauthProvider) {
            return false;
        }

        // Check if user has password (can still login)
        if (!$user->password && OAuthProvider::where('user_id', $user->id)->count() === 1) {
            throw new Exception('Cannot revoke last authentication method');
        }

        return $oauthProvider->delete();
    }
}
