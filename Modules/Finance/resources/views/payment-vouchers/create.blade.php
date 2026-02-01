<x-dashboard :pageTitle="__('finance::payment.create_payment')">
    <div class="max-w-4xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('finance.payment-vouchers.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('finance::payment.payment_vouchers') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('finance::payment.create_payment') }}</span>
                </li>
            </ol>
        </nav>

        <form action="{{ route('finance.payment-vouchers.store') }}" method="POST" id="paymentForm">
            @csrf

            <div class="space-y-6">
                <!-- Payment Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">{{ __('finance::payment.voucher_info') }}</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Vendor -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('finance::payment.vendor') }} <span class="text-red-500">*</span>
                                </label>
                                <select name="party_id" id="party_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="">{{ __('finance::payment.select_vendor') }}</option>
                                    @foreach($parties as $party)
                                        <option value="{{ $party->id }}" {{ $selectedParty == $party->id ? 'selected' : '' }}>
                                            {{ $party->name }} - {{ __('finance::payment.balance') }}: {{ number_format($party->vendor_balance, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('party_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Purchase Invoice (Optional) -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('finance::payment.invoice') }}
                                    <span class="text-gray-500 text-xs">({{ __('finance::payment.optional') }})</span>
                                </label>
                                <select name="purchase_invoice_id" id="purchase_invoice_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="">{{ __('finance::payment.select_invoice') }}</option>
                                    @foreach($invoices as $invoice)
                                        <option value="{{ $invoice->id }}" 
                                            data-outstanding="{{ $invoice->outstanding_balance }}"
                                            {{ $selectedInvoice == $invoice->id ? 'selected' : '' }}>
                                            {{ $invoice->invoice_number }} - {{ __('finance::payment.outstanding') }}: {{ number_format($invoice->outstanding_balance, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="invoiceInfo" class="mt-2 hidden">
                                    <p class="text-sm text-gray-600">
                                        {{ __('finance::payment.outstanding_amount') }}: 
                                        <span id="outstandingAmount" class="font-bold text-red-600"></span>
                                    </p>
                                </div>
                            </div>

                            <!-- Payment Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('finance::payment.voucher_date') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="voucher_date" value="{{ old('voucher_date', now()->format('Y-m-d')) }}" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                @error('voucher_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Amount -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('finance::payment.amount') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="amount" id="amount" step="0.01" min="0.01" value="{{ old('amount') }}" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                @error('amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Account -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('finance::payment.account') }} <span class="text-red-500">*</span>
                                </label>
                                <select name="account_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="">{{ __('finance::payment.select_account') }}</option>
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
                                    {{ __('finance::payment.payment_method') }} <span class="text-red-500">*</span>
                                </label>
                                <select name="payment_method" id="payment_method" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="cash">{{ __('finance::payment.method_cash') }}</option>
                                    <option value="cheque">{{ __('finance::payment.method_cheque') }}</option>
                                    <option value="bank_transfer">{{ __('finance::payment.method_bank_transfer') }}</option>
                                    <option value="credit_card">{{ __('finance::payment.method_credit_card') }}</option>
                                    <option value="other">{{ __('finance::payment.method_other') }}</option>
                                </select>
                                @error('payment_method')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Cheque Details (conditional) -->
                            <div id="chequeDetails" class="md:col-span-2 hidden">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <h3 class="text-sm font-medium text-blue-900 mb-3">{{ __('finance::payment.cheque_details') }}</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                {{ __('finance::payment.cheque_number') }} <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" name="cheque_number" id="cheque_number"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                {{ __('finance::payment.cheque_date') }} <span class="text-red-500">*</span>
                                            </label>
                                            <input type="date" name="cheque_date" id="cheque_date"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Transaction Reference (for bank transfers) -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('finance::payment.transaction_reference') }}
                                </label>
                                <input type="text" name="transaction_reference" value="{{ old('transaction_reference') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('finance::payment.description') }}
                                </label>
                                <textarea name="description" rows="3" placeholder="{{ __('finance::payment.enter_description') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('finance.payment-vouchers.index') }}" 
                            class="px-6 py-3 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors">
                            {{ __('finance::payment.cancel') }}
                        </a>
                        <button type="submit"
                            class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                            {{ __('finance::payment.create_payment') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const partySelect = document.getElementById('party_id');
            const invoiceSelect = document.getElementById('purchase_invoice_id');
            const invoiceInfo = document.getElementById('invoiceInfo');
            const outstandingAmount = document.getElementById('outstandingAmount');
            const amountInput = document.getElementById('amount');
            const paymentMethodSelect = document.getElementById('payment_method');
            const chequeDetails = document.getElementById('chequeDetails');
            const chequeNumber = document.getElementById('cheque_number');
            const chequeDate = document.getElementById('cheque_date');

            // Load invoices when party changes
            partySelect.addEventListener('change', function() {
                const partyId = this.value;
                invoiceSelect.innerHTML = '<option value="">{{ __("finance::payment.select_invoice") }}</option>';
                invoiceInfo.classList.add('hidden');

                if (partyId) {
                    fetch(`/finance/parties/${partyId}/purchase-invoices`)
                        .then(response => response.json())
                        .then(invoices => {
                            invoices.forEach(invoice => {
                                const option = document.createElement('option');
                                option.value = invoice.id;
                                option.textContent = `${invoice.invoice_number} - {{ __("finance::payment.outstanding") }}: ${parseFloat(invoice.outstanding_balance).toFixed(2)}`;
                                option.dataset.outstanding = invoice.outstanding_balance;
                                invoiceSelect.appendChild(option);
                            });
                        });
                }
            });

            // Show outstanding amount when invoice is selected
            invoiceSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (this.value && selectedOption.dataset.outstanding) {
                    const outstanding = parseFloat(selectedOption.dataset.outstanding);
                    outstandingAmount.textContent = outstanding.toFixed(2);
                    invoiceInfo.classList.remove('hidden');
                    
                    // Suggest amount if empty
                    if (!amountInput.value || parseFloat(amountInput.value) === 0) {
                        amountInput.value = outstanding.toFixed(2);
                    }
                } else {
                    invoiceInfo.classList.add('hidden');
                }
            });

            // Show/hide cheque details based on payment method
            paymentMethodSelect.addEventListener('change', function() {
                if (this.value === 'cheque') {
                    chequeDetails.classList.remove('hidden');
                    chequeNumber.required = true;
                    chequeDate.required = true;
                } else {
                    chequeDetails.classList.add('hidden');
                    chequeNumber.required = false;
                    chequeDate.required = false;
                }
            });

            // Trigger invoice selection if pre-selected
            if (invoiceSelect.value) {
                invoiceSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
    @endpush
</x-dashboard>