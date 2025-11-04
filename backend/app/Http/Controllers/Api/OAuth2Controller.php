<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OAuthClient;
use App\Services\OAuth2Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OAuth2Controller extends Controller
{
    protected OAuth2Service $oauth2Service;

    public function __construct(OAuth2Service $oauth2Service)
    {
        $this->oauth2Service = $oauth2Service;
    }

    /**
     * Authorization endpoint
     */
    public function authorize(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|string',
            'redirect_uri' => 'required|url',
            'response_type' => 'required|in:code',
            'scope' => 'nullable|string',
            'state' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $client = OAuthClient::where('client_id', $request->client_id)->first();

        if (! $client) {
            return response()->json(['error' => 'Invalid client'], 401);
        }

        if (! in_array($request->redirect_uri, json_decode($client->redirect_uris, true))) {
            return response()->json(['error' => 'Invalid redirect URI'], 400);
        }

        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $scopes = explode(' ', $request->scope ?? '');
        $code = $this->oauth2Service->generateAuthorizationCode($user, $client, $scopes);

        $redirectUrl = $request->redirect_uri.'?'.http_build_query([
            'code' => $code,
            'state' => $request->state,
        ]);

        return response()->json([
            'redirect_url' => $redirectUrl,
            'code' => $code,
        ]);
    }

    /**
     * Token endpoint
     */
    public function token(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'grant_type' => 'required|in:authorization_code,refresh_token',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->grant_type === 'authorization_code') {
            return $this->handleAuthorizationCode($request);
        }

        return $this->handleRefreshToken($request);
    }

    /**
     * Handle authorization code grant
     */
    protected function handleAuthorizationCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tokens = $this->oauth2Service->exchangeCodeForToken(
            $request->code,
            $request->client_id,
            $request->client_secret
        );

        if (! $tokens) {
            return response()->json(['error' => 'Invalid authorization code'], 400);
        }

        return response()->json($tokens);
    }

    /**
     * Handle refresh token grant
     */
    protected function handleRefreshToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tokens = $this->oauth2Service->refreshAccessToken(
            $request->refresh_token,
            $request->client_id,
            $request->client_secret
        );

        if (! $tokens) {
            return response()->json(['error' => 'Invalid refresh token'], 400);
        }

        return response()->json($tokens);
    }

    /**
     * Revoke token
     */
    public function revoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $this->oauth2Service->revokeToken($request->token);

        return response()->json(['message' => 'Token revoked successfully']);
    }

    /**
     * Token introspection
     */
    public function introspect(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tokenData = $this->oauth2Service->validateAccessToken($request->token);

        if (! $tokenData) {
            return response()->json(['active' => false]);
        }

        return response()->json([
            'active' => true,
            'scope' => implode(' ', $tokenData['scopes']),
            'client_id' => $tokenData['client_id'],
            'user_id' => $tokenData['user_id'],
            'exp' => $tokenData['expires_at']->timestamp,
        ]);
    }
}
