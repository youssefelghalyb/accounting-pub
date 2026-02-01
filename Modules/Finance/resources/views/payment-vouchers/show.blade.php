<x-dashboard :pageTitle="$paymentVoucher->voucher_number">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('finance.payment-vouchers.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('finance::payment.payment_vouchers') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ $paymentVoucher->voucher_number }}</span>
                </li>
            </ol>
        </nav>

        <!-- Action Buttons -->
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">{{ $paymentVoucher->voucher_number }}</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('finance.payment-vouchers.edit', $paymentVoucher) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    {{ __('finance::payment.edit') }}
                </a>

                <button onclick="window.print()" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    {{ __('finance::payment.print') }}
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Payment Voucher Header -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $orgSettings->organization_name ?? config('app.name') }}</h2>
                            @if($orgSettings)
                                <p class="text-sm text-gray-600">{{ $orgSettings->address }}</p>
                                <p class="text-sm text-gray-600">{{ $orgSettings->phone }}</p>
                                <p class="text-sm text-gray-600">{{ $orgSettings->email }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500 mb-2">{{ __('finance::payment.payment_voucher') }}</div>
                            <div class="text-lg font-bold text-green-600">{{ $paymentVoucher->voucher_number }}</div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('finance::payment.paid_to') }}</h3>
                                <p class="text-base font-semibold text-gray-900">{{ $paymentVoucher->party->name }}</p>
                                @if($paymentVoucher->party->email)
                                    <p class="text-sm text-gray-600">{{ $paymentVoucher->party->email }}</p>
                                @endif
                                @if($paymentVoucher->party->phone)
                                    <p class="text-sm text-gray-600">{{ $paymentVoucher->party->phone }}</p>
                                @endif
                            </div>

                            <div class="text-right">
                                <div class="mb-3">
                                    <span class="text-sm font-medium text-gray-500">{{ __('finance::payment.date') }}:</span>
                                    <span class="text-sm text-gray-900 font-medium">{{ $paymentVoucher->voucher_date->format('Y-m-d') }}</span>
                                </div>
                                <div class="mb-3">
                                    <span class="text-sm font-medium text-gray-500">{{ __('finance::payment.payment_method') }}:</span>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $paymentVoucher->payment_method_label }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">{{ __('finance::payment.payment_details') }}</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <!-- Amount -->
                            <div class="flex justify-between items-center pb-4 border-b border-gray-200">
                                <span class="text-gray-600">{{ __('finance::payment.amount') }}:</span>
                                <span class="text-3xl font-bold text-green-600">{{ number_format($paymentVoucher->amount, 2) }}</span>
                            </div>

                            <!-- Account -->
                            <div class="flex justify-between items-center pb-4 border-b border-gray-200">
                                <span class="text-gray-600">{{ __('finance::payment.paid_from_account') }}:</span>
                                <span class="text-gray-900 font-medium">{{ $paymentVoucher->account->display_name }}</span>
                            </div>

                            <!-- Linked Invoice -->
                            @if($paymentVoucher->purchaseInvoice)
                                <div class="flex justify-between items-center pb-4 border-b border-gray-200">
                                    <span class="text-gray-600">{{ __('finance::payment.linked_invoice') }}:</span>
                                    <a href="{{ route('finance.purchase-invoices.show', $paymentVoucher->purchaseInvoice) }}" 
                                        class="text-blue-600 hover:text-blue-800 font-medium">
                                        {{ $paymentVoucher->purchaseInvoice->invoice_number }}
                                    </a>
                                </div>
                            @endif

                            <!-- Cheque Details -->
                            @if($paymentVoucher->payment_method === 'cheque')
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-blue-900 mb-3">{{ __('finance::payment.cheque_details') }}</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <span class="text-xs text-gray-600">{{ __('finance::payment.cheque_number') }}:</span>
                                            <p class="text-sm font-medium text-gray-900">{{ $paymentVoucher->cheque_number }}</p>
                                        </div>
                                        @if($paymentVoucher->cheque_date)
                                            <div>
                                                <span class="text-xs text-gray-600">{{ __('finance::payment.cheque_date') }}:</span>
                                                <p class="text-sm font-medium text-gray-900">{{ $paymentVoucher->cheque_date->format('Y-m-d') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Transaction Reference -->
                            @if($paymentVoucher->transaction_reference)
                                <div class="flex justify-between items-center pb-4 border-b border-gray-200">
                                    <span class="text-gray-600">{{ __('finance::payment.transaction_reference') }}:</span>
                                    <span class="text-gray-900 font-medium">{{ $paymentVoucher->transaction_reference }}</span>
                                </div>
                            @endif

                            <!-- Description -->
                            @if($paymentVoucher->description)
                                <div class="pt-2">
                                    <span class="text-sm font-medium text-gray-600">{{ __('finance::payment.description') }}:</span>
                                    <p class="mt-1 text-sm text-gray-900">{{ $paymentVoucher->description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payment Confirmation Box -->
                <div class="bg-green-50 border-2 border-green-200 rounded-xl p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-green-900 mb-2">{{ __('finance::payment.payment_confirmed') }}</h3>
                            <p class="text-sm text-green-800">
                                {{ __('finance::payment.payment_received_text', [
                                    'amount' => number_format($paymentVoucher->amount, 2),
                                    'vendor' => $paymentVoucher->party->name,
                                    'date' => $paymentVoucher->voucher_date->format('Y-m-d')
                                ]) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('finance::payment.quick_actions') }}</h3>
                    <div class="space-y-3">
                        @if($paymentVoucher->purchaseInvoice)
                            <a href="{{ route('finance.purchase-invoices.show', $paymentVoucher->purchaseInvoice) }}"
                                class="block w-full px-4 py-2 bg-blue-600 text-white text-center rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                {{ __('finance::payment.view_invoice') }}
                            </a>
                        @endif
                        
                        <a href="{{ route('finance.payment-vouchers.index') }}"
                            class="block w-full px-4 py-2 bg-gray-200 text-gray-700 text-center rounded-lg hover:bg-gray-300 transition-colors text-sm">
                            {{ __('finance::payment.back_to_payments') }}
                        </a>

                        <form action="{{ route('finance.payment-vouchers.destroy', $paymentVoucher) }}" method="POST" 
                            onsubmit="return confirm('{{ __('finance::payment.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                                {{ __('finance::payment.delete_payment') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('finance::payment.summary') }}</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ __('finance::payment.vendor') }}:</span>
                            <span class="text-gray-900 font-medium">{{ $paymentVoucher->party->name }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ __('finance::payment.amount') }}:</span>
                            <span class="text-green-600 font-bold">{{ number_format($paymentVoucher->amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ __('finance::payment.method') }}:</span>
                            <span class="text-gray-900 font-medium">{{ $paymentVoucher->payment_method_label }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ __('finance::payment.account') }}:</span>
                            <span class="text-gray-900 font-medium">{{ $paymentVoucher->account->display_name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Voucher Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('finance::payment.voucher_info') }}</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ __('finance::payment.created_by') }}:</span>
                            <span class="text-gray-900 font-medium">{{ $paymentVoucher->creator->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ __('finance::payment.created_at') }}:</span>
                            <span class="text-gray-900 font-medium">{{ $paymentVoucher->created_at->format('Y-m-d H:i') }}</span>
                        </div>
                        @if($paymentVoucher->updated_at != $paymentVoucher->created_at)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ __('finance::payment.last_updated') }}:</span>
                                <span class="text-gray-900 font-medium">{{ $paymentVoucher->updated_at->format('Y-m-d H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .printable-area, .printable-area * {
                visibility: visible;
            }
            .printable-area {
                position: absolute;
                left: 0;
                top: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
    @endpush
</x-dashboard>