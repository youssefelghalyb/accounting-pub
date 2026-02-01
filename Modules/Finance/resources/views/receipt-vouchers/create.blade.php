<x-dashboard :pageTitle="__('finance::receipt.create_receipt')">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
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
                    <span class="text-gray-900 font-medium">{{ __('finance::receipt.create_receipt') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('finance::receipt.create_receipt') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
            </div>

            <div class="p-6">
                <form action="{{ route('finance.receipt-vouchers.store') }}" method="POST" id="receiptForm">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Party -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('finance::receipt.party') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="party_id" id="party_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">{{ __('finance::receipt.select_party') }}</option>
                                @foreach($parties as $party)
                                    <option value="{{ $party->id }}" {{ $selectedParty == $party->id ? 'selected' : '' }}>
                                        {{ $party->name }} - {{ __('finance::party.outstanding') }}: {{ number_format($party->customer_balance, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('party_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Invoice (Optional) -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('finance::receipt.invoice') }}
                            </label>
                            <select name="sales_invoice_id" id="sales_invoice_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">{{ __('finance::receipt.select_invoice') }}</option>
                                @foreach($invoices as $invoice)
                                    <option value="{{ $invoice->id }}" 
                                        data-party="{{ $invoice->party_id }}"
                                        data-outstanding="{{ $invoice->outstanding_balance }}"
                                        {{ $selectedInvoice == $invoice->id ? 'selected' : '' }}
                                        style="display: {{ $selectedParty == $invoice->party_id || !$selectedParty ? 'block' : 'none' }}">
                                        {{ $invoice->invoice_number }} - {{ $invoice->party->name }} - {{ __('finance::receipt.invoice_outstanding') }}: {{ number_format($invoice->outstanding_balance, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">{{ __('common.optional') }}</p>
                        </div>

                        <!-- Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('finance::receipt.amount') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="amount" id="amount" step="0.01" min="0.01" value="{{ old('amount') }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="{{ __('finance::receipt.enter_amount') }}">
                            @error('amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p id="outstandingHint" class="mt-1 text-xs text-gray-500" style="display: none;"></p>
                        </div>

                        <!-- Voucher Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('finance::receipt.voucher_date') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="voucher_date" value="{{ old('voucher_date', now()->format('Y-m-d')) }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('voucher_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Account -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('finance::receipt.account') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="account_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">{{ __('finance::receipt.select_account') }}</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->display_name }} - {{ number_format($account->current_balance, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('account_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('finance::receipt.payment_method') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="payment_method" id="payment_method" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">{{ __('finance::receipt.select_payment_method') }}</option>
                                <option value="cash">{{ __('finance::receipt.payment_methods.cash') }}</option>
                                <option value="cheque">{{ __('finance::receipt.payment_methods.cheque') }}</option>
                                <option value="bank_transfer">{{ __('finance::receipt.payment_methods.bank_transfer') }}</option>
                                <option value="credit_card">{{ __('finance::receipt.payment_methods.credit_card') }}</option>
                                <option value="other">{{ __('finance::receipt.payment_methods.other') }}</option>
                            </select>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Reference Number -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('finance::receipt.reference_number') }}
                            </label>
                            <input type="text" name="reference_number" value="{{ old('reference_number') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="{{ __('finance::receipt.enter_reference') }}">
                            <p class="mt-1 text-xs text-gray-500">{{ __('finance::receipt.cheque_number') }}, {{ __('finance::receipt.transaction_id') }}, {{ __('common.etc') }}</p>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('finance::receipt.description') }}
                            </label>
                            <textarea name="description" rows="2"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="{{ __('finance::receipt.enter_description') }}">{{ old('description') }}</textarea>
                        </div>

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('finance::receipt.notes') }}
                            </label>
                            <textarea name="notes" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="{{ __('finance::receipt.enter_notes') }}">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                        <a href="{{ route('finance.receipt-vouchers.index') }}"
                           class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            {{ __('common.cancel') }}
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            {{ __('finance::receipt.create_receipt') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const partySelect = document.getElementById('party_id');
            const invoiceSelect = document.getElementById('sales_invoice_id');
            const amountInput = document.getElementById('amount');
            const outstandingHint = document.getElementById('outstandingHint');

            // Filter invoices by party
            partySelect.addEventListener('change', function() {
                const partyId = this.value;
                const invoiceOptions = invoiceSelect.querySelectorAll('option');

                invoiceOptions.forEach(option => {
                    if (option.value === '') {
                        option.style.display = 'block';
                        return;
                    }
                    
                    const optionPartyId = option.dataset.party;
                    option.style.display = (partyId === '' || partyId === optionPartyId) ? 'block' : 'none';
                });

                // Reset invoice selection if party changed
                invoiceSelect.value = '';
                outstandingHint.style.display = 'none';
            });

            // Show outstanding amount when invoice selected
            invoiceSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                
                if (this.value && selectedOption.dataset.outstanding) {
                    const outstanding = parseFloat(selectedOption.dataset.outstanding);
                    outstandingHint.textContent = '{{ __("finance::receipt.invoice_outstanding") }}: ' + outstanding.toFixed(2);
                    outstandingHint.style.display = 'block';
                    
                    // Auto-fill amount with outstanding balance
                    if (!amountInput.value || parseFloat(amountInput.value) === 0) {
                        amountInput.value = outstanding.toFixed(2);
                    }
                } else {
                    outstandingHint.style.display = 'none';
                }
            });

            // Trigger change if invoice pre-selected
            if (invoiceSelect.value) {
                invoiceSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
    @endpush
</x-dashboard>