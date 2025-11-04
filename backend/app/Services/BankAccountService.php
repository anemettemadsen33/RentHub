<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Property;
use App\Models\User;

class BankAccountService
{
    /**
     * Get default bank account for a user (owner/agent)
     */
    public function getDefaultForUser(int $userId): ?BankAccount
    {
        return BankAccount::where('user_id', $userId)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Get any active bank account for a user
     */
    public function getAnyActiveForUser(int $userId): ?BankAccount
    {
        return BankAccount::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get default company bank account (fallback)
     */
    public function getCompanyDefault(): ?BankAccount
    {
        return BankAccount::whereNull('user_id')
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Get any active company bank account
     */
    public function getAnyCompanyAccount(): ?BankAccount
    {
        return BankAccount::whereNull('user_id')
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get bank account for a property (owner's account or company)
     */
    public function getForProperty(int $propertyId): ?BankAccount
    {
        $property = Property::find($propertyId);

        if (! $property) {
            return null;
        }

        // Try to get owner's default account
        if ($property->user_id) {
            $account = $this->getDefaultForUser($property->user_id);

            if ($account) {
                return $account;
            }

            // Try any active account of the owner
            $account = $this->getAnyActiveForUser($property->user_id);

            if ($account) {
                return $account;
            }
        }

        // Fallback to company default account
        $account = $this->getCompanyDefault();

        if ($account) {
            return $account;
        }

        // Last resort: any company account
        return $this->getAnyCompanyAccount();
    }

    /**
     * Get all active accounts for a user
     */
    public function getActiveAccountsForUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return BankAccount::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('account_name')
            ->get();
    }

    /**
     * Get all company accounts
     */
    public function getCompanyAccounts(): \Illuminate\Database\Eloquent\Collection
    {
        return BankAccount::whereNull('user_id')
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('account_name')
            ->get();
    }

    /**
     * Set an account as default for user
     */
    public function setAsDefault(BankAccount $account): bool
    {
        try {
            $account->setAsDefault();

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to set bank account as default', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Validate if account can be used for invoicing
     */
    public function validateForInvoicing(BankAccount $account): array
    {
        $errors = [];

        if (! $account->is_active) {
            $errors[] = 'Bank account is not active';
        }

        if (empty($account->iban)) {
            $errors[] = 'IBAN is required';
        }

        if (empty($account->bic_swift)) {
            $errors[] = 'BIC/SWIFT code is required';
        }

        if (empty($account->bank_name)) {
            $errors[] = 'Bank name is required';
        }

        if (empty($account->account_holder_name)) {
            $errors[] = 'Account holder name is required';
        }

        return $errors;
    }

    /**
     * Check if account is valid for invoicing
     */
    public function isValidForInvoicing(BankAccount $account): bool
    {
        return empty($this->validateForInvoicing($account));
    }
}
