<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Warehouse\Models\Warehouse;
use Modules\Warehouse\Models\SubWarehouse;
use Modules\Warehouse\Models\SubWarehouseProduct;
use Modules\Product\Models\Product;
use Modules\Warehouse\Http\Requests\StoreSubWarehouseRequest;
use Modules\Warehouse\Http\Requests\UpdateSubWarehouseRequest;

class SubWarehouseController extends Controller
{
    /**
     * Display a listing of sub-warehouses.
     */
    public function index(Request $request)
    {
        $query = SubWarehouse::query()->with('warehouse');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhereHas('warehouse', function($wq) use ($search) {
                      $wq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Calculate statistics
        $stats = [
            'total_sub_warehouses' => SubWarehouse::count(),
            'total_products' => SubWarehouseProduct::count(),
            'total_stock' => SubWarehouseProduct::sum('quantity'),
        ];

        $subWarehouses = $query->orderBy('created_at', 'desc')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('warehouse::sub_warehouses.index', compact('subWarehouses', 'warehouses', 'stats'));
    }

    /**
     * Show the form for creating a new sub-warehouse.
     */
    public function create()
    {
        $warehouses = Warehouse::orderBy('name')->get();

        return view('warehouse::sub_warehouses.create', compact('warehouses'));
    }

    /**
     * Store a newly created sub-warehouse.
     */
    public function store(StoreSubWarehouseRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();

        SubWarehouse::create($validated);

        return redirect()
            ->route('warehouse.sub_warehouses.index')
            ->with('success', __('warehouse::sub_warehouse.sub_warehouse_added'));
    }

    /**
     * Display the specified sub-warehouse.
     */
    public function show($id)
    {
        $subWarehouse = SubWarehouse::with([
            'warehouse',
            'products.product.book.author',
            'products.product.book.category',
            'creator',
            'editor'
        ])->findOrFail($id);

        return view('warehouse::sub_warehouses.show', compact('subWarehouse'));
    }

    /**
     * Show the form for editing the specified sub-warehouse.
     */
    public function edit($id)
    {
        $subWarehouse = SubWarehouse::findOrFail($id);
        $warehouses = Warehouse::orderBy('name')->get();

        return view('warehouse::sub_warehouses.edit', compact('subWarehouse', 'warehouses'));
    }

    /**
     * Update the specified sub-warehouse.
     */
    public function update(UpdateSubWarehouseRequest $request, $id)
    {
        $subWarehouse = SubWarehouse::findOrFail($id);
        $validated = $request->validated();
        $validated['edited_by'] = Auth::id();

        $subWarehouse->update($validated);

        return redirect()
            ->route('warehouse.sub_warehouses.index')
            ->with('success', __('warehouse::sub_warehouse.sub_warehouse_updated'));
    }

    /**
     * Remove the specified sub-warehouse.
     */
    public function destroy($id)
    {
        $subWarehouse = SubWarehouse::findOrFail($id);

        // Check if sub-warehouse has products
        if ($subWarehouse->products()->count() > 0) {
            return redirect()
                ->route('warehouse.sub_warehouses.index')
                ->with('error', __('warehouse::sub_warehouse.cannot_delete_has_products'));
        }

        $subWarehouse->delete();

        return redirect()
            ->route('warehouse.sub_warehouses.index')
            ->with('success', __('warehouse::sub_warehouse.sub_warehouse_deleted'));
    }

    /**
     * Show the form for adding stock to sub-warehouse (bulk).
     */
    public function addStock($id)
    {
        $subWarehouse = SubWarehouse::with('warehouse')->findOrFail($id);
        $products = Product::with('book')->orderBy('name')->get();

        return view('warehouse::sub_warehouses.add_stock', compact('subWarehouse', 'products'));
    }

    /**
     * Store bulk stock additions.
     */
    public function storeStock(Request $request, $id)
    {
        $subWarehouse = SubWarehouse::findOrFail($id);

        $request->validate([
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        foreach ($request->products as $productData) {
            $existingProduct = SubWarehouseProduct::where('sub_warehouse_id', $subWarehouse->id)
                ->where('product_id', $productData['product_id'])
                ->first();

            if ($existingProduct) {
                // Update quantity
                $existingProduct->quantity += $productData['quantity'];
                $existingProduct->edited_by = Auth::id();
                $existingProduct->save();
            } else {
                // Create new record
                SubWarehouseProduct::create([
                    'sub_warehouse_id' => $subWarehouse->id,
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                    'created_by' => Auth::id(),
                ]);
            }

            // Create inbound stock movement
            \Modules\Warehouse\Models\StockMovement::create([
                'product_id' => $productData['product_id'],
                'to_sub_warehouse_id' => $subWarehouse->id,
                'quantity' => $productData['quantity'],
                'movement_type' => 'inbound',
                'reason' => 'Stock Addition',
                'user_id' => Auth::id(),
                'created_by' => Auth::id(),
            ]);
        }

        return redirect()
            ->route('warehouse.sub_warehouses.show', $subWarehouse)
            ->with('success', __('warehouse::sub_warehouse.stock_added'));
    }
}
