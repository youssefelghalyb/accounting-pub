<x-dashboard :pageTitle="__('finance::purchase.edit_invoice')">
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
                    <span class="text-gray-900 font-medium">{{ __('finance::purchase.edit_invoice') }} - {{ $purchaseInvoice->invoice_number }}</span>
                </li>
            </ol>
        </nav>

        <form action="{{ route('finance.purchase-invoices.update', $purchaseInvoice) }}" method="POST" id="invoiceForm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Invoice Details -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900">{{ __('finance::purchase.invoice_info') }}</h2>
                            <p class="text-sm text-gray-600 mt-1">{{ $purchaseInvoice->invoice_number }}</p>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Vendor -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('finance::purchase.vendor') }} <span class="text-red-500">*</span>
                                    </label>
                                    <select name="party_id" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">{{ __('finance::purchase.select_vendor') }}</option>
                                        @foreach($parties as $party)
                                            <option value="{{ $party->id }}" {{ $purchaseInvoice->party_id == $party->id ? 'selected' : '' }}>
                                                {{ $party->name }} - {{ number_format($party->vendor_balance, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('party_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Invoice Date -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('finance::purchase.invoice_date') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="invoice_date" value="{{ old('invoice_date', $purchaseInvoice->invoice_date->format('Y-m-d')) }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('invoice_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Due Date -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('finance::purchase.due_date') }}
                                    </label>
                                    <input type="date" name="due_date" value="{{ old('due_date', $purchaseInvoice->due_date?->format('Y-m-d')) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('due_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Reference Number -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('finance::purchase.reference_number') }}
                                    </label>
                                    <input type="text" name="reference_number" value="{{ old('reference_number', $purchaseInvoice->reference_number) }}" placeholder="{{ __('finance::purchase.vendor_invoice_number') }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-bold text-gray-900">{{ __('finance::purchase.items') }}</h2>
                                <button type="button" id="addItemBtn"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    {{ __('finance::purchase.add_item') }}
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            <div id="itemsContainer" class="space-y-4">
                                <!-- Existing items will be loaded here -->
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900">{{ __('finance::purchase.notes') }}</h2>
                        </div>
                        <div class="p-6">
                            <textarea name="notes" rows="3" placeholder="{{ __('finance::purchase.enter_notes') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('notes', $purchaseInvoice->notes) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Tax & Discount -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-bold text-gray-900">{{ __('finance::purchase.amount_breakdown') }}</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <!-- Tax Rate -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('finance::purchase.tax_rate') }}
                                </label>
                                <input type="number" name="tax_rate" id="tax_rate" step="0.01" min="0" max="100"
                                    value="{{ old('tax_rate', $purchaseInvoice->tax_rate) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <!-- Discount Amount -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('finance::purchase.discount_amount') }}
                                </label>
                                <input type="number" name="discount_amount" id="discount_amount" step="0.01" min="0"
                                    value="{{ old('discount_amount', $purchaseInvoice->discount_amount) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <!-- Totals Display -->
                            <div class="pt-4 border-t border-gray-200 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('finance::purchase.subtotal') }}:</span>
                                    <span id="subtotalDisplay" class="font-medium text-gray-900">{{ number_format($purchaseInvoice->subtotal_amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('finance::purchase.discount_amount') }}:</span>
                                    <span id="discountDisplay" class="font-medium text-gray-900">{{ number_format($purchaseInvoice->discount_amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('finance::purchase.tax_amount') }}:</span>
                                    <span id="taxDisplay" class="font-medium text-gray-900">{{ number_format($purchaseInvoice->tax_amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                                    <span class="text-gray-900">{{ __('finance::purchase.total_amount') }}:</span>
                                    <span id="totalDisplay" class="text-blue-600">{{ number_format($purchaseInvoice->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <button type="submit"
                            class="w-full py-3 px-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            {{ __('finance::purchase.update_invoice') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        let itemCounter = 0;
        const products = @json($productsForJs);
        const existingItems = @json($existingItemsForJs);

        document.addEventListener('DOMContentLoaded', function() {
            // Load existing items
            existingItems.forEach(item => {
                addInvoiceItem(item);
            });

            // Add item button
            document.getElementById('addItemBtn').addEventListener('click', () => addInvoiceItem());

            // Recalculate on discount/tax changes
            document.getElementById('discount_amount').addEventListener('input', calculateTotals);
            document.getElementById('tax_rate').addEventListener('input', calculateTotals);
        });

        function addInvoiceItem(existingItem = null) {
            itemCounter++;
            const container = document.getElementById('itemsContainer');
            
            const itemHtml = `
                <div class="item-row border border-gray-200 rounded-lg p-4" data-item="${itemCounter}">
                    <div class="grid grid-cols-12 gap-4">
                        <!-- Product -->
                        <div class="col-span-12 md:col-span-4">
                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('finance::purchase.product') }}</label>
                            <select name="items[${itemCounter}][product_id]" required
                                class="product-select w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                onchange="selectProduct(${itemCounter}, this.value)">
                                <option value="">{{ __('finance::purchase.select_product') }}</option>
                                ${products.map(p => `<option value="${p.id}" ${existingItem?.product_id == p.id ? 'selected' : ''} data-price="${p.price}">${p.name} (${p.sku})</option>`).join('')}
                            </select>
                        </div>

                        <!-- Quantity -->
                        <div class="col-span-6 md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('finance::purchase.quantity') }}</label>
                            <input type="number" name="items[${itemCounter}][quantity]" min="1" value="${existingItem?.quantity || 1}" required
                                class="item-quantity w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                oninput="calculateLineTotal(${itemCounter})">
                        </div>

                        <!-- Unit Price -->
                        <div class="col-span-6 md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('finance::purchase.unit_price') }}</label>
                            <input type="number" name="items[${itemCounter}][unit_price]" step="0.01" min="0" value="${existingItem?.unit_price || 0}" required
                                class="item-price w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                oninput="calculateLineTotal(${itemCounter})">
                        </div>

                        <!-- Discount -->
                        <div class="col-span-6 md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('finance::purchase.item_discount') }}</label>
                            <input type="number" name="items[${itemCounter}][discount_amount]" step="0.01" min="0" value="${existingItem?.discount_amount || 0}"
                                class="item-discount w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                oninput="calculateLineTotal(${itemCounter})">
                        </div>

                        <!-- Line Total -->
                        <div class="col-span-5 md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('finance::purchase.line_total') }}</label>
                            <input type="text" readonly
                                class="item-total w-full px-3 py-2 text-sm bg-gray-50 border border-gray-300 rounded-lg font-medium text-gray-900"
                                value="0.00">
                        </div>

                        <!-- Remove Button -->
                        <div class="col-span-1 flex items-end">
                            <button type="button" onclick="removeItem(${itemCounter})"
                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Description -->
                        <div class="col-span-12">
                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('finance::purchase.description') }}</label>
                            <input type="text" name="items[${itemCounter}][description]" value="${existingItem?.description || ''}" placeholder="{{ __('finance::purchase.description') }}"
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', itemHtml);
            calculateLineTotal(itemCounter);
        }

        function selectProduct(itemId, productId) {
            if (!productId) return;
            
            const product = products.find(p => p.id == productId);
            if (!product) return;
            
            const row = document.querySelector(`[data-item="${itemId}"]`);
            const priceInput = row.querySelector('.item-price');
            priceInput.value = product.price;
            
            calculateLineTotal(itemId);
        }

        function calculateLineTotal(itemId) {
            const row = document.querySelector(`[data-item="${itemId}"]`);
            const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const discount = parseFloat(row.querySelector('.item-discount').value) || 0;
            
            const lineTotal = (quantity * price) - discount;
            row.querySelector('.item-total').value = lineTotal.toFixed(2);
            
            calculateTotals();
        }

        function removeItem(itemId) {
            const items = document.querySelectorAll('.item-row');
            if (items.length <= 1) {
                alert('{{ __("At least one item is required") }}');
                return;
            }
            
            document.querySelector(`[data-item="${itemId}"]`).remove();
            calculateTotals();
        }

        function calculateTotals() {
            // Calculate subtotal
            let subtotal = 0;
            document.querySelectorAll('.item-total').forEach(input => {
                subtotal += parseFloat(input.value) || 0;
            });

            // Get discount
            const discountAmount = parseFloat(document.getElementById('discount_amount').value) || 0;
            const amountAfterDiscount = subtotal - discountAmount;

            // Calculate tax
            const taxRate = parseFloat(document.getElementById('tax_rate').value) || 0;
            const taxAmount = (amountAfterDiscount * taxRate) / 100;

            // Calculate total
            const total = amountAfterDiscount + taxAmount;

            // Update displays
            document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2);
            document.getElementById('discountDisplay').textContent = discountAmount.toFixed(2);
            document.getElementById('taxDisplay').textContent = taxAmount.toFixed(2);
            document.getElementById('totalDisplay').textContent = total.toFixed(2);
        }
    </script>
    @endpush
</x-dashboard>