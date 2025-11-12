<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GoogleCalendarToken;
use App\Models\Property;
use App\Services\GoogleCalendarService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleCalendarController extends Controller
{
    public function __construct(
        private GoogleCalendarService $googleCalendarService
    ) {}

    /**
     * Get authorization URL
     */
    public function getAuthUrl(Request $request): JsonResponse
    {
        $request->validate([
            'property_id' => 'nullable|exists:properties,id',
        ]);

        $user = $request->user();
        $property = $request->property_id
            ? Property::findOrFail($request->property_id)
            : null;

        // Check authorization
        if ($property && $property->owner_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized to connect this property',
            ], 403);
        }

        try {
            $authUrl = $this->googleCalendarService->getAuthorizationUrl($user, $property);

            return response()->json([
                'authorization_url' => $authUrl,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to generate Google Calendar auth URL', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return response()->json([
                'message' => 'Failed to generate authorization URL',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle OAuth callback
     */
    public function callback(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'state' => 'required|string',
        ]);

        try {
            $state = json_decode(base64_decode($request->state), true);

            if (! $state || ! isset($state['user_id'])) {
                return response()->json([
                    'message' => 'Invalid state parameter',
                ], 400);
            }

            $googleToken = $this->googleCalendarService->handleCallback(
                $request->code,
                $state
            );

            return response()->json([
                'message' => 'Google Calendar connected successfully',
                'data' => [
                    'id' => $googleToken->id,
                    'calendar_id' => $googleToken->calendar_id,
                    'calendar_name' => $googleToken->calendar_name,
                    'sync_enabled' => $googleToken->sync_enabled,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to handle Google Calendar callback', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to connect Google Calendar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get connected calendars
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $calendars = GoogleCalendarToken::where('user_id', $user->id)
            ->with('property:id,title')
            ->get()
            ->map(fn ($token) => [
                'id' => $token->id,
                'calendar_id' => $token->calendar_id,
                'calendar_name' => $token->calendar_name,
                'property' => $token->property ? [
                    'id' => $token->property->id,
                    'title' => $token->property->title,
                ] : null,
                'sync_enabled' => $token->sync_enabled,
                'last_sync_at' => $token->last_sync_at?->toISOString(),
                'webhook_expires_at' => $token->webhook_expiration?->toISOString(),
                'has_errors' => ! empty($token->sync_errors),
            ]);

        return response()->json([
            'data' => $calendars,
        ]);
    }

    /**
     * Get specific calendar details
     */
    public function show(Request $request, GoogleCalendarToken $googleCalendarToken): JsonResponse
    {
        if ($googleCalendarToken->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'data' => [
                'id' => $googleCalendarToken->id,
                'calendar_id' => $googleCalendarToken->calendar_id,
                'calendar_name' => $googleCalendarToken->calendar_name,
                'property' => $googleCalendarToken->property ? [
                    'id' => $googleCalendarToken->property->id,
                    'title' => $googleCalendarToken->property->title,
                ] : null,
                'sync_enabled' => $googleCalendarToken->sync_enabled,
                'last_sync_at' => $googleCalendarToken->last_sync_at?->toISOString(),
                'webhook_expires_at' => $googleCalendarToken->webhook_expiration?->toISOString(),
                'sync_errors' => $googleCalendarToken->sync_errors,
            ],
        ]);
    }

    /**
     * Toggle sync
     */
    public function toggleSync(Request $request, GoogleCalendarToken $googleCalendarToken): JsonResponse
    {
        if ($googleCalendarToken->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $googleCalendarToken->update([
            'sync_enabled' => ! $googleCalendarToken->sync_enabled,
        ]);

        return response()->json([
            'message' => 'Sync '.($googleCalendarToken->sync_enabled ? 'enabled' : 'disabled'),
            'data' => [
                'sync_enabled' => $googleCalendarToken->sync_enabled,
            ],
        ]);
    }

    /**
     * Import events from Google Calendar
     */
    public function import(Request $request, GoogleCalendarToken $googleCalendarToken): JsonResponse
    {
        if ($googleCalendarToken->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if (! $googleCalendarToken->property) {
            return response()->json([
                'message' => 'No property associated with this calendar',
            ], 400);
        }

        try {
            $imported = $this->googleCalendarService->importFromGoogle(
                $googleCalendarToken,
                $googleCalendarToken->property
            );

            return response()->json([
                'message' => 'Events imported successfully',
                'data' => [
                    'imported_count' => count($imported),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to import from Google Calendar', [
                'token_id' => $googleCalendarToken->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to import events',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh webhook
     */
    public function refreshWebhook(Request $request, GoogleCalendarToken $googleCalendarToken): JsonResponse
    {
        if ($googleCalendarToken->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            // Stop old webhook
            if ($googleCalendarToken->webhook_id) {
                $this->googleCalendarService->stopWebhook($googleCalendarToken);
            }

            // Setup new webhook
            $this->googleCalendarService->setupWebhook($googleCalendarToken);

            return response()->json([
                'message' => 'Webhook refreshed successfully',
                'data' => [
                    'webhook_expires_at' => $googleCalendarToken->webhook_expiration?->toISOString(),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to refresh webhook', [
                'token_id' => $googleCalendarToken->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to refresh webhook',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Disconnect Google Calendar
     */
    public function disconnect(Request $request, GoogleCalendarToken $googleCalendarToken): JsonResponse
    {
        if ($googleCalendarToken->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $this->googleCalendarService->disconnect($googleCalendarToken);

            return response()->json([
                'message' => 'Google Calendar disconnected successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to disconnect Google Calendar', [
                'token_id' => $googleCalendarToken->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to disconnect Google Calendar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle webhook notification
     */
    public function webhook(Request $request): JsonResponse
    {
        $channelId = $request->header('X-Goog-Channel-ID');
        $resourceId = $request->header('X-Goog-Resource-ID');
        $resourceState = $request->header('X-Goog-Resource-State');

        if (! $channelId || ! $resourceId) {
            return response()->json([
                'message' => 'Invalid webhook request',
            ], 400);
        }

        Log::info('Google Calendar webhook received', [
            'channel_id' => $channelId,
            'resource_id' => $resourceId,
            'resource_state' => $resourceState,
        ]);

        // Only process 'exists' and 'update' events
        if (! in_array($resourceState, ['exists', 'sync'])) {
            $this->googleCalendarService->handleWebhook($channelId, $resourceId);
        }

        return response()->json([
            'message' => 'Webhook processed',
        ]);
    }
}

