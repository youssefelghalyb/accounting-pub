<x-dashboard :pageTitle="__('finance::receipt.receipt_details')">
    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('finance.receipt-vouchers.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('finance::receipt.receipt_vouchers') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ $receiptVoucher->voucher_number }}</span>
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $receiptVoucher->voucher_number }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ __('finance::receipt.receipt_voucher') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('finance.receipt-vouchers.edit', $receiptVoucher) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    {{ __('common.edit') }}
                </a>
                
                <form action="{{ route('finance.receipt-vouchers.destroy', $receiptVoucher) }}" method="POST" class="inline"
                      onsubmit="return confirm('{{ __('finance::receipt.confirm_delete') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        {{ __('common.delete') }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Receipt Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <!-- Amount Banner -->
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-8 text-center">
                <p class="text-white text-sm font-medium uppercase tracking-wide">{{ __('finance::receipt.amount') }}</p>
                <p class="text-white text-5xl font-bold mt-2">{{ number_format($receiptVoucher->amount, 2) }}</p>
                <p class="text-green-100 text-sm mt-2">{{ $receiptVoucher->voucher_date->format('F d, Y') }}</p>
            </div>

            <!-- Receipt Details -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Party Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-3">{{ __('finance::receipt.party') }}</h3>
                        <p class="font-bold text-gray-900 text-lg">{{ $receiptVoucher->party->name }}</p>
                        @if($receiptVoucher->party->email)
                            <p class="text-sm text-gray-600 mt-1">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                {{ $receiptVoucher->party->email }}
                            </p>
                        @endif
                        @if($receiptVoucher->party->phone)
                            <p class="text-sm text-gray-600 mt-1">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                {{ $receiptVoucher->party->phone }}
                            </p>
                        @endif
                    </div>

                    <!-- Account Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-3">{{ __('finance::receipt.account') }}</h3>
                        <p class="font-bold text-gray-900 text-lg">{{ $receiptVoucher->account->account_name }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-{{ $receiptVoucher->account->type_color }}-100 text-{{ $receiptVoucher->account->type_color }}-800">
                                {{ $receiptVoucher->account->type_label }}
                            </span>
                        </div>
                        @if($receiptVoucher->account->account_number)
                            <p class="text-sm text-gray-600 mt-2">{{ __('finance::account.account_number') }}: {{ $receiptVoucher->account->account_number }}</p>
                        @endif
                        @if($receiptVoucher->account->bank_name)
                            <p class="text-sm text-gray-600 mt-1">{{ $receiptVoucher->account->bank_name }}</p>
                        @endif
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 pt-6 border-t border-gray-200">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('finance::receipt.voucher_date') }}</p>
                        <p class="text-sm text-gray-900 mt-1">{{ $receiptVoucher->voucher_date->format('Y-m-d') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('finance::receipt.payment_method') }}</p>
                        <div class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-{{ $receiptVoucher->payment_method_color }}-100 text-{{ $receiptVoucher->payment_method_color }}-800">
                                {{ $receiptVoucher->payment_method_label }}
                            </span>
                        </div>
                    </div>
                    @if($receiptVoucher->reference_number)
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('finance::receipt.reference_number') }}</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $receiptVoucher->reference_number }}</p>
                        </div>
                    @endif
                </div>

                <!-- Linked Invoice -->
                @if($receiptVoucher->salesInvoice)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ __('finance::receipt.linked_invoice') }}</h3>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-blue-900">{{ $receiptVoucher->salesInvoice->invoice_number }}</p>
                                    <p class="text-sm text-blue-700 mt-1">
                                        {{ __('common.date') }}: {{ $receiptVoucher->salesInvoice->invoice_date->format('Y-m-d') }}
                                    </p>
                                    <p class="text-sm text-blue-700">
                                        {{ __('finance::invoice.total_amount') }}: {{ number_format($receiptVoucher->salesInvoice->total_amount, 2) }}
                                    </p>
                                    <p class="text-sm text-blue-700">
                                        {{ __('finance::invoice.outstanding_balance') }}: {{ number_format($receiptVoucher->salesInvoice->outstanding_balance, 2) }}
                                    </p>
                                </div>
                                <a href="{{ route('finance.sales-invoices.show', $receiptVoucher->salesInvoice) }}"
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    {{ __('common.view') }}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <p class="text-sm text-gray-500 italic">{{ __('finance::receipt.no_invoice') }}</p>
                    </div>
                @endif

                <!-- Description -->
                @if($receiptVoucher->description)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">{{ __('finance::receipt.description') }}</h3>
                        <p class="text-sm text-gray-600">{{ $receiptVoucher->description }}</p>
                    </div>
                @endif

                <!-- Notes -->
                @if($receiptVoucher->notes)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">{{ __('finance::receipt.notes') }}</h3>
                        <p class="text-sm text-gray-600">{{ $receiptVoucher->notes }}</p>
                    </div>
                @endif

                <!-- Audit Info -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-500">
                        <div>
                            <p>{{ __('common.created_by') }}: {{ $receiptVoucher->createdBy?->name ?? __('common.system') }}</p>
                            <p>{{ __('common.created_at') }}: {{ $receiptVoucher->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        @if($receiptVoucher->editedBy)
                            <div>
                                <p>{{ __('common.edited_by') }}: {{ $receiptVoucher->editedBy->name }}</p>
                                <p>{{ __('common.updated_at') }}: {{ $receiptVoucher->updated_at->format('Y-m-d H:i') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Party Balance -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::party.customer_balance') }}</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($receiptVoucher->party->customer_balance, 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Account Balance -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::account.current_balance') }}</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($receiptVoucher->account->current_balance, 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Receipt Amount -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::receipt.amount') }}</p>
                        <p class="text-2xl font-bold text-green-600 mt-2">{{ number_format($receiptVoucher->amount, 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard>