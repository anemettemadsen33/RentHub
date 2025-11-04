<?php

namespace App\Providers;

use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Property;
use App\Observers\BlockedDateObserver;
use App\Observers\BookingObserver;
use App\Observers\PropertyObserver;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Smart Lock Service as singleton
        $this->app->singleton(\App\Services\SmartLock\SmartLockService::class, function ($app) {
            $service = new \App\Services\SmartLock\SmartLockService;

            // Register available providers
            $service->registerProvider('mock', new \App\Services\SmartLock\Providers\MockSmartLockProvider);
            $service->registerProvider('generic', new \App\Services\SmartLock\Providers\GenericWebhookProvider);

            // Additional providers can be registered here
            // $service->registerProvider('august', new \App\Services\SmartLock\Providers\AugustProvider());
            // $service->registerProvider('yale', new \App\Services\SmartLock\Providers\YaleProvider());

            return $service;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        // Booking and BlockedDate observers temporarily disabled to avoid Google Calendar dependency on boot
        // Uncomment after Google API client is properly configured and autoloaded
        // Booking::observe(BookingObserver::class);
        // BlockedDate::observe(BlockedDateObserver::class);

        Property::observe(PropertyObserver::class);

        // Add socialite event listeners
        $this->app->make(SocialiteFactory::class)
            ->extend('google', function ($app) {
                $config = $app['config']['services.google'];

                return \SocialiteProviders\Manager\OAuth2\User::class;
            });

        $this->app->make(SocialiteFactory::class)
            ->extend('facebook', function ($app) {
                $config = $app['config']['services.facebook'];

                return \SocialiteProviders\Manager\OAuth2\User::class;
            });
    }
}
