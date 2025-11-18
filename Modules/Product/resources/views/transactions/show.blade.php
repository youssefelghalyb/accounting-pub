<x-dashboard :pageTitle="__('product::transaction.transaction_details')">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('product.transactions.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('product::transaction.transactions') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('product::transaction.transaction') }} #{{ $transaction->id }}</span>
                </li>
            </ol>
        </nav>

        <!-- Header with Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ __('product::transaction.transaction') }} #{{ $transaction->id }}</h1>
                        <p class="text-sm text-gray-500 mt-1">{{ $transaction->contract->author->full_name }} - {{ $transaction->contract->book->product->name }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('product.transactions.edit', $transaction) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            {{ __('common.edit') }}
                        </a>
                        <form action="{{ route('product.transactions.destroy', $transaction) }}" method="POST" onsubmit="return confirm('{{ __('common.are_you_sure') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                {{ __('common.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Amount Card -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-sm mb-6 text-white">
            <div class="p-8 text-center">
                <p class="text-sm font-medium opacity-90">{{ __('product::transaction.payment_amount') }}</p>
                <p class="text-5xl font-bold mt-2">{{ number_format($transaction->amount, 2) }}</p>
                <p class="text-sm opacity-75 mt-2">{{ __('product::transaction.paid_on') }} {{ $transaction->payment_date->format('Y-m-d') }}</p>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::transaction.transaction_details') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::transaction.payment_date') }}</label>
                        <p class="text-gray-900 font-medium">{{ $transaction->payment_date->format('Y-m-d') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::transaction.amount') }}</label>
                        <p class="text-green-600 font-bold text-xl">{{ number_format($transaction->amount, 2) }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('common.created_at') }}</label>
                        <p class="text-gray-900">{{ $transaction->created_at->format('Y-m-d H:i') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('common.updated_at') }}</label>
                        <p class="text-gray-900">{{ $transaction->updated_at->format('Y-m-d H:i') }}</p>
                    </div>

                    @if($transaction->notes)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::transaction.notes') }}</label>
                        <p class="text-gray-900">{{ $transaction->notes }}</p>
                    </div>
                    @endif

                    @if($transaction->receipt_file)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-2">{{ __('product::transaction.receipt_file') }}</label>
                        <a href="{{ asset('storage/' . $transaction->receipt_file) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            {{ __('product::transaction.view_receipt') }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Contract Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">{{ __('product::transaction.contract_information') }}</h2>
                    <a href="{{ route('product.contracts.show', $transaction->contract) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        {{ __('product::contract.view_contract') }} →
                    </a>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::contract.author') }}</label>
                        <a href="{{ route('product.authors.show', $transaction->contract->author) }}" class="text-blue-600 hover:text-blue-700 font-medium">
                            {{ $transaction->contract->author->full_name }}
                        </a>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::contract.book') }}</label>
                        <a href="{{ route('product.books.show', $transaction->contract->book) }}" class="text-blue-600 hover:text-blue-700 font-medium">
                            {{ $transaction->contract->book->product->name }}
                        </a>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::contract.contract_price') }}</label>
                        <p class="text-gray-900 font-bold">{{ number_format($transaction->contract->contract_price, 2) }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::contract.total_paid') }}</label>
                        <p class="text-green-600 font-bold">{{ number_format($transaction->contract->total_paid, 2) }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::contract.outstanding_balance') }}</label>
                        <p class="text-orange-600 font-bold">{{ number_format($transaction->contract->outstanding_balance, 2) }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::contract.payment_status') }}</label>
                        @php
                            $colors = [
                                'paid' => 'bg-green-100 text-green-800',
                                'partial' => 'bg-yellow-100 text-yellow-800',
                                'pending' => 'bg-red-100 text-red-800',
                            ];
                            $color = $colors[$transaction->contract->payment_status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $color }}">
                            {{ __('product::contract.' . $transaction->contract->payment_status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Progress -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::transaction.payment_progress') }}</h2>
            </div>
            <div class="p-6">
                @php
                    $percentage = ($transaction->contract->total_paid / $transaction->contract->contract_price) * 100;
                @endphp
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">{{ __('product::transaction.paid') }}</span>
                        <span class="text-sm font-medium text-gray-700">{{ number_format($percentage, 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mt-6">
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <p class="text-xs text-gray-600 mb-1">{{ __('product::contract.total_paid') }}</p>
                        <p class="text-lg font-bold text-green-600">{{ number_format($transaction->contract->total_paid, 2) }}</p>
                    </div>
                    <div class="text-center p-4 bg-orange-50 rounded-lg">
                        <p class="text-xs text-gray-600 mb-1">{{ __('product::contract.outstanding_balance') }}</p>
                        <p class="text-lg font-bold text-orange-600">{{ number_format($transaction->contract->outstanding_balance, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard>
