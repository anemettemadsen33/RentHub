<?php

namespace App\\Http\\Controllers\\Api\Security;

use App\Http\Controllers\Controller;
use App\Services\Security\OAuth2Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OAuth2Controller extends Controller
{
    public function __construct(
        private OAuth2Service $oauth2Service
    ) {}

    /**
     * Authorization endpoint
     */
    public function authorize(Request $request): JsonResponse
    {
        $request->validate([
            'client_id' => 'required|string',
            'redirect_uri' => 'required|url',
            'response_type' => 'required|in:code',
            'scope' => 'nullable|string',
        ]);

        $user = $request->user();
        $client = \App\Models\OAuthClient::where('client_id', $request->client_id)->firstOrFail();

        $scopes = $request->scope ? explode(' ', $request->scope) : [];
        $code = $this->oauth2Service->generateAuthorizationCode($user, $client, $scopes);

        $redirectUri = $request->redirect_uri.'?code='.$code;

        return response()->json([
            'redirect_uri' => $redirectUri,
        ]);
    }

    /**
     * Token endpoint
     */
    public function token(Request $request): JsonResponse
    {
        $request->validate([
            'grant_type' => 'required|in:authorization_code,refresh_token',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
        ]);

        try {
            if ($request->grant_type === 'authorization_code') {
                $request->validate([
                    'code' => 'required|string',
                    'redirect_uri' => 'required|url',
                ]);

                $tokens = $this->oauth2Service->exchangeAuthorizationCode(
                    $request->code,
                    $request->client_id,
                    $request->client_secret,
                    $request->redirect_uri
                );
            } else {
                $request->validate([
                    'refresh_token' => 'required|string',
                ]);

                $tokens = $this->oauth2Service->refreshAccessToken(
                    $request->refresh_token,
                    $request->client_id,
                    $request->client_secret
                );
            }

            return response()->json($tokens);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'invalid_grant',
                'error_description' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Revoke token
     */
    public function revoke(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $this->oauth2Service->revokeToken($request->token);

        return response()->json([
            'message' => 'Token revoked successfully',
        ]);
    }

    /**
     * Token introspection
     */
    public function introspect(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $info = $this->oauth2Service->introspectToken($request->token);

        return response()->json($info);
    }
}

