<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Finance\Models\PaymentVoucher;
use Modules\Finance\Models\Party;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\PurchaseInvoice;
use Modules\Finance\Services\PaymentVoucherService;
use Modules\Finance\Http\Requests\StorePaymentVoucherRequest;
use Modules\Finance\Http\Requests\UpdatePaymentVoucherRequest;
use Modules\Settings\Models\OrganizationSetting;

class PaymentVoucherController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentVoucherService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of payment vouchers
     */
    public function index(Request $request)
    {
        $query = PaymentVoucher::with(['party', 'account', 'purchaseInvoice']);

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

        $payments = $query->orderBy('voucher_date', 'desc')->get();
        $stats = $this->paymentService->getStatistics();
        $parties = Party::active()->vendors()->get();
        $accounts = Account::active()->get();

        return view('finance::payment-vouchers.index', compact('payments', 'stats', 'parties', 'accounts'));
    }

    /**
     * Show the form for creating a new payment voucher
     */
    public function create(Request $request)
    {
        $parties = Party::active()->get();
        $accounts = Account::active()->get();
        
        // Get unpaid/partial invoices for dropdown
        $invoices = PurchaseInvoice::whereIn('status', ['unpaid', 'partial'])
            ->with('party')
            ->orderBy('invoice_date', 'desc')
            ->get();

        // Pre-select based on query params
        $selectedInvoice = $request->get('invoice');
        $selectedParty = null;

        if ($selectedInvoice) {
            $invoice = PurchaseInvoice::find($selectedInvoice);
            if ($invoice) {
                $selectedParty = $invoice->party_id;
            }
        }

        return view('finance::payment-vouchers.create', compact(
            'parties',
            'accounts',
            'invoices',
            'selectedInvoice',
            'selectedParty'
        ));
    }

    /**
     * Store a newly created payment voucher
     */
    public function store(StorePaymentVoucherRequest $request)
    {
        try {
            $payment = $this->paymentService->createPayment($request->validated());

            return redirect()
                ->route('finance.payment-vouchers.show', $payment)
                ->with('success', __('finance::payment.created_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified payment voucher
     */
    public function show(PaymentVoucher $paymentVoucher)
    {
        $paymentVoucher->load(['party', 'account', 'purchaseInvoice']);
        $orgSettings = OrganizationSetting::first();

        return view('finance::payment-vouchers.show', compact('paymentVoucher', 'orgSettings'));
    }

    /**
     * Show the form for editing the specified payment voucher
     */
    public function edit(PaymentVoucher $paymentVoucher)
    {
        $paymentVoucher->load(['party', 'account', 'purchaseInvoice']);
        $parties = Party::active()->vendors()->get();
        $accounts = Account::active()->get();
        
        $invoices = PurchaseInvoice::whereIn('status', ['unpaid', 'partial'])
            ->orWhere('id', $paymentVoucher->purchase_invoice_id)
            ->with('party')
            ->orderBy('invoice_date', 'desc')
            ->get();

        return view('finance::payment-vouchers.edit', compact(
            'paymentVoucher',
            'parties',
            'accounts',
            'invoices'
        ));
    }

    /**
     * Update the specified payment voucher
     */
    public function update(UpdatePaymentVoucherRequest $request, PaymentVoucher $paymentVoucher)
    {
        try {
            $this->paymentService->updatePayment($paymentVoucher, $request->validated());

            return redirect()
                ->route('finance.payment-vouchers.show', $paymentVoucher)
                ->with('success', __('finance::payment.updated_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified payment voucher
     */
    public function destroy(PaymentVoucher $paymentVoucher)
    {
        try {
            $this->paymentService->deletePayment($paymentVoucher);

            return redirect()
                ->route('finance.payment-vouchers.index')
                ->with('success', __('finance::payment.deleted_successfully'));
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
        $invoices = PurchaseInvoice::where('party_id', $partyId)
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