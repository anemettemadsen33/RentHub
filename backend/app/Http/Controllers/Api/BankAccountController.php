<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BankAccountController extends Controller
{
    /**
     * Get all bank accounts for authenticated user
     */
    public function index()
    {
        $accounts = auth()->user()->bankAccounts()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'accounts' => $accounts,
        ]);
    }

    /**
     * Store a new bank account
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

        try {
            $account = auth()->user()->bankAccounts()->create([
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
            if (auth()->user()->bankAccounts()->count() === 1) {
                $account->setAsDefault();
            }

            Log::info('Bank account created', [
                'account_id' => $account->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Bank account added successfully',
                'account' => $account->fresh(),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Bank account creation failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to add bank account'
            ], 500);
        }
    }

    /**
     * Update bank account
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

            Log::info('Bank account updated', [
                'account_id' => $account->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Bank account updated successfully',
                'account' => $account->fresh(),
            ]);
        } catch (\Exception $e) {
            Log::error('Bank account update failed', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to update bank account'
            ], 500);
        }
    }

    /**
     * Delete bank account
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

            Log::info('Bank account deleted', [
                'account_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Bank account deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Bank account deletion failed', [
                'account_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to delete bank account'
            ], 500);
        }
    }

    /**
     * Get default bank account for guest (for payment instructions)
     */
    public function getForPayment($paymentId)
    {
        $payment = \App\Models\Payment::with('booking.property.user.bankAccounts')->findOrFail($paymentId);

        // Get host's default bank account
        $account = $payment->booking->property->user->bankAccounts()
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();

        if (!$account) {
            return response()->json([
                'error' => 'Host has not configured bank account yet'
            ], 400);
        }

        return response()->json([
            'account' => $account,
            'payment' => $payment,
            'instructions' => [
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'reference' => $payment->payment_number,
                'note' => 'Please include the payment number in your transfer reference',
            ],
        ]);
    }
}
