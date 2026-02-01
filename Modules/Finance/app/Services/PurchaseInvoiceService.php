<?php

namespace Modules\Finance\Services;

use Modules\Finance\Models\PurchaseInvoice;
use Modules\Finance\Models\PurchaseInvoiceItem;
use Modules\Finance\Models\Party;
use Modules\Finance\Models\PaymentVoucher;
use Modules\Product\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Settings\Models\OrganizationSetting;

class PurchaseInvoiceService
{
    /**
     * Generate next invoice number
     */
    public function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $lastInvoice = PurchaseInvoice::withTrashed()->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'PI-' . $year . '-' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new purchase invoice
     */
    public function createInvoice(array $data): PurchaseInvoice
    {
        return DB::transaction(function () use ($data) {
            // Generate invoice number
            $data['invoice_number'] = $this->generateInvoiceNumber();
            $data['created_by'] = Auth::id();
            $data['status'] = 'unpaid';

            // Extract items (may be empty)
            $items = $data['items'] ?? [];
            unset($data['items']);

            // Extract manual amount if provided
            $manualAmount = $data['manual_amount'] ?? null;
            unset($data['manual_amount']);

            // Extract payment info if exists
            $paidAmount = $data['paid_amount'] ?? 0;
            $accountId = $data['account_id'] ?? null;
            unset($data['paid_amount'], $data['account_id']);

            // Handle tax rate
            $isTaxable = $data['is_taxable'] ?? false;
            $taxRate = 0;

            if ($isTaxable) {
                // Get tax rate from organization settings if not provided
                if (!isset($data['tax_rate']) || $data['tax_rate'] === null) {
                    $orgSettings = OrganizationSetting::first();
                    $taxRate = $orgSettings->tax_rate ?? 0;
                } else {
                    $taxRate = $data['tax_rate'];
                }
            }

            $data['tax_rate'] = $taxRate;
            unset($data['is_taxable']); // Remove is_taxable as it's not a database column

            // Initialize amounts
            $data['subtotal_amount'] = 0;
            $data['tax_amount'] = 0;
            $data['discount_amount'] = $data['discount_amount'] ?? 0;
            $data['total_amount'] = 0;
            $data['paid_amount'] = 0;

            // Create invoice
            $invoice = PurchaseInvoice::create($data);

            // Handle manual amount (for services/expenses without items)
            if ($manualAmount !== null && $manualAmount > 0) {
                $invoice->subtotal_amount = $manualAmount;

                // Calculate discount first
                $amountAfterDiscount = $invoice->subtotal_amount - $invoice->discount_amount;

                // Calculate tax on manual amount ONLY if taxable
                if ($isTaxable && $invoice->tax_rate > 0) {
                    $invoice->tax_amount = ($amountAfterDiscount * $invoice->tax_rate) / 100;
                } else {
                    $invoice->tax_amount = 0;
                }

                // Calculate total
                $invoice->total_amount = $amountAfterDiscount + $invoice->tax_amount;
                $invoice->save();
            }
            // Handle items-based invoice
            elseif (!empty($items)) {
                foreach ($items as $itemData) {
                    $product = Product::findOrFail($itemData['product_id']);

                    $item = new PurchaseInvoiceItem([
                        'purchase_invoice_id' => $invoice->id,
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
                }

                // Calculate totals from items
                $invoice->calculateTotals();
            }

            // Create payment voucher if payment provided
            if ($paidAmount > 0 && $accountId) {
                $paymentService = new PaymentVoucherService();
                $paymentService->createPayment([
                    'party_id' => $invoice->party_id,
                    'account_id' => $accountId,
                    'purchase_invoice_id' => $invoice->id,
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
     * Update an existing purchase invoice
     */
    public function updateInvoice(PurchaseInvoice $invoice, array $data): PurchaseInvoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            // Store old total for balance adjustment
            $oldTotal = $invoice->total_amount;

            $data['edited_by'] = Auth::id();

            // Extract items
            $items = $data['items'];
            unset($data['items']);

            // Update invoice
            $invoice->update($data);

            // Delete old items
            $invoice->items()->delete();

            // Create new items
            foreach ($items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                $item = new PurchaseInvoiceItem([
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
            }

            // Recalculate totals
            $invoice->calculateTotals();



            return $invoice->refresh();
        });
    }

    /**
     * Delete a purchase invoice
     */
    public function deleteInvoice(PurchaseInvoice $invoice): bool
    {
        // Check if invoice has payments
        if ($invoice->paid_amount > 0) {
            throw new \Exception(__('finance::invoice.cannot_delete_has_payments'));
        }

        return DB::transaction(function () use ($invoice) {
            // Reduce party vendor balance
            // Delete invoice (items will cascade)
            return $invoice->delete();
        });
    }

    /**
     * Cancel invoice
     */
    public function cancelInvoice(PurchaseInvoice $invoice): PurchaseInvoice
    {
        if ($invoice->paid_amount > 0) {
            throw new \Exception(__('finance::invoice.cannot_cancel_has_payments'));
        }

        DB::transaction(function () use ($invoice) {

            $invoice->cancel();
        });

        return $invoice->refresh();
    }

    /**
     * Get invoice statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_invoices' => PurchaseInvoice::count(),
            'unpaid_invoices' => PurchaseInvoice::unpaid()->count(),
            'partial_invoices' => PurchaseInvoice::partial()->count(),
            'paid_invoices' => PurchaseInvoice::paid()->count(),
            'overdue_invoices' => PurchaseInvoice::overdue()->count(),
            'total_purchases' => PurchaseInvoice::sum('total_amount'),
            'total_outstanding' => PurchaseInvoice::whereIn('status', ['unpaid', 'partial'])
                ->get()
                ->sum('outstanding_balance'),
        ];
    }
}
