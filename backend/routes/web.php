<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// CRITICAL: This route is required by Laravel's default Authenticate middleware
// It redirects unauthenticated users to Filament's login page
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

// ALL /admin routes are handled by Filament Panel Provider
// Do NOT add custom routes - Filament manages all admin routing and authentication


