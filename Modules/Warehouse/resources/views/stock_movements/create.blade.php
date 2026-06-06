<x-dashboard :pageTitle="__('warehouse::stock_movement.create_movements')">
    <div class="max-w-7xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('warehouse.stock_movements.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('warehouse::stock_movement.stock_movements') }}
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
                    <span
                        class="text-gray-900 font-medium">{{ __('warehouse::stock_movement.create_movements') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-medium text-blue-900 mb-2">{{ __('warehouse::stock_movement.bulk_instructions') }}</h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>• {{ __('warehouse::stock_movement.transfer_instructions') }}</li>
                <li>• {{ __('warehouse::stock_movement.inbound_instructions') }}</li>
                <li>• {{ __('warehouse::stock_movement.outbound_instructions') }}</li>
            </ul>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('warehouse::stock_movement.create_movements') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('warehouse::stock_movement.add_another_movement') }}</p>
            </div>

            <div class="p-6">
                <form action="{{ route('warehouse.stock_movements.store') }}" method="POST" id="movementForm">
                    @csrf

                    <div id="movements-container">
                        <!-- Initial Movement Row -->
                        <div class="movement-row mb-6 p-4 border-2 border-gray-200 rounded-lg">
                            <div class="grid grid-cols-12 gap-4">
                                <!-- Movement Type -->
                                <div class="col-span-12 md:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('warehouse::stock_movement.movement_type') }} <span
                                            class="text-red-500">*</span>
                                    </label>
                                    <select name="movements[0][movement_type]"
                                        class="movement-type-select w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                        required>
                                        <option value="">{{ __('warehouse::stock_movement.select_type') }}
                                        </option>
                                        <option value="transfer">{{ __('warehouse::stock_movement.transfer') }}
                                        </option>
                                        <option value="inbound">{{ __('warehouse::stock_movement.inbound') }}</option>
                                        <option value="outbound">{{ __('warehouse::stock_movement.outbound') }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Product -->
                                <div class="col-span-12 md:col-span-5">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('warehouse::stock_movement.product') }} <span
                                            class="text-red-500">*</span>
                                    </label>
                                    <select name="movements[0][product_id]"
                                        class="product-select w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                        required>
                                        <option value="">{{ __('warehouse::stock_movement.select_product') }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Quantity -->
                                <div class="col-span-12 md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('warehouse::stock_movement.quantity') }} <span
                                            class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="movements[0][quantity]" min="1"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="0" required>
                                </div>

                                <!-- Remove Button -->
                                <div class="col-span-12 md:col-span-2 flex items-end">
                                    <button type="button"
                                        class="remove-movement-btn w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 disabled:opacity-50"
                                        disabled>
                                        {{ __('warehouse::stock_movement.remove_movement') }}
                                    </button>
                                </div>

                                <!-- From Warehouse (for transfer and outbound) -->
                                <div class="col-span-12 md:col-span-6 from-warehouse-field">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('warehouse::stock_movement.from_sub_warehouse') }} <span
                                            class="required-indicator text-red-500">*</span>
                                    </label>
                                    <select name="movements[0][from_sub_warehouse_id]"
                                        class="from-warehouse-select w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">
                                            {{ __('warehouse::stock_movement.select_from_warehouse') }}</option>
                                        @foreach ($subWarehouses as $subWarehouse)
                                            <option value="{{ $subWarehouse->id }}">
                                                {{ $subWarehouse->warehouse->name }} > {{ $subWarehouse->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- To Warehouse (for transfer and inbound) -->
                                <div class="col-span-12 md:col-span-6 to-warehouse-field">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('warehouse::stock_movement.to_sub_warehouse') }} <span
                                            class="required-indicator text-red-500">*</span>
                                    </label>
                                    <select name="movements[0][to_sub_warehouse_id]"
                                        class="to-warehouse-select w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">
                                            {{ __('warehouse::stock_movement.select_to_warehouse') }}</option>
                                        @foreach ($subWarehouses as $subWarehouse)
                                            <option value="{{ $subWarehouse->id }}">
                                                {{ $subWarehouse->warehouse->name }} > {{ $subWarehouse->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Reason -->
                                <div class="col-span-12 md:col-span-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('warehouse::stock_movement.reason') }}
                                    </label>
                                    <input type="text" name="movements[0][reason]"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="{{ __('warehouse::stock_movement.enter_reason') }}">
                                </div>

                                <!-- Notes -->
                                <div class="col-span-12 md:col-span-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('warehouse::stock_movement.notes') }}
                                    </label>
                                    <input type="text" name="movements[0][notes]"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="{{ __('warehouse::stock_movement.enter_notes') }}">
                                </div>

                                <!-- Book Details Display -->
                                <div
                                    class="col-span-12 book-details hidden mt-2 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                    <h4 class="font-medium text-blue-900 mb-2">
                                        {{ __('warehouse::sub_warehouse.book_details') }}</h4>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                        <div>
                                            <span
                                                class="text-blue-700 font-medium">{{ __('warehouse::sub_warehouse.isbn') }}:</span>
                                            <span class="book-isbn text-blue-900"></span>
                                        </div>
                                        <div>
                                            <span
                                                class="text-blue-700 font-medium">{{ __('warehouse::sub_warehouse.author') }}:</span>
                                            <span class="book-author text-blue-900"></span>
                                        </div>
                                        <div>
                                            <span
                                                class="text-blue-700 font-medium">{{ __('warehouse::sub_warehouse.category') }}:</span>
                                            <span class="book-category text-blue-900"></span>
                                        </div>
                                        <div>
                                            <span
                                                class="text-blue-700 font-medium">{{ __('warehouse::sub_warehouse.pages') }}:</span>
                                            <span class="book-pages text-blue-900"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add Another Movement Button -->
                    <div class="mb-6">
                        <button type="button" id="addMovementBtn"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            {{ __('warehouse::stock_movement.add_another_movement') }}
                        </button>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                        <a href="{{ route('warehouse.stock_movements.index') }}"
                            class="px-6 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                            {{ __('common.cancel') }}
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                            {{ __('warehouse::stock_movement.create_movements') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .select2-container--default .select2-selection--single {
                height: 42px;
                border: 1px solid #d1d5db;
                border-radius: 0.5rem;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 40px;
                padding-left: 12px;
                padding-right: 12px;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 40px;
            }

            .select2-container--default.select2-container--focus .select2-selection--single {
                border-color: #3b82f6;
                outline: 2px solid transparent;
                outline-offset: 2px;
                --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
                --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);
                box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
                --tw-ring-color: rgb(59 130 246 / 0.5);
            }

            .select2-dropdown {
                border: 1px solid #d1d5db;
                border-radius: 0.5rem;
            }

            .select2-container {
                width: 100% !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            let movementIndex = 1;
            const isRTL = {{ app()->getLocale() == 'ar' ? 'true' : 'false' }};

            // Initialize Select2 for product selects
            function initializeProductSelect(element) {
                $(element).select2({
                    ajax: {
                        url: '{{ route('warehouse.stock_movements.search_products') }}',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term,
                                page: params.page || 1
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.results,
                                pagination: {
                                    more: data.pagination.more
                                }
                            };
                        },
                        cache: true
                    },
                    placeholder: '{{ __('warehouse::stock_movement.select_product') }}',
                    minimumInputLength: 0,
                    allowClear: true,
                    dir: isRTL ? 'rtl' : 'ltr',
                    language: {
                        inputTooShort: function() {
                            return '{{ __('common.type_to_search') }}';
                        },
                        searching: function() {
                            return '{{ __('common.searching') }}...';
                        },
                        noResults: function() {
                            return '{{ __('common.no_results') }}';
                        },
                        loadingMore: function() {
                            return '{{ __('common.loading_more') }}...';
                        }
                    }
                });

                // Handle selection change
                $(element).on('select2:select', function(e) {
                    const data = e.params.data;
                    const movementRow = $(this).closest('.movement-row');
                    const bookDetails = movementRow.find('.book-details');

                    if (data.type === 'book' && data.book) {
                        const book = data.book;
                        if (book.isbn || book.contract.authors || book.category || book.pages) {
                            bookDetails.find('.book-isbn').text(book.isbn || '-');
                            bookDetails.find('.book-author').text(book.contract.authors?.full_name || '-');
                            bookDetails.find('.book-category').text(book.category?.name || '-');
                            bookDetails.find('.book-pages').text(book.pages || '-');
                            bookDetails.removeClass('hidden');
                        } else {
                            bookDetails.addClass('hidden');
                        }
                    } else {
                        bookDetails.addClass('hidden');
                    }
                });

                // Handle clear
                $(element).on('select2:clear', function() {
                    const movementRow = $(this).closest('.movement-row');
                    movementRow.find('.book-details').addClass('hidden');
                });
            }

            // Initialize first product select
            $(document).ready(function() {
                initializeProductSelect('.product-select');

                const firstRow = document.querySelector('.movement-row');
                if (firstRow) {
                    firstRow.querySelector('.from-warehouse-field')?.classList.add('hidden');
                    firstRow.querySelector('.to-warehouse-field')?.classList.add('hidden');
                }
            });

            document.getElementById('addMovementBtn').addEventListener('click', function() {
                const container = document.getElementById('movements-container');
                const template = container.querySelector('.movement-row').cloneNode(true);

                // Update indices
                template.querySelectorAll('[name^="movements[0]"]').forEach(input => {
                    input.name = input.name.replace('[0]', `[${movementIndex}]`);
                    if (input.type !== 'button') {
                        input.value = '';
                    }
                });

                // Reset regular selects
                template.querySelectorAll('select:not(.product-select)').forEach(select => {
                    select.selectedIndex = 0;
                });

                // Destroy and recreate product select
                const productSelect = template.querySelector('.product-select');
                if ($(productSelect).hasClass('select2-hidden-accessible')) {
                    $(productSelect).select2('destroy');
                }
                productSelect.innerHTML =
                    '<option value="">{{ __('warehouse::stock_movement.select_product') }}</option>';

                // Hide book details and warehouse fields
                template.querySelector('.book-details')?.classList.add('hidden');
                template.querySelector('.from-warehouse-field')?.classList.add('hidden');
                template.querySelector('.to-warehouse-field')?.classList.add('hidden');

                // Enable remove button
                template.querySelector('.remove-movement-btn').disabled = false;

                container.appendChild(template);

                // Initialize Select2 on new product select
                initializeProductSelect(template.querySelector('.product-select'));

                movementIndex++;
                updateRemoveButtons();
            });

            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-movement-btn')) {
                    const row = e.target.closest('.movement-row');
                    const productSelect = row.querySelector('.product-select');

                    // Destroy Select2 before removing
                    if ($(productSelect).hasClass('select2-hidden-accessible')) {
                        $(productSelect).select2('destroy');
                    }

                    row.remove();
                    updateRemoveButtons();
                }
            });

            document.addEventListener('change', function(e) {
                const movementRow = e.target.closest('.movement-row');
                if (!movementRow) return;

                // Handle movement type change
                if (e.target.classList.contains('movement-type-select')) {
                    const type = e.target.value;
                    const fromField = movementRow.querySelector('.from-warehouse-field');
                    const toField = movementRow.querySelector('.to-warehouse-field');
                    const fromSelect = movementRow.querySelector('.from-warehouse-select');
                    const toSelect = movementRow.querySelector('.to-warehouse-select');

                    // Reset selections
                    fromSelect.value = '';
                    toSelect.value = '';

                    // Show/hide fields based on type
                    if (type === 'transfer') {
                        fromField.classList.remove('hidden');
                        toField.classList.remove('hidden');
                        fromSelect.required = true;
                        toSelect.required = true;
                    } else if (type === 'inbound') {
                        fromField.classList.add('hidden');
                        toField.classList.remove('hidden');
                        fromSelect.required = false;
                        toSelect.required = true;
                    } else if (type === 'outbound') {
                        fromField.classList.remove('hidden');
                        toField.classList.add('hidden');
                        fromSelect.required = true;
                        toSelect.required = false;
                    } else {
                        fromField.classList.add('hidden');
                        toField.classList.add('hidden');
                        fromSelect.required = false;
                        toSelect.required = false;
                    }
                }
            });

            function updateRemoveButtons() {
                const rows = document.querySelectorAll('.movement-row');
                rows.forEach((row, index) => {
                    const removeBtn = row.querySelector('.remove-movement-btn');
                    removeBtn.disabled = rows.length === 1;
                });
            }
        </script>
    @endpush
</x-dashboard>
