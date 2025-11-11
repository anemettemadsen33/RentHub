<?php

namespace App\Providers;

use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Setting;
use App\Observers\BlockedDateObserver;
use App\Observers\BookingObserver;
use App\Observers\PropertyObserver;
use App\Policies\SettingPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        // Register policies
        Gate::policy(Setting::class, SettingPolicy::class);
        Gate::policy(\App\Models\SavedSearch::class, \App\Policies\SavedSearchPolicy::class);
        Gate::policy(\App\Models\NotificationPreference::class, \App\Policies\NotificationPreferencePolicy::class);
        Gate::policy(\App\Models\Wishlist::class, \App\Policies\WishlistPolicy::class);
    Gate::policy(Property::class, \App\Policies\PropertyPolicy::class);

        // Register gates for dashboard access
        Gate::define('view-owner-dashboard', [\App\Policies\DashboardPolicy::class, 'viewOwnerDashboard']);
        Gate::define('view-tenant-dashboard', [\App\Policies\DashboardPolicy::class, 'viewTenantDashboard']);
        Gate::define('view-admin-dashboard', [\App\Policies\DashboardPolicy::class, 'viewAdminDashboard']);
        Gate::define('view-analytics', [\App\Policies\DashboardPolicy::class, 'viewAnalytics']);
        Gate::define('export-data', [\App\Policies\DashboardPolicy::class, 'exportData']);

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

        // API Rate Limiting
        RateLimiter::for('api', function (Request $request) {
            return [
                Limit::perMinute(300)->by(optional($request->user())->id ?: $request->ip()),
            ];
        });

        // Stricter limits for authentication endpoints
        RateLimiter::for('auth', function (Request $request) {
            $key = md5(strtolower((string) $request->input('email')) . '|' . $request->ip());
            return [
                Limit::perMinute(20)->by($key)->response(function () {
                    return response()->json([
                        'message' => 'Too many authentication attempts. Please try again later.'
                    ], 429);
                }),
            ];
        });

        // Map/search endpoints can be noisy – moderate them
        RateLimiter::for('search', function (Request $request) {
            return [
                Limit::perMinute(120)->by($request->ip()),
            ];
        });

        // Property comparison actions – lightweight but prevent abuse
        RateLimiter::for('comparison', function (Request $request) {
            return [
                Limit::perMinute(180)->by(optional($request->user())->id ?: $request->ip()),
            ];
        });
    }
}
