<x-dashboard :pageTitle="__('warehouse::sub_warehouse.add_stock')">
    <div class="max-w-6xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('warehouse.sub_warehouses.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('warehouse::sub_warehouse.sub_warehouses') }}
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
                    <a href="{{ route('warehouse.sub_warehouses.show', $subWarehouse) }}" class="text-gray-500 hover:text-gray-700">
                        {{ $subWarehouse->name }}
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
                    <span class="text-gray-900 font-medium">{{ __('warehouse::sub_warehouse.add_stock') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('warehouse::sub_warehouse.add_stock') }} - {{ $subWarehouse->name }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('warehouse::sub_warehouse.add_another_product') }}</p>
            </div>

            <div class="p-6">
                <form action="{{ route('warehouse.sub_warehouses.store_stock', $subWarehouse) }}" method="POST" id="stockForm">
                    @csrf

                    <div id="products-container">
                        <!-- Initial Product Row -->
                        <div class="product-row grid grid-cols-12 gap-4 mb-4 p-4 border border-gray-200 rounded-lg">
                            <div class="col-span-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('warehouse::sub_warehouse.product') }} <span class="text-red-500">*</span>
                                </label>
                                <select name="products[0][product_id]" class="product-select w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">{{ __('warehouse::sub_warehouse.select_product') }}</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-type="{{ $product->type }}" data-book="{{ $product->book ? json_encode($product->book) : '{}' }}">
                                            {{ $product->name }}
                                            @if($product->sku)
                                                (SKU: {{ $product->sku }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('warehouse::sub_warehouse.quantity') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="products[0][quantity]" min="1"
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="{{ __('warehouse::sub_warehouse.enter_quantity') }}" required>
                            </div>

                            <div class="col-span-2 flex items-end">
                                <button type="button" class="remove-product-btn w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 disabled:opacity-50" disabled>
                                    {{ __('warehouse::sub_warehouse.remove_product') }}
                                </button>
                            </div>

                            <!-- Book Details Display -->
                            <div class="col-span-12 book-details hidden mt-2 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <h4 class="font-medium text-blue-900 mb-2">{{ __('warehouse::sub_warehouse.book_details') }}</h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                    <div>
                                        <span class="text-blue-700 font-medium">{{ __('warehouse::sub_warehouse.isbn') }}:</span>
                                        <span class="book-isbn text-blue-900"></span>
                                    </div>
                                    <div>
                                        <span class="text-blue-700 font-medium">{{ __('warehouse::sub_warehouse.author') }}:</span>
                                        <span class="book-author text-blue-900"></span>
                                    </div>
                                    <div>
                                        <span class="text-blue-700 font-medium">{{ __('warehouse::sub_warehouse.category') }}:</span>
                                        <span class="book-category text-blue-900"></span>
                                    </div>
                                    <div>
                                        <span class="text-blue-700 font-medium">{{ __('warehouse::sub_warehouse.pages') }}:</span>
                                        <span class="book-pages text-blue-900"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add Another Product Button -->
                    <div class="mb-6">
                        <button type="button" id="addProductBtn" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            {{ __('warehouse::sub_warehouse.add_another_product') }}
                        </button>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                        <a href="{{ route('warehouse.sub_warehouses.show', $subWarehouse) }}"
                           class="px-6 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                            {{ __('common.cancel') }}
                        </a>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                            {{ __('warehouse::sub_warehouse.add_stock') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let productIndex = 1;

        document.getElementById('addProductBtn').addEventListener('click', function() {
            const container = document.getElementById('products-container');
            const template = container.querySelector('.product-row').cloneNode(true);

            // Update indices
            template.querySelectorAll('[name^="products[0]"]').forEach(input => {
                input.name = input.name.replace('[0]', `[${productIndex}]`);
                if (input.type !== 'button') {
                    input.value = '';
                }
            });

            // Reset select
            template.querySelector('select').selectedIndex = 0;

            // Hide book details
            const bookDetails = template.querySelector('.book-details');
            if (bookDetails) {
                bookDetails.classList.add('hidden');
            }

            // Enable remove button
            const removeBtn = template.querySelector('.remove-product-btn');
            removeBtn.disabled = false;

            container.appendChild(template);
            productIndex++;

            updateRemoveButtons();
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-product-btn')) {
                e.target.closest('.product-row').remove();
                updateRemoveButtons();
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('product-select')) {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const productRow = e.target.closest('.product-row');
                const bookDetails = productRow.querySelector('.book-details');

                if (selectedOption.value && selectedOption.dataset.type === 'book' && selectedOption.dataset.book) {
                    try {
                        const book = JSON.parse(selectedOption.dataset.book);

                        if (book.isbn || book.author || book.category || book.pages) {
                            bookDetails.querySelector('.book-isbn').textContent = book.isbn || '-';
                            bookDetails.querySelector('.book-author').textContent = book.author?.full_name || '-';
                            bookDetails.querySelector('.book-category').textContent = book.category?.name || '-';
                            bookDetails.querySelector('.book-pages').textContent = book.pages || '-';
                            bookDetails.classList.remove('hidden');
                        } else {
                            bookDetails.classList.add('hidden');
                        }
                    } catch (e) {
                        bookDetails.classList.add('hidden');
                    }
                } else {
                    bookDetails.classList.add('hidden');
                }
            }
        });

        function updateRemoveButtons() {
            const rows = document.querySelectorAll('.product-row');
            rows.forEach((row, index) => {
                const removeBtn = row.querySelector('.remove-product-btn');
                removeBtn.disabled = rows.length === 1;
            });
        }
    </script>
    @endpush
</x-dashboard>
