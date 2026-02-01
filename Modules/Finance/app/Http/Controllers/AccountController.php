<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Finance\Models\Account;
use Modules\Finance\Services\AccountService;
use Modules\Finance\Http\Requests\StoreAccountRequest;
use Modules\Finance\Http\Requests\UpdateAccountRequest;

class AccountController extends Controller
{
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * Display a listing of accounts
     */
    public function index(Request $request)
    {
        $query = Account::query();

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        $accounts = $query->orderBy('account_type')->orderBy('created_at', 'desc')->get();
        $stats = $this->accountService->getStatistics();

        return view('finance::accounts.index', compact('accounts', 'stats'));
    }

    /**
     * Show the form for creating a new account
     */
    public function create()
    {
        return view('finance::accounts.create');
    }

    /**
     * Store a newly created account
     */
    public function store(StoreAccountRequest $request)
    {
        try {
            $this->accountService->createAccount($request->validated());

            return redirect()
                ->route('finance.accounts.index')
                ->with('success', __('finance::account.created_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified account
     */
    public function show(Account $account)
    {
        $account->load(['receiptVouchers']);
        
        $summary = $this->accountService->getAccountSummary($account);
        
        // Get recent transactions
        $recentTransactions = $account->receiptVouchers()
            ->with('party')
            ->orderBy('voucher_date', 'desc')
            ->limit(10)
            ->get();

        return view('finance::accounts.show', compact('account', 'summary', 'recentTransactions'));
    }

    /**
     * Show the form for editing the specified account
     */
    public function edit(Account $account)
    {
        return view('finance::accounts.edit', compact('account'));
    }

    /**
     * Update the specified account
     */
    public function update(UpdateAccountRequest $request, Account $account)
    {
        try {
            $this->accountService->updateAccount($account, $request->validated());

            return redirect()
                ->route('finance.accounts.index')
                ->with('success', __('finance::account.updated_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified account
     */
    public function destroy(Account $account)
    {
        try {
            $this->accountService->deleteAccount($account);

            return redirect()
                ->route('finance.accounts.index')
                ->with('success', __('finance::account.deleted_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle account status
     */
    public function toggleStatus(Account $account)
    {
        try {
            $this->accountService->toggleStatus($account);

            return redirect()
                ->back()
                ->with('success', __('finance::account.status_updated'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}