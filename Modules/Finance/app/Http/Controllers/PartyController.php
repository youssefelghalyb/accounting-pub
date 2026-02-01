<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Finance\Models\Party;
use Modules\Finance\Services\PartyService;
use Modules\Finance\Http\Requests\StorePartyRequest;
use Modules\Finance\Http\Requests\UpdatePartyRequest;

class PartyController extends Controller
{
    protected $partyService;

    public function __construct(PartyService $partyService)
    {
        $this->partyService = $partyService;
    }

    /**
     * Display a listing of parties
     */
    public function index(Request $request)
    {
        $query = Party::query();

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        // Filter by role
        if ($request->filled('role')) {
            if ($request->role === 'customer') {
                $query->customers();
            } elseif ($request->role === 'vendor') {
                $query->vendors();
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        $parties = $query->orderBy('created_at', 'desc')->get();
        $stats = $this->partyService->getStatistics();

        return view('finance::parties.index', compact('parties', 'stats'));
    }

    /**
     * Show the form for creating a new party
     */
    public function create()
    {
        return view('finance::parties.create');
    }

    /**
     * Store a newly created party
     */
    public function store(StorePartyRequest $request)
    {
        try {
            $this->partyService->createParty($request->validated());

            return redirect()
                ->route('finance.parties.index')
                ->with('success', __('finance::party.created_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Quick store party (for invoice creation)
     */
    public function quickStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:individual,company,online',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
        ]);

        try {
            $party = $this->partyService->createParty($request->only(['name', 'type', 'phone', 'email']));

            return response()->json([
                'success' => true,
                'party' => [
                    'id' => $party->id,
                    'text' => $party->name . ' - ' . number_format($party->customer_balance, 2),
                    'balance' => $party->customer_balance,
                ],
                'message' => __('finance::party.created_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified party
     */
    public function show(Party $party, Request $request)
    {
        // Get filter parameters
        $filterType = $request->get('filter_type', 'all'); // all, sales, purchases, receipts, payments
        $filterStatus = $request->get('filter_status');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Sales Invoices
        $salesQuery = $party->salesInvoices()->with('items');
        if ($dateFrom) $salesQuery->where('invoice_date', '>=', $dateFrom);
        if ($dateTo) $salesQuery->where('invoice_date', '<=', $dateTo);
        if ($filterStatus) $salesQuery->where('status', $filterStatus);
        $salesInvoices = $salesQuery->orderBy('invoice_date', 'desc')->get();

        // Purchase Invoices
        $purchaseQuery = $party->purchaseInvoices()->with('items');
        if ($dateFrom) $purchaseQuery->where('invoice_date', '>=', $dateFrom);
        if ($dateTo) $purchaseQuery->where('invoice_date', '<=', $dateTo);
        if ($filterStatus) $purchaseQuery->where('status', $filterStatus);
        $purchaseInvoices = $purchaseQuery->orderBy('invoice_date', 'desc')->get();

        // Receipt Vouchers
        $receiptsQuery = $party->receiptVouchers()->with('salesInvoice');
        if ($dateFrom) $receiptsQuery->where('voucher_date', '>=', $dateFrom);
        if ($dateTo) $receiptsQuery->where('voucher_date', '<=', $dateTo);
        $receiptVouchers = $receiptsQuery->orderBy('voucher_date', 'desc')->get();

        // Payment Vouchers
        $paymentsQuery = $party->paymentVouchers()->with('purchaseInvoice');
        if ($dateFrom) $paymentsQuery->where('voucher_date', '>=', $dateFrom);
        if ($dateTo) $paymentsQuery->where('voucher_date', '<=', $dateTo);
        $paymentVouchers = $paymentsQuery->orderBy('voucher_date', 'desc')->get();

        // Statistics
        $stats = [
            'total_sales' => $party->salesInvoices()->sum('total_amount'),
            'total_payments' => $party->receiptVouchers()->sum('amount'),
            'pending_invoices' => $party->salesInvoices()->whereIn('status', ['unpaid', 'partial'])->count(),
            'total_purchases' => $party->purchaseInvoices()->sum('total_amount'),
            'total_payments_made' => $party->paymentVouchers()->sum('amount'),
            'sales_count' => $salesInvoices->count(),
            'purchases_count' => $purchaseInvoices->count(),
            'receipts_count' => $receiptVouchers->count(),
            'payments_count' => $paymentVouchers->count(),
        ];

        return view('finance::parties.show', compact(
            'party',
            'stats',
            'salesInvoices',
            'purchaseInvoices',
            'receiptVouchers',
            'paymentVouchers',
            'filterType',
            'filterStatus',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Show the form for editing the specified party
     */
    public function edit(Party $party)
    {
        return view('finance::parties.edit', compact('party'));
    }

    /**
     * Update the specified party
     */
    public function update(UpdatePartyRequest $request, Party $party)
    {
        try {
            $this->partyService->updateParty($party, $request->validated());

            return redirect()
                ->route('finance.parties.index')
                ->with('success', __('finance::party.updated_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified party
     */
    public function destroy(Party $party)
    {
        try {
            $this->partyService->deleteParty($party);

            return redirect()
                ->route('finance.parties.index')
                ->with('success', __('finance::party.deleted_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle party status
     */
    public function toggleStatus(Party $party)
    {
        try {
            $this->partyService->toggleStatus($party);

            return redirect()
                ->back()
                ->with('success', __('finance::party.status_updated'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display party account statement (كشف حساب)
     */
    public function accountStatement(Party $party, Request $request)
    {
        // Date range filters
        $dateFrom = $request->get('date_from', now()->subMonths(3)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Get all transactions
        $salesInvoices = $party->salesInvoices()
            ->whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->get();

        $purchaseInvoices = $party->purchaseInvoices()
            ->whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->get();

        $receiptVouchers = $party->receiptVouchers()
            ->whereBetween('voucher_date', [$dateFrom, $dateTo])
            ->get();

        $paymentVouchers = $party->paymentVouchers()
            ->whereBetween('voucher_date', [$dateFrom, $dateTo])
            ->get();

        // Combine all transactions
        $transactions = collect();

        // Add sales invoices (Debit - customer owes us)
        foreach ($salesInvoices as $invoice) {
            $transactions->push([
                'date' => $invoice->invoice_date,
                'type' => 'sales_invoice',
                'reference' => $invoice->invoice_number,
                'description' => __('finance::party.sales_invoice'),
                'debit' => $invoice->total_amount,
                'credit' => 0,
                'balance' => 0,
                'status' => $invoice->status,
                'url' => route('finance.sales-invoices.show', $invoice),
                'model' => $invoice,
            ]);
        }

        // Add purchase invoices (Credit - we owe vendor)
        foreach ($purchaseInvoices as $invoice) {
            $transactions->push([
                'date' => $invoice->invoice_date,
                'type' => 'purchase_invoice',
                'reference' => $invoice->invoice_number,
                'description' => __('finance::party.purchase_invoice'),
                'debit' => 0,
                'credit' => $invoice->total_amount,
                'balance' => 0,
                'status' => $invoice->status,
                'url' => route('finance.purchase-invoices.show', $invoice),
                'model' => $invoice,
            ]);
        }

        // Add receipt vouchers (Credit - customer paid us)
        foreach ($receiptVouchers as $receipt) {
            $transactions->push([
                'date' => $receipt->voucher_date,
                'type' => 'receipt_voucher',
                'reference' => $receipt->voucher_number,
                'description' => $receipt->salesInvoice
                    ? __('finance::party.payment_for_invoice', ['number' => $receipt->salesInvoice->invoice_number])
                    : ($receipt->description ?? __('finance::party.receipt_payment')),
                'debit' => 0,
                'credit' => $receipt->amount,
                'balance' => 0,
                'status' => 'completed',
                'url' => route('finance.receipt-vouchers.show', $receipt),
                'model' => $receipt,
            ]);
        }

        // Add payment vouchers (Debit - we paid vendor)
        foreach ($paymentVouchers as $payment) {
            $transactions->push([
                'date' => $payment->voucher_date,
                'type' => 'payment_voucher',
                'reference' => $payment->voucher_number,
                'description' => $payment->purchaseInvoice
                    ? __('finance::party.payment_for_invoice', ['number' => $payment->purchaseInvoice->invoice_number])
                    : ($payment->description ?? __('finance::party.payment_made')),
                'debit' => $payment->amount,
                'credit' => 0,
                'balance' => 0,
                'status' => 'completed',
                'url' => route('finance.payment-vouchers.show', $payment),
                'model' => $payment,
            ]);
        }

        // Sort by date and calculate running balance using map
        $transactions = $transactions->sortBy('date')->values();

        $runningBalance = 0;
        $transactions = $transactions->map(function ($transaction) use (&$runningBalance) {
            $runningBalance += ($transaction['debit'] - $transaction['credit']);
            $transaction['balance'] = $runningBalance;
            return $transaction;
        });

        // Calculate statistics
        $stats = [
            'opening_balance' => 0,
            'total_debit' => $transactions->sum('debit'),
            'total_credit' => $transactions->sum('credit'),
            'closing_balance' => $runningBalance,
            'transaction_count' => $transactions->count(),
            'sales_count' => $salesInvoices->count(),
            'purchases_count' => $purchaseInvoices->count(),
            'receipts_count' => $receiptVouchers->count(),
            'payments_count' => $paymentVouchers->count(),
        ];

        $orgSettings = \Modules\Settings\Models\OrganizationSetting::first();

        return view('finance::parties.account-statement', compact(
            'party',
            'transactions',
            'stats',
            'dateFrom',
            'dateTo',
            'orgSettings'
        ));
    }

    /**
     * Print party account statement
     */
    public function printAccountStatement(Party $party, Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subMonths(3)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $printLang = $request->get('lang', app()->getLocale());

        // Get all transactions
        $salesInvoices = $party->salesInvoices()
            ->whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->get();

        $purchaseInvoices = $party->purchaseInvoices()
            ->whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->get();

        $receiptVouchers = $party->receiptVouchers()
            ->whereBetween('voucher_date', [$dateFrom, $dateTo])
            ->get();

        $paymentVouchers = $party->paymentVouchers()
            ->whereBetween('voucher_date', [$dateFrom, $dateTo])
            ->get();

        $transactions = collect();

        foreach ($salesInvoices as $invoice) {
            $transactions->push([
                'date' => $invoice->invoice_date,
                'type' => 'sales_invoice',
                'reference' => $invoice->invoice_number,
                'description' => __('finance::party.sales_invoice'),
                'debit' => $invoice->total_amount,
                'credit' => 0,
                'balance' => 0,
            ]);
        }

        foreach ($purchaseInvoices as $invoice) {
            $transactions->push([
                'date' => $invoice->invoice_date,
                'type' => 'purchase_invoice',
                'reference' => $invoice->invoice_number,
                'description' => __('finance::party.purchase_invoice'),
                'debit' => 0,
                'credit' => $invoice->total_amount,
                'balance' => 0,
            ]);
        }

        foreach ($receiptVouchers as $receipt) {
            $transactions->push([
                'date' => $receipt->voucher_date,
                'type' => 'receipt_voucher',
                'reference' => $receipt->voucher_number,
                'description' => $receipt->salesInvoice
                    ? __('finance::party.payment_for_invoice', ['number' => $receipt->salesInvoice->invoice_number])
                    : ($receipt->description ?? __('finance::party.receipt_payment')),
                'debit' => 0,
                'credit' => $receipt->amount,
                'balance' => 0,
            ]);
        }

        foreach ($paymentVouchers as $payment) {
            $transactions->push([
                'date' => $payment->voucher_date,
                'type' => 'payment_voucher',
                'reference' => $payment->voucher_number,
                'description' => $payment->purchaseInvoice
                    ? __('finance::party.payment_for_invoice', ['number' => $payment->purchaseInvoice->invoice_number])
                    : ($payment->description ?? __('finance::party.payment_made')),
                'debit' => $payment->amount,
                'credit' => 0,
                'balance' => 0,
            ]);
        }

        // Sort and calculate running balance using map
        $transactions = $transactions->sortBy('date')->values();

        $runningBalance = 0;
        $transactions = $transactions->map(function ($transaction) use (&$runningBalance) {
            $runningBalance += ($transaction['debit'] - $transaction['credit']);
            $transaction['balance'] = $runningBalance;
            return $transaction;
        });

        $stats = [
            'opening_balance' => 0,
            'total_debit' => $transactions->sum('debit'),
            'total_credit' => $transactions->sum('credit'),
            'closing_balance' => $runningBalance,
            'transaction_count' => $transactions->count(),
        ];

        $orgSettings = \Modules\Settings\Models\OrganizationSetting::first();

        return view('finance::parties.print-statement', compact(
            'party',
            'transactions',
            'stats',
            'dateFrom',
            'dateTo',
            'orgSettings',
            'printLang'
        ));
    }
}
