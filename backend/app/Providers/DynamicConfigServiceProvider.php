<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class DynamicConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only load settings if the settings table exists
        if (Schema::hasTable('settings')) {
            $this->loadEmailSettings();
            $this->loadFrontendSettings();
            $this->loadPaymentSettings();
            $this->loadSmsSettings();
            $this->loadSocialLoginSettings();
            $this->loadMapSettings();
            $this->loadAnalyticsSettings();
        }
    }

    /**
     * Load email settings from database
     */
    protected function loadEmailSettings(): void
    {
        try {
            $mailMailer = setting('mail_mailer');
            $mailHost = setting('mail_host');
            $mailPort = setting('mail_port');
            $mailUsername = setting('mail_username');
            $mailPassword = setting('mail_password');
            $mailEncryption = setting('mail_encryption');
            $mailFromAddress = setting('mail_from_address');
            $mailFromName = setting('mail_from_name');

            if ($mailMailer) {
                Config::set('mail.default', $mailMailer);
            }

            if ($mailHost) {
                Config::set('mail.mailers.smtp.host', $mailHost);
            }

            if ($mailPort) {
                Config::set('mail.mailers.smtp.port', $mailPort);
            }

            if ($mailUsername) {
                Config::set('mail.mailers.smtp.username', $mailUsername);
            }

            if ($mailPassword) {
                Config::set('mail.mailers.smtp.password', $mailPassword);
            }

            if ($mailEncryption) {
                Config::set('mail.mailers.smtp.encryption', $mailEncryption);
            }

            if ($mailFromAddress) {
                Config::set('mail.from.address', $mailFromAddress);
            }

            if ($mailFromName) {
                Config::set('mail.from.name', $mailFromName);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to load email settings: ' . $e->getMessage());
        }
    }

    /**
     * Load frontend settings
     */
    protected function loadFrontendSettings(): void
    {
        try {
            $frontendUrl = setting('frontend_url');
            $apiUrl = setting('api_url');
            $websocketUrl = setting('websocket_url');

            if ($frontendUrl) {
                Config::set('app.frontend_url', $frontendUrl);
                
                // Update CORS allowed origins
                $currentOrigins = Config::get('cors.allowed_origins', []);
                if (!in_array($frontendUrl, $currentOrigins)) {
                    $currentOrigins[] = $frontendUrl;
                    Config::set('cors.allowed_origins', $currentOrigins);
                }
            }
            
            if ($apiUrl) {
                Config::set('app.api_url', $apiUrl);
            }
            
            if ($websocketUrl) {
                Config::set('app.websocket_url', $websocketUrl);
            }
            
            // Reverb settings
            if (setting('use_reverb') === '1') {
                $reverbHost = setting('reverb_host');
                $reverbPort = setting('reverb_port');
                $reverbScheme = setting('reverb_scheme');
                
                if ($reverbHost) {
                    Config::set('reverb.host', $reverbHost);
                }
                if ($reverbPort) {
                    Config::set('reverb.port', $reverbPort);
                }
                if ($reverbScheme) {
                    Config::set('reverb.scheme', $reverbScheme);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to load frontend settings: ' . $e->getMessage());
        }
    }

    /**
     * Load payment settings
     */
    protected function loadPaymentSettings(): void
    {
        try {
            $stripeEnabled = setting('stripe_enabled') === '1';
            $stripePublicKey = setting('stripe_public_key');
            $stripeSecretKey = setting('stripe_secret_key');

            if ($stripeEnabled && $stripePublicKey && $stripeSecretKey) {
                Config::set('services.stripe.enabled', true);
                Config::set('services.stripe.key', $stripePublicKey);
                Config::set('services.stripe.secret', $stripeSecretKey);
            } else {
                Config::set('services.stripe.enabled', false);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to load payment settings: ' . $e->getMessage());
        }
    }

    /**
     * Load SMS settings
     */
    protected function loadSmsSettings(): void
    {
        try {
            $twilioEnabled = setting('twilio_enabled') === '1';
            $twilioSid = setting('twilio_sid');
            $twilioToken = setting('twilio_auth_token');
            $twilioPhone = setting('twilio_phone_number');

            if ($twilioEnabled && $twilioSid && $twilioToken) {
                Config::set('services.twilio.enabled', true);
                Config::set('services.twilio.sid', $twilioSid);
                Config::set('services.twilio.token', $twilioToken);
                Config::set('services.twilio.from', $twilioPhone);
            } else {
                Config::set('services.twilio.enabled', false);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to load SMS settings: ' . $e->getMessage());
        }
    }
    
    /**
     * Load social login settings
     */
    protected function loadSocialLoginSettings(): void
    {
        try {
            // Google OAuth
            if (setting('enable_google_login') === '1') {
                $googleClientId = setting('google_client_id');
                $googleClientSecret = setting('google_client_secret');
                
                if ($googleClientId && $googleClientSecret) {
                    Config::set('services.google.enabled', true);
                    Config::set('services.google.client_id', $googleClientId);
                    Config::set('services.google.client_secret', $googleClientSecret);
                    Config::set('services.google.redirect', config('app.url') . '/api/v1/auth/google/callback');
                }
            }
            
            // Facebook OAuth
            if (setting('enable_facebook_login') === '1') {
                $facebookClientId = setting('facebook_client_id');
                $facebookClientSecret = setting('facebook_client_secret');
                
                if ($facebookClientId && $facebookClientSecret) {
                    Config::set('services.facebook.enabled', true);
                    Config::set('services.facebook.client_id', $facebookClientId);
                    Config::set('services.facebook.client_secret', $facebookClientSecret);
                    Config::set('services.facebook.redirect', config('app.url') . '/api/v1/auth/facebook/callback');
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to load social login settings: ' . $e->getMessage());
        }
    }
    
    /**
     * Load map settings
     */
    protected function loadMapSettings(): void
    {
        try {
            $mapboxToken = setting('mapbox_token');
            $googleMapsKey = setting('google_maps_api_key');
            $ipstackKey = setting('ipstack_api_key');
            
            if ($mapboxToken) {
                Config::set('services.mapbox.token', $mapboxToken);
            }
            
            if ($googleMapsKey) {
                Config::set('services.google_maps.api_key', $googleMapsKey);
            }
            
            if ($ipstackKey) {
                Config::set('services.ipstack.api_key', $ipstackKey);
            }
            
            // Default map center
            $defaultLat = setting('default_map_center_lat');
            $defaultLng = setting('default_map_center_lng');
            
            if ($defaultLat && $defaultLng) {
                Config::set('app.default_map_center', [
                    'lat' => (float) $defaultLat,
                    'lng' => (float) $defaultLng,
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to load map settings: ' . $e->getMessage());
        }
    }
    
    /**
     * Load analytics settings
     */
    protected function loadAnalyticsSettings(): void
    {
        try {
            if (setting('enable_analytics') === '1') {
                $googleAnalyticsId = setting('google_analytics_id');
                $facebookPixelId = setting('facebook_pixel_id');
                
                if ($googleAnalyticsId) {
                    Config::set('services.google_analytics.tracking_id', $googleAnalyticsId);
                }
                
                if ($facebookPixelId) {
                    Config::set('services.facebook_pixel.pixel_id', $facebookPixelId);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to load analytics settings: ' . $e->getMessage());
        }
    }
}
