<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Finance\Models\SalesInvoice;
use Modules\Finance\Models\Party;
use Modules\Finance\Models\Account;
use Modules\Finance\Services\SalesInvoiceService;
use Modules\Finance\Services\PartyService;
use Modules\Finance\Services\AccountService;
use Modules\Finance\Http\Requests\StoreSalesInvoiceRequest;
use Modules\Finance\Http\Requests\UpdateSalesInvoiceRequest;
use Modules\Product\Http\Controllers\CategoryController;
use Modules\Product\Models\Author;
use Modules\Product\Models\BookCategory;
use Modules\Product\Models\Product;
use Modules\Settings\Models\OrganizationSetting;
use Modules\Warehouse\Models\SubWarehouse;
use Modules\Warehouse\Models\SubWarehouseProduct;

class SalesInvoiceController extends Controller
{
    protected $invoiceService;
    protected $partyService;
    protected $accountService;

    public function __construct(
        SalesInvoiceService $invoiceService,
        PartyService $partyService,
        AccountService $accountService
    ) {
        $this->invoiceService = $invoiceService;
        $this->partyService = $partyService;
        $this->accountService = $accountService;
    }

    /**
     * Display a listing of sales invoices
     */
    public function index(Request $request)
    {
        $query = SalesInvoice::with('party');

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
        $parties = Party::active()->get();

        return view('finance::sales-invoices.index', compact('invoices', 'stats', 'parties'));
    }

    /**
     * Show the form for creating a new sales invoice
     */
    public function create(Request $request)
    {
        $parties = Party::active()
            ->limit(10)
            ->get();
        $accounts = Account::active()->get();
        $orgSettings = OrganizationSetting::first();
        $subWarehouses = SubWarehouse::with('warehouse')->orderBy('name')->get();

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

                // Add relationship names
                $data['category_name'] = $p->book->category ? $p->book->category->name : null;
                $data['sub_category_name'] = $p->book->subCategory ? $p->book->subCategory->name : null;
                $data['author_name'] = $p->book->author ? $p->book->author->name : null;
            }

            return $data;
        })->values();

        $selectedParty = $request->get('party_id');

        return view('finance::sales-invoices.create', compact(
            'parties',
            'products',
            'accounts',
            'orgSettings',
            'selectedParty',
            'productsForJs',
            'allProductsWithBooks',
            'categories',
            'subCategories',
            'authors',
            'subWarehouses'
        ));
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
                    'text' => $party->name . ' - ' . number_format($party->customer_balance, 2),
                    'balance' => $party->customer_balance,
                ];
            });

        return response()->json([
            'results' => $parties,
            'pagination' => ['more' => false]
        ]);
    }



    /**
     * Store a newly created sales invoice
     */
    public function store(StoreSalesInvoiceRequest $request)
    {
        try {
            $invoice = $this->invoiceService->createInvoice($request->validated());

            return redirect()
                ->route('finance.sales-invoices.show', $invoice)
                ->with('success', __('finance::invoice.created_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified sales invoice
     */
    public function show(SalesInvoice $salesInvoice)
    {
        $salesInvoice->load(['party', 'items.product', 'receiptVouchers']);
        $orgSettings = OrganizationSetting::first();

        return view('finance::sales-invoices.show', compact('salesInvoice', 'orgSettings'));
    }

    /**
     * Show the form for editing the specified sales invoice
     */
    public function edit(SalesInvoice $salesInvoice)
    {
        // Don't allow editing paid or cancelled invoices
        if (in_array($salesInvoice->status, ['cancelled'])) {
            return redirect()
                ->back()
                ->with('error', __('finance::invoice.cannot_edit_status', ['status' => $salesInvoice->status_label]));
        }

        $salesInvoice->load(['party', 'items.product', 'receiptVouchers']);
        $parties = Party::active()->limit(10)->get();
        $products = Product::where('status', 'active')->get();
        $accounts = Account::active()->get();
        $orgSettings = OrganizationSetting::first();
        $subWarehouses = SubWarehouse::with('warehouse')->orderBy('name')->get();

        // Get categories, sub-categories, and authors for filters
        $categories = BookCategory::whereHas('books')->get();
        $subCategories = BookCategory::whereHas('books')->get();
        $authors = Author::whereHas('books')->get();

        $productsForJs = $products->map(function ($p) {
            return [
                'id'    => (int) $p->id,
                'name'  => $p->name,
                'sku'   => $p->sku,
                'price' => (float) $p->base_price,
            ];
        })->values();

        $allProductsWithBooks = $products->map(function ($p) {
            $data = [
                'id'             => (int) $p->id,
                'name'           => $p->name,
                'sku'            => $p->sku,
                'price'          => (float) $p->base_price,
                'stock_quantity' => $p->stock_quantity,
            ];

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

        // FIX: Add existingItems variable
        $existingItems = $salesInvoice->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_amount' => $item->discount_amount,
                'description' => $item->description,
            ];
        })->values();

        // Check if invoice has receipt vouchers
        $hasReceiptVouchers = $salesInvoice->receiptVouchers()->exists();
        $receiptVouchersCount = $salesInvoice->receiptVouchers()->count();

        return view('finance::sales-invoices.edit', compact(
            'salesInvoice',
            'parties',
            'products',
            'accounts',
            'orgSettings',
            'productsForJs',
            'allProductsWithBooks',
            'existingItems',
            'subWarehouses',
            'categories',
            'subCategories',
            'authors',
            'hasReceiptVouchers',
            'receiptVouchersCount'
        ));
    }

    /**
     * Update the specified sales invoice
     */
    public function update(UpdateSalesInvoiceRequest $request, SalesInvoice $salesInvoice)
    {
        try {
            $this->invoiceService->updateInvoice($salesInvoice, $request->validated());

            return redirect()
                ->route('finance.sales-invoices.show', $salesInvoice)
                ->with('success', __('finance::invoice.updated_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified sales invoice
     */
    public function destroy(SalesInvoice $salesInvoice)
    {
        try {
            $this->invoiceService->deleteInvoice($salesInvoice);

            return redirect()
                ->route('finance.sales-invoices.index')
                ->with('success', __('finance::invoice.deleted_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel the specified sales invoice
     */
    public function cancel(SalesInvoice $salesInvoice)
    {
        try {
            $this->invoiceService->cancelInvoice($salesInvoice);

            return redirect()
                ->back()
                ->with('success', __('finance::invoice.cancelled_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Activate Invoice
     */
    public function activate(SalesInvoice $salesInvoice)
    {
        try {
            $this->invoiceService->activateInvoice($salesInvoice);

            return redirect()
                ->back()
                ->with('success', __('finance::invoice.activated_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
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
    /**
     * Get product stock in specific sub-warehouse
     */
    public function getProductStock(Request $request)
    {
        $productId = $request->product_id;
        $subWarehouseId = $request->sub_warehouse_id;

        if (!$productId || !$subWarehouseId) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        $stock = SubWarehouseProduct::where('sub_warehouse_id', $subWarehouseId)
            ->where('product_id', $productId)
            ->first();
        return response()->json([
            'product_id' => $productId,
            'sub_warehouse_id' => $subWarehouseId,
            'quantity' => $stock ? $stock->quantity : 0,
        ]);
    }

    public function print(SalesInvoice $salesInvoice, Request $request)
    {
        $salesInvoice->load(['party', 'items.product', 'receiptVouchers']);
        $orgSettings = OrganizationSetting::first();

        // Get print language from request or use default
        $printLang = $request->get('lang', $orgSettings->default_language);

        return view('finance::sales-invoices.print', compact('salesInvoice', 'orgSettings', 'printLang'));
    }
}
