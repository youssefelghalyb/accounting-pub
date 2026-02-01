<x-dashboard :pageTitle="__('finance::party.account_statement') . ' - ' . $party->name">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li><a href="{{ route('finance.parties.index') }}"
                        class="text-gray-500 hover:text-gray-700">{{ __('finance::party.parties') }}</a></li>
                <li><svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg></li>
                <li><a href="{{ route('finance.parties.show', $party) }}"
                        class="text-gray-500 hover:text-gray-700">{{ $party->name }}</a></li>
                <li><svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg></li>
                <li><span class="text-gray-900 font-medium">{{ __('finance::party.account_statement') }}</span></li>
            </ol>
        </nav>

        <!-- Header with Print Button -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('finance::party.account_statement') }}</h1>
                <p class="text-gray-600 mt-1">{{ $party->name }}</p>
            </div>

            <!-- Print Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    {{ __('common.print') }}
                </button>

                <div x-show="open" @click.away="open = false"
                    class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} mt-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                    <div class="py-1">
                        @foreach ($orgSettings->available_languages ?? ['en'] as $lang)
                            <a href="{{ route('finance.parties.print-statement', ['party' => $party, 'lang' => $lang, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                                target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ $lang == 'ar' ? 'العربية' : 'English' }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form method="GET" class="flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('common.date_from') }}</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('common.date_to') }}</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    {{ __('common.filter') }}
                </button>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-600">{{ __('finance::party.total_debit') }}</p>
                <p class="text-2xl font-bold text-red-600 mt-2">{{ number_format($stats['total_debit'], 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $stats['sales_count'] + $stats['payments_count'] }}
                    {{ __('finance::party.transactions') }}</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-600">{{ __('finance::party.total_credit') }}</p>
                <p class="text-2xl font-bold text-green-600 mt-2">{{ number_format($stats['total_credit'], 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $stats['purchases_count'] + $stats['receipts_count'] }}
                    {{ __('finance::party.transactions') }}</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-600">{{ __('finance::party.closing_balance') }}</p>
                <p
                    class="text-2xl font-bold {{ $stats['closing_balance'] > 0 ? 'text-red-600' : 'text-green-600' }} mt-2">
                    {{ number_format($stats['closing_balance'], 2) }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    @if ($stats['closing_balance'] > 0)
                        {{ __('finance::party.balance_receivable') }}
                    @elseif($stats['closing_balance'] < 0)
                        {{ __('finance::party.balance_payable') }}
                    @else
                        {{ __('finance::party.no_dues') }}
                    @endif
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $stats['closing_balance'] > 0 ? __('finance::party.receivable') : __('finance::party.payable') }}
                </p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-600">{{ __('finance::party.total_transactions') }}</p>
                <p class="text-2xl font-bold text-blue-600 mt-2">{{ $stats['transaction_count'] }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ __('finance::party.in_period') }}</p>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('finance::party.transactions') }}</h2>
            </div>

            @if ($transactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">
                                    {{ __('common.date') }}</th>
                                <th
                                    class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">
                                    {{ __('finance::party.reference') }}</th>
                                <th
                                    class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">
                                    {{ __('common.description') }}</th>
                                <th
                                    class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-xs font-semibold text-gray-600 uppercase">
                                    {{ __('finance::party.debit') }}</th>
                                <th
                                    class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-xs font-semibold text-gray-600 uppercase">
                                    {{ __('finance::party.credit') }}</th>
                                <th
                                    class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-xs font-semibold text-gray-600 uppercase">
                                    {{ __('finance::party.balance') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($transactions as $transaction)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $transaction['date']->format('Y-m-d') }}</td>
                                    <td class="px-6 py-4">
                                        <a href="{{ $transaction['url'] }}"
                                            class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                            {{ $transaction['reference'] }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $transaction['description'] }}</td>
                                    <td
                                        class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-sm font-medium text-red-600">
                                        {{ $transaction['debit'] > 0 ? number_format($transaction['debit'], 2) : '-' }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-sm font-medium text-green-600">
                                        {{ $transaction['credit'] > 0 ? number_format($transaction['credit'], 2) : '-' }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-sm font-bold {{ $transaction['balance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ number_format($transaction['balance'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t-2 border-gray-300">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-sm font-bold text-gray-900">
                                    {{ __('finance::party.total') }}</td>
                                <td
                                    class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-sm font-bold text-red-600">
                                    {{ number_format($stats['total_debit'], 2) }}
                                </td>
                                <td
                                    class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-sm font-bold text-green-600">
                                    {{ number_format($stats['total_credit'], 2) }}
                                </td>
                                <td
                                    class="px-6 py-4 text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-sm font-bold {{ $stats['closing_balance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format($stats['closing_balance'], 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="p-12 text-center">
                    <p class="text-gray-500">{{ __('finance::party.no_transactions') }}</p>
                </div>
            @endif
        </div>
    </div>
</x-dashboard>
