<x-dashboard :pageTitle="__('finance::invoice.invoice_details')">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('finance.sales-invoices.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('finance::invoice.sales_invoices') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ $salesInvoice->invoice_number }}</span>
                </li>
            </ol>
        </nav>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $salesInvoice->invoice_number }}</h1>
                <div class="flex items-center gap-2 mt-2">
                    <span
                        class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-{{ $salesInvoice->status_color }}-100 text-{{ $salesInvoice->status_color }}-800">
                        {{ $salesInvoice->status_label }}
                    </span>
                    @if ($salesInvoice->is_overdue)
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                            {{ __('finance::invoice.overdue') }} - {{ $salesInvoice->days_overdue }}
                            {{ __('common.days') }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if (!in_array($salesInvoice->status, ['paid', 'cancelled']))
                    <a href="{{ route('finance.sales-invoices.edit', $salesInvoice) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        {{ __('common.edit') }}
                    </a>
                @endif

                @if ($salesInvoice->status != 'cancelled')
                    <form action="{{ route('finance.sales-invoices.cancel', $salesInvoice) }}" method="POST"
                        class="inline" onsubmit="return confirm('{{ __('finance::invoice.confirm_cancel') }}')">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            {{ __('finance::invoice.cancel_invoice') }}
                        </button>
                    </form>
                @else
                    <form action="{{ route('finance.sales-invoices.activate', $salesInvoice) }}" method="POST"
                        class="inline" onsubmit="return confirm('{{ __('finance::invoice.confirm_activate') }}')">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />


                            </svg>
                            {{ __('finance::invoice.activate_invoice') }}
                        </button>
                    </form>
                @endif

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                            </path>
                        </svg>
                        {{ __('common.print') }}
                    </button>


                    <a href="{{ route('finance.sales-invoices.export-excel', $salesInvoice) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ __('common.print') }} Excel
                    </a>

                    <!-- Dropdown Menu -->
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} mt-2 w-48 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                        <div class="py-1">
                            @foreach ($orgSettings->available_languages ?? '["en"]' as $lang)
                                <a href="{{ route('finance.sales-invoices.print', ['salesInvoice' => $salesInvoice, 'lang' => $lang]) }}"
                                    target="_blank"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129">
                                            </path>
                                        </svg>
                                        {{ __('common.print_in_' . $lang) }}
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Invoice Header -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="grid grid-cols-2 gap-6">
                        <!-- From (Organization) -->
                        <div>
                            <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ __('common.from') }}</h3>
                            <p class="font-bold text-gray-900">{{ $orgSettings->organization_name }}</p>
                            @if ($orgSettings->address)
                                <p class="text-sm text-gray-600 mt-1">{{ $orgSettings->address }}</p>
                            @endif
                            @if ($orgSettings->phone)
                                <p class="text-sm text-gray-600">{{ $orgSettings->phone }}</p>
                            @endif
                            @if ($orgSettings->email)
                                <p class="text-sm text-gray-600">{{ $orgSettings->email }}</p>
                            @endif
                        </div>

                        <!-- To (Customer) -->
                        <div>
                            <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ __('common.to') }}</h3>
                            <p class="font-bold text-gray-900">{{ $salesInvoice->party->name }}</p>
                            @if ($salesInvoice->party->address)
                                <p class="text-sm text-gray-600 mt-1">{{ $salesInvoice->party->address }}</p>
                            @endif
                            @if ($salesInvoice->party->phone)
                                <p class="text-sm text-gray-600">{{ $salesInvoice->party->phone }}</p>
                            @endif
                            @if ($salesInvoice->party->email)
                                <p class="text-sm text-gray-600">{{ $salesInvoice->party->email }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t border-gray-200">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">
                                {{ __('finance::invoice.invoice_date') }}</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $salesInvoice->invoice_date->format('Y-m-d') }}
                            </p>
                        </div>
                        @if ($salesInvoice->due_date)
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase">
                                    {{ __('finance::invoice.due_date') }}</p>
                                <p class="text-sm text-gray-900 mt-1">{{ $salesInvoice->due_date->format('Y-m-d') }}
                                </p>
                            </div>
                        @endif
                        @if ($salesInvoice->payment_terms)
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase">
                                    {{ __('finance::invoice.payment_terms') }}</p>
                                <p class="text-sm text-gray-900 mt-1">{{ $salesInvoice->payment_terms }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900">{{ __('finance::invoice.items') }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">
                                        {{ __('finance::invoice.product') }}</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">
                                        {{ __('finance::invoice.quantity') }}</th>
                                    <th
                                        class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">
                                        {{ __('finance::invoice.unit_price') }}</th>
                                    <th
                                        class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">
                                        {{ __('finance::invoice.item_discount') }}</th>
                                    <th
                                        class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">
                                        {{ __('finance::invoice.line_total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($salesInvoice->items as $item)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <p class="font-medium text-gray-900">{{ $item->product_name }}</p>
                                            @if ($item->product_sku)
                                                <p class="text-xs text-gray-500">{{ $item->product_sku }}</p>
                                            @endif
                                            @if ($item->description)
                                                <p class="text-sm text-gray-600 mt-1">{{ $item->description }}</p>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="font-medium text-gray-900">{{ $item->quantity }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="text-sm text-gray-900">{{ number_format($item->unit_price, 2) }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="text-sm text-gray-900">{{ number_format($item->discount_amount, 2) }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="font-medium text-gray-900">{{ number_format($item->line_total, 2) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="p-6 bg-gray-50 border-t border-gray-200">
                        <div class="max-w-md ml-auto space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ __('finance::invoice.subtotal') }}:</span>
                                <span
                                    class="font-medium text-gray-900">{{ number_format($salesInvoice->subtotal, 2) }}</span>
                            </div>
                            @if ($salesInvoice->discount_amount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('finance::invoice.discount_amount') }}:</span>
                                    <span
                                        class="font-medium text-gray-900">-{{ number_format($salesInvoice->discount_amount, 2) }}</span>
                                </div>
                            @endif
                            @if ($salesInvoice->is_taxable)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('finance::invoice.tax_amount') }}
                                        ({{ $salesInvoice->tax_rate }}%):</span>
                                    <span
                                        class="font-medium text-gray-900">{{ number_format($salesInvoice->tax_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                                <span class="text-gray-900">{{ __('finance::invoice.total_amount') }}:</span>
                                <span class="text-blue-600">{{ number_format($salesInvoice->total_amount, 2) }}
                                    {{ $orgSettings->currency }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                @if ($salesInvoice->notes)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">{{ __('finance::invoice.notes') }}</h3>
                        <p class="text-sm text-gray-600">{{ $salesInvoice->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Payment Summary -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('finance::invoice.payment_info') }}</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">{{ __('finance::invoice.total_amount') }}:</span>
                            <span
                                class="font-medium text-gray-900">{{ number_format($salesInvoice->total_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">{{ __('finance::invoice.paid_amount') }}:</span>
                            <span
                                class="font-medium text-green-600">{{ number_format($salesInvoice->paid_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-200">
                            <span
                                class="text-sm font-semibold text-gray-900">{{ __('finance::invoice.outstanding_balance') }}:</span>
                            <span
                                class="font-bold {{ $salesInvoice->outstanding_balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($salesInvoice->outstanding_balance, 2) }}
                            </span>
                        </div>
                    </div>

                    @if ($salesInvoice->outstanding_balance > 0)
                        <a href="{{ route('finance.receipt-vouchers.create') }}?invoice={{ $salesInvoice->id }}"
                            class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            {{ __('finance::invoice.record_payment') }}
                        </a>
                    @endif
                </div>

                <!-- Payment History -->
                @if ($salesInvoice->receiptVouchers->count() > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900">{{ __('finance::invoice.payment_history') }}
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                @foreach ($salesInvoice->receiptVouchers as $receipt)
                                    <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $receipt->voucher_number }}</p>
                                            <p class="text-xs text-gray-600">
                                                {{ $receipt->voucher_date->format('Y-m-d') }}</p>
                                        </div>
                                        <span
                                            class="font-medium text-green-600">{{ number_format($receipt->amount, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-dashboard>
