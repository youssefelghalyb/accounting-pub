<?php

namespace Modules\Product\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Finance\Services\PaymentVoucherService;
use Modules\Finance\Services\ReceiptVoucherService;
use Modules\Product\Models\ContractorBook;
use Modules\Product\Models\ContractTransaction;

/**
 * Records a voucher-backed transaction against a contractor_book (see
 * docs/contractor-migration-plan.md §4.2b). Never creates a ContractTransaction without first
 * creating its backing voucher — that ordering is what keeps the exactly-one-voucher invariant
 * satisfiable in practice (the model's `saving` guard is the actual enforcement).
 */
class ContractTransactionService
{
    public function __construct(
        private ContractorService $contractorService,
        private ReceiptVoucherService $receiptVoucherService,
        private PaymentVoucherService $paymentVoucherService,
    ) {}

    /**
     * @param  string  $direction  'receipt' (money in, from the contractor) or 'payment' (money out, to the contractor)
     */
    public function record(ContractorBook $contractorBook, array $data, string $direction): ContractTransaction
    {
        return DB::transaction(function () use ($contractorBook, $data, $direction) {
            $party = $this->contractorService->ensureParty($contractorBook->contractor);

            $voucherPayload = [
                'party_id' => $party->id,
                'account_id' => $data['account_id'],
                'amount' => $data['amount'],
                'voucher_date' => $data['transaction_date'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'description' => $data['notes'] ?? ucfirst(str_replace('_', ' ', $data['type'])) . ' — ' . $contractorBook->contractor->name,
            ];

            $transactionData = [
                'contractor_book_id' => $contractorBook->id,
                'type' => $data['type'],
                'amount' => $data['amount'],
                'transaction_date' => $data['transaction_date'],
                'notes' => $data['notes'] ?? null,
                'receipt_file' => $data['receipt_file'] ?? null,
                'created_by' => auth()->id(),
            ];

            if ($direction === 'receipt') {
                $voucher = $this->receiptVoucherService->createReceipt($voucherPayload);
                $transactionData['receipt_voucher_id'] = $voucher->id;
            } elseif ($direction === 'payment') {
                $voucher = $this->paymentVoucherService->createPayment($voucherPayload);
                $transactionData['payment_voucher_id'] = $voucher->id;
            } else {
                throw new \InvalidArgumentException("Invalid direction [{$direction}] — expected 'receipt' or 'payment'.");
            }

            return ContractTransaction::create($transactionData);
        });
    }

    /**
     * Deletes the transaction's dedicated voucher too — that voucher exists solely to back this
     * transaction (1:1, enforced by the unique index on receipt_voucher_id/payment_voucher_id).
     */
    public function delete(ContractTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            if ($transaction->receipt_file) {
                Storage::disk('public')->delete($transaction->receipt_file);
            }

            if ($transaction->receipt_voucher_id) {
                $this->receiptVoucherService->deleteReceipt($transaction->receiptVoucher);
            } elseif ($transaction->payment_voucher_id) {
                $this->paymentVoucherService->deletePayment($transaction->paymentVoucher);
            }

            $transaction->delete();
        });
    }
}
