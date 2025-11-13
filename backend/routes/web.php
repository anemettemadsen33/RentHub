<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;

Route::get('/', function () {
    return view('welcome');
});

// Admin Routes
Route::prefix('admin')->group(function () {
    // Public admin routes (login)
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
    
    // Protected admin routes (requires auth)
    Route::middleware(['auth'])->group(function () {
        Route::get('/', [AdminAuthController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/dashboard', [AdminAuthController::class, 'dashboard'])->name('admin.dashboard.index');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
        
        // Add more admin routes here as needed
        Route::get('/users', function() {
            return view('admin.users');
        })->name('admin.users');
        
        Route::get('/properties', function() {
            return view('admin.properties');
        })->name('admin.properties');
        
        Route::get('/bookings', function() {
            return view('admin.bookings');
        })->name('admin.bookings');
        
        Route::get('/settings', function() {
            return view('admin.settings');
        })->name('admin.settings');
    });
});

