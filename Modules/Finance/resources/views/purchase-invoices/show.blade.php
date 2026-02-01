<x-dashboard :pageTitle="$purchaseInvoice->invoice_number">
    <div class="max-w-7xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('finance.purchase-invoices.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('finance::purchase.purchase_invoices') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ $purchaseInvoice->invoice_number }}</span>
                </li>
            </ol>
        </nav>

        <!-- Action Buttons -->
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">{{ $purchaseInvoice->invoice_number }}</h1>
            <div class="flex items-center gap-3">
                @if(!in_array($purchaseInvoice->status, ['paid', 'cancelled']))
                    <a href="{{ route('finance.purchase-invoices.edit', $purchaseInvoice) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        {{ __('finance::purchase.edit') }}
                    </a>
                @endif

                @if($purchaseInvoice->status !== 'cancelled' && $purchaseInvoice->outstanding_balance > 0)
                    <a href="{{ route('finance.payment-vouchers.create', ['invoice' => $purchaseInvoice->id]) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        {{ __('finance::purchase.pay') }}
                    </a>
                @endif

                <button onclick="window.print()" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    {{ __('finance::purchase.print') }}
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Invoice Header -->
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
                            <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $purchaseInvoice->status_badge_class }}">
                                {{ $purchaseInvoice->status_label }}
                            </span>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('finance::purchase.vendor') }}</h3>
                                <p class="text-base font-semibold text-gray-900">{{ $purchaseInvoice->party->name }}</p>
                                @if($purchaseInvoice->party->email)
                                    <p class="text-sm text-gray-600">{{ $purchaseInvoice->party->email }}</p>
                                @endif
                                @if($purchaseInvoice->party->phone)
                                    <p class="text-sm text-gray-600">{{ $purchaseInvoice->party->phone }}</p>
                                @endif
                            </div>

                            <div class="text-right">
                                <div class="mb-3">
                                    <span class="text-sm font-medium text-gray-500">{{ __('finance::purchase.invoice_date') }}:</span>
                                    <span class="text-sm text-gray-900 font-medium">{{ $purchaseInvoice->invoice_date->format('Y-m-d') }}</span>
                                </div>
                                @if($purchaseInvoice->due_date)
                                    <div class="mb-3">
                                        <span class="text-sm font-medium text-gray-500">{{ __('finance::purchase.due_date') }}:</span>
                                        <span class="text-sm font-medium {{ $purchaseInvoice->isOverdue() ? 'text-red-600' : 'text-gray-900' }}">
                                            {{ $purchaseInvoice->due_date->format('Y-m-d') }}
                                        </span>
                                    </div>
                                @endif
                                @if($purchaseInvoice->reference_number)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">{{ __('finance::purchase.reference_number') }}:</span>
                                        <span class="text-sm text-gray-900 font-medium">{{ $purchaseInvoice->reference_number }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('finance::purchase.product') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('finance::purchase.quantity') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('finance::purchase.unit_price') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('finance::purchase.discount') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('finance::purchase.total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($purchaseInvoice->items as $item)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->product_name }}</div>
                                            @if($item->product_sku)
                                                <div class="text-xs text-gray-500">SKU: {{ $item->product_sku }}</div>
                                            @endif
                                            @if($item->description)
                                                <div class="text-xs text-gray-600 mt-1">{{ $item->description }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm text-gray-900">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="px-6 py-4 text-right text-sm text-gray-900">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 text-right text-sm text-gray-900">{{ number_format($item->discount_amount, 2) }}</td>
                                        <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">{{ number_format($item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="bg-gray-50 p-6 border-t border-gray-200">
                        <div class="max-w-md ml-auto space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ __('finance::purchase.subtotal') }}:</span>
                                <span class="font-medium text-gray-900">{{ number_format($purchaseInvoice->subtotal_amount, 2) }}</span>
                            </div>
                            @if($purchaseInvoice->discount_amount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('finance::purchase.discount') }}:</span>
                                    <span class="font-medium text-gray-900">-{{ number_format($purchaseInvoice->discount_amount, 2) }}</span>
                                </div>
                            @endif
                            @if($purchaseInvoice->tax_amount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('finance::purchase.tax') }} ({{ $purchaseInvoice->tax_rate }}%):</span>
                                    <span class="font-medium text-gray-900">{{ number_format($purchaseInvoice->tax_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-300">
                                <span class="text-gray-900">{{ __('finance::purchase.total') }}:</span>
                                <span class="text-blue-600">{{ number_format($purchaseInvoice->total_amount, 2) }}</span>
                            </div>
                            @if($purchaseInvoice->paid_amount > 0)
                                <div class="flex justify-between text-sm text-green-600">
                                    <span>{{ __('finance::purchase.paid') }}:</span>
                                    <span class="font-medium">{{ number_format($purchaseInvoice->paid_amount, 2) }}</span>
                                </div>
                            @endif
                            @if($purchaseInvoice->outstanding_balance > 0)
                                <div class="flex justify-between text-lg font-bold text-red-600">
                                    <span>{{ __('finance::purchase.outstanding') }}:</span>
                                    <span>{{ number_format($purchaseInvoice->outstanding_balance, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                @if($purchaseInvoice->notes)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">{{ __('finance::purchase.notes') }}</h3>
                        <p class="text-sm text-gray-600">{{ $purchaseInvoice->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Payment History -->
                @if($purchaseInvoice->paymentVouchers->count() > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900">{{ __('finance::purchase.payment_history') }}</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                @foreach($purchaseInvoice->paymentVouchers as $payment)
                                    <div class="flex items-start justify-between pb-4 border-b border-gray-200 last:border-0">
                                        <div>
                                            <a href="{{ route('finance.payment-vouchers.show', $payment) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                                {{ $payment->voucher_number }}
                                            </a>
                                            <p class="text-xs text-gray-500 mt-1">{{ $payment->voucher_date->format('Y-m-d') }}</p>
                                            <p class="text-xs text-gray-600 mt-1">{{ $payment->payment_method_label }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-bold text-green-600">{{ number_format($payment->amount, 2) }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Quick Actions -->
                @if($purchaseInvoice->status !== 'cancelled')
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('finance::purchase.quick_actions') }}</h3>
                        <div class="space-y-3">
                            @if(!in_array($purchaseInvoice->status, ['paid', 'cancelled']))
                                <form action="{{ route('finance.purchase-invoices.cancel', $purchaseInvoice) }}" method="POST" 
                                    onsubmit="return confirm('{{ __('finance::purchase.confirm_cancel') }}')">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                                        {{ __('finance::purchase.cancel_invoice') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Invoice Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('finance::purchase.invoice_details') }}</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ __('finance::purchase.created_by') }}:</span>
                            <span class="text-gray-900 font-medium">{{ $purchaseInvoice->creator->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ __('finance::purchase.created_at') }}:</span>
                            <span class="text-gray-900 font-medium">{{ $purchaseInvoice->created_at->format('Y-m-d H:i') }}</span>
                        </div>
                        @if($purchaseInvoice->updated_at != $purchaseInvoice->created_at)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ __('finance::purchase.last_updated') }}:</span>
                                <span class="text-gray-900 font-medium">{{ $purchaseInvoice->updated_at->format('Y-m-d H:i') }}</span>
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