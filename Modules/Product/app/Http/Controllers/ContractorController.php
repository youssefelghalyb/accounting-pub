<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Finance\Models\Account;
use Modules\Product\Http\Requests\StoreContractorGiftRequest;
use Modules\Product\Http\Requests\StoreContractorRequest;
use Modules\Product\Http\Requests\StoreContractTransactionRequest;
use Modules\Product\Http\Requests\UpdateContractorRequest;
use Modules\Product\Models\Contractor;
use Modules\Product\Models\ContractorBook;
use Modules\Product\Models\ContractTransaction;
use Modules\Warehouse\Models\SubWarehouse;
use Modules\Product\Services\ContractorGiftService;
use Modules\Product\Services\ContractorService;
use Modules\Product\Services\ContractTransactionService;

class ContractorController extends Controller
{
    public function __construct(
        private ContractorService $contractorService,
        private ContractTransactionService $contractTransactionService,
        private ContractorGiftService $contractorGiftService,
    ) {}

    public function index(Request $request)
    {
        $query = Contractor::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nationality', 'like', "%{$search}%");
            });
        }

        $contractors = $query->withCount('contractorBooks')
            ->orderBy('name')
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        $stats = [
            'total_contractors' => Contractor::count(),
            'total_contracted_books' => ContractorBook::count(),
            'total_royalty_accrued' => \Modules\Product\Models\BookSale::where('is_gift', false)->sum('contractor_profit'),
        ];

        return view('product::contractors.index', compact('contractors', 'stats'));
    }

    public function create()
    {
        return view('product::contractors.create');
    }

    public function store(StoreContractorRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();

        $this->contractorService->createContractor($validated, $request->file('national_id_file'));

        return redirect()
            ->route('product.contractors.index')
            ->with('success', __('product::contractor.contractor_added'));
    }

    public function show(Contractor $contractor)
    {
        $contractor->load(['contractorBooks.book.product', 'contractorBooks.contractTransactions']);

        $stats = $this->contractorService->getContractorStats($contractor);
        $accounts = Account::where('is_active', true)->orderBy('account_name')->get();
        $subWarehouses = SubWarehouse::orderBy('name')->get();

        return view('product::contractors.show', compact('contractor', 'stats', 'accounts', 'subWarehouses'));
    }

    public function edit(Contractor $contractor)
    {
        return view('product::contractors.edit', compact('contractor'));
    }

    public function update(UpdateContractorRequest $request, Contractor $contractor)
    {
        $validated = $request->validated();
        $validated['edited_by'] = Auth::id();

        $this->contractorService->updateContractor($contractor, $validated, $request->file('national_id_file'));

        return redirect()
            ->route('product.contractors.index')
            ->with('success', __('product::contractor.contractor_updated'));
    }

    public function destroy(Contractor $contractor)
    {
        try {
            $this->contractorService->deleteContractor($contractor);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('product.contractors.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('product.contractors.index')
            ->with('success', __('product::contractor.contractor_deleted'));
    }

    public function registerAsClient(Contractor $contractor)
    {
        $party = $this->contractorService->ensureParty($contractor);

        return response()->json([
            'success' => true,
            'party_id' => $party->id,
            'invoice_url' => route('finance.sales-invoices.create', ['party_id' => $party->id]),
        ]);
    }

    public function storeGift(StoreContractorGiftRequest $request, Contractor $contractor)
    {
        $lines = collect($request->validated()['lines'])->map(function (array $line) use ($contractor) {
            $contractorBook = ContractorBook::where('id', $line['contractor_book_id'])
                ->where('contractor_id', $contractor->id)
                ->firstOrFail();

            return [
                'book_id' => $contractorBook->book_id,
                'product_id' => $contractorBook->book->product_id,
                'quantity' => $line['quantity'],
                'unit_price' => $contractorBook->book->product->base_price,
            ];
        })->all();

        try {
            $this->contractorGiftService->createGift($contractor, $lines, $request->validated()['sub_warehouse_id']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('product.contractors.show', $contractor)
            ->with('success', __('product::contractor.gift_recorded'));
    }

    public function storeTransaction(StoreContractTransactionRequest $request, Contractor $contractor)
    {
        $validated = $request->validated();

        $contractorBook = ContractorBook::where('id', $validated['contractor_book_id'])
            ->where('contractor_id', $contractor->id)
            ->firstOrFail();

        if ($request->hasFile('receipt_file')) {
            $validated['receipt_file'] = $request->file('receipt_file')->store('contract_transactions', 'public');
        }

        try {
            $this->contractTransactionService->record($contractorBook, $validated, $validated['direction']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('product.contractors.show', $contractor)
            ->with('success', __('product::contractor.transaction_recorded'));
    }

    public function destroyTransaction(ContractTransaction $transaction)
    {
        $contractor = $transaction->contractorBook->contractor;

        $this->contractTransactionService->delete($transaction);

        return redirect()
            ->route('product.contractors.show', $contractor)
            ->with('success', __('product::contractor.transaction_deleted'));
    }
}
