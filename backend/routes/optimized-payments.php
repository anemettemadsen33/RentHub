<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OptimizedPaymentController;
use App\Http\Controllers\Api\OptimizedPaymentProofController;
use App\Http\Controllers\Api\OptimizedBankAccountController;

/*
|--------------------------------------------------------------------------
| Optimized Payment API Routes
|--------------------------------------------------------------------------
|
| These routes handle payment processing with improved performance
| and caching mechanisms for the bank transfer payment system.
|
*/

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    
    // Payment routes
    Route::prefix('payments')->group(function () {
        // List user payments with caching
        Route::get('/', [OptimizedPaymentController::class, 'index'])
            ->name('payments.index');
        
        // Create new payment with optimized processing
        Route::post('/', [OptimizedPaymentController::class, 'store'])
            ->name('payments.store');
        
        // Get payment details with caching
        Route::get('/{payment}', [OptimizedPaymentController::class, 'show'])
            ->name('payments.show');
        
        // Update payment status with validation
        Route::put('/{payment}/status', [OptimizedPaymentController::class, 'updateStatus'])
            ->name('payments.updateStatus');
        
        // Confirm payment completion
        Route::post('/{payment}/confirm', [OptimizedPaymentController::class, 'confirm'])
            ->name('payments.confirm');
        
        // Process payment refund
        Route::post('/{payment}/refund', [OptimizedPaymentController::class, 'refund'])
            ->name('payments.refund');
    });
    
    // Payment proof routes
    Route::prefix('payment-proofs')->group(function () {
        // Upload payment proof with optimized processing
        Route::post('/{paymentId}', [OptimizedPaymentProofController::class, 'upload'])
            ->name('payment-proofs.upload');
        
        // Verify payment proof (host only)
        Route::post('/{proofId}/verify', [OptimizedPaymentProofController::class, 'verify'])
            ->name('payment-proofs.verify');
        
        // Get payment proofs for a payment
        Route::get('/payment/{paymentId}', [OptimizedPaymentProofController::class, 'index'])
            ->name('payment-proofs.index');
        
        // Download payment proof
        Route::get('/{proofId}/download', [OptimizedPaymentProofController::class, 'download'])
            ->name('payment-proofs.download');
        
        // Get pending proofs for host
        Route::get('/pending/host', [OptimizedPaymentProofController::class, 'pendingForHost'])
            ->name('payment-proofs.pendingForHost');
        
        // Bulk verify payment proofs
        Route::post('/bulk/verify', [OptimizedPaymentProofController::class, 'bulkVerify'])
            ->name('payment-proofs.bulkVerify');
    });
    
    // Bank account routes
    Route::prefix('bank-accounts')->group(function () {
        // Get user bank accounts with caching
        Route::get('/', [OptimizedBankAccountController::class, 'index'])
            ->name('bank-accounts.index');
        
        // Create new bank account
        Route::post('/', [OptimizedBankAccountController::class, 'store'])
            ->name('bank-accounts.store');
        
        // Update bank account
        Route::put('/{id}', [OptimizedBankAccountController::class, 'update'])
            ->name('bank-accounts.update');
        
        // Delete bank account
        Route::delete('/{id}', [OptimizedBankAccountController::class, 'destroy'])
            ->name('bank-accounts.destroy');
        
        // Get bank account for payment instructions
        Route::get('/payment/{paymentId}', [OptimizedBankAccountController::class, 'getForPayment'])
            ->name('bank-accounts.getForPayment');
        
        // Get bank account statistics
        Route::get('/statistics', [OptimizedBankAccountController::class, 'statistics'])
            ->name('bank-accounts.statistics');
    });
    
    // Payment methods route (for backward compatibility)
    Route::get('/payment-methods', function () {
        return response()->json([
            'payment_methods' => [
                [
                    'id' => 1,
                    'name' => 'Bank Transfer',
                    'type' => 'bank_transfer',
                    'description' => 'Pay via bank transfer with PDF invoice',
                    'processing_time' => '1-3 business days',
                    'fees' => 0,
                    'is_active' => true,
                    'requires_proof' => true,
                ],
                [
                    'id' => 2,
                    'name' => 'Cash Payment',
                    'type' => 'cash',
                    'description' => 'Pay in cash upon arrival',
                    'processing_time' => 'Instant',
                    'fees' => 0,
                    'is_active' => true,
                    'requires_proof' => false,
                ],
            ],
        ]);
    })->name('payment-methods');
    
});

// Public routes for payment processing (if needed)
Route::prefix('payments')->middleware(['throttle:60,1'])->group(function () {
    // Add any public payment routes here if needed
});