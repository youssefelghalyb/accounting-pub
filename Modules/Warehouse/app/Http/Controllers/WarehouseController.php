<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Warehouse\Models\Warehouse;
use Modules\Warehouse\Http\Requests\StoreWarehouseRequest;
use Modules\Warehouse\Http\Requests\UpdateWarehouseRequest;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses.
     */
    public function index(Request $request)
    {
        $query = Warehouse::query()->with('subWarehouses');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Calculate statistics
        $stats = [
            'total_warehouses' => Warehouse::count(),
            'total_sub_warehouses' => \Modules\Warehouse\Models\SubWarehouse::count(),
            'total_stock' => \Modules\Warehouse\Models\SubWarehouseProduct::sum('quantity'),
        ];

        $warehouses = $query->orderBy('created_at', 'desc')->get();

        return view('warehouse::warehouses.index', compact('warehouses', 'stats'));
    }

    /**
     * Show the form for creating a new warehouse.
     */
    public function create()
    {
        return view('warehouse::warehouses.create');
    }

    /**
     * Store a newly created warehouse.
     */
    public function store(StoreWarehouseRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();

        Warehouse::create($validated);

        return redirect()
            ->route('warehouse.warehouses.index')
            ->with('success', __('warehouse::warehouse.warehouse_added'));
    }

    /**
     * Display the specified warehouse.
     */
    public function show($id)
    {
        $warehouse = Warehouse::with([
            'subWarehouses.products.product.book',
            'creator',
            'editor'
        ])->findOrFail($id);

        return view('warehouse::warehouses.show', compact('warehouse'));
    }

    /**
     * Show the form for editing the specified warehouse.
     */
    public function edit($id)
    {
        $warehouse = Warehouse::findOrFail($id);

        return view('warehouse::warehouses.edit', compact('warehouse'));
    }

    /**
     * Update the specified warehouse.
     */
    public function update(UpdateWarehouseRequest $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $validated = $request->validated();
        $validated['edited_by'] = Auth::id();

        $warehouse->update($validated);

        return redirect()
            ->route('warehouse.warehouses.index')
            ->with('success', __('warehouse::warehouse.warehouse_updated'));
    }

    /**
     * Remove the specified warehouse.
     */
    public function destroy($id)
    {
        $warehouse = Warehouse::findOrFail($id);

        // Check if warehouse has sub-warehouses
        if ($warehouse->subWarehouses()->count() > 0) {
            return redirect()
                ->route('warehouse.warehouses.index')
                ->with('error', __('warehouse::warehouse.cannot_delete_has_sub_warehouses'));
        }

        $warehouse->delete();

        return redirect()
            ->route('warehouse.warehouses.index')
            ->with('success', __('warehouse::warehouse.warehouse_deleted'));
    }
}
