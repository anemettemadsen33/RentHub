<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Services\IntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IntegrationController extends Controller
{
    protected $integrationService;

    public function __construct(IntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    /**
     * Get all integrations for the authenticated user
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $integrations = Integration::where('user_id', $user->id)
                ->orWhere('is_global', true)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $integrations
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch integrations', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch integrations'
            ], 500);
        }
    }

    /**
     * Get a specific integration
     */
    public function show($id)
    {
        try {
            $integration = Integration::where('id', $id)
                ->where(function ($query) {
                    $query->where('user_id', Auth::id())
                          ->orWhere('is_global', true);
                })
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $integration
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch integration', [
                'error' => $e->getMessage(),
                'integration_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Integration not found'
            ], 404);
        }
    }

    /**
     * Connect to a platform (initiate OAuth flow)
     */
    public function getOAuthUrl(Request $request)
    {
        $request->validate([
            'platform' => 'required|string|in:airbnb,booking,vrbo,google_calendar,stripe'
        ]);

        try {
            $platform = $request->platform;
            $oauthUrl = $this->integrationService->getOAuthUrl($platform);

            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $oauthUrl
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get OAuth URL', [
                'error' => $e->getMessage(),
                'platform' => $platform,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get OAuth URL'
            ], 500);
        }
    }

    /**
     * Connect integration after OAuth callback
     */
    public function connect(Request $request)
    {
        $request->validate([
            'platform' => 'required|string|in:airbnb,booking,vrbo,google_calendar,stripe',
            'auth_code' => 'required|string'
        ]);

        try {
            $user = Auth::user();
            $platform = $request->platform;
            $authCode = $request->auth_code;

            $integration = $this->integrationService->connectIntegration($user, $platform, $authCode);

            return response()->json([
                'success' => true,
                'data' => $integration,
                'message' => 'Integration connected successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to connect integration', [
                'error' => $e->getMessage(),
                'platform' => $platform,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to connect integration'
            ], 500);
        }
    }

    /**
     * Disconnect an integration
     */
    public function disconnect($id)
    {
        try {
            $integration = Integration::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $this->integrationService->disconnectIntegration($integration);

            return response()->json([
                'success' => true,
                'message' => 'Integration disconnected successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to disconnect integration', [
                'error' => $e->getMessage(),
                'integration_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to disconnect integration'
            ], 500);
        }
    }

    /**
     * Sync an integration
     */
    public function sync($id)
    {
        try {
            $integration = Integration::where('id', $id)
                ->where(function ($query) {
                    $query->where('user_id', Auth::id())
                          ->orWhere('is_global', true);
                })
                ->firstOrFail();

            $result = $this->integrationService->syncIntegration($integration);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sync integration', [
                'error' => $e->getMessage(),
                'integration_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync integration'
            ], 500);
        }
    }

    /**
     * Update integration settings
     */
    public function updateSettings(Request $request, $id)
    {
        $request->validate([
            'settings' => 'required|array'
        ]);

        try {
            $integration = Integration::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $integration->settings = array_merge($integration->settings ?? [], $request->settings);
            $integration->save();

            return response()->json([
                'success' => true,
                'data' => $integration,
                'message' => 'Settings updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update integration settings', [
                'error' => $e->getMessage(),
                'integration_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings'
            ], 500);
        }
    }

    /**
     * Get sync history for an integration
     */
    public function getSyncHistory($id)
    {
        try {
            $integration = Integration::where('id', $id)
                ->where(function ($query) {
                    $query->where('user_id', Auth::id())
                          ->orWhere('is_global', true);
                })
                ->firstOrFail();

            $history = $this->integrationService->getSyncHistory($integration);

            return response()->json([
                'success' => true,
                'data' => $history
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get sync history', [
                'error' => $e->getMessage(),
                'integration_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get sync history'
            ], 500);
        }
    }
}