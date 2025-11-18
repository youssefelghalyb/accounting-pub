<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Product\Models\ContractTransaction;
use Modules\Product\Models\Contract;
use Modules\Product\Http\Requests\StoreTransactionRequest;
use Modules\Product\Http\Requests\UpdateTransactionRequest;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        $query = ContractTransaction::with('contract.author', 'contract.book.product');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                  ->orWhereHas('contract.author', function($aq) use ($search) {
                      $aq->where('full_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by contract
        if ($request->filled('contract_id')) {
            $query->where('contract_id', $request->contract_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('payment_date', 'desc')->get();
        $contracts = Contract::with('author', 'book.product')->get();

        // Statistics
        $stats = [
            'total_transactions' => ContractTransaction::count(),
            'total_amount' => ContractTransaction::sum('amount'),
            'this_month_amount' => ContractTransaction::thisMonth()->sum('amount'),
            'this_year_amount' => ContractTransaction::thisYear()->sum('amount'),
        ];

        return view('product::transactions.index', compact('transactions', 'contracts', 'stats'));
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create(Request $request)
    {
        $contracts = Contract::with('author', 'book.product')->get();
        $selectedContract = $request->get('contract_id');

        // Get contract details if selected
        $contractDetails = null;
        if ($selectedContract) {
            $contract = Contract::find($selectedContract);
            if ($contract) {
                $contractDetails = [
                    'contract_price' => $contract->contract_price,
                    'total_paid' => $contract->total_paid,
                    'outstanding_balance' => $contract->outstanding_balance,
                ];
            }
        }

        return view('product::transactions.create', compact('contracts', 'selectedContract', 'contractDetails'));
    }

    /**
     * Store a newly created transaction.
     */
    public function store(StoreTransactionRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();

        // Handle file upload
        if ($request->hasFile('receipt_file')) {
            $validated['receipt_file'] = $request->file('receipt_file')->store('receipts', 'public');
        }

        ContractTransaction::create($validated);

        return redirect()
            ->route('product.transactions.index')
            ->with('success', __('product::transaction.transaction_added'));
    }

    /**
     * Display the specified transaction.
     */
    public function show($id)
    {
        $transaction = ContractTransaction::with('contract.author', 'contract.book.product')
            ->findOrFail($id);

        return view('product::transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit($id)
    {
        $transaction = ContractTransaction::findOrFail($id);
        $contracts = Contract::with('author', 'book.product')->get();

        return view('product::transactions.edit', compact('transaction', 'contracts'));
    }

    /**
     * Update the specified transaction.
     */
    public function update(UpdateTransactionRequest $request, $id)
    {
        $transaction = ContractTransaction::findOrFail($id);
        $validated = $request->validated();
        $validated['edited_by'] = Auth::id();

        // Handle file upload
        if ($request->hasFile('receipt_file')) {
            // Delete old file
            if ($transaction->receipt_file) {
                Storage::disk('public')->delete($transaction->receipt_file);
            }
            $validated['receipt_file'] = $request->file('receipt_file')->store('receipts', 'public');
        }

        $transaction->update($validated);

        return redirect()
            ->route('product.transactions.index')
            ->with('success', __('product::transaction.transaction_updated'));
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy($id)
    {
        $transaction = ContractTransaction::findOrFail($id);

        // Delete receipt file if exists
        if ($transaction->receipt_file) {
            Storage::disk('public')->delete($transaction->receipt_file);
        }

        $transaction->delete();

        return redirect()
            ->route('product.transactions.index')
            ->with('success', __('product::transaction.transaction_deleted'));
    }
}
