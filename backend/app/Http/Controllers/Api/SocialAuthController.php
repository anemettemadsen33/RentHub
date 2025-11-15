<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to OAuth provider
     */
    public function redirect(string $provider)
    {
        if (! in_array($provider, ['google', 'facebook', 'github'])) {
            return response()->json(['error' => 'Invalid provider'], 400);
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Handle OAuth callback
     */
    public function callback(string $provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();

            // Find or create social account
            $socialAccount = SocialAccount::where('provider', $provider)
                ->where('provider_id', $socialUser->getId())
                ->first();

            if ($socialAccount) {
                // Update existing social account
                $socialAccount->update([
                    'access_token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken,
                    'expires_at' => now()->addSeconds($socialUser->expiresIn ?? 3600),
                    'provider_data' => $socialUser->getRaw(),
                ]);

                $user = $socialAccount->user;
            } else {
                // Check if user exists by email
                $user = User::where('email', $socialUser->getEmail())->first();

                if (! $user) {
                    // Create new user
                    $user = User::create([
                        'name' => $socialUser->getName(),
                        'email' => $socialUser->getEmail(),
                        'email_verified_at' => now(),
                        'password' => Hash::make(Str::random(32)),
                        'avatar' => $socialUser->getAvatar(),
                    ]);
                }

                // Create social account
                SocialAccount::create([
                    'user_id' => $user->id,
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'access_token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken,
                    'expires_at' => now()->addSeconds($socialUser->expiresIn ?? 3600),
                    'provider_data' => $socialUser->getRaw(),
                ]);
            }

            // Create token
            $token = $user->createToken('social-auth')->plainTextToken;

            // Redirect to frontend callback with token
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            $callbackUrl = $frontendUrl . '/auth/callback?token=' . $token;
            
            return redirect($callbackUrl);
        } catch (\Exception $e) {
            // On error, redirect to login with error message
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            $errorUrl = $frontendUrl . '/auth/login?error=' . urlencode($e->getMessage());
            
            return redirect($errorUrl);
        }
    }
}

