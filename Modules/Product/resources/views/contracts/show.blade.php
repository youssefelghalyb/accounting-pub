<x-dashboard :pageTitle="__('product::contract.contract_details')">
    <div class="max-w-5xl mx-auto">
        {{-- Breadcrumb --}}
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('product.contracts.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('product::contract.contracts') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                </li>
                <li><span class="text-gray-900 font-medium">{{ __('product::contract.contract') }} #{{ $contract->id }}</span></li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ __('product::contract.contract') }} #{{ $contract->id }}</h1>
                        {{-- Authors list --}}
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach ($contract->authors as $author)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium
                                    {{ $author->pivot->is_representative ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700' }}">
                                    <a href="{{ route('product.authors.show', $author) }}" class="hover:underline">
                                        {{ $author->full_name }}
                                    </a>
                                    @if ($author->pivot->is_representative)
                                        <span class="text-xs opacity-75">({{ __('product::contract.representative') }})</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                        <p class="text-sm text-gray-500 mt-2">
                            {{ isset($contract->book) ? $contract->book->product->name : $contract->book_name }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('product.contracts.edit', $contract) }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            {{ __('common.edit') }}
                        </a>
                        <form action="{{ route('product.contracts.destroy', $contract) }}" method="POST"
                              onsubmit="return confirm('{{ __('common.are_you_sure') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                {{ __('common.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm font-medium text-gray-600">{{ __('product::contract.contract_price') }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($contract->contract_price, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm font-medium text-gray-600">{{ __('product::contract.total_paid') }}</p>
                <p class="text-2xl font-bold text-green-600 mt-2">{{ number_format($contract->total_paid, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm font-medium text-gray-600">{{ __('product::contract.outstanding_balance') }}</p>
                <p class="text-2xl font-bold text-orange-600 mt-2">{{ number_format($contract->outstanding_balance, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm font-medium text-gray-600">{{ __('product::contract.payment_status') }}</p>
                @php
                    $colors = ['paid' => 'bg-green-100 text-green-800', 'partial' => 'bg-yellow-100 text-yellow-800', 'pending' => 'bg-red-100 text-red-800'];
                    $color = $colors[$contract->payment_status] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $color }} mt-2">
                    {{ __('product::contract.' . $contract->payment_status) }}
                </span>
            </div>
        </div>

        {{-- Contract Details --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::contract.contract_details') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Authors with representative badge --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-2">{{ __('product::contract.authors') }}</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($contract->authors as $author)
                                <a href="{{ route('product.authors.show', $author) }}"
                                   class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border text-sm font-medium transition hover:shadow-sm
                                       {{ $author->pivot->is_representative ? 'border-blue-300 bg-blue-50 text-blue-800' : 'border-gray-200 bg-gray-50 text-gray-700' }}">
                                    {{ $author->full_name }}
                                    @if ($author->pivot->is_representative)
                                        <span class="px-1.5 py-0.5 text-xs bg-blue-200 text-blue-800 rounded-full">
                                            {{ __('product::contract.representative') }}
                                        </span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::contract.book') }}</label>
                        @if (isset($contract->book))
                            <a href="{{ route('product.books.show', $contract->book) }}" class="text-blue-600 hover:text-blue-700 font-medium">
                                {{ $contract->book->product->name }}
                            </a>
                        @else
                            <p class="text-gray-900">{{ $contract->book_name }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::contract.contract_date') }}</label>
                        <p class="text-gray-900">{{ $contract->contract_date->format('Y-m-d') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::contract.contract_price') }}</label>
                        <p class="text-gray-900 font-bold text-lg">{{ number_format($contract->contract_price, 2) }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::contract.percentage_from_book_profit') }}</label>
                        <p class="text-gray-900 font-medium">{{ $contract->percentage_from_book_profit }}%</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('common.created_at') }}</label>
                        <p class="text-gray-900">{{ $contract->created_at->format('Y-m-d H:i') }}</p>
                    </div>

                    @if ($contract->contract_file)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-600 mb-2">{{ __('product::contract.contract_file') }}</label>
                            <a href="{{ asset('storage/' . $contract->contract_file) }}" target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                                <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                {{ __('product::contract.download_contract') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Payment History --}}
        @if ($contract->transactions()->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">{{ __('product::contract.payment_history') }}</h2>
                    <a href="{{ route('product.transactions.create', ['contract_id' => $contract->id]) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'ml-1.5' : 'mr-1.5' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('product::transaction.add_payment') }}
                    </a>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('product::transaction.payment_date') }}</th>
                                    <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('product::transaction.amount') }}</th>
                                    <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('product::transaction.notes') }}</th>
                                    <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($transactions as $transaction)
                                    <tr>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->payment_date->format('Y-m-d') }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-green-600">{{ number_format($transaction->amount, 2) }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-600">{{ $transaction->notes ?? '-' }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-end text-sm">
                                            <a href="{{ route('product.transactions.show', $transaction) }}" class="text-blue-600 hover:text-blue-700">{{ __('common.view') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('product::contract.no_payments') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('product::contract.no_payments_description') }}</p>
                    <div class="mt-6">
                        <a href="{{ route('product.transactions.create', ['contract_id' => $contract->id]) }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('product::transaction.add_payment') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-dashboard>