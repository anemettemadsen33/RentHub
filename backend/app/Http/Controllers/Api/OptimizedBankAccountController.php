<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OptimizedBankAccountController extends Controller
{
    private const CACHE_TTL = 3600; // 1 hour
    private const LIST_CACHE_TTL = 900; // 15 minutes

    /**
     * Get all bank accounts for authenticated user with caching
     */
    public function index()
    {
        $user = auth()->user();
        $cacheKey = "user_bank_accounts_{$user->id}";

        // Check cache first
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $startTime = microtime(true);

        // Optimized query with selective fields
        $accounts = $user->bankAccounts()
            ->select([
                'id', 'user_id', 'bank_name', 'account_name', 'account_holder_name',
                'iban', 'bic_swift', 'currency', 'account_type', 'is_default',
                'is_active', 'created_at', 'updated_at'
            ])
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $response = [
            'success' => true,
            'accounts' => $accounts,
            'total' => $accounts->count(),
            'meta' => [
                'query_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'cached' => false,
            ],
        ];

        // Cache the response
        Cache::put($cacheKey, $response, self::LIST_CACHE_TTL);

        return response()->json($response);
    }

    /**
     * Store a new bank account with validation and caching
     */
    public function store(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'iban' => 'required|string|max:34|unique:bank_accounts,iban',
            'bic_swift' => 'required|string|max:11',
            'bank_address' => 'nullable|string|max:500',
            'currency' => 'nullable|string|max:3',
            'account_type' => 'nullable|string|in:business,personal',
            'notes' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        $user = auth()->user();
        $startTime = microtime(true);

        try {
            $account = $user->bankAccounts()->create([
                'bank_name' => $request->bank_name,
                'account_name' => $request->account_name,
                'account_holder_name' => $request->account_holder_name,
                'iban' => $request->iban,
                'bic_swift' => $request->bic_swift,
                'bank_address' => $request->bank_address,
                'currency' => $request->currency ?? 'RON',
                'account_type' => $request->account_type ?? 'business',
                'notes' => $request->notes,
                'is_default' => $request->is_default ?? false,
                'is_active' => true,
            ]);

            // If set as default, unset others
            if ($account->is_default) {
                $account->setAsDefault();
            }

            // If this is the first account, make it default
            if ($user->bankAccounts()->count() === 1) {
                $account->setAsDefault();
            }

            // Clear user accounts cache
            Cache::forget("user_bank_accounts_{$user->id}");

            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('Bank account created successfully', [
                'account_id' => $account->id,
                'user_id' => $user->id,
                'is_default' => $account->is_default,
                'processing_time_ms' => $processingTime,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bank account added successfully',
                'account' => $account->fresh(['user:id,name,email']),
                'processing_time_ms' => $processingTime,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Bank account creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to add bank account',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update bank account with validation and caching
     */
    public function update(Request $request, $id)
    {
        $account = BankAccount::findOrFail($id);

        // Check ownership
        if ($account->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'bank_name' => 'string|max:255',
            'account_name' => 'string|max:255',
            'account_holder_name' => 'string|max:255',
            'iban' => 'string|max:34|unique:bank_accounts,iban,' . $account->id,
            'bic_swift' => 'string|max:11',
            'bank_address' => 'nullable|string|max:500',
            'currency' => 'string|max:3',
            'account_type' => 'string|in:business,personal',
            'notes' => 'nullable|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $startTime = microtime(true);

        try {
            $account->update($request->only([
                'bank_name',
                'account_name',
                'account_holder_name',
                'iban',
                'bic_swift',
                'bank_address',
                'currency',
                'account_type',
                'notes',
                'is_active',
            ]));

            // Handle default status
            if ($request->has('is_default') && $request->is_default) {
                $account->setAsDefault();
            }

            // Clear user accounts cache
            Cache::forget("user_bank_accounts_{$account->user_id}");
            Cache::forget("default_bank_account");
            Cache::forget("user_default_bank_{$account->user_id}");

            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('Bank account updated successfully', [
                'account_id' => $account->id,
                'user_id' => auth()->id(),
                'is_default' => $account->is_default,
                'processing_time_ms' => $processingTime,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bank account updated successfully',
                'account' => $account->fresh(),
                'processing_time_ms' => $processingTime,
            ]);

        } catch (\Exception $e) {
            Log::error('Bank account update failed', [
                'account_id' => $account->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to update bank account',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete bank account with proper cleanup
     */
    public function destroy($id)
    {
        $account = BankAccount::findOrFail($id);

        // Check ownership
        if ($account->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $wasDefault = $account->is_default;
            $userId = $account->user_id;
            
            $account->delete();

            // If deleted account was default, set another as default
            if ($wasDefault) {
                $nextAccount = BankAccount::where('user_id', $userId)
                    ->where('is_active', true)
                    ->first();
                
                if ($nextAccount) {
                    $nextAccount->setAsDefault();
                }
            }

            // Clear user accounts cache
            Cache::forget("user_bank_accounts_{$userId}");
            Cache::forget("default_bank_account");
            Cache::forget("user_default_bank_{$userId}");

            Log::info('Bank account deleted successfully', [
                'account_id' => $id,
                'user_id' => auth()->id(),
                'was_default' => $wasDefault,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bank account deleted successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Bank account deletion failed', [
                'account_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to delete bank account',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get default bank account for payment with caching
     */
    public function getForPayment($paymentId)
    {
        $cacheKey = "payment_bank_account_{$paymentId}";

        // Check cache first
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $startTime = microtime(true);

        try {
            $payment = \App\Models\Payment::with([
                'booking.property.user.bankAccounts' => function ($query) {
                    $query->where('is_default', true)
                        ->where('is_active', true)
                        ->select([
                            'id', 'user_id', 'bank_name', 'account_name', 'account_holder_name',
                            'iban', 'bic_swift', 'currency', 'is_default', 'is_active'
                        ]);
                }
            ])->findOrFail($paymentId);

            // Get host's default bank account
            $account = $payment->booking->property->user->bankAccounts->first();

            if (!$account) {
                // Check for system default account
                $account = Cache::remember("default_bank_account", self::CACHE_TTL, function () {
                    return BankAccount::where('is_default', true)
                        ->whereNull('user_id')
                        ->where('is_active', true)
                        ->first();
                });

                if (!$account) {
                    return response()->json([
                        'error' => 'Host has not configured bank account yet'
                    ], 400);
                }
            }

            $response = [
                'success' => true,
                'account' => $account,
                'payment' => [
                    'id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                ],
                'instructions' => [
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'reference' => $payment->payment_number,
                    'note' => 'Please include the payment number in your transfer reference',
                    'bank_name' => $account->bank_name,
                    'account_holder' => $account->account_holder_name,
                    'iban' => $account->iban,
                    'bic_swift' => $account->bic_swift,
                ],
                'meta' => [
                    'query_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                    'cached' => false,
                ],
            ];

            Cache::put($cacheKey, $response, self::CACHE_TTL);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Payment bank account retrieval failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to retrieve bank account information',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get bank account statistics
     */
    public function statistics()
    {
        $user = auth()->user();
        $cacheKey = "user_bank_statistics_{$user->id}";

        // Check cache first
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        try {
            $totalAccounts = $user->bankAccounts()->count();
            $activeAccounts = $user->bankAccounts()->where('is_active', true)->count();
            $defaultAccount = $user->bankAccounts()->where('is_default', true)->first();

            $response = [
                'success' => true,
                'statistics' => [
                    'total_accounts' => $totalAccounts,
                    'active_accounts' => $activeAccounts,
                    'inactive_accounts' => $totalAccounts - $activeAccounts,
                    'has_default_account' => $defaultAccount !== null,
                    'default_account_id' => $defaultAccount?->id,
                ],
                'accounts_by_currency' => $user->bankAccounts()
                    ->selectRaw('currency, COUNT(*) as count')
                    ->groupBy('currency')
                    ->pluck('count', 'currency'),
                'accounts_by_type' => $user->bankAccounts()
                    ->selectRaw('account_type, COUNT(*) as count')
                    ->groupBy('account_type')
                    ->pluck('count', 'account_type'),
            ];

            Cache::put($cacheKey, $response, self::LIST_CACHE_TTL);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Bank account statistics failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to retrieve bank account statistics',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}