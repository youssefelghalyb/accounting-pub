<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Product\Http\Requests\StoreContractRequest;
use Modules\Product\Http\Requests\UpdateContractRequest;
use Modules\Product\Models\Author;
use Modules\Product\Models\Book;
use Modules\Product\Models\Contract;
use Modules\Product\Services\ContractService;

class ContractController extends Controller
{
    public function __construct(private ContractService $contractService)
    {
    }

    public function index(Request $request)
    {
        $query = Contract::with('authors', 'book.product');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('authors', function ($aq) use ($search) {
                    $aq->where('full_name', 'like', "%{$search}%");
                })->orWhereHas('book.product', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('author_id')) {
            $query->whereHas('authors', function ($q) use ($request) {
                $q->where('authors.id', $request->author_id);
            });
        }

        if ($request->filled('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'paid'    => $query->fullyPaid(),
                'pending' => $query->pending(),
                default   => null,
            };
        }

        $contracts = $query->orderBy('contract_date', 'desc')->get();
        $authors   = Author::orderBy('full_name')->get();
        $books     = Book::with('product')->get();

        $stats = [
            'total_contracts' => Contract::count(),
            'total_value'     => Contract::sum('contract_price'),
            'total_paid'      => Contract::all()->sum('total_paid'),
            'outstanding'     => Contract::sum('contract_price') - Contract::all()->sum('total_paid'),
        ];

        return view('product::contracts.index', compact('contracts', 'authors', 'books', 'stats'));
    }

    public function create(Request $request)
    {
        $authors         = Author::orderBy('full_name')->get();
        $books           = Book::with('product')->get();
        $selectedAuthor  = $request->get('author_id');
        $selectedBook    = $request->get('book_id');

        return view('product::contracts.create', compact('authors', 'books', 'selectedAuthor', 'selectedBook'));
    }

    public function store(StoreContractRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();

        $authorIds        = $validated['author_ids'];
        $representativeId = (int) $validated['representative_id'];

        unset($validated['author_ids'], $validated['representative_id']);

        $this->contractService->createContract(
            data:             $validated,
            authorIds:        $authorIds,
            representativeId: $representativeId,
            file:             $request->file('contract_file'),
        );

        return redirect()
            ->route('product.contracts.index')
            ->with('success', __('product::contract.contract_added'));
    }

    public function show($id)
    {
        $contract     = Contract::with('authors', 'book.product', 'transactions')->findOrFail($id);
        $transactions = $contract->transactions()->orderBy('payment_date', 'desc')->get();

        $stats = [
            'contract_price'      => $contract->contract_price,
            'total_paid'          => $contract->total_paid,
            'outstanding_balance' => $contract->outstanding_balance,
            'payment_percentage'  => $contract->payment_percentage,
            'payment_status'      => $contract->payment_status,
        ];

        return view('product::contracts.show', compact('contract', 'transactions', 'stats'));
    }

    public function edit($id)
    {
        $contract = Contract::with('authors')->findOrFail($id);
        $authors  = Author::orderBy('full_name')->get();
        $books    = Book::with('product')->get();

        return view('product::contracts.edit', compact('contract', 'authors', 'books'));
    }

    public function update(UpdateContractRequest $request, $id)
    {
        $contract  = Contract::findOrFail($id);
        $validated = $request->validated();
        $validated['edited_by'] = Auth::id();

        $authorIds        = $validated['author_ids'];
        $representativeId = (int) $validated['representative_id'];

        unset($validated['author_ids'], $validated['representative_id']);

        $this->contractService->updateContract(
            contract:         $contract,
            data:             $validated,
            authorIds:        $authorIds,
            representativeId: $representativeId,
            file:             $request->file('contract_file'),
        );

        return redirect()
            ->route('product.contracts.index')
            ->with('success', __('product::contract.contract_updated'));
    }

    public function destroy($id)
    {
        $contract = Contract::findOrFail($id);

        try {
            $this->contractService->deleteContract($contract);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('product.contracts.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('product.contracts.index')
            ->with('success', __('product::contract.contract_deleted'));
    }
}