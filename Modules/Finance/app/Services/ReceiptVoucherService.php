<?php

namespace Modules\Finance\Services;

use Modules\Finance\Models\ReceiptVoucher;
use Modules\Finance\Models\SalesInvoice;
use Modules\Finance\Models\Party;
use Modules\Finance\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReceiptVoucherService
{
    /**
     * Generate next voucher number
     */
    public function generateVoucherNumber(): string
    {
        $year = now()->year;
        $lastVoucher = ReceiptVoucher::withTrashed()
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();
        if ($lastVoucher) {
            $lastNumber = (int) substr($lastVoucher->voucher_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'RV-' . $year . '-' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new receipt voucher
     */
    public function createReceipt(array $data): ReceiptVoucher
    {
        return DB::transaction(function () use ($data) {
            // Generate voucher number
            $data['voucher_number'] = $this->generateVoucherNumber();
            $data['created_by'] = Auth::id();

            // Create receipt voucher
            $receipt = ReceiptVoucher::create($data);

            // Update account balance (add receipt amount)
            $account = Account::findOrFail($data['account_id']);
            // Balance is calculated dynamically, no need to update

            // Update party balance (reduce customer balance)
            $party = Party::findOrFail($data['party_id']);
            // Balance is calculated dynamically, no need to update

            // Update invoice if specified
            if (!empty($data['sales_invoice_id'])) {
                $invoice = SalesInvoice::findOrFail($data['sales_invoice_id']);
                $invoice->addPayment($data['amount']);
            }

            return $receipt->refresh();
        });
    }

    /**
     * Update an existing receipt voucher
     */
    public function updateReceipt(ReceiptVoucher $receipt, array $data): ReceiptVoucher
    {
        return DB::transaction(function () use ($receipt, $data) {
            // Store old values for adjustment
            $oldAmount = $receipt->amount;
            $oldInvoiceId = $receipt->sales_invoice_id;

            $data['edited_by'] = Auth::id();

            // Reverse old invoice payment
            if ($oldInvoiceId) {
                $oldInvoice = SalesInvoice::find($oldInvoiceId);
                if ($oldInvoice) {
                    $oldInvoice->paid_amount -= $oldAmount;
                    $oldInvoice->updatePaymentStatus();
                }
            }

            // Update receipt
            $receipt->update($data);

            // Apply new invoice payment
            if (!empty($data['sales_invoice_id'])) {
                $newInvoice = SalesInvoice::findOrFail($data['sales_invoice_id']);
                $newInvoice->addPayment($data['amount']);
            }

            return $receipt->refresh();
        });
    }

    /**
     * Delete a receipt voucher
     */
    public function deleteReceipt(ReceiptVoucher $receipt): bool
    {
        return DB::transaction(function () use ($receipt) {
            // Reverse invoice payment if linked
            if ($receipt->sales_invoice_id) {
                $invoice = SalesInvoice::find($receipt->sales_invoice_id);
                if ($invoice) {
                    $invoice->paid_amount -= $receipt->amount;
                    $invoice->updatePaymentStatus();
                }
            }

            // Delete receipt
            return $receipt->delete();
        });
    }

    /**
     * Get receipt statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_receipts' => ReceiptVoucher::count(),
            'total_amount' => ReceiptVoucher::sum('amount'),
            'cash_receipts' => ReceiptVoucher::where('payment_method', 'cash')->sum('amount'),
            'cheque_receipts' => ReceiptVoucher::where('payment_method', 'cheque')->sum('amount'),
            'bank_transfer_receipts' => ReceiptVoucher::where('payment_method', 'bank_transfer')->sum('amount'),
            'today_receipts' => ReceiptVoucher::whereDate('voucher_date', today())->sum('amount'),
            'this_month_receipts' => ReceiptVoucher::whereYear('voucher_date', now()->year)
                ->whereMonth('voucher_date', now()->month)
                ->sum('amount'),
        ];
    }
}
