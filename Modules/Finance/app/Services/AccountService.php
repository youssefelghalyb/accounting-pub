<?php

namespace Modules\Finance\Services;

use Modules\Finance\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AccountService
{
    /**
     * Create a new account
     */
    public function createAccount(array $data): Account
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = Auth::id();
            
            // Clear bank fields if account type is cash
            if ($data['account_type'] === 'cash') {
                $data['bank_name'] = null;
                $data['branch_name'] = null;
                $data['swift_code'] = null;
                $data['iban'] = null;
                $data['account_number'] = null;
            }
            
            return Account::create($data);
        });
    }

    /**
     * Update an existing account
     */
    public function updateAccount(Account $account, array $data): Account
    {
        DB::transaction(function () use ($account, $data) {
            $data['edited_by'] = Auth::id();
            
            // Clear bank fields if account type is cash
            if ($data['account_type'] === 'cash') {
                $data['bank_name'] = null;
                $data['branch_name'] = null;
                $data['swift_code'] = null;
                $data['iban'] = null;
                $data['account_number'] = null;
            }
            
            $account->update($data);
        });

        return $account->refresh();
    }

    /**
     * Delete an account (with validation)
     */
    public function deleteAccount(Account $account): bool
    {
        // Check if account has any transactions
        if ($account->receiptVouchers()->exists()) {
            throw new \Exception(__('finance::account.cannot_delete_has_transactions'));
        }

        return $account->delete();
    }

    /**
     * Toggle account active status
     */
    public function toggleStatus(Account $account): Account
    {
        $account->update([
            'is_active' => !$account->is_active,
            'edited_by' => Auth::id(),
        ]);

        return $account->refresh();
    }

    /**
     * Get account statistics
     */
    public function getStatistics(): array
    {
        $allAccounts = Account::all();
        
        return [
            'total_accounts' => $allAccounts->count(),
            'cash_accounts' => $allAccounts->where('account_type', 'cash')->count(),
            'bank_accounts' => $allAccounts->where('account_type', 'bank')->count(),
            'active_accounts' => Account::active()->count(),
            'total_balance' => $allAccounts->sum('current_balance'),
            'total_cash_balance' => $allAccounts->where('account_type', 'cash')->sum('current_balance'),
            'total_bank_balance' => $allAccounts->where('account_type', 'bank')->sum('current_balance'),
        ];
    }

    /**
     * Get account transaction summary
     */
    public function getAccountSummary(Account $account): array
    {
        return [
            'opening_balance' => $account->opening_balance,
            'total_receipts' => $account->total_receipts,
            'total_payments' => $account->total_payments,
            'current_balance' => $account->current_balance,
            'transaction_count' => $account->receiptVouchers()->count(),
        ];
    }

    /**
     * Get accounts for dropdown (active only)
     */
    public function getActiveAccountsForSelect(): array
    {
        return Account::active()
            ->orderBy('account_type')
            ->orderBy('account_name')
            ->get()
            ->map(function($account) {
                return [
                    'value' => $account->id,
                    'label' => $account->display_name . ' - ' . number_format($account->current_balance, 2),
                    'type' => $account->account_type,
                ];
            })
            ->toArray();
    }
}