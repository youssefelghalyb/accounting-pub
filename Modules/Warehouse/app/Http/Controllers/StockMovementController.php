<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Warehouse\Models\StockMovement;
use Modules\Warehouse\Models\SubWarehouse;
use Modules\Warehouse\Models\SubWarehouseProduct;
use Modules\Product\Models\Product;
use Modules\Warehouse\Http\Requests\StoreStockMovementRequest;
use Modules\Warehouse\Http\Requests\UpdateStockMovementRequest;

class StockMovementController extends Controller
{
    /**
     * Display a listing of stock movements.
     */
    public function index(Request $request)
    {
        $query = StockMovement::query()->with([
            'product.book',
            'fromSubWarehouse.warehouse',
            'toSubWarehouse.warehouse',
            'user'
        ]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('product', function($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                       ->orWhere('sku', 'like', "%{$search}%");
                })
                ->orWhereHas('fromSubWarehouse', function($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('toSubWarehouse', function($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })
                ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        // Filter by movement type
        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        // Filter by sub-warehouse
        if ($request->filled('sub_warehouse_id')) {
            $query->where(function($q) use ($request) {
                $q->where('from_sub_warehouse_id', $request->sub_warehouse_id)
                  ->orWhere('to_sub_warehouse_id', $request->sub_warehouse_id);
            });
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Calculate statistics
        $stats = [
            'total_movements' => StockMovement::count(),
            'total_transfers' => StockMovement::where('movement_type', 'transfer')->count(),
            'total_inbound' => StockMovement::where('movement_type', 'inbound')->sum('quantity'),
            'total_outbound' => StockMovement::where('movement_type', 'outbound')->sum('quantity'),
        ];

        $movements = $query->orderBy('created_at', 'desc')->paginate(20);
        $subWarehouses = SubWarehouse::with('warehouse')->orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('warehouse::stock_movements.index', compact('movements', 'subWarehouses', 'products', 'stats'));
    }

    /**
     * Show the form for creating new stock movements (bulk).
     */
    public function create()
    {
        $subWarehouses = SubWarehouse::with('warehouse')->orderBy('name')->get();
        $products = Product::with('book')->orderBy('name')->get();

        return view('warehouse::stock_movements.create', compact('subWarehouses', 'products'));
    }

    /**
     * Store newly created stock movements (bulk).
     */
    public function store(StoreStockMovementRequest $request)
    {
        DB::beginTransaction();

        try {
            foreach ($request->movements as $movementData) {
                // Validate movement type constraints
                if ($movementData['movement_type'] === 'transfer') {
                    if (!isset($movementData['from_sub_warehouse_id']) || !isset($movementData['to_sub_warehouse_id'])) {
                        DB::rollBack();
                        return redirect()
                            ->back()
                            ->with('error', __('warehouse::stock_movement.transfer_requires_both_warehouses'));
                    }

                    // Check if source has enough stock
                    $sourceStock = SubWarehouseProduct::where('sub_warehouse_id', $movementData['from_sub_warehouse_id'])
                        ->where('product_id', $movementData['product_id'])
                        ->first();

                    if (!$sourceStock || $sourceStock->quantity < $movementData['quantity']) {
                        DB::rollBack();
                        return redirect()
                            ->back()
                            ->with('error', __('warehouse::stock_movement.insufficient_stock'));
                    }

                    // Decrease source warehouse stock
                    $sourceStock->quantity -= $movementData['quantity'];
                    $sourceStock->edited_by = Auth::id();
                    $sourceStock->save();

                    // Increase destination warehouse stock
                    $destStock = SubWarehouseProduct::firstOrCreate(
                        [
                            'sub_warehouse_id' => $movementData['to_sub_warehouse_id'],
                            'product_id' => $movementData['product_id'],
                        ],
                        [
                            'quantity' => 0,
                            'created_by' => Auth::id(),
                        ]
                    );
                    $destStock->quantity += $movementData['quantity'];
                    $destStock->edited_by = Auth::id();
                    $destStock->save();
                }

                if ($movementData['movement_type'] === 'inbound') {
                    if (!isset($movementData['to_sub_warehouse_id'])) {
                        DB::rollBack();
                        return redirect()
                            ->back()
                            ->with('error', __('warehouse::stock_movement.inbound_requires_destination'));
                    }

                    // Increase destination warehouse stock
                    $destStock = SubWarehouseProduct::firstOrCreate(
                        [
                            'sub_warehouse_id' => $movementData['to_sub_warehouse_id'],
                            'product_id' => $movementData['product_id'],
                        ],
                        [
                            'quantity' => 0,
                            'created_by' => Auth::id(),
                        ]
                    );
                    $destStock->quantity += $movementData['quantity'];
                    $destStock->edited_by = Auth::id();
                    $destStock->save();
                }

                if ($movementData['movement_type'] === 'outbound') {
                    if (!isset($movementData['from_sub_warehouse_id'])) {
                        DB::rollBack();
                        return redirect()
                            ->back()
                            ->with('error', __('warehouse::stock_movement.outbound_requires_source'));
                    }

                    // Check if source has enough stock
                    $sourceStock = SubWarehouseProduct::where('sub_warehouse_id', $movementData['from_sub_warehouse_id'])
                        ->where('product_id', $movementData['product_id'])
                        ->first();

                    if (!$sourceStock || $sourceStock->quantity < $movementData['quantity']) {
                        DB::rollBack();
                        return redirect()
                            ->back()
                            ->with('error', __('warehouse::stock_movement.insufficient_stock'));
                    }

                    // Decrease source warehouse stock
                    $sourceStock->quantity -= $movementData['quantity'];
                    $sourceStock->edited_by = Auth::id();
                    $sourceStock->save();
                }

                // Create stock movement record
                StockMovement::create([
                    'product_id' => $movementData['product_id'],
                    'from_sub_warehouse_id' => $movementData['from_sub_warehouse_id'] ?? null,
                    'to_sub_warehouse_id' => $movementData['to_sub_warehouse_id'] ?? null,
                    'quantity' => $movementData['quantity'],
                    'movement_type' => $movementData['movement_type'],
                    'reason' => $movementData['reason'] ?? null,
                    'reference_id' => $movementData['reference_id'] ?? null,
                    'notes' => $movementData['notes'] ?? null,
                    'user_id' => Auth::id(),
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('warehouse.stock_movements.index')
                ->with('success', __('warehouse::stock_movement.movements_created'));
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', __('warehouse::stock_movement.movements_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified stock movement.
     */
    public function show($id)
    {
        $movement = StockMovement::with([
            'product.book.author',
            'product.book.category',
            'fromSubWarehouse.warehouse',
            'toSubWarehouse.warehouse',
            'user',
            'creator',
            'editor'
        ])->findOrFail($id);

        return view('warehouse::stock_movements.show', compact('movement'));
    }

    /**
     * Show the form for editing the specified stock movement.
     */
    public function edit($id)
    {
        $movement = StockMovement::findOrFail($id);
        $subWarehouses = SubWarehouse::with('warehouse')->orderBy('name')->get();
        $products = Product::with('book')->orderBy('name')->get();

        return view('warehouse::stock_movements.edit', compact('movement', 'subWarehouses', 'products'));
    }

    /**
     * Update the specified stock movement.
     */
    public function update(UpdateStockMovementRequest $request, $id)
    {
        $movement = StockMovement::findOrFail($id);
        $validated = $request->validated();
        $validated['edited_by'] = Auth::id();

        $movement->update($validated);

        return redirect()
            ->route('warehouse.stock_movements.index')
            ->with('success', __('warehouse::stock_movement.movement_updated'));
    }

    /**
     * Remove the specified stock movement.
     */
    public function destroy($id)
    {
        $movement = StockMovement::findOrFail($id);
        $movement->delete();

        return redirect()
            ->route('warehouse.stock_movements.index')
            ->with('success', __('warehouse::stock_movement.movement_deleted'));
    }
}
