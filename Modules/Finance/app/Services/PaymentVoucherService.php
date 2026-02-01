<?php

namespace Modules\Finance\Services;

use Modules\Finance\Models\PaymentVoucher;
use Modules\Finance\Models\PurchaseInvoice;
use Modules\Finance\Models\Party;
use Modules\Finance\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentVoucherService
{
    /**
     * Generate next voucher number
     */
    public function generateVoucherNumber(): string
    {
        $year = now()->year;
        $lastVoucher = PaymentVoucher::withTrashed()->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastVoucher) {
            $lastNumber = (int) substr($lastVoucher->voucher_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'PV-' . $year . '-' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new payment voucher
     */
    public function createPayment(array $data): PaymentVoucher
    {
        return DB::transaction(function () use ($data) {
            // Generate voucher number
            $data['voucher_number'] = $this->generateVoucherNumber();
            $data['created_by'] = Auth::id();

            // Create payment voucher
            $payment = PaymentVoucher::create($data);

            // Update account balance (subtract payment amount - money going out)

            // Update invoice if specified
            if (!empty($data['purchase_invoice_id'])) {
                $invoice = PurchaseInvoice::findOrFail($data['purchase_invoice_id']);
                $invoice->addPayment($data['amount']);
            }

            return $payment->refresh();
        });
    }

    /**
     * Update an existing payment voucher
     */
    public function updatePayment(PaymentVoucher $payment, array $data): PaymentVoucher
    {
        return DB::transaction(function () use ($payment, $data) {
            // Store old values for adjustment
            $oldAmount = $payment->amount;
            $oldPartyId = $payment->party_id;
            $oldInvoiceId = $payment->purchase_invoice_id;

            $data['edited_by'] = Auth::id();

            // Reverse old party balance

            // Reverse old invoice payment
            if ($oldInvoiceId) {
                $oldInvoice = PurchaseInvoice::find($oldInvoiceId);
                if ($oldInvoice) {
                    $oldInvoice->paid_amount -= $oldAmount;
                    $oldInvoice->updatePaymentStatus();
                }
            }

            // Update payment
            $payment->update($data);


            // Apply new invoice payment
            if (!empty($data['purchase_invoice_id'])) {
                $newInvoice = PurchaseInvoice::findOrFail($data['purchase_invoice_id']);
                $newInvoice->addPayment($data['amount']);
            }

            return $payment->refresh();
        });
    }

    /**
     * Delete a payment voucher
     */
    public function deletePayment(PaymentVoucher $payment): bool
    {
        return DB::transaction(function () use ($payment) {
            // Reverse party balance (add back - we "unpay" vendor)
            if ($payment->purchase_invoice_id) {
                $invoice = PurchaseInvoice::find($payment->purchase_invoice_id);
                if ($invoice) {
                    $invoice->paid_amount -= $payment->amount;
                    $invoice->updatePaymentStatus();
                }
            }

            // Delete payment
            return $payment->delete();
        });
    }

    /**
     * Get payment statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_payments' => PaymentVoucher::count(),
            'total_amount' => PaymentVoucher::sum('amount'),
            'cash_payments' => PaymentVoucher::where('payment_method', 'cash')->sum('amount'),
            'cheque_payments' => PaymentVoucher::where('payment_method', 'cheque')->sum('amount'),
            'bank_transfer_payments' => PaymentVoucher::where('payment_method', 'bank_transfer')->sum('amount'),
            'today_payments' => PaymentVoucher::whereDate('voucher_date', today())->sum('amount'),
            'this_month_payments' => PaymentVoucher::whereYear('voucher_date', now()->year)
                ->whereMonth('voucher_date', now()->month)
                ->sum('amount'),
        ];
    }
}