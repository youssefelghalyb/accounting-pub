<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Finance\Models\ReceiptVoucher;
use Modules\Finance\Models\Party;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\SalesInvoice;
use Modules\Finance\Services\ReceiptVoucherService;
use Modules\Finance\Http\Requests\StoreReceiptVoucherRequest;
use Modules\Finance\Http\Requests\UpdateReceiptVoucherRequest;

class ReceiptVoucherController extends Controller
{
    protected $receiptService;

    public function __construct(ReceiptVoucherService $receiptService)
    {
        $this->receiptService = $receiptService;
    }

    /**
     * Display a listing of receipt vouchers
     */
    public function index(Request $request)
    {
        $query = ReceiptVoucher::with(['party', 'account', 'salesInvoice']);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by party
        if ($request->filled('party_id')) {
            $query->byParty($request->party_id);
        }

        // Filter by account
        if ($request->filled('account_id')) {
            $query->byAccount($request->account_id);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->byPaymentMethod($request->payment_method);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('voucher_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('voucher_date', '<=', $request->date_to);
        }

        $receipts = $query->orderBy('voucher_date', 'desc')->get();
        $stats = $this->receiptService->getStatistics();
        $parties = Party::active()->get();
        $accounts = Account::active()->get();

        return view('finance::receipt-vouchers.index', compact('receipts', 'stats', 'parties', 'accounts'));
    }

    /**
     * Show the form for creating a new receipt voucher
     */
    public function create(Request $request)
    {
        $parties = Party::active()->get();
        $accounts = Account::active()->get();
        
        // Get unpaid/partial invoices for dropdown
        $invoices = SalesInvoice::whereIn('status', ['unpaid', 'partial'])
            ->with('party')
            ->orderBy('invoice_date', 'desc')
            ->get();

        // Pre-select based on query params
        $selectedInvoice = $request->get('invoice');
        $selectedParty = null;

        if ($selectedInvoice) {
            $invoice = SalesInvoice::find($selectedInvoice);
            if ($invoice) {
                $selectedParty = $invoice->party_id;
            }
        }

        return view('finance::receipt-vouchers.create', compact(
            'parties',
            'accounts',
            'invoices',
            'selectedInvoice',
            'selectedParty'
        ));
    }

    /**
     * Store a newly created receipt voucher
     */
    public function store(StoreReceiptVoucherRequest $request)
    {
        try {
            $receipt = $this->receiptService->createReceipt($request->validated());

            return redirect()
                ->route('finance.receipt-vouchers.show', $receipt)
                ->with('success', __('finance::receipt.created_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified receipt voucher
     */
    public function show(ReceiptVoucher $receiptVoucher)
    {
        $receiptVoucher->load(['party', 'account', 'salesInvoice']);

        return view('finance::receipt-vouchers.show', compact('receiptVoucher'));
    }

    /**
     * Show the form for editing the specified receipt voucher
     */
    public function edit(ReceiptVoucher $receiptVoucher)
    {
        $receiptVoucher->load(['party', 'account', 'salesInvoice']);
        $parties = Party::active()->get();
        $accounts = Account::active()->get();
        
        $invoices = SalesInvoice::whereIn('status', ['unpaid', 'partial'])
            ->orWhere('id', $receiptVoucher->sales_invoice_id)
            ->with('party')
            ->orderBy('invoice_date', 'desc')
            ->get();

        return view('finance::receipt-vouchers.edit', compact(
            'receiptVoucher',
            'parties',
            'accounts',
            'invoices'
        ));
    }

    /**
     * Update the specified receipt voucher
     */
    public function update(UpdateReceiptVoucherRequest $request, ReceiptVoucher $receiptVoucher)
    {
        try {
            $this->receiptService->updateReceipt($receiptVoucher, $request->validated());

            return redirect()
                ->route('finance.receipt-vouchers.show', $receiptVoucher)
                ->with('success', __('finance::receipt.updated_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified receipt voucher
     */
    public function destroy(ReceiptVoucher $receiptVoucher)
    {
        try {
            $this->receiptService->deleteReceipt($receiptVoucher);

            return redirect()
                ->route('finance.receipt-vouchers.index')
                ->with('success', __('finance::receipt.deleted_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Get unpaid invoices for a specific party (AJAX)
     */
    public function getPartyInvoices($partyId)
    {
        $invoices = SalesInvoice::where('party_id', $partyId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('invoice_date', 'desc')
            ->get()
            ->map(function($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'outstanding_balance' => $invoice->outstanding_balance,
                ];
            });

        return response()->json($invoices);
    }
}