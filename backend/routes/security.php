<?php

use App\Http\Controllers\API\Security\APIKeyController;
use App\Http\Controllers\API\Security\GDPRController;
use App\Http\Controllers\API\Security\OAuth2Controller;
use App\Http\Controllers\API\Security\SecurityAuditController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Security API Routes
|--------------------------------------------------------------------------
*/

// OAuth 2.0 Routes
Route::prefix('oauth')->group(function () {
    Route::post('/authorize', [OAuth2Controller::class, 'authorize'])->middleware('auth:sanctum');
    Route::post('/token', [OAuth2Controller::class, 'token']);
    Route::post('/revoke', [OAuth2Controller::class, 'revoke'])->middleware('auth:sanctum');
    Route::post('/introspect', [OAuth2Controller::class, 'introspect'])->middleware('auth:sanctum');
});

// API Key Management
Route::prefix('api-keys')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [APIKeyController::class, 'index']);
    Route::post('/', [APIKeyController::class, 'store']);
    Route::delete('/{keyId}', [APIKeyController::class, 'destroy']);
    Route::post('/{keyId}/rotate', [APIKeyController::class, 'rotate']);
});

// GDPR & Data Privacy
Route::prefix('gdpr')->middleware('auth:sanctum')->group(function () {
    Route::post('/export', [GDPRController::class, 'exportData']);
    Route::post('/delete', [GDPRController::class, 'requestDeletion']);
    Route::post('/cancel-deletion', [GDPRController::class, 'cancelDeletion']);

    Route::get('/consents', [GDPRController::class, 'getConsents']);
    Route::post('/consents', [GDPRController::class, 'grantConsent']);
    Route::delete('/consents', [GDPRController::class, 'revokeConsent']);
});

// Security Audit & Monitoring
Route::prefix('security')->middleware('auth:sanctum')->group(function () {
    Route::get('/audit-trail', [SecurityAuditController::class, 'getUserAuditTrail']);
    Route::get('/incidents', [SecurityAuditController::class, 'getSecurityIncidents']);
    Route::post('/scan', [SecurityAuditController::class, 'runVulnerabilityScan']);
    Route::get('/report', [SecurityAuditController::class, 'getSecurityReport']);
});
