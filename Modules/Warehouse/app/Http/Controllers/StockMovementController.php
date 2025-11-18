<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Warehouse\Models\StockMovement;
use Modules\Warehouse\Models\StockMovementItem;
use Modules\Warehouse\Models\Stock;
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
        $query = StockMovement::with('items.product.book', 'creator');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('source_warehouse', 'like', "%{$search}%")
                  ->orWhere('destination_warehouse', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('movement_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('movement_date', '<=', $request->date_to);
        }

        $movements = $query->orderBy('created_at', 'desc')->get();

        // Calculate statistics
        $stats = [
            'total_movements' => StockMovement::count(),
            'pending_movements' => StockMovement::where('status', 'pending')->count(),
            'completed_movements' => StockMovement::where('status', 'completed')->count(),
            'total_items_moved' => StockMovement::sum('total_items'),
        ];

        return view('warehouse::movements.index', compact('movements', 'stats'));
    }

    /**
     * Show the form for creating a new stock movement.
     */
    public function create()
    {
        $products = Product::with('book.author')->where('status', 'active')->get();
        $warehouses = Stock::distinct()->pluck('warehouse_name');

        // Generate reference number
        $lastMovement = StockMovement::orderBy('id', 'desc')->first();
        $nextNumber = $lastMovement ? (intval(substr($lastMovement->reference_number, -6)) + 1) : 1;
        $referenceNumber = 'MOV-' . date('Ymd') . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        return view('warehouse::movements.create', compact('products', 'warehouses', 'referenceNumber'));
    }

    /**
     * Store a newly created stock movement with multiple items.
     */
    public function store(StoreStockMovementRequest $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();

            // Extract items from validated data
            $items = $validated['items'];
            unset($validated['items']);

            // Create movement
            $validated['created_by'] = Auth::id();
            $validated['total_items'] = count($items);

            $movement = StockMovement::create($validated);

            // Create movement items
            foreach ($items as $item) {
                StockMovementItem::create([
                    'stock_movement_id' => $movement->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);

                // Update stock if movement is completed
                if ($movement->status === 'completed') {
                    $this->updateStock($movement, $item['product_id'], $item['quantity']);
                }
            }

            DB::commit();

            return redirect()
                ->route('warehouse.movements.index')
                ->with('success', __('warehouse::movements.movement_added'));

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('warehouse::movements.movement_error') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified stock movement.
     */
    public function show($id)
    {
        $movement = StockMovement::with('items.product.book.author', 'items.product.book.category', 'creator', 'editor')
            ->findOrFail($id);

        return view('warehouse::movements.show', compact('movement'));
    }

    /**
     * Show the form for editing the specified stock movement.
     */
    public function edit($id)
    {
        $movement = StockMovement::with('items.product')->findOrFail($id);

        // Check if movement can be edited
        if (!$movement->canBeEdited()) {
            return redirect()
                ->route('warehouse.movements.index')
                ->with('error', __('warehouse::movements.cannot_edit_completed'));
        }

        $products = Product::with('book.author')->where('status', 'active')->get();
        $warehouses = Stock::distinct()->pluck('warehouse_name');

        return view('warehouse::movements.edit', compact('movement', 'products', 'warehouses'));
    }

    /**
     * Update the specified stock movement.
     */
    public function update(UpdateStockMovementRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $movement = StockMovement::findOrFail($id);

            // Check if movement can be edited
            if (!$movement->canBeEdited()) {
                return redirect()
                    ->route('warehouse.movements.index')
                    ->with('error', __('warehouse::movements.cannot_edit_completed'));
            }

            $validated = $request->validated();

            // Extract items from validated data
            $items = $validated['items'];
            unset($validated['items']);

            // Update movement
            $validated['edited_by'] = Auth::id();
            $validated['total_items'] = count($items);

            $movement->update($validated);

            // Delete old items
            $movement->items()->delete();

            // Create new movement items
            foreach ($items as $item) {
                StockMovementItem::create([
                    'stock_movement_id' => $movement->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);

                // Update stock if movement is completed
                if ($movement->status === 'completed') {
                    $this->updateStock($movement, $item['product_id'], $item['quantity']);
                }
            }

            DB::commit();

            return redirect()
                ->route('warehouse.movements.index')
                ->with('success', __('warehouse::movements.movement_updated'));

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('warehouse::movements.movement_error') . ': ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified stock movement.
     */
    public function destroy($id)
    {
        $movement = StockMovement::findOrFail($id);

        // Check if movement can be deleted
        if ($movement->status === 'completed') {
            return redirect()
                ->route('warehouse.movements.index')
                ->with('error', __('warehouse::movements.cannot_delete_completed'));
        }

        $movement->delete();

        return redirect()
            ->route('warehouse.movements.index')
            ->with('success', __('warehouse::movements.movement_deleted'));
    }

    /**
     * Update stock quantities based on movement type.
     */
    private function updateStock($movement, $productId, $quantity)
    {
        switch ($movement->type) {
            case 'in':
                // Add stock to destination warehouse
                if ($movement->destination_warehouse) {
                    $this->adjustStock($productId, $movement->destination_warehouse, $quantity);
                }
                break;

            case 'out':
                // Remove stock from source warehouse
                if ($movement->source_warehouse) {
                    $this->adjustStock($productId, $movement->source_warehouse, -$quantity);
                }
                break;

            case 'transfer':
                // Remove from source, add to destination
                if ($movement->source_warehouse) {
                    $this->adjustStock($productId, $movement->source_warehouse, -$quantity);
                }
                if ($movement->destination_warehouse) {
                    $this->adjustStock($productId, $movement->destination_warehouse, $quantity);
                }
                break;

            case 'adjustment':
                // Adjust stock in destination warehouse
                if ($movement->destination_warehouse) {
                    $this->adjustStock($productId, $movement->destination_warehouse, $quantity);
                }
                break;
        }
    }

    /**
     * Adjust stock quantity for a product in a warehouse.
     */
    private function adjustStock($productId, $warehouse, $quantityChange)
    {
        $stock = Stock::where('product_id', $productId)
            ->where('warehouse_name', $warehouse)
            ->first();

        if ($stock) {
            $stock->increment('quantity', $quantityChange);
        } else {
            // Create new stock record if it doesn't exist
            Stock::create([
                'product_id' => $productId,
                'warehouse_name' => $warehouse,
                'quantity' => max(0, $quantityChange),
                'status' => 'active',
                'created_by' => Auth::id(),
            ]);
        }
    }
}
