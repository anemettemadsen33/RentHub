<?php

namespace App\Providers;

use App\Http\Controllers\Api\OptimizedAuthController;
use App\Repositories\CachedUserRepository;
use Illuminate\Support\ServiceProvider;

class OptimizedAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the cached user repository as singleton
        $this->app->singleton(CachedUserRepository::class, function ($app) {
            return new CachedUserRepository();
        });
        
        // Register the optimized auth controller
        $this->app->bind(OptimizedAuthController::class, function ($app) {
            return new OptimizedAuthController(
                $app->make(CachedUserRepository::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}