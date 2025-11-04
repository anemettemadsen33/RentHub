<?php

namespace App\Services;

use App\Models\GoogleCalendarToken;
use App\Models\Property;
use App\Models\User;
use App\Models\Booking;
use App\Models\BlockedDate;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleCalendarService
{
    private $client = null;
    private $service = null;

    public function __construct()
    {
        // Don't initialize Google Client in constructor to avoid autoload dependency
        // Initialize lazily when needed
    }
    
    private function initializeClient()
    {
        if ($this->client !== null) {
            return;
        }
        
        try {
            if (!class_exists('Google\Client')) {
                throw new Exception('Google API Client not installed');
            }
            
            $this->client = new \Google\Client();
            $this->client->setApplicationName(config('app.name'));
            $this->client->setScopes([
                \Google\Service\Calendar::CALENDAR,
                \Google\Service\Calendar::CALENDAR_EVENTS,
            ]);
            $this->client->setClientId(config('services.google.calendar_client_id'));
            $this->client->setClientSecret(config('services.google.calendar_client_secret'));
            $this->client->setRedirectUri(config('services.google.calendar_redirect_uri'));
            $this->client->setAccessType('offline');
            $this->client->setPrompt('consent');
        } catch (\Exception $e) {
            Log::error('Failed to initialize Google Calendar Service: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get authorization URL for OAuth
     */
    public function getAuthorizationUrl(User $user, ?Property $property = null): string
    {
        $this->initializeClient();

        $state = base64_encode(json_encode([
            'user_id' => $user->id,
            'property_id' => $property?->id,
            'timestamp' => now()->timestamp,
        ]));

        $this->client->setState($state);
        
        return $this->client->createAuthUrl();
    }

    /**
     * Handle OAuth callback and store tokens
     */
    public function handleCallback(string $code, array $state): GoogleCalendarToken
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new Exception("OAuth error: " . $token['error']);
        }

        $userId = $state['user_id'];
        $propertyId = $state['property_id'] ?? null;

        // Get calendar info
        $this->client->setAccessToken($token);
        $this->service = new GoogleCalendar($this->client);
        
        $calendarList = $this->service->calendarList->get('primary');

        // Store or update token
        $googleToken = GoogleCalendarToken::updateOrCreate(
            [
                'user_id' => $userId,
                'property_id' => $propertyId,
                'calendar_id' => $calendarList->getId(),
            ],
            [
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'] ?? null,
                'token_type' => $token['token_type'] ?? 'Bearer',
                'expires_at' => now()->addSeconds($token['expires_in']),
                'calendar_name' => $calendarList->getSummary(),
                'sync_enabled' => true,
            ]
        );

        // Setup webhook
        $this->setupWebhook($googleToken);

        return $googleToken;
    }

    /**
     * Refresh access token if expired
     */
    public function refreshToken(GoogleCalendarToken $googleToken): void
    {
        if (!$googleToken->isTokenExpired()) {
            return;
        }

        $this->client->setAccessToken([
            'access_token' => $googleToken->access_token,
            'refresh_token' => $googleToken->refresh_token,
            'expires_in' => $googleToken->expires_at->diffInSeconds(now()),
        ]);

        if ($this->client->isAccessTokenExpired()) {
            $token = $this->client->fetchAccessTokenWithRefreshToken($googleToken->refresh_token);

            if (isset($token['error'])) {
                throw new Exception("Token refresh error: " . $token['error']);
            }

            $googleToken->update([
                'access_token' => $token['access_token'],
                'expires_at' => now()->addSeconds($token['expires_in']),
            ]);
        }
    }

    /**
     * Get authenticated Google Calendar service
     */
    private function getService(GoogleCalendarToken $googleToken): GoogleCalendar
    {
        $this->refreshToken($googleToken);

        $this->client->setAccessToken([
            'access_token' => $googleToken->access_token,
            'refresh_token' => $googleToken->refresh_token,
            'expires_in' => $googleToken->expires_at->diffInSeconds(now()),
        ]);

        return new GoogleCalendar($this->client);
    }

    /**
     * Setup webhook for push notifications
     */
    public function setupWebhook(GoogleCalendarToken $googleToken): void
    {
        try {
            $service = $this->getService($googleToken);
            
            $webhook = new \Google\Service\Calendar\Channel();
            $webhook->setId(uniqid('renthub_', true));
            $webhook->setType('web_hook');
            $webhook->setAddress(route('api.google-calendar.webhook'));
            $webhook->setExpiration((now()->addDays(7)->timestamp) * 1000); // 7 days in milliseconds

            $response = $service->events->watch($googleToken->calendar_id, $webhook);

            $googleToken->update([
                'webhook_id' => $response->getId(),
                'webhook_resource_id' => $response->getResourceId(),
                'webhook_expiration' => now()->addDays(7),
            ]);

            Log::info('Google Calendar webhook setup successful', [
                'token_id' => $googleToken->id,
                'webhook_id' => $response->getId(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to setup Google Calendar webhook', [
                'token_id' => $googleToken->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Stop webhook
     */
    public function stopWebhook(GoogleCalendarToken $googleToken): void
    {
        if (!$googleToken->webhook_id || !$googleToken->webhook_resource_id) {
            return;
        }

        try {
            $service = $this->getService($googleToken);
            
            $channel = new \Google\Service\Calendar\Channel();
            $channel->setId($googleToken->webhook_id);
            $channel->setResourceId($googleToken->webhook_resource_id);

            $service->channels->stop($channel);

            $googleToken->update([
                'webhook_id' => null,
                'webhook_resource_id' => null,
                'webhook_expiration' => null,
            ]);

            Log::info('Google Calendar webhook stopped', [
                'token_id' => $googleToken->id,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to stop Google Calendar webhook', [
                'token_id' => $googleToken->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Sync booking to Google Calendar
     */
    public function syncBookingToGoogle(Booking $booking, GoogleCalendarToken $googleToken): void
    {
        try {
            $service = $this->getService($googleToken);

            $event = new GoogleCalendarEvent([
                'summary' => "Booking #{$booking->id} - {$booking->property->title}",
                'description' => "Tenant: {$booking->tenant->name}\nEmail: {$booking->tenant->email}\nPhone: {$booking->tenant->phone}",
                'start' => new EventDateTime([
                    'date' => $booking->check_in->format('Y-m-d'),
                    'timeZone' => config('app.timezone'),
                ]),
                'end' => new EventDateTime([
                    'date' => $booking->check_out->format('Y-m-d'),
                    'timeZone' => config('app.timezone'),
                ]),
                'colorId' => '11', // Red color for bookings
                'extendedProperties' => [
                    'private' => [
                        'renthub_booking_id' => $booking->id,
                        'renthub_property_id' => $booking->property_id,
                    ],
                ],
            ]);

            if ($booking->google_event_id) {
                // Update existing event
                $service->events->update($googleToken->calendar_id, $booking->google_event_id, $event);
            } else {
                // Create new event
                $createdEvent = $service->events->insert($googleToken->calendar_id, $event);
                $booking->update(['google_event_id' => $createdEvent->getId()]);
            }

            $googleToken->markSyncSuccess();
            
            Log::info('Booking synced to Google Calendar', [
                'booking_id' => $booking->id,
                'event_id' => $booking->google_event_id,
            ]);
        } catch (Exception $e) {
            $googleToken->markSyncFailure($e->getMessage());
            Log::error('Failed to sync booking to Google Calendar', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete booking from Google Calendar
     */
    public function deleteBookingFromGoogle(Booking $booking, GoogleCalendarToken $googleToken): void
    {
        if (!$booking->google_event_id) {
            return;
        }

        try {
            $service = $this->getService($googleToken);
            $service->events->delete($googleToken->calendar_id, $booking->google_event_id);

            $booking->update(['google_event_id' => null]);

            Log::info('Booking deleted from Google Calendar', [
                'booking_id' => $booking->id,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete booking from Google Calendar', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Sync blocked dates to Google Calendar
     */
    public function syncBlockedDateToGoogle(BlockedDate $blockedDate, GoogleCalendarToken $googleToken): void
    {
        try {
            $service = $this->getService($googleToken);

            $event = new GoogleCalendarEvent([
                'summary' => "Blocked - {$blockedDate->property->title}",
                'description' => "Reason: {$blockedDate->reason}",
                'start' => new EventDateTime([
                    'date' => $blockedDate->start_date->format('Y-m-d'),
                    'timeZone' => config('app.timezone'),
                ]),
                'end' => new EventDateTime([
                    'date' => $blockedDate->end_date->format('Y-m-d'),
                    'timeZone' => config('app.timezone'),
                ]),
                'colorId' => '8', // Gray color for blocked dates
                'extendedProperties' => [
                    'private' => [
                        'renthub_blocked_date_id' => $blockedDate->id,
                        'renthub_property_id' => $blockedDate->property_id,
                    ],
                ],
            ]);

            if ($blockedDate->google_event_id) {
                $service->events->update($googleToken->calendar_id, $blockedDate->google_event_id, $event);
            } else {
                $createdEvent = $service->events->insert($googleToken->calendar_id, $event);
                $blockedDate->update(['google_event_id' => $createdEvent->getId()]);
            }

            $googleToken->markSyncSuccess();
            
            Log::info('Blocked date synced to Google Calendar', [
                'blocked_date_id' => $blockedDate->id,
                'event_id' => $blockedDate->google_event_id,
            ]);
        } catch (Exception $e) {
            $googleToken->markSyncFailure($e->getMessage());
            Log::error('Failed to sync blocked date to Google Calendar', [
                'blocked_date_id' => $blockedDate->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Import events from Google Calendar
     */
    public function importFromGoogle(GoogleCalendarToken $googleToken, Property $property): array
    {
        try {
            $service = $this->getService($googleToken);
            
            $optParams = [
                'maxResults' => 100,
                'orderBy' => 'startTime',
                'singleEvents' => true,
                'timeMin' => now()->toRfc3339String(),
            ];

            $results = $service->events->listEvents($googleToken->calendar_id, $optParams);
            $events = $results->getItems();

            $imported = [];

            foreach ($events as $event) {
                // Skip events that were created by our app
                $extendedProps = $event->getExtendedProperties();
                if ($extendedProps && isset($extendedProps['private']['renthub_booking_id'])) {
                    continue;
                }

                $start = $event->getStart()->getDate() ?? $event->getStart()->getDateTime();
                $end = $event->getEnd()->getDate() ?? $event->getEnd()->getDateTime();

                // Create blocked date for external events
                $blockedDate = BlockedDate::create([
                    'property_id' => $property->id,
                    'start_date' => $start,
                    'end_date' => $end,
                    'reason' => 'Imported from Google Calendar: ' . $event->getSummary(),
                    'google_event_id' => $event->getId(),
                ]);

                $imported[] = $blockedDate;
            }

            $googleToken->markSyncSuccess();

            Log::info('Events imported from Google Calendar', [
                'token_id' => $googleToken->id,
                'count' => count($imported),
            ]);

            return $imported;
        } catch (Exception $e) {
            $googleToken->markSyncFailure($e->getMessage());
            Log::error('Failed to import from Google Calendar', [
                'token_id' => $googleToken->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle webhook notification
     */
    public function handleWebhook(string $channelId, string $resourceId): void
    {
        $googleToken = GoogleCalendarToken::where('webhook_id', $channelId)
            ->where('webhook_resource_id', $resourceId)
            ->where('sync_enabled', true)
            ->first();

        if (!$googleToken) {
            Log::warning('Webhook received for unknown channel', [
                'channel_id' => $channelId,
                'resource_id' => $resourceId,
            ]);
            return;
        }

        try {
            // Import new/updated events
            if ($googleToken->property) {
                $this->importFromGoogle($googleToken, $googleToken->property);
            }

            Log::info('Webhook processed successfully', [
                'token_id' => $googleToken->id,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to process webhook', [
                'token_id' => $googleToken->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Disconnect Google Calendar
     */
    public function disconnect(GoogleCalendarToken $googleToken): void
    {
        $this->stopWebhook($googleToken);
        
        try {
            $this->client->revokeToken($googleToken->access_token);
        } catch (Exception $e) {
            Log::error('Failed to revoke Google Calendar token', [
                'token_id' => $googleToken->id,
                'error' => $e->getMessage(),
            ]);
        }

        $googleToken->delete();
    }
}
