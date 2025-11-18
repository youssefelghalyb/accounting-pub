<x-dashboard :pageTitle="__('warehouse::movements.add_movement')">
    <div class="max-w-7xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('warehouse.movements.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('warehouse::movements.movements') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('warehouse::movements.add_movement') }}</span>
                </li>
            </ol>
        </nav>

        <form action="{{ route('warehouse.movements.store') }}" method="POST" id="movementForm">
            @csrf

            <!-- Movement Details Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">{{ __('warehouse::movements.movement_details') }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Reference Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('warehouse::movements.reference_number') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="reference_number" value="{{ old('reference_number', $referenceNumber) }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('reference_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Movement Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('warehouse::movements.type') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="type" id="movementType" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">{{ __('warehouse::movements.select_type') }}</option>
                                <option value="in" {{ old('type') == 'in' ? 'selected' : '' }}>{{ __('warehouse::movements.type_in') }}</option>
                                <option value="out" {{ old('type') == 'out' ? 'selected' : '' }}>{{ __('warehouse::movements.type_out') }}</option>
                                <option value="transfer" {{ old('type') == 'transfer' ? 'selected' : '' }}>{{ __('warehouse::movements.type_transfer') }}</option>
                                <option value="adjustment" {{ old('type') == 'adjustment' ? 'selected' : '' }}>{{ __('warehouse::movements.type_adjustment') }}</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Movement Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('warehouse::movements.movement_date') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="movement_date" value="{{ old('movement_date', date('Y-m-d')) }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('movement_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('warehouse::movements.status') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">{{ __('warehouse::movements.select_status') }}</option>
                                <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>{{ __('warehouse::movements.status_pending') }}</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>{{ __('warehouse::movements.status_completed') }}</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>{{ __('warehouse::movements.status_cancelled') }}</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Source Warehouse -->
                        <div id="sourceWarehouseField">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('warehouse::movements.source_warehouse') }}
                            </label>
                            <input type="text" name="source_warehouse" value="{{ old('source_warehouse') }}" list="warehouseList"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            @error('source_warehouse')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Destination Warehouse -->
                        <div id="destinationWarehouseField">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('warehouse::movements.destination_warehouse') }}
                            </label>
                            <input type="text" name="destination_warehouse" value="{{ old('destination_warehouse') }}" list="warehouseList"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('destination_warehouse')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Warehouse datalist -->
                        <datalist id="warehouseList">
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse }}">
                            @endforeach
                        </datalist>

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('warehouse::movements.notes') }}
                            </label>
                            <textarea name="notes" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Card with Bulk Add -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ __('warehouse::movements.products') }}</h2>
                            <p class="text-sm text-gray-600 mt-1">{{ __('warehouse::movements.add_multiple_products') }}</p>
                        </div>
                        <button type="button" id="addProductRow" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            {{ __('warehouse::movements.add_product') }}
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div id="productsContainer" class="space-y-4">
                        <!-- Product rows will be added here dynamically -->
                    </div>
                    @error('items')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('warehouse.movements.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    {{ __('common.cancel') }}
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    {{ __('common.save') }}
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        // Products data from Laravel
        const products = @json($products->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'type' => $p->type,
                'author' => $p->book && $p->book->author ? $p->book->author->name : null
            ];
        }));

        let productRowIndex = 0;

        // Add product row
        document.getElementById('addProductRow').addEventListener('click', function() {
            addProductRow();
        });

        function addProductRow(productId = '', quantity = '', notes = '') {
            const container = document.getElementById('productsContainer');
            const row = document.createElement('div');
            row.className = 'flex items-start gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200';
            row.id = `productRow${productRowIndex}`;

            row.innerHTML = `
                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Product Select -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('warehouse::movements.product') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="items[${productRowIndex}][product_id]" required onchange="showProductDetails(this, ${productRowIndex})"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                            <option value="">{{ __('warehouse::movements.select_product') }}</option>
                            ${products.map(p => {
                                let label = p.name;
                                if (p.sku) label += ` (${p.sku})`;
                                if (p.author) label += ` - ${p.author}`;
                                return `<option value="${p.id}" ${productId == p.id ? 'selected' : ''} data-type="${p.type}" data-author="${p.author || ''}">${label}</option>`;
                            }).join('')}
                        </select>
                        <div id="productDetails${productRowIndex}" class="mt-2 text-xs text-gray-600"></div>
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('warehouse::movements.quantity') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="items[${productRowIndex}][quantity]" value="${quantity}" min="1" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('warehouse::movements.item_notes') }}
                        </label>
                        <input type="text" name="items[${productRowIndex}][notes]" value="${notes}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm">
                    </div>
                </div>

                <!-- Remove Button -->
                <button type="button" onclick="removeProductRow(${productRowIndex})" class="mt-8 p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            `;

            container.appendChild(row);
            productRowIndex++;
        }

        function removeProductRow(index) {
            const row = document.getElementById(`productRow${index}`);
            if (row) {
                row.remove();
            }
        }

        function showProductDetails(select, index) {
            const selectedOption = select.options[select.selectedIndex];
            const detailsDiv = document.getElementById(`productDetails${index}`);

            if (selectedOption.value) {
                const type = selectedOption.dataset.type;
                const author = selectedOption.dataset.author;

                let details = `<span class="inline-block px-2 py-0.5 rounded bg-blue-100 text-blue-700 font-medium">${type}</span>`;
                if (author) {
                    details += ` <span class="ml-2">Author: ${author}</span>`;
                }
                detailsDiv.innerHTML = details;
            } else {
                detailsDiv.innerHTML = '';
            }
        }

        // Add one row on page load
        addProductRow();

        // Update warehouse field visibility based on movement type
        document.getElementById('movementType').addEventListener('change', function() {
            const type = this.value;
            const sourceField = document.getElementById('sourceWarehouseField');
            const destField = document.getElementById('destinationWarehouseField');

            // Reset visibility
            sourceField.style.display = 'block';
            destField.style.display = 'block';

            // Adjust based on type
            if (type === 'in') {
                sourceField.style.display = 'none';
            } else if (type === 'out') {
                destField.style.display = 'none';
            }
        });

        // Trigger on page load
        document.getElementById('movementType').dispatchEvent(new Event('change'));
    </script>
    @endpush
</x-dashboard>
