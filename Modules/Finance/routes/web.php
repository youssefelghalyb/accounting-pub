<?php

use Illuminate\Support\Facades\Route;
use Modules\Finance\Http\Controllers\AccountController;
use Modules\Finance\Http\Controllers\PartyController;
use Modules\Finance\Http\Controllers\PaymentVoucherController;
use Modules\Finance\Http\Controllers\PurchaseInvoiceController;
use Modules\Finance\Http\Controllers\ReceiptVoucherController;
use Modules\Finance\Http\Controllers\SalesInvoiceController;

Route::prefix('finance')->name('finance.')->middleware(['web', 'auth'])->group(function () {

    // Parties (Customers/Vendors)
    Route::resource('parties', PartyController::class);
    Route::post('parties/{party}/toggle-status', [PartyController::class, 'toggleStatus'])
        ->name('parties.toggle-status');

    Route::post('parties/quick-store', [PartyController::class, 'quickStore'])
        ->name('parties.quick-store');

    Route::get('parties/{party}/account-statement', [PartyController::class, 'accountStatement'])
        ->name('parties.account-statement');
    Route::get('parties/{party}/print-statement', [PartyController::class, 'printAccountStatement'])
        ->name('parties.print-statement');

    // Accounts (Cash & Bank)
    Route::resource('accounts', AccountController::class);
    Route::post('accounts/{account}/toggle-status', [AccountController::class, 'toggleStatus'])
        ->name('accounts.toggle-status');


    // Sales Invoices
    Route::resource('sales-invoices', SalesInvoiceController::class);
    Route::post('sales-invoices/{salesInvoice}/cancel', [SalesInvoiceController::class, 'cancel'])
        ->name('sales-invoices.cancel');
    Route::post('sales-invoices/{salesInvoice}/activate', [SalesInvoiceController::class, 'activate'])
        ->name('sales-invoices.activate');
    Route::get('sales-invoices/{salesInvoice}/export-excel', [SalesInvoiceController::class, 'exportExcel'])
        ->name('sales-invoices.export-excel');

    Route::get('products/{product}/details', [SalesInvoiceController::class, 'getProduct'])
        ->name('products.details');

    Route::get('sales-invoices/{salesInvoice}/print', [SalesInvoiceController::class, 'print'])
        ->name('sales-invoices.print');
    Route::get('q/sales-invoices/search-products', [SalesInvoiceController::class, 'searchProducts'])
        ->name('sales-invoices.search-products');

    // Receipt Vouchers
    Route::resource('receipt-vouchers', ReceiptVoucherController::class);
    Route::get('parties/{party}/invoices', [ReceiptVoucherController::class, 'getPartyInvoices'])
        ->name('parties.invoices');

    Route::get('receipt-vouchers/{receiptVoucher}/print', [ReceiptVoucherController::class, 'print'])->name('receipt-vouchers.print');
    Route::get('receipt-vouchers/{receiptVoucher}/export-excel', [ReceiptVoucherController::class, 'exportExcel'])->name('receipt-vouchers.export-excel');

    //////////////////////////////////////////////////////////////////
    Route::resource('purchase-invoices', PurchaseInvoiceController::class);
    Route::post('purchase-invoices/{purchaseInvoice}/cancel', [PurchaseInvoiceController::class, 'cancel'])
        ->name('purchase-invoices.cancel');

    // Payment Vouchers (Vendor Payments)
    Route::resource('payment-vouchers', PaymentVoucherController::class);
    Route::get('parties/{party}/purchase-invoices', [PaymentVoucherController::class, 'getPartyInvoices'])
        ->name('parties.purchase-invoices');

    Route::get('sales-invoices/check/product-stock', [SalesInvoiceController::class, 'getProductStock'])
        ->name('sales-invoices.product-stock');

    // In your routes file (web.php or finance module routes)
    Route::get('search/parties/sales', [SalesInvoiceController::class, 'searchParties'])
        ->name('parties.search');

    Route::get('parties/search/purchase', [PurchaseInvoiceController::class, 'searchParties'])
        ->name('purchase-parties.search');
});
