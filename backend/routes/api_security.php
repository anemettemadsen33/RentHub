<?php

use App\Http\Controllers\Api\PerformanceController;
use App\Http\Controllers\Api\SecurityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Security & Performance API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {

    // GDPR & Data Privacy Routes
    Route::prefix('security')->group(function () {
        Route::get('/data-export', [SecurityController::class, 'requestDataExport']);
        Route::post('/data-deletion', [SecurityController::class, 'requestDataDeletion']);
        Route::get('/audit-log', [SecurityController::class, 'getAuditLog']);
    });

    // Session Management Routes
    Route::prefix('sessions')->group(function () {
        Route::get('/', [SecurityController::class, 'getActiveSessions']);
        Route::delete('/{sessionId}', [SecurityController::class, 'revokeSession']);
    });

    // API Key Management Routes
    Route::prefix('api-keys')->group(function () {
        Route::get('/', [SecurityController::class, 'listApiKeys']);
        Route::post('/', [SecurityController::class, 'generateApiKey']);
        Route::delete('/{keyId}', [SecurityController::class, 'revokeApiKey']);
    });

    // Performance Monitoring Routes (Admin only)
    Route::middleware(['role:admin'])->prefix('monitoring')->group(function () {
        Route::get('/metrics', [PerformanceController::class, 'getMetrics']);
        Route::get('/health', [PerformanceController::class, 'getHealth']);
        Route::get('/slow-queries', [PerformanceController::class, 'getSlowQueries']);
        Route::get('/cache-stats', [PerformanceController::class, 'getCacheStats']);
    });
});

// Public health check endpoint
Route::get('/health', [PerformanceController::class, 'getHealth']);
