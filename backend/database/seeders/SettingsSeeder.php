<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings
            ['key' => 'site_name', 'value' => 'RentHub', 'group' => 'general', 'type' => 'text', 'description' => 'Numele site-ului afișat în interfață'],
            ['key' => 'site_description', 'value' => 'Platformă modernă de închirieri pentru proprietăți', 'group' => 'general', 'type' => 'textarea', 'description' => 'Descrierea scurtă a site-ului pentru SEO'],
            ['key' => 'site_logo', 'value' => '', 'group' => 'general', 'type' => 'text', 'description' => 'Calea către logo-ul site-ului'],
            ['key' => 'site_favicon', 'value' => '', 'group' => 'general', 'type' => 'text', 'description' => 'Calea către favicon'],
            ['key' => 'items_per_page', 'value' => '12', 'group' => 'general', 'type' => 'text', 'description' => 'Număr de elemente pe pagină'],

            // Frontend & API Integration
            ['key' => 'frontend_url', 'value' => env('FRONTEND_URL', 'http://localhost:3000'), 'group' => 'frontend', 'type' => 'url', 'description' => 'URL-ul aplicației frontend (React)'],
            ['key' => 'api_url', 'value' => env('APP_URL', 'http://localhost:8000'), 'group' => 'frontend', 'type' => 'url', 'description' => 'URL-ul backend API (Laravel)'],
            ['key' => 'sanctum_stateful_domains', 'value' => env('SANCTUM_STATEFUL_DOMAINS', 'localhost:3000,localhost,127.0.0.1:3000'), 'group' => 'frontend', 'type' => 'text', 'description' => 'Domenii permise pentru autentificare Sanctum'],
            ['key' => 'cors_allowed_origins', 'value' => env('FRONTEND_URL', 'http://localhost:3000'), 'group' => 'frontend', 'type' => 'text', 'description' => 'Origini permise pentru CORS'],

            // Company Information
            ['key' => 'company_name', 'value' => 'RentHub', 'group' => 'company', 'type' => 'text', 'description' => 'Numele companiei'],
            ['key' => 'company_email', 'value' => env('MAIL_FROM_ADDRESS', 'info@renthub.ro'), 'group' => 'company', 'type' => 'email', 'description' => 'Email-ul principal al companiei'],
            ['key' => 'company_phone', 'value' => '', 'group' => 'company', 'type' => 'tel', 'description' => 'Număr de telefon al companiei'],
            ['key' => 'company_address', 'value' => '', 'group' => 'company', 'type' => 'textarea', 'description' => 'Adresa fizică a companiei'],
            ['key' => 'company_google_maps', 'value' => '', 'group' => 'company', 'type' => 'text', 'description' => 'Link Google Maps'],
            ['key' => 'support_email', 'value' => 'support@renthub.ro', 'group' => 'company', 'type' => 'email', 'description' => 'Email suport clienți'],
            ['key' => 'support_phone', 'value' => '', 'group' => 'company', 'type' => 'tel', 'description' => 'Telefon suport clienți'],

            // Mail Configuration
            ['key' => 'mail_mailer', 'value' => env('MAIL_MAILER', 'smtp'), 'group' => 'mail', 'type' => 'text', 'description' => 'Driver pentru trimitere email'],
            ['key' => 'mail_host', 'value' => env('MAIL_HOST', 'smtp.mailtrap.io'), 'group' => 'mail', 'type' => 'text', 'description' => 'Server SMTP pentru trimitere email'],
            ['key' => 'mail_port', 'value' => env('MAIL_PORT', '2525'), 'group' => 'mail', 'type' => 'text', 'description' => 'Port SMTP'],
            ['key' => 'mail_username', 'value' => env('MAIL_USERNAME', ''), 'group' => 'mail', 'type' => 'text', 'description' => 'Username SMTP'],
            ['key' => 'mail_password', 'value' => env('MAIL_PASSWORD', ''), 'group' => 'mail', 'type' => 'password', 'description' => 'Parola SMTP'],
            ['key' => 'mail_encryption', 'value' => env('MAIL_ENCRYPTION', 'tls'), 'group' => 'mail', 'type' => 'text', 'description' => 'Tip de criptare (tls, ssl)'],
            ['key' => 'mail_from_address', 'value' => env('MAIL_FROM_ADDRESS', 'noreply@renthub.ro'), 'group' => 'mail', 'type' => 'email', 'description' => 'Adresa email expeditor'],
            ['key' => 'mail_from_name', 'value' => env('MAIL_FROM_NAME', 'RentHub'), 'group' => 'mail', 'type' => 'text', 'description' => 'Numele afișat ca expeditor'],

            // Payment Gateways
            ['key' => 'stripe_enabled', 'value' => '1', 'group' => 'payment', 'type' => 'boolean', 'description' => 'Activează plăți prin Stripe'],
            ['key' => 'stripe_public_key', 'value' => env('STRIPE_KEY', ''), 'group' => 'payment', 'type' => 'password', 'description' => 'Cheia publică Stripe'],
            ['key' => 'stripe_secret_key', 'value' => env('STRIPE_SECRET', ''), 'group' => 'payment', 'type' => 'password', 'description' => 'Cheia secretă Stripe'],
            ['key' => 'paypal_enabled', 'value' => '0', 'group' => 'payment', 'type' => 'boolean', 'description' => 'Activează plăți prin PayPal'],
            ['key' => 'paypal_client_id', 'value' => env('PAYPAL_CLIENT_ID', ''), 'group' => 'payment', 'type' => 'password', 'description' => 'Client ID PayPal'],
            ['key' => 'paypal_mode', 'value' => env('PAYPAL_MODE', 'sandbox'), 'group' => 'payment', 'type' => 'text', 'description' => 'PayPal mode (sandbox/live)'],

            // SEO Settings
            ['key' => 'meta_title', 'value' => 'RentHub - Platformă Închirieri', 'group' => 'seo', 'type' => 'text', 'description' => 'Title tag pentru SEO'],
            ['key' => 'meta_description', 'value' => 'Descoperă cele mai bune proprietăți de închiriat. Platformă modernă, sigură și ușor de utilizat.', 'group' => 'seo', 'type' => 'textarea', 'description' => 'Meta description pentru SEO'],
            ['key' => 'meta_keywords', 'value' => 'închirieri, proprietăți, cazare, apartamente, case', 'group' => 'seo', 'type' => 'text', 'description' => 'Meta keywords'],
            ['key' => 'default_meta_image', 'value' => '', 'group' => 'seo', 'type' => 'text', 'description' => 'Imagine default pentru social sharing'],
            ['key' => 'robots_txt_enabled', 'value' => '1', 'group' => 'seo', 'type' => 'boolean', 'description' => 'Activează robots.txt'],
            ['key' => 'sitemap_enabled', 'value' => '1', 'group' => 'seo', 'type' => 'boolean', 'description' => 'Activează sitemap.xml'],

            // Social Authentication
            ['key' => 'google_auth_enabled', 'value' => '0', 'group' => 'social', 'type' => 'boolean', 'description' => 'Activează autentificare cu Google'],
            ['key' => 'google_client_id', 'value' => env('GOOGLE_CLIENT_ID', ''), 'group' => 'social', 'type' => 'text', 'description' => 'Google OAuth Client ID'],
            ['key' => 'facebook_auth_enabled', 'value' => '0', 'group' => 'social', 'type' => 'boolean', 'description' => 'Activează autentificare cu Facebook'],
            ['key' => 'facebook_client_id', 'value' => env('FACEBOOK_CLIENT_ID', ''), 'group' => 'social', 'type' => 'text', 'description' => 'Facebook OAuth Client ID'],

            // Features & Security
            ['key' => 'maintenance_mode', 'value' => '0', 'group' => 'features', 'type' => 'boolean', 'description' => 'Activează modul mentenanță'],
            ['key' => 'registration_enabled', 'value' => '1', 'group' => 'features', 'type' => 'boolean', 'description' => 'Permite înregistrarea de noi utilizatori'],
            ['key' => 'enable_registrations', 'value' => '1', 'group' => 'features', 'type' => 'boolean', 'description' => 'Permite înregistrarea (compatibilitate)'],
            ['key' => 'email_verification_required', 'value' => '1', 'group' => 'features', 'type' => 'boolean', 'description' => 'Necesită verificare email'],
            ['key' => 'require_email_verification', 'value' => '1', 'group' => 'features', 'type' => 'boolean', 'description' => 'Necesită verificare email (compatibilitate)'],
            ['key' => 'enable_reviews', 'value' => '1', 'group' => 'features', 'type' => 'boolean', 'description' => 'Activează sistem de review-uri'],
            ['key' => 'enable_messaging', 'value' => '1', 'group' => 'features', 'type' => 'boolean', 'description' => 'Activează sistem de mesagerie'],
            ['key' => 'enable_wishlist', 'value' => '1', 'group' => 'features', 'type' => 'boolean', 'description' => 'Activează wishlist'],

            // Maps & Location
            ['key' => 'mapbox_token', 'value' => '', 'group' => 'maps', 'type' => 'password', 'description' => 'Mapbox API Token'],
            ['key' => 'google_maps_api_key', 'value' => '', 'group' => 'maps', 'type' => 'password', 'description' => 'Google Maps API Key'],
            ['key' => 'default_map_center_lat', 'value' => '44.4268', 'group' => 'maps', 'type' => 'text', 'description' => 'Latitudine centru hartă default'],
            ['key' => 'default_map_center_lng', 'value' => '26.1025', 'group' => 'maps', 'type' => 'text', 'description' => 'Longitudine centru hartă default'],

            // Analytics
            ['key' => 'enable_analytics', 'value' => '0', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'Activează analytics'],
            ['key' => 'google_analytics_id', 'value' => '', 'group' => 'analytics', 'type' => 'text', 'description' => 'Google Analytics ID'],
            ['key' => 'facebook_pixel_id', 'value' => '', 'group' => 'analytics', 'type' => 'text', 'description' => 'Facebook Pixel ID'],

            // Notifications
            ['key' => 'enable_push_notifications', 'value' => '0', 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Activează push notifications'],
            ['key' => 'pusher_beams_instance_id', 'value' => '', 'group' => 'notifications', 'type' => 'text', 'description' => 'Pusher Beams Instance ID'],

            // WebSocket/Reverb
            ['key' => 'use_reverb', 'value' => '1', 'group' => 'websocket', 'type' => 'boolean', 'description' => 'Folosește Laravel Reverb pentru WebSocket'],
            ['key' => 'reverb_host', 'value' => 'localhost', 'group' => 'websocket', 'type' => 'text', 'description' => 'Reverb host'],
            ['key' => 'reverb_port', 'value' => '8080', 'group' => 'websocket', 'type' => 'text', 'description' => 'Reverb port'],
            ['key' => 'reverb_scheme', 'value' => 'ws', 'group' => 'websocket', 'type' => 'text', 'description' => 'Reverb scheme (ws/wss)'],
            ['key' => 'websocket_url', 'value' => 'http://localhost:6001', 'group' => 'websocket', 'type' => 'url', 'description' => 'WebSocket URL'],

            // SMS Settings (Twilio)
            ['key' => 'twilio_enabled', 'value' => '0', 'group' => 'sms', 'type' => 'boolean', 'description' => 'Activează SMS prin Twilio'],
            ['key' => 'twilio_sid', 'value' => '', 'group' => 'sms', 'type' => 'password', 'description' => 'Twilio SID'],
            ['key' => 'twilio_auth_token', 'value' => '', 'group' => 'sms', 'type' => 'password', 'description' => 'Twilio Auth Token'],
            ['key' => 'twilio_phone_number', 'value' => '', 'group' => 'sms', 'type' => 'text', 'description' => 'Twilio Phone Number'],

            // Currency
            ['key' => 'currency', 'value' => 'RON', 'group' => 'general', 'type' => 'text', 'description' => 'Moneda default'],
            ['key' => 'currency_symbol', 'value' => 'RON', 'group' => 'general', 'type' => 'text', 'description' => 'Simbolul monedei'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('✅ Settings seeded successfully!');
    }
}
