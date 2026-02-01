<?php

namespace Modules\Finance\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\SalesInvoice;
use Modules\Finance\Models\SalesInvoiceItem;
use Modules\Product\Models\Product;
use Modules\Warehouse\Models\StockMovement;
use Modules\Warehouse\Models\SubWarehouseProduct;

class SalesInvoiceService
{
    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $lastInvoice = SalesInvoice::withTrashed()->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'SI-' . $year . '-' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new sales invoice
     */
    /**
     * Create a new sales invoice
     */
    public function createInvoice(array $data): SalesInvoice
    {
        return DB::transaction(function () use ($data) {
            // Generate invoice number
            $data['invoice_number'] = $this->generateInvoiceNumber();
            $data['created_by'] = Auth::id();
            $data['status'] = 'unpaid';

            // Extract items
            $items = $data['items'];
            unset($data['items']);

            // Extract payment info if exists
            $paidAmount = $data['paid_amount'] ?? 0;
            $accountId = $data['account_id'] ?? null;
            unset($data['paid_amount'], $data['account_id']);

            // Extract sub_warehouse_id for stock movements
            $subWarehouseId = $data['sub_warehouse_id'] ?? null;
            unset($data['sub_warehouse_id']);

            // Handle tax rate - get from organization settings if taxable but not provided
            $isTaxable = $data['is_taxable'] ?? false;
            if ($isTaxable && (!isset($data['tax_rate']) || $data['tax_rate'] === null)) {
                $orgSettings = \Modules\Settings\Models\OrganizationSetting::first();
                $data['tax_rate'] = $orgSettings->tax_rate ?? 0;
            } elseif (!$isTaxable) {
                $data['tax_rate'] = 0;
            }

            // Initialize amounts
            $data['subtotal'] = 0;
            $data['discount_amount'] = 0;
            $data['tax_amount'] = 0;
            $data['total_amount'] = 0;
            $data['paid_amount'] = 0;

            // Create invoice
            $invoice = SalesInvoice::create($data);

            // Create invoice items and stock movements
            foreach ($items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                $item = new SalesInvoiceItem([
                    'sales_invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_amount' => $itemData['discount_amount'] ?? 0,
                ]);
                $item->calculateLineTotal();
                $invoice->items()->save($item);

                // Create outbound stock movement
                if ($subWarehouseId) {
                    // Check if source has enough stock
                    $sourceStock = SubWarehouseProduct::where('sub_warehouse_id', $subWarehouseId)
                        ->where('product_id', $product->id)
                        ->first();

                    if (!$sourceStock || $sourceStock->quantity < $itemData['quantity']) {
                        throw new \Exception(__('finance::invoice.insufficient_stock_for_product', [
                            'product' => $product->name
                        ]));
                    }

                    // Decrease warehouse stock
                    $sourceStock->quantity -= $itemData['quantity'];
                    $sourceStock->edited_by = Auth::id();
                    $sourceStock->save();

                    // Create stock movement record
                    StockMovement::create([
                        'product_id' => $product->id,
                        'from_sub_warehouse_id' => $subWarehouseId,
                        'to_sub_warehouse_id' => null,
                        'quantity' => $itemData['quantity'],
                        'movement_type' => 'outbound',
                        'reason' => 'sales',
                        'reference_id' => $invoice->id,
                        'notes' => 'Sales Invoice: ' . $invoice->invoice_number,
                        'user_id' => Auth::id(),
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            // Calculate totals
            $invoice->calculateTotals();

            // Create receipt voucher if payment provided
            if ($paidAmount > 0 && $accountId) {
                $receiptService = new ReceiptVoucherService();
                $receiptService->createReceipt([
                    'party_id' => $invoice->party_id,
                    'account_id' => $accountId,
                    'sales_invoice_id' => $invoice->id,
                    'amount' => $paidAmount,
                    'voucher_date' => $invoice->invoice_date,
                    'payment_method' => 'cash',
                    'description' => 'Payment for invoice ' . $invoice->invoice_number,
                ]);
            }

            return $invoice->refresh();
        });
    }

    /**
     * Update an existing sales invoice
     */
    public function updateInvoice(SalesInvoice $invoice, array $data): SalesInvoice
    {
        return DB::transaction(function () use ($invoice, $data) {


            $data['edited_by'] = Auth::id();
            $items = $data['items'];
            unset($data['items']);

            $subWarehouseId = $data['sub_warehouse_id'] ?? null;
            unset($data['sub_warehouse_id']);

            // FIX: Handle party change with receipt vouchers
            $partyChanged = isset($data['party_id']) && $data['party_id'] != $invoice->party_id;
            $oldPartyId = $invoice->party_id;
            $newPartyId = $data['party_id'] ?? $oldPartyId;

            // Handle tax rate
            $isTaxable = $data['is_taxable'] ?? false;
            if ($isTaxable && (!isset($data['tax_rate']) || $data['tax_rate'] === null)) {
                $orgSettings = \Modules\Settings\Models\OrganizationSetting::first();
                $data['tax_rate'] = $orgSettings->tax_rate ?? 0;
            } elseif (!$isTaxable) {
                $data['tax_rate'] = 0;
            }

            // Step 1: Reverse all old stock movements
            $oldStockMovements = StockMovement::where('reference_id', $invoice->id)
                ->where('reason', 'sales')
                ->get();

            foreach ($oldStockMovements as $movement) {
                $stock = SubWarehouseProduct::firstOrCreate(
                    [
                        'sub_warehouse_id' => $movement->from_sub_warehouse_id,
                        'product_id' => $movement->product_id,
                    ],
                    [
                        'quantity' => 0,
                        'created_by' => Auth::id(),
                    ]
                );
                $stock->quantity += $movement->quantity;
                $stock->edited_by = Auth::id();
                $stock->save();
                $movement->delete();
            }

            // Step 2: Delete old items
            $invoice->items()->delete();

            // Step 3: Update invoice basic data
            $invoice->update($data);

            // Step 4: FIX - Update receipt vouchers if party changed
            if ($partyChanged) {
                $invoice->receiptVouchers()->update([
                    'party_id' => $newPartyId,
                    'edited_by' => Auth::id(),
                ]);
            }

            // Step 5: Create new items and stock movements
            foreach ($items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                $item = new SalesInvoiceItem([
                    'sales_invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_amount' => $itemData['discount_amount'] ?? 0,
                ]);
                $item->calculateLineTotal();
                $invoice->items()->save($item);

                // Create new stock movement
                if ($subWarehouseId) {
                    $sourceStock = SubWarehouseProduct::where('sub_warehouse_id', $subWarehouseId)
                        ->where('product_id', $product->id)
                        ->first();

                    if (!$sourceStock || $sourceStock->quantity < $itemData['quantity']) {
                        throw new \Exception(__('finance::invoice.insufficient_stock_for_product', [
                            'product' => $product->name
                        ]));
                    }

                    $sourceStock->quantity -= $itemData['quantity'];
                    $sourceStock->edited_by = Auth::id();
                    $sourceStock->save();

                    StockMovement::create([
                        'product_id' => $product->id,
                        'from_sub_warehouse_id' => $subWarehouseId,
                        'to_sub_warehouse_id' => null,
                        'quantity' => $itemData['quantity'],
                        'movement_type' => 'outbound',
                        'reason' => 'sales',
                        'reference_id' => $invoice->id,
                        'notes' => 'Sales Invoice: ' . $invoice->invoice_number . ' (Updated)',
                        'user_id' => Auth::id(),
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            // Recalculate totals
            $invoice->calculateTotals();

            return $invoice->refresh();
        });
    }

    /**
     * Delete a sales invoice
     */
    public function deleteInvoice(SalesInvoice $invoice): bool
    {
        // Check if invoice has payments
        if ($invoice->paid_amount > 0) {
            throw new \Exception(__('finance::invoice.cannot_delete_has_payments'));
        }

        return DB::transaction(function () use ($invoice) {
            // Reverse stock movements
            $stockMovements = StockMovement::where('reference_id', $invoice->id)
                ->where('reason', 'sales')
                ->get();

            foreach ($stockMovements as $movement) {
                // Return stock to warehouse
                $stock = SubWarehouseProduct::firstOrCreate(
                    [
                        'sub_warehouse_id' => $movement->from_sub_warehouse_id,
                        'product_id' => $movement->product_id,
                    ],
                    [
                        'quantity' => 0,
                        'created_by' => Auth::id(),
                    ]
                );
                $stock->quantity += $movement->quantity;
                $stock->edited_by = Auth::id();
                $stock->save();

                // Delete the movement record
                $movement->delete();
            }

            // Reduce party balance

            // Delete invoice (items will cascade)
            return $invoice->delete();
        });
    }

    /**
     * Cancel invoice
     */
    public function cancelInvoice(SalesInvoice $invoice): SalesInvoice
    {
        return DB::transaction(function () use ($invoice) {

            // ✅ Lock invoice to avoid concurrent edits/payments
            $invoice = SalesInvoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if ($invoice->paid_amount > 0) {
                throw new \Exception(__('finance::invoice.cannot_cancel_has_payments'));
            }

            if ($invoice->status === 'cancelled') {
                return $invoice; // already cancelled
            }

            // ✅ Get stock movements for this invoice
            $stockMovements = StockMovement::where('reference_id', $invoice->id)
                ->where('reason', 'sales')
                ->lockForUpdate()
                ->get();

            foreach ($stockMovements as $movement) {

                if (!$movement->from_sub_warehouse_id) {
                    throw new \Exception("Invalid stock movement: missing from_sub_warehouse_id");
                }

                // ✅ Lock stock row to prevent race condition
                $stock = SubWarehouseProduct::where('sub_warehouse_id', $movement->from_sub_warehouse_id)
                    ->where('product_id', $movement->product_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    $stock = SubWarehouseProduct::create([
                        'sub_warehouse_id' => $movement->from_sub_warehouse_id,
                        'product_id' => $movement->product_id,
                        'quantity' => 0,
                        'created_by' => Auth::id(),
                    ]);
                }

                $stock->quantity += $movement->quantity;
                $stock->edited_by = Auth::id();
                $stock->save();

                // ✅ optional: create reverse movement record (recommended)
                StockMovement::create([
                    'product_id' => $movement->product_id,
                    'quantity' => $movement->quantity,
                    'from_sub_warehouse_id' => null,
                    'to_sub_warehouse_id' => $movement->from_sub_warehouse_id,
                    'reason' => 'sales_cancel',
                    'reference_id' => $invoice->id,
                    'created_by' => Auth::id(),
                ]);
            }
            $invoice->status = 'cancelled';
            $invoice->save();

            return $invoice->refresh();
        });
    }

    /**
     * Activate invoice
     */
    public function activateInvoice(SalesInvoice $invoice): SalesInvoice
    {
        return DB::transaction(function () use ($invoice) {

            // ✅ Lock invoice (avoid concurrent updates)
            $invoice = SalesInvoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if ($invoice->status !== 'cancelled') {
                throw new \Exception(__('finance::invoice.only_cancelled_can_be_activated'));
            }

            // ✅ Get original sales movements (same used for cancellation)
            $stockMovements = StockMovement::where('reference_id', $invoice->id)
                ->where('reason', 'sales')
                ->lockForUpdate()
                ->get();

            foreach ($stockMovements as $movement) {

                if (!$movement->from_sub_warehouse_id) {
                    throw new \Exception("Invalid stock movement: missing from_sub_warehouse_id");
                }

                // ✅ Lock stock row
                $stock = SubWarehouseProduct::where('sub_warehouse_id', $movement->from_sub_warehouse_id)
                    ->where('product_id', $movement->product_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock || $stock->quantity < $movement->quantity) {
                    throw new \Exception(__('finance::invoice.not_enough_stock_to_activate'));
                }

                // ✅ Deduct stock again (re-apply invoice impact)
                $stock->quantity -= $movement->quantity;
                $stock->edited_by = Auth::id();
                $stock->save();

                // ✅ Optional but recommended: log reactivation movement
                StockMovement::create([
                    'product_id' => $movement->product_id,
                    'quantity' => $movement->quantity,
                    'from_sub_warehouse_id' => $movement->from_sub_warehouse_id,
                    'to_sub_warehouse_id' => null,
                    'reason' => 'sales_reactivate',
                    'reference_id' => $invoice->id,
                    'created_by' => Auth::id(),
                ]);
            }


            // ✅ Activate invoice again
            if($invoice->paid_amount == 0){
            $invoice->status = 'unpaid';
            }elseif($invoice->paid_amount == $invoice->total_amount){
                $invoice->status = 'paid';
            }elseif($invoice->paid_amount < $invoice->total_amount && $invoice->paid_amount > 0){
                $invoice->status = 'partial';
            }
            $invoice->save();

            return $invoice->refresh();
        });
    }


    /**
     * Get invoice statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_invoices' => SalesInvoice::count(),
            'unpaid_invoices' => SalesInvoice::unpaid()->count(),
            'partial_invoices' => SalesInvoice::partial()->count(),
            'paid_invoices' => SalesInvoice::paid()->count(),
            'overdue_invoices' => SalesInvoice::overdue()->count(),
            'total_sales' => SalesInvoice::sum('total_amount'),
            'total_outstanding' => SalesInvoice::whereIn('status', ['unpaid', 'partial'])
                ->get()
                ->sum('outstanding_balance'),
        ];
    }
}
