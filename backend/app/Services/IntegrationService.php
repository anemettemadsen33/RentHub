<?php

namespace App\Services;

use App\Models\Integration;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class IntegrationService
{
    protected $platformConfigs = [
        'airbnb' => [
            'name' => 'Airbnb',
            'oauth_url' => 'https://api.airbnb.com/v1/authorize',
            'token_url' => 'https://api.airbnb.com/v1/oauth/token',
            'api_url' => 'https://api.airbnb.com/v1',
            'client_id' => null, // Set from env
            'client_secret' => null, // Set from env
            'scope' => ['listings', 'calendar', 'messages'],
        ],
        'booking' => [
            'name' => 'Booking.com',
            'oauth_url' => 'https://supply-xml.booking.com/oauth/authorize',
            'token_url' => 'https://supply-xml.booking.com/oauth/token',
            'api_url' => 'https://supply-xml.booking.com/api',
            'client_id' => null, // Set from env
            'client_secret' => null, // Set from env
            'scope' => ['reservations', 'inventory', 'rates'],
        ],
        'vrbo' => [
            'name' => 'VRBO',
            'oauth_url' => 'https://api.vrbo.com/v1/authorize',
            'token_url' => 'https://api.vrbo.com/v1/oauth/token',
            'api_url' => 'https://api.vrbo.com/v1',
            'client_id' => null, // Set from env
            'client_secret' => null, // Set from env
            'scope' => ['listings', 'bookings', 'reviews'],
        ],
        'google_calendar' => [
            'name' => 'Google Calendar',
            'oauth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'api_url' => 'https://www.googleapis.com/calendar/v3',
            'client_id' => null, // Set from env
            'client_secret' => null, // Set from env
            'scope' => ['https://www.googleapis.com/auth/calendar'],
        ],
        'stripe' => [
            'name' => 'Stripe',
            'oauth_url' => 'https://connect.stripe.com/oauth/authorize',
            'token_url' => 'https://connect.stripe.com/oauth/token',
            'api_url' => 'https://api.stripe.com/v1',
            'client_id' => null, // Set from env
            'client_secret' => null, // Set from env
            'scope' => ['read_write'],
        ],
    ];

    public function __construct()
    {
        // Load platform configs from environment
        foreach ($this->platformConfigs as $platform => &$config) {
            $config['client_id'] = env(strtoupper($platform) . '_CLIENT_ID');
            $config['client_secret'] = env(strtoupper($platform) . '_CLIENT_SECRET');
        }
    }

    /**
     * Get OAuth URL for platform connection
     */
    public function getOAuthUrl(string $platform): string
    {
        if (!isset($this->platformConfigs[$platform])) {
            throw new \InvalidArgumentException("Unsupported platform: {$platform}");
        }

        $config = $this->platformConfigs[$platform];
        
        if (empty($config['client_id'])) {
            throw new \Exception("{$platform} client ID not configured");
        }

        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => route('api.integrations.callback', ['platform' => $platform]),
            'response_type' => 'code',
            'scope' => implode(' ', $config['scope']),
            'state' => $this->generateState(),
        ];

        return $config['oauth_url'] . '?' . http_build_query($params);
    }

    /**
     * Connect integration after OAuth callback
     */
    public function connectIntegration(User $user, string $platform, string $authCode): Integration
    {
        if (!isset($this->platformConfigs[$platform])) {
            throw new \InvalidArgumentException("Unsupported platform: {$platform}");
        }

        $config = $this->platformConfigs[$platform];
        
        // Exchange auth code for access token
        $tokenData = $this->exchangeAuthCode($platform, $authCode);
        
        // Create or update integration
        $integration = Integration::updateOrCreate(
            [
                'user_id' => $user->id,
                'type' => $platform,
            ],
            [
                'name' => $config['name'],
                'status' => 'connected',
                'credentials' => [
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'] ?? null,
                    'expires_at' => isset($tokenData['expires_in']) 
                        ? Carbon::now()->addSeconds($tokenData['expires_in'])->toIso8601String()
                        : null,
                    'scope' => $tokenData['scope'] ?? $config['scope'],
                ],
                'is_active' => true,
                'last_sync_at' => now(),
                'last_sync_status' => 'success',
            ]
        );

        // Initial sync
        $this->syncIntegration($integration);

        return $integration;
    }

    /**
     * Disconnect integration
     */
    public function disconnectIntegration(Integration $integration): void
    {
        try {
            // Revoke access token if possible
            $this->revokeAccessToken($integration);
            
            // Clear credentials and mark as disconnected
            $integration->clearCredentials();
            $integration->markAsDisconnected();
            
        } catch (\Exception $e) {
            Log::error('Failed to disconnect integration', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage()
            ]);
            
            // Still mark as disconnected even if revocation fails
            $integration->markAsDisconnected();
        }
    }

    /**
     * Sync integration data
     */
    public function syncIntegration(Integration $integration): array
    {
        try {
            $integration->markAsPending();
            
            $platform = $integration->type;
            $result = [
                'success' => true,
                'message' => 'Sync completed successfully',
                'properties_synced' => 0,
                'bookings_synced' => 0,
                'errors' => [],
            ];

            // Refresh token if needed
            $this->refreshTokenIfNeeded($integration);
            
            // Platform-specific sync logic
            switch ($platform) {
                case 'airbnb':
                    $result = $this->syncAirbnb($integration);
                    break;
                case 'booking':
                    $result = $this->syncBooking($integration);
                    break;
                case 'vrbo':
                    $result = $this->syncVrbo($integration);
                    break;
                case 'google_calendar':
                    $result = $this->syncGoogleCalendar($integration);
                    break;
                default:
                    throw new \Exception("Sync not implemented for platform: {$platform}");
            }
            
            if ($result['success']) {
                $integration->updateLastSync('success');
                $integration->markAsConnected();
            } else {
                $integration->markAsError($result['message']);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('Failed to sync integration', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage()
            ]);
            
            $integration->markAsError($e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'properties_synced' => 0,
                'bookings_synced' => 0,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Get sync history
     */
    public function getSyncHistory(Integration $integration): array
    {
        // This would typically come from a sync_history table
        // For now, return recent sync status
        return [
            [
                'timestamp' => $integration->last_sync_at?->toIso8601String(),
                'status' => $integration->last_sync_status,
                'errors' => $integration->sync_errors,
            ]
        ];
    }

    /**
     * Exchange auth code for access token
     */
    protected function exchangeAuthCode(string $platform, string $authCode): array
    {
        $config = $this->platformConfigs[$platform];
        
        $response = Http::asForm()->post($config['token_url'], [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'code' => $authCode,
            'grant_type' => 'authorization_code',
            'redirect_uri' => route('api.integrations.callback', ['platform' => $platform]),
        ]);

        if (!$response->successful()) {
            throw new \Exception("Failed to exchange auth code: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Refresh access token if needed
     */
    protected function refreshTokenIfNeeded(Integration $integration): void
    {
        $credentials = $integration->credentials ?? [];
        
        if (empty($credentials['refresh_token'])) {
            return;
        }

        $expiresAt = isset($credentials['expires_at']) ? Carbon::parse($credentials['expires_at']) : null;
        
        if ($expiresAt && $expiresAt->isFuture()) {
            return; // Token is still valid
        }

        try {
            $config = $this->platformConfigs[$integration->type];
            
            $response = Http::asForm()->post($config['token_url'], [
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'refresh_token' => $credentials['refresh_token'],
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                $tokenData = $response->json();
                
                $integration->setCredential('access_token', $tokenData['access_token']);
                
                if (isset($tokenData['refresh_token'])) {
                    $integration->setCredential('refresh_token', $tokenData['refresh_token']);
                }
                
                if (isset($tokenData['expires_in'])) {
                    $integration->setCredential('expires_at', 
                        Carbon::now()->addSeconds($tokenData['expires_in'])->toIso8601String()
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to refresh token', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Revoke access token
     */
    protected function revokeAccessToken(Integration $integration): void
    {
        // Implementation depends on platform
        // Some platforms support token revocation via API
    }

    /**
     * Sync Airbnb integration
     */
    protected function syncAirbnb(Integration $integration): array
    {
        // Mock implementation - replace with actual Airbnb API calls
        return [
            'success' => true,
            'message' => 'Airbnb sync completed',
            'properties_synced' => rand(1, 5),
            'bookings_synced' => rand(0, 10),
            'errors' => [],
        ];
    }

    /**
     * Sync Booking.com integration
     */
    protected function syncBooking(Integration $integration): array
    {
        // Mock implementation - replace with actual Booking.com API calls
        return [
            'success' => true,
            'message' => 'Booking.com sync completed',
            'properties_synced' => rand(1, 5),
            'bookings_synced' => rand(0, 10),
            'errors' => [],
        ];
    }

    /**
     * Sync VRBO integration
     */
    protected function syncVrbo(Integration $integration): array
    {
        // Mock implementation - replace with actual VRBO API calls
        return [
            'success' => true,
            'message' => 'VRBO sync completed',
            'properties_synced' => rand(1, 5),
            'bookings_synced' => rand(0, 10),
            'errors' => [],
        ];
    }

    /**
     * Sync Google Calendar integration
     */
    protected function syncGoogleCalendar(Integration $integration): array
    {
        // Mock implementation - replace with actual Google Calendar API calls
        return [
            'success' => true,
            'message' => 'Google Calendar sync completed',
            'properties_synced' => 0,
            'bookings_synced' => rand(0, 5),
            'errors' => [],
        ];
    }

    /**
     * Generate random state for OAuth
     */
    protected function generateState(): string
    {
        return bin2hex(random_bytes(16));
    }
}