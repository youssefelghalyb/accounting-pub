<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Product\Models\Contract;
use Modules\Product\Models\Author;
use Modules\Product\Models\Book;
use Modules\Product\Http\Requests\StoreContractRequest;
use Modules\Product\Http\Requests\UpdateContractRequest;

class ContractController extends Controller
{
    /**
     * Display a listing of contracts.
     */
    public function index(Request $request)
    {
        $query = Contract::with('author', 'book.product');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('author', function($aq) use ($search) {
                    $aq->where('full_name', 'like', "%{$search}%");
                })
                ->orWhereHas('book.product', function($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filter by author
        if ($request->filled('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        // Filter by book
        if ($request->filled('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        // Filter by payment status
        if ($request->filled('status')) {
            if ($request->status === 'paid') {
                $query->whereHas('transactions', function($q) {
                    $q->selectRaw('contract_id, SUM(amount) as total')
                        ->groupBy('contract_id')
                        ->havingRaw('total >= contracts.contract_price');
                });
            } elseif ($request->status === 'pending') {
                $query->whereDoesntHave('transactions');
            }
        }

        $contracts = $query->orderBy('contract_date', 'desc')->get();
        $authors = Author::all();
        $books = Book::with('product')->get();

        // Statistics
        $stats = [
            'total_contracts' => Contract::count(),
            'total_value' => Contract::sum('contract_price'),
            'total_paid' => Contract::all()->sum('total_paid'),
            'outstanding' => Contract::sum('contract_price') - Contract::all()->sum('total_paid'),
        ];

        return view('product::contracts.index', compact('contracts', 'authors', 'books', 'stats'));
    }

    /**
     * Show the form for creating a new contract.
     */
    public function create(Request $request)
    {
        $authors = Author::all();
        $books = Book::with('product')->get();
        $selectedAuthor = $request->get('author_id');
        $selectedBook = $request->get('book_id');

        return view('product::contracts.create', compact('authors', 'books', 'selectedAuthor', 'selectedBook'));
    }

    /**
     * Store a newly created contract.
     */
    public function store(StoreContractRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();

        // Handle file upload
        if ($request->hasFile('contract_file')) {
            $validated['contract_file'] = $request->file('contract_file')->store('contracts', 'public');
        }

        Contract::create($validated);

        return redirect()
            ->route('product.contracts.index')
            ->with('success', __('product::contract.contract_added'));
    }

    /**
     * Display the specified contract.
     */
    public function show($id)
    {
        $contract = Contract::with('author', 'book.product', 'transactions')->findOrFail($id);

        // Get payment history
        $transactions = $contract->transactions()->orderBy('payment_date', 'desc')->get();

        $stats = [
            'contract_price' => $contract->contract_price,
            'total_paid' => $contract->total_paid,
            'outstanding_balance' => $contract->outstanding_balance,
            'payment_percentage' => $contract->payment_percentage,
            'payment_status' => $contract->payment_status,
        ];

        return view('product::contracts.show', compact('contract', 'transactions', 'stats'));
    }

    /**
     * Show the form for editing the specified contract.
     */
    public function edit($id)
    {
        $contract = Contract::findOrFail($id);
        $authors = Author::all();
        $books = Book::with('product')->get();

        return view('product::contracts.edit', compact('contract', 'authors', 'books'));
    }

    /**
     * Update the specified contract.
     */
    public function update(UpdateContractRequest $request, $id)
    {
        $contract = Contract::findOrFail($id);
        $validated = $request->validated();
        $validated['edited_by'] = Auth::id();

        // Handle file upload
        if ($request->hasFile('contract_file')) {
            // Delete old file
            if ($contract->contract_file) {
                Storage::disk('public')->delete($contract->contract_file);
            }
            $validated['contract_file'] = $request->file('contract_file')->store('contracts', 'public');
        }

        $contract->update($validated);

        return redirect()
            ->route('product.contracts.index')
            ->with('success', __('product::contract.contract_updated'));
    }

    /**
     * Remove the specified contract.
     */
    public function destroy($id)
    {
        $contract = Contract::findOrFail($id);

        // Check if contract has transactions
        if ($contract->transactions()->count() > 0) {
            return redirect()
                ->route('product.contracts.index')
                ->with('error', __('product::contract.cannot_delete_has_transactions'));
        }

        // Delete contract file if exists
        if ($contract->contract_file) {
            Storage::disk('public')->delete($contract->contract_file);
        }

        $contract->delete();

        return redirect()
            ->route('product.contracts.index')
            ->with('success', __('product::contract.contract_deleted'));
    }
}
