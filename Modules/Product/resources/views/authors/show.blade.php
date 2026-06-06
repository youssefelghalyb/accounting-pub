<x-dashboard :pageTitle="$author->full_name">
    <div class="max-w-5xl mx-auto">
        {{-- Breadcrumb --}}
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('product.authors.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('product::author.authors') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </li>
                <li><span class="text-gray-900 font-medium">{{ $author->full_name }}</span></li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl">
                        {{ strtoupper(mb_substr($author->full_name, 0, 2)) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $author->full_name }}</h1>
                        @if ($author->occupation)
                            <p class="text-sm text-gray-500 mt-1">{{ $author->occupation }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('product.authors.edit', $author) }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        {{ __('common.edit') }}
                    </a>
                    <form action="{{ route('product.authors.destroy', $author) }}" method="POST"
                        onsubmit="return confirm('{{ __('common.are_you_sure') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            {{ __('common.delete') }}
                        </button>
                    </form>
                    <button type="button" onclick="openInvoiceModal()"
                        class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                        <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ __('finance::invoice.create_invoice') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm font-medium text-gray-600">{{ __('product::author.total_books') }}</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_books'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm font-medium text-gray-600">{{ __('product::author.total_contracts') }}</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_contracts'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm font-medium text-gray-600">{{ __('product::author.total_contract_value') }}</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_contract_value'], 2) }}
                </p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm font-medium text-gray-600">{{ __('product::author.remaining_payments') }}</p>
                <p class="text-3xl font-bold text-orange-600 mt-2">
                    {{ number_format($stats['outstanding_balance'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm font-medium text-gray-600">{{ __('product::author.gift_copies') }}</p>
                <p class="text-3xl font-bold text-purple-600 mt-2">{{ $stats['gift_copies_count'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm font-medium text-gray-600">{{ __('product::author.client_invoices') }}</p>
                <p class="text-3xl font-bold text-emerald-600 mt-2">{{ $stats['invoice_count'] }}</p>
            </div>
        </div>

        {{-- Personal Information --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::author.personal_info') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::author.full_name') }}</label>
                        <p class="text-gray-900 font-medium">{{ $author->full_name }}</p>
                    </div>
                    @if ($author->nationality)
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::author.nationality') }}</label>
                            <p class="text-gray-900">{{ $author->nationality }}</p>
                        </div>
                    @endif
                    @if ($author->country_of_residence)
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::author.country_of_residence') }}</label>
                            <p class="text-gray-900">{{ $author->country_of_residence }}</p>
                        </div>
                    @endif
                    @if ($author->occupation)
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::author.occupation') }}</label>
                            <p class="text-gray-900">{{ $author->occupation }}</p>
                        </div>
                    @endif
                    @if ($author->bio)
                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::author.bio') }}</label>
                            <p class="text-gray-900">{{ $author->bio }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Contact Information --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::author.contact_info') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if ($author->email)
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::author.email') }}</label>
                            <a href="mailto:{{ $author->email }}"
                                class="text-blue-600 hover:text-blue-700">{{ $author->email }}</a>
                        </div>
                    @endif
                    @if ($author->phone_number)
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::author.phone_number') }}</label>
                            <a href="tel:{{ $author->phone_number }}"
                                class="text-blue-600 hover:text-blue-700">{{ $author->phone_number }}</a>
                        </div>
                    @endif
                    @if ($author->whatsapp_number)
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::author.whatsapp_number') }}</label>
                            <a href="https://wa.me/{{ $author->whatsapp_number }}" target="_blank"
                                class="text-green-600 hover:text-green-700">{{ $author->whatsapp_number }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Contracts with co-authors shown --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::author.contracts') }}</h2>
            </div>
            <div class="p-6">
                @php $contracts = $author->contracts()->with('book.product', 'authors')->get(); @endphp
                @if ($contracts->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                        {{ __('product::contract.book') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                        {{ __('product::contract.co_authors') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                        {{ __('product::contract.contract_price') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                        {{ __('product::contract.total_paid') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                        {{ __('product::contract.outstanding_balance') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                        {{ __('product::contract.payment_status') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                        {{ __('common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($contracts as $contract)
                                    @php
                                        $coAuthors = $contract->authors->where('id', '!=', $author->id);
                                        $colors = [
                                            'paid' => 'bg-green-100 text-green-800',
                                            'partial' => 'bg-yellow-100 text-yellow-800',
                                            'pending' => 'bg-red-100 text-red-800',
                                        ];
                                        $color = $colors[$contract->payment_status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                            {{ isset($contract->book) ? $contract->book->product->name : $contract->book_name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            @if ($coAuthors->count() > 0)
                                                {{ $coAuthors->pluck('full_name')->implode('، ') }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ number_format($contract->contract_price, 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-green-600 font-medium">
                                            {{ number_format($contract->total_paid, 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-orange-600 font-medium">
                                            {{ number_format($contract->outstanding_balance, 2) }}</td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $color }}">
                                                {{ __('product::contract.' . $contract->payment_status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <a href="{{ route('product.contracts.show', $contract) }}"
                                                class="text-blue-600 hover:text-blue-900">{{ __('common.view') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">{{ __('product::author.no_contracts') }}</p>
                @endif
            </div>
        </div>

        {{-- Transactions --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::author.transactions') }}</h2>
            </div>
            <div class="p-6">
                @if ($transactions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                        {{ __('product::contract.book') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                        {{ __('product::transaction.payment_date') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                        {{ __('product::transaction.amount') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                        {{ __('product::transaction.notes') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                        {{ __('common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($transactions as $transaction)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                            {{ isset($transaction->contract->book) ? $transaction->contract->book->product->name : $transaction->contract->book_name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ $transaction->payment_date->format('Y-m-d') }}</td>
                                        <td class="px-6 py-4 text-sm text-green-600 font-bold">
                                            {{ number_format($transaction->amount, 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $transaction->notes ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <a href="{{ route('product.transactions.show', $transaction) }}"
                                                class="text-blue-600 hover:text-blue-900">{{ __('common.view') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">{{ __('product::author.no_transactions') }}</p>
                @endif
            </div>
        </div>

        @if ($author->id_image)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">{{ __('product::author.additional_info') }}</h2>
                </div>
                <div class="p-6">
                    <label
                        class="block text-sm font-medium text-gray-600 mb-2">{{ __('product::author.id_image') }}</label>
                    <img src="{{ asset('storage/' . $author->id_image) }}" alt="ID Image"
                        class="max-w-md rounded-lg border border-gray-300">
                </div>
            </div>
        @endif

        {{-- Client Invoices --}}
        @if ($author->party_id)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">{{ __('product::author.client_invoices') }}</h2>
                    <a href="{{ route('finance.sales-invoices.create', ['party_id' => $author->party_id]) }}"
                        class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                        + {{ __('finance::invoice.create_invoice') }}
                    </a>
                </div>
                <div class="p-6">
                    @php $invoices = $author->salesInvoices(); @endphp
                    @if ($invoices->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                            {{ __('finance::invoice.invoice_number') }}</th>
                                        <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                            {{ __('finance::invoice.invoice_date') }}</th>
                                        <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                            {{ __('finance::invoice.total_amount') }}</th>
                                        <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                            {{ __('finance::invoice.status') }}</th>
                                        <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                            {{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($invoices as $invoice)
                                        @php
                                            $statusColors = [
                                                'paid' => 'bg-green-100 text-green-800',
                                                'partial' => 'bg-yellow-100 text-yellow-800',
                                                'unpaid' => 'bg-red-100 text-red-800',
                                                'cancelled' => 'bg-gray-100 text-gray-800',
                                            ];
                                            $color = $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                                {{ $invoice->invoice_number }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-600">
                                                {{ $invoice->invoice_date->format('Y-m-d') }}</td>
                                            <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                                {{ number_format($invoice->total_amount, 2) }}</td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $color }}">
                                                    {{ __('finance::invoice.statuses.' . $invoice->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                <a href="{{ route('finance.sales-invoices.show', $invoice) }}"
                                                    class="text-blue-600 hover:text-blue-900">{{ __('common.view') }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">{{ __('product::author.no_invoices_yet') }}</p>
                    @endif
                </div>
            </div>

            {{-- Receipt Vouchers --}}
            @if ($author->party_id)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900">{{ __('finance::receipt.receipt_vouchers') }}
                        </h2>
                        <a href="{{ route('finance.receipt-vouchers.create', ['party_id' => $author->party_id]) }}"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                            + {{ __('finance::voucher.create_receipt_voucher') }}
                        </a>
                    </div>
                    <div class="p-6">
                        @php $receiptVouchers = $author->receiptVouchers(); @endphp
                        @if ($receiptVouchers->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                {{ __('finance::voucher.voucher_number') }}</th>
                                            <th
                                                class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                {{ __('finance::voucher.voucher_date') }}</th>
                                            <th
                                                class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                {{ __('finance::voucher.amount') }}</th>
                                            <th
                                                class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                {{ __('finance::voucher.notes') }}</th>
                                            <th
                                                class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                {{ __('common.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($receiptVouchers as $voucher)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                                    {{ $voucher->voucher_number }}</td>
                                                <td class="px-6 py-4 text-sm text-gray-600">
                                                    {{ $voucher->voucher_date->format('Y-m-d') }}</td>
                                                <td class="px-6 py-4 text-sm font-bold text-blue-600">
                                                    {{ number_format($voucher->amount, 2) }}</td>
                                                <td class="px-6 py-4 text-sm text-gray-600">
                                                    {{ $voucher->notes ?? '-' }}</td>
                                                <td class="px-6 py-4 text-sm">
                                                    <a href="{{ route('finance.receipt-vouchers.show', $voucher) }}"
                                                        class="text-blue-600 hover:text-blue-900">{{ __('common.view') }}</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">
                                {{ __('finance::voucher.no_receipt_vouchers') }}</p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Payment Vouchers --}}
            @if ($author->party_id)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900">{{ __('finance::payment.payment_vouchers') }}
                        </h2>
                        <a href="{{ route('finance.payment-vouchers.create', ['party_id' => $author->party_id]) }}"
                            class="text-sm text-orange-600 hover:text-orange-700 font-medium">
                            + {{ __('finance::voucher.create_payment_voucher') }}
                        </a>
                    </div>
                    <div class="p-6">
                        @php $paymentVouchers = $author->paymentVouchers(); @endphp
                        @if ($paymentVouchers->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                {{ __('finance::voucher.voucher_number') }}</th>
                                            <th
                                                class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                {{ __('finance::voucher.voucher_date') }}</th>
                                            <th
                                                class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                {{ __('finance::voucher.amount') }}</th>
                                            <th
                                                class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                {{ __('finance::voucher.notes') }}</th>
                                            <th
                                                class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                {{ __('common.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($paymentVouchers as $voucher)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                                    {{ $voucher->voucher_number }}</td>
                                                <td class="px-6 py-4 text-sm text-gray-600">
                                                    {{ $voucher->voucher_date->format('Y-m-d') }}</td>
                                                <td class="px-6 py-4 text-sm font-bold text-orange-600">
                                                    {{ number_format($voucher->amount, 2) }}</td>
                                                <td class="px-6 py-4 text-sm text-gray-600">
                                                    {{ $voucher->notes ?? '-' }}</td>
                                                <td class="px-6 py-4 text-sm">
                                                    <a href="{{ route('finance.payment-vouchers.show', $voucher) }}"
                                                        class="text-blue-600 hover:text-blue-900">{{ __('common.view') }}</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">
                                {{ __('finance::voucher.no_payment_vouchers') }}</p>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        {{-- Gift Copies --}}
        @if ($stats['gift_copies_count'] > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">{{ __('product::author.gift_copies') }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ __('product::author.gift_copies_desc') }}</p>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                        {{ __('finance::invoice.invoice_number') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                        {{ __('product::author.book') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                        {{ __('finance::invoice.quantity') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                        {{ __('finance::invoice.invoice_date') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($author->giftCopies() as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                            <a href="{{ route('finance.sales-invoices.show', $item->salesInvoice) }}"
                                                class="text-blue-600 hover:text-blue-900">
                                                {{ $item->salesInvoice->invoice_number }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $item->product_name }}</td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                × {{ $item->quantity }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            {{ $item->salesInvoice->invoice_date->format('Y-m-d') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif


    </div>
    {{-- Register as Client Modal --}}
    <div id="registerClientModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeInvoiceModal()"></div>
        <div
            class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-2xl shadow-2xl">

            {{-- Icon header --}}
            <div class="p-6 text-center border-b border-gray-100">
                <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">
                    {{ __('product::author.register_as_client_title') }}
                </h3>
                <p class="text-sm text-gray-500 mt-2">
                    {{ __('product::author.register_as_client_desc', ['name' => $author->full_name]) }}
                </p>
            </div>

            {{-- Details preview --}}
            <div class="px-6 py-4 bg-gray-50 mx-6 mt-4 rounded-xl text-sm space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-500">{{ __('finance::party.name') }}</span>
                    <span class="font-medium text-gray-900">{{ $author->full_name }}</span>
                </div>
                @if ($author->email)
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('finance::party.email') }}</span>
                        <span class="font-medium text-gray-900">{{ $author->email }}</span>
                    </div>
                @endif
                @if ($author->phone_number)
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('finance::party.phone') }}</span>
                        <span class="font-medium text-gray-900">{{ $author->phone_number }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500">{{ __('finance::party.type') }}</span>
                    <span class="font-medium text-gray-900">{{ __('finance::party.types.individual') }}</span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="p-6 flex gap-3">
                <button type="button" onclick="closeInvoiceModal()"
                    class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 font-medium transition-colors">
                    {{ __('common.cancel') }}
                </button>
                <button type="button" id="confirmRegisterBtn" onclick="confirmRegisterAsClient()"
                    class="flex-1 px-4 py-3 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 font-medium transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ __('product::author.confirm_and_create_invoice') }}
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const isAlreadyClient = {{ $author->party_id ? 'true' : 'false' }};
            const registerUrl = '{{ route('product.authors.register-as-client', $author) }}';
            const csrfToken = '{{ csrf_token() }}';

            function openInvoiceModal() {
                if (isAlreadyClient) {
                    // Already a client — go straight to invoice creation
                    window.location.href =
                        '{{ route('finance.sales-invoices.create', ['party_id' => $author->party_id ?? '']) }}';
                    return;
                }
                document.getElementById('registerClientModal').classList.remove('hidden');
            }

            function closeInvoiceModal() {
                document.getElementById('registerClientModal').classList.add('hidden');
            }

            async function confirmRegisterAsClient() {
                const btn = document.getElementById('confirmRegisterBtn');
                const orig = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = `
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ __('common.saving') }}...`;

                try {
                    const res = await fetch(registerUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                    });
                    const data = await res.json();

                    if (data.success) {
                        window.location.href = data.invoice_url;
                    } else {
                        alert('{{ __('common.error_occurred') }}');
                        btn.disabled = false;
                        btn.innerHTML = orig;
                    }
                } catch {
                    alert('{{ __('common.error_occurred') }}');
                    btn.disabled = false;
                    btn.innerHTML = orig;
                }
            }

            // Close on Escape
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeInvoiceModal();
            });
        </script>
    @endpush
</x-dashboard>
