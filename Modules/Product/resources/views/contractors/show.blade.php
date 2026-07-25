<x-dashboard :pageTitle="$contractor->name">
    <div class="max-w-6xl mx-auto">
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('product.contractors.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('product::contractor.contractors') }}
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
                <li><span class="text-gray-900 font-medium">{{ $contractor->name }}</span></li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-xl">
                        {{ strtoupper(mb_substr($contractor->name, 0, 2)) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $contractor->name }}</h1>
                        @if ($contractor->nationality)
                            <p class="text-sm text-gray-500 mt-1">{{ $contractor->nationality }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <a href="{{ route('product.contractors.edit', $contractor) }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        {{ __('common.edit') }}
                    </a>
                    <form action="{{ route('product.contractors.destroy', $contractor) }}" method="POST"
                        onsubmit="return confirm('{{ __('common.are_you_sure') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                            {{ __('common.delete') }}
                        </button>
                    </form>
                    <button type="button" onclick="openInvoiceModal()"
                        class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                        {{ __('finance::invoice.create_invoice') }}
                    </button>
                    @if ($contractor->contractorBooks->isNotEmpty())
                        <button type="button" onclick="openGiftModal()"
                            class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
                            {{ __('product::contractor.add_gift') }}
                        </button>
                        <button type="button" onclick="openTransactionModal()"
                            class="inline-flex items-center px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition-colors">
                            {{ __('product::contractor.record_transaction') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm font-medium text-gray-600">{{ __('product::contractor.total_books') }}</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_books'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm font-medium text-gray-600">{{ __('product::contractor.total_royalty_accrued') }}</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_royalty_accrued'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm font-medium text-gray-600">{{ __('product::contractor.total_royalty_paid') }}</p>
                <p class="text-3xl font-bold text-emerald-600 mt-2">{{ number_format($stats['total_royalty_paid'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm font-medium text-gray-600">{{ __('product::contractor.outstanding_royalty') }}</p>
                <p class="text-3xl font-bold text-orange-600 mt-2">{{ number_format($stats['outstanding_royalty'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm font-medium text-gray-600">{{ __('product::contractor.gift_copies') }}</p>
                <p class="text-3xl font-bold text-purple-600 mt-2">{{ $stats['gift_copies_count'] }}</p>
            </div>
        </div>

        {{-- Personal info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::contractor.personal_info') }}</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                @if ($contractor->email)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::contractor.email') }}</label>
                        <p class="text-gray-900 font-medium">{{ $contractor->email }}</p>
                    </div>
                @endif
                @if ($contractor->phone)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::contractor.phone') }}</label>
                        <p class="text-gray-900 font-medium">{{ $contractor->phone }}</p>
                    </div>
                @endif
                @if ($contractor->address)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::contractor.address') }}</label>
                        <p class="text-gray-900 font-medium">{{ $contractor->address }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Books under contract --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::contractor.contracted_books') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('product::book.book') }}</th>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('product::contractor.profit_percentage') }}</th>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('product::contractor.percentage_basis') }}</th>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('product::contractor.contract_date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($contractor->contractorBooks as $contractorBook)
                            <tr>
                                <td class="px-6 py-4">
                                    <a href="{{ route('product.books.show', $contractorBook->book_id) }}" class="text-blue-600 hover:text-blue-700 font-medium">
                                        {{ $contractorBook->book->product->name ?? '—' }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ number_format($contractorBook->profit_percentage, 2) }}%</td>
                                <td class="px-6 py-4 text-gray-600">{{ __('product::contractor.basis_' . $contractorBook->percentage_basis) }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $contractorBook->contract_date?->format('Y-m-d') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">{{ __('common.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Transaction ledger --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::contractor.transactions') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('product::contractor.date') }}</th>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('product::book.book') }}</th>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('product::contractor.type') }}</th>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('product::contractor.direction') }}</th>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('product::contractor.amount') }}</th>
                            <th class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($contractor->contractorBooks->flatMap->contractTransactions->sortByDesc('transaction_date') as $transaction)
                            <tr>
                                <td class="px-6 py-4 text-gray-600">{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $transaction->contractorBook->book->product->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ __('product::contractor.type_' . $transaction->type) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $transaction->direction === 'in' ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800' }}">
                                        {{ $transaction->direction === 'in' ? __('product::contractor.direction_in') : __('product::contractor.direction_out') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ number_format($transaction->amount, 2) }}</td>
                                <td class="px-6 py-4 text-end">
                                    <form action="{{ route('product.contractors.transactions.destroy', $transaction) }}" method="POST"
                                        onsubmit="return confirm('{{ __('common.are_you_sure') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">{{ __('common.delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">{{ __('common.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Register-as-client modal --}}
    <div id="registerClientModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900">{{ __('product::contractor.register_as_client_title') }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ __('product::contractor.register_as_client_description') }}</p>
            </div>
            <div class="p-6 flex gap-3">
                <button type="button" onclick="closeInvoiceModal()"
                    class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 font-medium transition-colors">
                    {{ __('common.cancel') }}
                </button>
                <button type="button" id="confirmRegisterBtn" onclick="confirmRegisterAsClient()"
                    class="flex-1 px-4 py-3 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 font-medium transition-colors">
                    {{ __('product::contractor.confirm_and_create_invoice') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Gift modal --}}
    <div id="giftModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full">
            <form action="{{ route('product.contractors.gift', $contractor) }}" method="POST">
                @csrf
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">{{ __('product::contractor.add_gift') }}</h3>
                </div>
                <div class="p-6 space-y-4 max-h-96 overflow-y-auto">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('product::contractor.sub_warehouse') }}</label>
                        <select name="sub_warehouse_id" required class="w-full rounded-lg border-gray-300">
                            @foreach ($subWarehouses as $subWarehouse)
                                <option value="{{ $subWarehouse->id }}">{{ $subWarehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @foreach ($contractor->contractorBooks as $contractorBook)
                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="lines[{{ $loop->index }}][contractor_book_id]" value="{{ $contractorBook->id }}"
                                onchange="this.nextElementSibling.nextElementSibling.disabled = !this.checked"
                                class="rounded border-gray-300">
                            <span class="flex-1 text-sm text-gray-900">{{ $contractorBook->book->product->name ?? '—' }}</span>
                            <input type="number" name="lines[{{ $loop->index }}][quantity]" min="1" value="1" disabled
                                class="w-20 rounded-lg border-gray-300 text-sm">
                        </div>
                    @endforeach
                </div>
                <div class="p-6 flex gap-3 border-t border-gray-200">
                    <button type="button" onclick="closeGiftModal()"
                        class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 font-medium">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 font-medium">
                        {{ __('product::contractor.confirm_gift') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Transaction modal --}}
    <div id="transactionModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full">
            <form action="{{ route('product.contractors.transactions.store', $contractor) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">{{ __('product::contractor.record_transaction') }}</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('product::book.book') }}</label>
                        <select name="contractor_book_id" required class="w-full rounded-lg border-gray-300">
                            @foreach ($contractor->contractorBooks as $contractorBook)
                                <option value="{{ $contractorBook->id }}">{{ $contractorBook->book->product->name ?? '—' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('product::contractor.type') }}</label>
                            <select name="type" required class="w-full rounded-lg border-gray-300">
                                @foreach (['publishing_fee', 'royalty_payment', 'advance_payment', 'refund', 'adjustment'] as $type)
                                    <option value="{{ $type }}">{{ __('product::contractor.type_' . $type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('product::contractor.direction') }}</label>
                            <select name="direction" required class="w-full rounded-lg border-gray-300">
                                <option value="receipt">{{ __('product::contractor.direction_in') }}</option>
                                <option value="payment">{{ __('product::contractor.direction_out') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('product::contractor.amount') }}</label>
                            <input type="number" step="0.01" min="0.01" name="amount" required class="w-full rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('product::contractor.date') }}</label>
                            <input type="date" name="transaction_date" value="{{ now()->toDateString() }}" required class="w-full rounded-lg border-gray-300">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('finance::account.account') }}</label>
                        <select name="account_id" required class="w-full rounded-lg border-gray-300">
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('product::contractor.notes') }}</label>
                        <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('product::contractor.receipt_file') }}</label>
                        <input type="file" name="receipt_file" class="w-full text-sm">
                    </div>
                </div>
                <div class="p-6 flex gap-3 border-t border-gray-200">
                    <button type="button" onclick="closeTransactionModal()"
                        class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 font-medium">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 bg-amber-600 text-white rounded-xl hover:bg-amber-700 font-medium">
                        {{ __('common.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            const isAlreadyClient = {{ $contractor->party_id ? 'true' : 'false' }};
            const registerUrl = '{{ route('product.contractors.register-as-client', $contractor) }}';
            const csrfToken = '{{ csrf_token() }}';

            function openInvoiceModal() {
                if (isAlreadyClient) {
                    window.location.href = '{{ route('finance.sales-invoices.create', ['party_id' => $contractor->party_id ?? '']) }}';
                    return;
                }
                document.getElementById('registerClientModal').classList.remove('hidden');
            }
            function closeInvoiceModal() {
                document.getElementById('registerClientModal').classList.add('hidden');
            }
            async function confirmRegisterAsClient() {
                const btn = document.getElementById('confirmRegisterBtn');
                btn.disabled = true;
                try {
                    const res = await fetch(registerUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    });
                    const data = await res.json();
                    if (data.success) {
                        window.location.href = data.invoice_url;
                    } else {
                        alert('{{ __('common.error_occurred') }}');
                        btn.disabled = false;
                    }
                } catch {
                    alert('{{ __('common.error_occurred') }}');
                    btn.disabled = false;
                }
            }

            function openGiftModal() { document.getElementById('giftModal').classList.remove('hidden'); }
            function closeGiftModal() { document.getElementById('giftModal').classList.add('hidden'); }

            function openTransactionModal() { document.getElementById('transactionModal').classList.remove('hidden'); }
            function closeTransactionModal() { document.getElementById('transactionModal').classList.add('hidden'); }

            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') {
                    closeInvoiceModal();
                    closeGiftModal();
                    closeTransactionModal();
                }
            });
        </script>
    @endpush
</x-dashboard>
