<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Warehouse\Models\Stock;
use Modules\Product\Models\Product;
use Modules\Warehouse\Http\Requests\StoreStockRequest;
use Modules\Warehouse\Http\Requests\UpdateStockRequest;

class StockController extends Controller
{
    /**
     * Display a listing of stocks.
     */
    public function index(Request $request)
    {
        $query = Stock::with('product.book.author', 'product.book.category');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('warehouse_name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('product', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by warehouse
        if ($request->filled('warehouse')) {
            $query->where('warehouse_name', $request->warehouse);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by stock level
        if ($request->filled('stock_level')) {
            switch ($request->stock_level) {
                case 'low':
                    $query->lowStock();
                    break;
                case 'out':
                    $query->outOfStock();
                    break;
            }
        }

        $stocks = $query->orderBy('created_at', 'desc')->get();

        // Calculate statistics
        $stats = [
            'total_stocks' => Stock::count(),
            'active_stocks' => Stock::where('status', 'active')->count(),
            'low_stock_items' => Stock::lowStock()->count(),
            'out_of_stock_items' => Stock::outOfStock()->count(),
            'total_quantity' => Stock::sum('quantity'),
        ];

        // Get unique warehouses for filter
        $warehouses = Stock::distinct()->pluck('warehouse_name');

        return view('warehouse::stocks.index', compact('stocks', 'stats', 'warehouses'));
    }

    /**
     * Show the form for creating a new stock.
     */
    public function create()
    {
        $products = Product::with('book')->where('status', 'active')->get();

        return view('warehouse::stocks.create', compact('products'));
    }

    /**
     * Store a newly created stock.
     */
    public function store(StoreStockRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();

        Stock::create($validated);

        return redirect()
            ->route('warehouse.stocks.index')
            ->with('success', __('warehouse::stocks.stock_added'));
    }

    /**
     * Display the specified stock.
     */
    public function show($id)
    {
        $stock = Stock::with('product.book.author', 'product.book.category', 'creator', 'editor')
            ->findOrFail($id);

        return view('warehouse::stocks.show', compact('stock'));
    }

    /**
     * Show the form for editing the specified stock.
     */
    public function edit($id)
    {
        $stock = Stock::findOrFail($id);
        $products = Product::with('book')->where('status', 'active')->get();

        return view('warehouse::stocks.edit', compact('stock', 'products'));
    }

    /**
     * Update the specified stock.
     */
    public function update(UpdateStockRequest $request, $id)
    {
        $stock = Stock::findOrFail($id);
        $validated = $request->validated();
        $validated['edited_by'] = Auth::id();

        $stock->update($validated);

        return redirect()
            ->route('warehouse.stocks.index')
            ->with('success', __('warehouse::stocks.stock_updated'));
    }

    /**
     * Remove the specified stock.
     */
    public function destroy($id)
    {
        $stock = Stock::findOrFail($id);
        $stock->delete();

        return redirect()
            ->route('warehouse.stocks.index')
            ->with('success', __('warehouse::stocks.stock_deleted'));
    }
}
