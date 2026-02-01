<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Finance\Models\PurchaseInvoice;
use Modules\Finance\Models\Party;
use Modules\Finance\Models\Account;
use Modules\Finance\Services\PurchaseInvoiceService;
use Modules\Finance\Services\PartyService;
use Modules\Finance\Services\AccountService;
use Modules\Finance\Http\Requests\StorePurchaseInvoiceRequest;
use Modules\Finance\Http\Requests\UpdatePurchaseInvoiceRequest;
use Modules\Product\Models\Author;
use Modules\Product\Models\BookCategory;
use Modules\Product\Models\Product;
use Modules\Settings\Models\OrganizationSetting;

class PurchaseInvoiceController extends Controller
{
    protected $invoiceService;
    protected $partyService;
    protected $accountService;

    public function __construct(
        PurchaseInvoiceService $invoiceService,
        PartyService $partyService,
        AccountService $accountService
    ) {
        $this->invoiceService = $invoiceService;
        $this->partyService = $partyService;
        $this->accountService = $accountService;
    }

    /**
     * Display a listing of purchase invoices
     */
    public function index(Request $request)
    {
        $query = PurchaseInvoice::with('party');

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by party
        if ($request->filled('party_id')) {
            $query->byParty($request->party_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('invoice_date', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->get();
        $stats = $this->invoiceService->getStatistics();
        $parties = Party::active()->vendors()->get();

        return view('finance::purchase-invoices.index', compact('invoices', 'stats', 'parties'));
    }

    /**
     * Show the form for creating a new purchase invoice
     */
    public function create(Request $request)
    {
        $parties = Party::active()
            ->limit(10)
            ->get();
        $accounts = Account::active()->get();
        $orgSettings = OrganizationSetting::first();

        // Get products with book information
        $products = Product::with(['book.author', 'book.category', 'book.subCategory'])
            ->where('status', 'active')
            ->get();

        // Get categories, sub-categories, and authors for filters
        $categories = BookCategory::whereHas('books')->get();
        $subCategories = BookCategory::whereHas('books')->get();
        $authors = Author::whereHas('books')->get();

        // Prepare simple products list for basic operations
        $productsForJs = $products->map(function ($p) {
            return [
                'id'    => (int) $p->id,
                'name'  => $p->name,
                'sku'   => $p->sku,
                'price' => (float) $p->base_price,
            ];
        })->values();

        // Prepare detailed products list with book information for drawer
        $allProductsWithBooks = $products->map(function ($p) {
            $data = [
                'id'             => (int) $p->id,
                'name'           => $p->name,
                'sku'            => $p->sku,
                'price'          => (float) $p->base_price,
                'stock_quantity' => $p->stock_quantity,
            ];

            // Add book-specific data if available
            if ($p->book) {
                $data['isbn'] = $p->book->isbn;
                $data['category_id'] = $p->book->category_id;
                $data['sub_category_id'] = $p->book->sub_category_id;
                $data['author_id'] = $p->book->author_id;
                $data['category_name'] = $p->book->category ? $p->book->category->name : null;
                $data['sub_category_name'] = $p->book->subCategory ? $p->book->subCategory->name : null;
                $data['author_name'] = $p->book->author ? $p->book->author->name : null;
            }

            return $data;
        })->values();

        $selectedParty = $request->get('party_id');

        return view('finance::purchase-invoices.create', compact(
            'parties',
            'products',
            'accounts',
            'orgSettings',
            'selectedParty',
            'productsForJs',
            'allProductsWithBooks',
            'categories',
            'subCategories',
            'authors'
        ));
    }


    /**
     * Store a newly created purchase invoice
     */
    public function store(StorePurchaseInvoiceRequest $request)
    {
        try {
            $invoice = $this->invoiceService->createInvoice($request->validated());

            return redirect()
                ->route('finance.purchase-invoices.show', $invoice)
                ->with('success', __('finance::invoice.created_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified purchase invoice
     */
    public function show(PurchaseInvoice $purchaseInvoice)
    {
        $purchaseInvoice->load(['party', 'items.product', 'paymentVouchers']);
        $orgSettings = OrganizationSetting::first();

        return view('finance::purchase-invoices.show', compact('purchaseInvoice', 'orgSettings'));
    }

    /**
     * Show the form for editing the specified purchase invoice
     */
    public function edit(PurchaseInvoice $purchaseInvoice)
    {
        // Don't allow editing paid or cancelled invoices
        if (in_array($purchaseInvoice->status, ['paid', 'cancelled'])) {
            return redirect()
                ->back()
                ->with('error', __('finance::invoice.cannot_edit_status', ['status' => $purchaseInvoice->status_label]));
        }

        $purchaseInvoice->load(['party', 'items.product']);
        $parties = Party::active()->get();
        $products = Product::where('status', 'active')->get();
        $orgSettings = OrganizationSetting::first();

        // Map products for JavaScript
        $productsForJs = $products->map(function ($p) {
            return [
                'id'    => (int) $p->id,
                'name'  => $p->name,
                'sku'   => $p->sku,
                'price' => (float) $p->base_price,
            ];
        })->values();

        // Map existing items for JavaScript
        $existingItemsForJs = $purchaseInvoice->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_amount' => $item->discount_amount,
                'description' => $item->description,
            ];
        })->values();

        return view('finance::purchase-invoices.edit', compact(
            'purchaseInvoice',
            'parties',
            'products',
            'orgSettings',
            'productsForJs',
            'existingItemsForJs'
        ));
    }

    /**
     * Update the specified purchase invoice
     */
    public function update(UpdatePurchaseInvoiceRequest $request, PurchaseInvoice $purchaseInvoice)
    {
        try {
            $this->invoiceService->updateInvoice($purchaseInvoice, $request->validated());

            return response()->json([
                'success' => true,
                'message' => __('finance::invoice.updated_successfully'),
                'invoice' => $purchaseInvoice->load(['party', 'items.product', 'paymentVouchers']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified purchase invoice
     */
    public function destroy(PurchaseInvoice $purchaseInvoice)
    {
        try {
            $this->invoiceService->deleteInvoice($purchaseInvoice);

            return response()->json([
                'success' => true,
                'message' => __('finance::invoice.deleted_successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel the specified purchase invoice
     */
    public function cancel(PurchaseInvoice $purchaseInvoice)
    {
        try {
            $this->invoiceService->cancelInvoice($purchaseInvoice);

            return response()->json([
                'success' => true,
                'message' => __('finance::invoice.cancelled_successfully'),
                'invoice' => $purchaseInvoice->refresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get product details for invoice line item
     */
    public function getProduct($productId)
    {
        $product = Product::findOrFail($productId);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->base_price,
        ]);
    }

    public function searchParties(Request $request)
    {
        $search = $request->get('q', '');

        $parties = Party::active()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get()
            ->map(function ($party) {
                return [
                    'id' => $party->id,
                    'text' => $party->name . ' - ' . number_format($party->vendor_balance, 2),
                    'balance' => $party->vendor_balance,
                ];
            });

        return response()->json([
            'results' => $parties,
            'pagination' => ['more' => false]
        ]);
    }
}
