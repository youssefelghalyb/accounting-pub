<x-dashboard :pageTitle="__('finance::party.party_details')">
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('finance.parties.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('finance::party.parties') }}
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
                    <span class="text-gray-900 font-medium">{{ $party->name }}</span>
                </li>
            </ol>
        </nav>

        <!-- Party Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-{{ $party->type_color }}-500 to-{{ $party->type_color }}-600 flex items-center justify-center text-white text-2xl font-bold">
                        {{ strtoupper(substr($party->name, 0, 2)) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $party->name }}</h1>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-{{ $party->type_color }}-100 text-{{ $party->type_color }}-800">
                                {{ $party->type_label }}
                            </span>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                {{ $party->role_label }}
                            </span>
                            @if ($party->is_active)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ __('common.active') }}
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    {{ __('common.inactive') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('finance.parties.edit', $party) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        {{ __('common.edit') }}
                    </a>

                    <a href="{{ route('finance.parties.account-statement', $party) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        {{ __('finance::party.account_statement') }}
                    </a>
                </div>
            </div>

            <!-- Party Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6 pt-6 border-t border-gray-200">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('finance::party.email') }}</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $party->email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('finance::party.phone') }}</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $party->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('finance::party.tax_number') }}</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $party->tax_number ?? '-' }}</p>
                </div>
                <div class="md:col-span-3">
                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('finance::party.address') }}</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $party->address ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Total Sales -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::party.total_sales') }}</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_sales'], 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $stats['sales_count'] }} {{ __('finance::party.invoices') }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Purchases -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::party.total_purchases') }}</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_purchases'], 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $stats['purchases_count'] }} {{ __('finance::party.invoices') }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Receipts -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::party.receipts') }}</p>
                        <p class="text-2xl font-bold text-green-600 mt-2">{{ number_format($stats['total_payments'], 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $stats['receipts_count'] }} {{ __('finance::party.vouchers') }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Payments -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::party.payments') }}</p>
                        <p class="text-2xl font-bold text-red-600 mt-2">{{ number_format($stats['total_payments_made'], 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $stats['payments_count'] }} {{ __('finance::party.vouchers') }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Transaction Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('finance::party.transaction_type') }}</label>
                    <select name="filter_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="all" {{ $filterType == 'all' ? 'selected' : '' }}>{{ __('finance::party.all_transactions') }}</option>
                        <option value="sales" {{ $filterType == 'sales' ? 'selected' : '' }}>{{ __('finance::party.sales_only') }}</option>
                        <option value="purchases" {{ $filterType == 'purchases' ? 'selected' : '' }}>{{ __('finance::party.purchases_only') }}</option>
                        <option value="receipts" {{ $filterType == 'receipts' ? 'selected' : '' }}>{{ __('finance::party.receipts_only') }}</option>
                        <option value="payments" {{ $filterType == 'payments' ? 'selected' : '' }}>{{ __('finance::party.payments_only') }}</option>
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('common.status') }}</label>
                    <select name="filter_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('common.all') }}</option>
                        <option value="unpaid" {{ $filterStatus == 'unpaid' ? 'selected' : '' }}>{{ __('finance::invoice.status_unpaid') }}</option>
                        <option value="partial" {{ $filterStatus == 'partial' ? 'selected' : '' }}>{{ __('finance::invoice.status_partial') }}</option>
                        <option value="paid" {{ $filterStatus == 'paid' ? 'selected' : '' }}>{{ __('finance::invoice.status_paid') }}</option>
                        <option value="cancelled" {{ $filterStatus == 'cancelled' ? 'selected' : '' }}>{{ __('finance::invoice.status_cancelled') }}</option>
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('common.date_from') }}</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('common.date_to') }}</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Filter Button -->
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        {{ __('common.filter') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Sales Invoices -->
        @if(in_array($filterType, ['all', 'sales']) && $salesInvoices->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">{{ __('finance::party.sales_invoices') }} ({{ $salesInvoices->count() }})</h2>
                <a href="{{ route('finance.sales-invoices.create', ['party_id' => $party->id]) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ __('finance::invoice.create_invoice') }}
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('finance::invoice.invoice_number') }}</th>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('common.date') }}</th>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('finance::invoice.due_date') }}</th>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-xs font-semibold text-gray-600 uppercase">{{ __('finance::invoice.total_amount') }}</th>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-xs font-semibold text-gray-600 uppercase">{{ __('finance::invoice.paid_amount') }}</th>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-xs font-semibold text-gray-600 uppercase">{{ __('finance::invoice.outstanding_balance') }}</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('common.status') }}</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($salesInvoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('finance.sales-invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-700 font-medium">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-' }}</td>
                            <td class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-sm font-medium text-gray-900">{{ number_format($invoice->total_amount, 2) }}</td>
                            <td class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-sm font-medium text-green-600">{{ number_format($invoice->paid_amount, 2) }}</td>
                            <td class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-sm font-medium {{ $invoice->outstanding_balance > 0 ? 'text-red-600' : 'text-gray-900' }}">
                                {{ number_format($invoice->outstanding_balance, 2) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-{{ $invoice->status_color }}-100 text-{{ $invoice->status_color }}-800">
                                    {{ $invoice->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('finance.sales-invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Purchase Invoices -->
        @if(in_array($filterType, ['all', 'purchases']) && $purchaseInvoices->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">{{ __('finance::party.purchase_invoices') }} ({{ $purchaseInvoices->count() }})</h2>
                <a href="{{ route('finance.purchase-invoices.create', ['party_id' => $party->id]) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ __('finance::invoice.create_invoice') }}
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('finance::invoice.invoice_number') }}</th>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('common.date') }}</th>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('finance::invoice.due_date') }}</th>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-xs font-semibold text-gray-600 uppercase">{{ __('finance::invoice.total_amount') }}</th>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-xs font-semibold text-gray-600 uppercase">{{ __('finance::invoice.paid_amount') }}</th>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-xs font-semibold text-gray-600 uppercase">{{ __('finance::invoice.outstanding_balance') }}</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('common.status') }}</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($purchaseInvoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('finance.purchase-invoices.show', $invoice) }}" class="text-orange-600 hover:text-orange-700 font-medium">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-' }}</td>
                            <td class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-sm font-medium text-gray-900">{{ number_format($invoice->total_amount, 2) }}</td>
                            <td class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-sm font-medium text-green-600">{{ number_format($invoice->paid_amount, 2) }}</td>
                            <td class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-sm font-medium {{ $invoice->outstanding_balance > 0 ? 'text-red-600' : 'text-gray-900' }}">
                                {{ number_format($invoice->outstanding_balance, 2) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-{{ $invoice->status_color ?? 'gray' }}-100 text-{{ $invoice->status_color ?? 'gray' }}-800">
                                    {{ $invoice->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('finance.purchase-invoices.show', $invoice) }}" class="text-orange-600 hover:text-orange-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Receipt Vouchers -->
        @if(in_array($filterType, ['all', 'receipts']) && $receiptVouchers->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">{{ __('finance::party.receipt_vouchers') }} ({{ $receiptVouchers->count() }})</h2>
                <a href="{{ route('finance.receipt-vouchers.create', ['party_id' => $party->id]) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ __('finance::receipt.create_receipt') }}
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('finance::receipt.voucher_number') }}</th>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('common.date') }}</th>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('finance::receipt.linked_invoice') }}</th>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-xs font-semibold text-gray-600 uppercase">{{ __('finance::receipt.amount') }}</th>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('finance::receipt.payment_method') }}</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($receiptVouchers as $receipt)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('finance.receipt-vouchers.show', $receipt) }}" class="text-green-600 hover:text-green-700 font-medium">
                                    {{ $receipt->voucher_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $receipt->voucher_date->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                @if($receipt->salesInvoice)
                                    <a href="{{ route('finance.sales-invoices.show', $receipt->salesInvoice) }}" class="text-blue-600 hover:text-blue-700">
                                        {{ $receipt->salesInvoice->invoice_number }}
                                    </a>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-sm font-medium text-green-600">{{ number_format($receipt->amount, 2) }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-{{ $receipt->payment_method_color }}-100 text-{{ $receipt->payment_method_color }}-800">
                                    {{ $receipt->payment_method_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('finance.receipt-vouchers.show', $receipt) }}" class="text-green-600 hover:text-green-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Payment Vouchers -->
        @if(in_array($filterType, ['all', 'payments']) && $paymentVouchers->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">{{ __('finance::party.payment_vouchers') }} ({{ $paymentVouchers->count() }})</h2>
                <a href="{{ route('finance.payment-vouchers.create', ['party_id' => $party->id]) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ __('finance::payment.create_payment') }}
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('finance::payment.voucher_number') }}</th>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('common.date') }}</th>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('finance::payment.linked_invoice') }}</th>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-xs font-semibold text-gray-600 uppercase">{{ __('finance::payment.amount') }}</th>
                            <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('finance::payment.payment_method') }}</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($paymentVouchers as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('finance.payment-vouchers.show', $payment) }}" class="text-red-600 hover:text-red-700 font-medium">
                                    {{ $payment->voucher_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $payment->voucher_date->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                @if($payment->purchaseInvoice)
                                    <a href="{{ route('finance.purchase-invoices.show', $payment->purchaseInvoice) }}" class="text-orange-600 hover:text-orange-700">
                                        {{ $payment->purchaseInvoice->invoice_number }}
                                    </a>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-sm font-medium text-red-600">{{ number_format($payment->amount, 2) }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ $payment->payment_method_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('finance.payment-vouchers.show', $payment) }}" class="text-red-600 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Empty State -->
        @if(
            (in_array($filterType, ['all', 'sales']) && $salesInvoices->count() == 0) &&
            (in_array($filterType, ['all', 'purchases']) && $purchaseInvoices->count() == 0) &&
            (in_array($filterType, ['all', 'receipts']) && $receiptVouchers->count() == 0) &&
            (in_array($filterType, ['all', 'payments']) && $paymentVouchers->count() == 0)
        )
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('finance::party.no_transactions_found') }}</h3>
            <p class="text-gray-500">{{ __('finance::party.no_transactions_message') }}</p>
        </div>
        @endif
    </div>
</x-dashboard>