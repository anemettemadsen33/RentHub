<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Get public settings (no authentication required)
     * Used by frontend to get company info, frontend URL, etc.
     */
    public function public(): JsonResponse
    {
        $publicSettings = [
            // General Settings
            'site_name' => Setting::get('site_name', 'RentHub'),
            'site_description' => Setting::get('site_description', 'Platformă de închirieri'),
            'site_logo' => Setting::get('site_logo', ''),
            'site_favicon' => Setting::get('site_favicon', ''),
            'site_keywords' => Setting::get('site_keywords', 'închirieri, proprietăți, cazare'),
            'items_per_page' => (int) Setting::get('items_per_page', '12'),

            // URLs
            'frontend_url' => Setting::get('frontend_url', config('app.frontend_url', 'http://localhost:3000')),
            'api_url' => Setting::get('api_url', config('app.url')),
            'api_base_url' => Setting::get('api_base_url', config('app.url').'/api/v1'),
            'websocket_url' => Setting::get('websocket_url', 'http://localhost:6001'),

            // Reverb/WebSocket Configuration
            'reverb' => [
                'enabled' => Setting::get('use_reverb', '1') === '1',
                'host' => Setting::get('reverb_host', 'localhost'),
                'port' => (int) Setting::get('reverb_port', '8080'),
                'scheme' => Setting::get('reverb_scheme', 'ws'),
                'key' => config('reverb.apps.0.key', 'renthub-key'),
            ],

            // Features
            'features' => [
                'registrations_enabled' => Setting::get('registration_enabled', '1') === '1' || Setting::get('enable_registrations', '1') === '1',
                'email_verification_required' => Setting::get('email_verification_required', '1') === '1' || Setting::get('require_email_verification', '1') === '1',
                'reviews_enabled' => Setting::get('enable_reviews', '1') === '1',
                'messaging_enabled' => Setting::get('enable_messaging', '1') === '1',
                'wishlist_enabled' => Setting::get('enable_wishlist', '1') === '1',
            ],

            // Maintenance
            'maintenance_mode' => Setting::get('maintenance_mode', '0') === '1' || Setting::get('maintenance_mode', false) === true,
            'maintenance_message' => Setting::get('maintenance_message', 'Site-ul este momentan în mentenanță. Vă rugăm reveniți mai târziu.'),

            // Social Login
            'social_login' => [
                'google_enabled' => Setting::get('google_auth_enabled', '0') === '1' || Setting::get('enable_google_login', '0') === '1',
                'google_client_id' => Setting::get('google_client_id', ''),
                'facebook_enabled' => Setting::get('facebook_auth_enabled', '0') === '1' || Setting::get('enable_facebook_login', '0') === '1',
                'facebook_client_id' => Setting::get('facebook_client_id', ''),
            ],

            // Payment
            'payment' => [
                'stripe_enabled' => Setting::get('stripe_enabled', '0') === '1' || Setting::get('stripe_enabled', false) === true,
                'stripe_public_key' => Setting::get('stripe_public_key', ''),
                'paypal_enabled' => Setting::get('paypal_enabled', '0') === '1' || Setting::get('paypal_enabled', false) === true,
                'paypal_client_id' => Setting::get('paypal_client_id', ''),
                'currency' => Setting::get('currency', 'RON'),
                'currency_symbol' => Setting::get('currency_symbol', 'RON'),
            ],

            // Maps
            'maps' => [
                'mapbox_token' => Setting::get('mapbox_token', ''),
                'google_maps_api_key' => Setting::get('google_maps_api_key', ''),
                'default_center' => [
                    'lat' => (float) Setting::get('default_map_center_lat', '44.4268'),
                    'lng' => (float) Setting::get('default_map_center_lng', '26.1025'),
                ],
            ],

            // Analytics
            'analytics' => [
                'enabled' => Setting::get('enable_analytics', '0') === '1',
                'google_analytics_id' => Setting::get('google_analytics_id', ''),
                'facebook_pixel_id' => Setting::get('facebook_pixel_id', ''),
            ],

            // Notifications
            'notifications' => [
                'push_enabled' => Setting::get('enable_push_notifications', '0') === '1',
                'pusher_beams_instance_id' => Setting::get('pusher_beams_instance_id', ''),
            ],

            // Company Info (Public)
            'company' => [
                'name' => Setting::get('company_name', 'RentHub'),
                'email' => Setting::get('company_email', 'info@renthub.ro'),
                'phone' => Setting::get('company_phone', '+40 XXX XXX XXX'),
                'address' => Setting::get('company_address', ''),
                'support_email' => Setting::get('support_email', 'support@renthub.ro'),
                'support_phone' => Setting::get('support_phone', '+40 XXX XXX XXX'),
                'google_maps' => Setting::get('company_google_maps', ''),
            ],

            // SEO
            'seo' => [
                'meta_title' => Setting::get('meta_title', 'RentHub'),
                'meta_description' => Setting::get('meta_description', ''),
                'meta_keywords' => Setting::get('meta_keywords', ''),
                'default_meta_image' => Setting::get('default_meta_image', ''),
                'robots_enabled' => Setting::get('robots_txt_enabled', '1') === '1',
                'sitemap_enabled' => Setting::get('sitemap_enabled', '1') === '1',
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $publicSettings,
        ]);
    }

    /**
     * Get all settings (admin only)
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        $settings = [
            // General
            'site_name' => Setting::get('site_name', 'RentHub'),
            'site_description' => Setting::get('site_description', ''),
            'site_logo' => Setting::get('site_logo', ''),
            'site_favicon' => Setting::get('site_favicon', ''),

            // Frontend & API
            'frontend_url' => Setting::get('frontend_url', env('FRONTEND_URL', 'http://localhost:3000')),
            'api_url' => Setting::get('api_url', env('APP_URL', 'http://localhost:8000')),
            'sanctum_stateful_domains' => Setting::get('sanctum_stateful_domains', env('SANCTUM_STATEFUL_DOMAINS', 'localhost:3000')),
            'cors_allowed_origins' => Setting::get('cors_allowed_origins', env('FRONTEND_URL', 'http://localhost:3000')),

            // Company
            'company_name' => Setting::get('company_name', 'RentHub'),
            'company_email' => Setting::get('company_email', env('MAIL_FROM_ADDRESS', 'info@renthub.ro')),
            'company_phone' => Setting::get('company_phone', ''),
            'company_address' => Setting::get('company_address', ''),
            'company_google_maps' => Setting::get('company_google_maps', ''),

            // Mail Configuration
            'mail_mailer' => Setting::get('mail_mailer', env('MAIL_MAILER', 'smtp')),
            'mail_host' => Setting::get('mail_host', env('MAIL_HOST', 'smtp.mailtrap.io')),
            'mail_port' => Setting::get('mail_port', env('MAIL_PORT', '2525')),
            'mail_username' => Setting::get('mail_username', env('MAIL_USERNAME', '')),
            'mail_password' => Setting::get('mail_password', env('MAIL_PASSWORD', '')),
            'mail_encryption' => Setting::get('mail_encryption', env('MAIL_ENCRYPTION', 'tls')),
            'mail_from_address' => Setting::get('mail_from_address', env('MAIL_FROM_ADDRESS', 'hello@example.com')),
            'mail_from_name' => Setting::get('mail_from_name', env('MAIL_FROM_NAME', 'RentHub')),

            // Payment
            'stripe_enabled' => Setting::get('stripe_enabled', true),
            'stripe_public_key' => Setting::get('stripe_public_key', env('STRIPE_KEY', '')),
            'stripe_secret_key' => Setting::get('stripe_secret_key', env('STRIPE_SECRET', '')),
            'paypal_enabled' => Setting::get('paypal_enabled', false),
            'paypal_client_id' => Setting::get('paypal_client_id', env('PAYPAL_CLIENT_ID', '')),
            'paypal_mode' => Setting::get('paypal_mode', env('PAYPAL_MODE', 'sandbox')),

            // SEO
            'meta_title' => Setting::get('meta_title', 'RentHub'),
            'meta_description' => Setting::get('meta_description', ''),
            'meta_keywords' => Setting::get('meta_keywords', ''),

            // Social Auth
            'google_auth_enabled' => Setting::get('google_auth_enabled', false),
            'google_client_id' => Setting::get('google_client_id', env('GOOGLE_CLIENT_ID', '')),
            'facebook_auth_enabled' => Setting::get('facebook_auth_enabled', false),
            'facebook_client_id' => Setting::get('facebook_client_id', env('FACEBOOK_CLIENT_ID', '')),

            // Features
            'maintenance_mode' => Setting::get('maintenance_mode', false),
            'registration_enabled' => Setting::get('registration_enabled', true),
            'email_verification_required' => Setting::get('email_verification_required', true),
        ];

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Update settings (admin only)
     */
    public function update(Request $request): JsonResponse
    {
        $this->authorize('update', Setting::class);

        $validated = $request->validate([
            // General
            'site_name' => 'sometimes|string|max:255',
            'site_description' => 'sometimes|string|max:500',
            'site_logo' => 'sometimes|string',
            'site_favicon' => 'sometimes|string',

            // Frontend & API
            'frontend_url' => 'sometimes|url',
            'api_url' => 'sometimes|url',
            'sanctum_stateful_domains' => 'sometimes|string',
            'cors_allowed_origins' => 'sometimes|string',

            // Company
            'company_name' => 'sometimes|string|max:255',
            'company_email' => 'sometimes|email|max:255',
            'company_phone' => 'sometimes|string|max:255',
            'company_address' => 'sometimes|string',
            'company_google_maps' => 'sometimes|string',

            // Mail
            'mail_mailer' => 'sometimes|in:smtp,sendmail,mailgun,ses,postmark,log',
            'mail_host' => 'sometimes|string|max:255',
            'mail_port' => 'sometimes|integer|min:1|max:65535',
            'mail_username' => 'sometimes|string|max:255',
            'mail_password' => 'sometimes|string|max:255',
            'mail_encryption' => 'sometimes|in:tls,ssl,',
            'mail_from_address' => 'sometimes|email|max:255',
            'mail_from_name' => 'sometimes|string|max:255',

            // Payment
            'stripe_enabled' => 'sometimes|boolean',
            'stripe_public_key' => 'sometimes|string',
            'stripe_secret_key' => 'sometimes|string',
            'paypal_enabled' => 'sometimes|boolean',
            'paypal_client_id' => 'sometimes|string',
            'paypal_mode' => 'sometimes|in:sandbox,live',

            // SEO
            'meta_title' => 'sometimes|string|max:60',
            'meta_description' => 'sometimes|string|max:160',
            'meta_keywords' => 'sometimes|string',

            // Social Auth
            'google_auth_enabled' => 'sometimes|boolean',
            'google_client_id' => 'sometimes|string',
            'facebook_auth_enabled' => 'sometimes|boolean',
            'facebook_client_id' => 'sometimes|string',

            // Features
            'maintenance_mode' => 'sometimes|boolean',
            'registration_enabled' => 'sometimes|boolean',
            'email_verification_required' => 'sometimes|boolean',
        ]);

        foreach ($validated as $key => $value) {
            $group = 'general';
            $type = 'text';

            // Determine group and type
            if (str_starts_with($key, 'site_')) {
                $group = 'general';
            } elseif (str_starts_with($key, 'frontend_') || str_starts_with($key, 'api_') || str_starts_with($key, 'cors_') || str_starts_with($key, 'sanctum_')) {
                $group = 'frontend';
                $type = str_contains($key, 'url') ? 'url' : 'text';
            } elseif (str_starts_with($key, 'company_')) {
                $group = 'company';
                $type = str_contains($key, 'email') ? 'email' :
                        (str_contains($key, 'phone') ? 'tel' :
                        (str_contains($key, 'address') || str_contains($key, 'maps') ? 'textarea' : 'text'));
            } elseif (str_starts_with($key, 'mail_')) {
                $group = 'mail';
                $type = str_contains($key, 'password') ? 'password' :
                        (str_contains($key, 'email') || str_contains($key, 'address') ? 'email' : 'text');
            } elseif (str_starts_with($key, 'stripe_') || str_starts_with($key, 'paypal_')) {
                $group = 'payment';
                $type = str_contains($key, 'enabled') ? 'boolean' :
                        (str_contains($key, 'key') || str_contains($key, 'secret') ? 'password' : 'text');
            } elseif (str_starts_with($key, 'meta_')) {
                $group = 'seo';
            } elseif (str_contains($key, 'auth') || str_contains($key, 'google_') || str_contains($key, 'facebook_')) {
                $group = 'social';
                $type = str_contains($key, 'enabled') ? 'boolean' : 'text';
            } elseif (str_contains($key, 'mode') || str_contains($key, 'enabled') || str_contains($key, 'required')) {
                $type = 'boolean';
            }

            Setting::set($key, $value, $group, $type);
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'data' => $this->index()->getData()->data,
        ]);
    }

    /**
     * Test email configuration
     */
    public function testEmail(Request $request): JsonResponse
    {
        $this->authorize('update', Setting::class);

        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            \Mail::raw('This is a test email from RentHub. Your email configuration is working correctly!', function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('RentHub - Test Email');
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully to '.$request->email,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: '.$e->getMessage(),
            ], 500);
        }
    }
}
