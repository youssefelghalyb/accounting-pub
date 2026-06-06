<x-dashboard :pageTitle="__('finance::invoice.create_invoice')">
    <div class="max-w-7xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('finance.sales-invoices.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('finance::invoice.sales_invoices') }}
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
                    <span class="text-gray-900 font-medium">{{ __('finance::invoice.create_invoice') }}</span>
                </li>
            </ol>
        </nav>

        <form action="{{ route('finance.sales-invoices.store') }}" method="POST" id="invoiceForm">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Invoice Details -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900">{{ __('finance::invoice.invoice_info') }}</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Party -->
                                <!-- Party -->
                                <div class="md:col-span-2">
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            {{ __('finance::invoice.party') }} <span class="text-red-500">*</span>
                                        </label>
                                        @if (!$selectedParty)
                                            <button type="button" onclick="openQuickPartyModal()"
                                                class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                {{ __('finance::party.quick_add') }}
                                            </button>
                                        @else
                                            {{-- Locked badge shown when party is pre-selected from author --}}
                                            <span
                                                class="inline-flex items-center gap-1 text-xs text-amber-700 bg-amber-50 border border-amber-200 px-2 py-1 rounded-full">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ __('finance::invoice.party_locked') }}
                                            </span>
                                        @endif
                                    </div>

                                    @if ($selectedParty)
                                        {{-- Pre-selected + locked: hidden input carries the value, styled display shows the name --}}
                                        <input type="hidden" name="party_id" value="{{ $selectedParty['id'] }}">
                                        <div
                                            class="w-full px-4 py-2.5 border border-amber-200 bg-amber-50 rounded-lg flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="w-7 h-7 rounded-full bg-amber-200 flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-4 h-4 text-amber-700" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                </div>
                                                <span
                                                    class="font-medium text-gray-900 text-sm">{{ $selectedParty['text'] }}</span>
                                            </div>
                                            <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    @else
                                        <x-searchable-select name="party_id"
                                            url="{{ route('search-select', 'parties') }}"
                                            placeholder="{{ __('finance::invoice.select_party') }}"
                                            :required="true" />
                                    @endif

                                    @error('party_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <x-dashboard.packages.quick-add-modal id="quick_party" target-ss-id="partyField"
                                    url="{{ route('finance.parties.quick-store') }}" :title="__('finance::party.quick_add_party')"
                                    :fields="[
                                        [
                                            'name' => 'name',
                                            'label' => 'Name',
                                            'type' => 'text',
                                            'required' => true,
                                            'span' => 2,
                                        ],
                                        [
                                            'name' => 'type',
                                            'label' => 'Type',
                                            'type' => 'select',
                                            'required' => true,
                                            'span' => 2,
                                            'options' => [
                                                ['value' => 'individual', 'label' => 'Individual'],
                                                ['value' => 'company', 'label' => 'Company'],
                                            ],
                                        ],
                                        ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'span' => 1],
                                        ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'span' => 1],
                                    ]" />



                                <!-- Sub-Warehouse -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('finance::invoice.sub_warehouse') }} <span class="text-red-500">*</span>
                                    </label>
                                    <select name="sub_warehouse_id" id="subWarehouseSelect" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">{{ __('finance::invoice.select_sub_warehouse') }}
                                        </option>
                                        @foreach ($subWarehouses as $subWarehouse)
                                            <option @if ($subWarehouse->type == 'main') selected @endif
                                                value="{{ $subWarehouse->id }}">
                                                {{ $subWarehouse->name }} - {{ $subWarehouse->warehouse->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('sub_warehouse_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Invoice Date -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('finance::invoice.invoice_date') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="invoice_date"
                                        value="{{ old('invoice_date', now()->format('Y-m-d')) }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('invoice_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Due Date -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('finance::invoice.due_date') }}
                                    </label>
                                    <input type="date" name="due_date" value="{{ old('due_date') }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('due_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Payment Terms -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('finance::invoice.payment_terms') }}
                                    </label>
                                    <input type="text" name="payment_terms" value="{{ old('payment_terms') }}"
                                        placeholder="e.g., Net 30"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Warning Alert -->
                    <div id="stockWarningAlert" class="hidden bg-red-50 border-l-4 border-red-500 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    {{ __('finance::invoice.insufficient_stock_warning') }}</h3>
                                <div id="stockWarningMessage" class="mt-1 text-sm text-red-700"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Add by ISBN/Barcode -->
                    <div
                        class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl shadow-sm border border-blue-200">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-3">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                                    </path>
                                </svg>
                                <h3 class="text-lg font-bold text-gray-900">
                                    {{ __('finance::invoice.quick_add_isbn') }}
                                </h3>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">{{ __('finance::invoice.scan_or_enter_isbn') }}</p>
                            <div class="flex gap-3">
                                <input type="text" id="isbnInput"
                                    placeholder="{{ __('finance::invoice.enter_isbn_placeholder') }}"
                                    class="flex-1 px-4 py-3 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                                    autocomplete="off">
                                <button type="button" id="addByIsbnBtn"
                                    class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    {{ __('finance::invoice.add') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-bold text-gray-900">{{ __('finance::invoice.items') }}</h2>
                                <button type="button" id="addItemBtn"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    {{ __('finance::invoice.add_item') }}
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            <div id="itemsContainer" class="space-y-4">
                                <!-- Items will be added here dynamically -->
                            </div>
                            <button type="button" id="floatingAddItemBtn"
                                class="fixed bottom-6 {{ app()->getLocale() == 'ar' ? 'left-6' : 'right-6' }} z-40 w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition-all hover:scale-110 flex items-center justify-center group">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span
                                    class="absolute {{ app()->getLocale() == 'ar' ? 'right-16' : 'left-16' }} bg-gray-900 text-white text-sm px-3 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                    {{ __('finance::invoice.add_item') }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900">{{ __('finance::invoice.notes') }}</h2>
                        </div>
                        <div class="p-6">
                            <textarea name="notes" rows="3" placeholder="{{ __('finance::invoice.enter_notes') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Tax & Discount -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-bold text-gray-900">{{ __('finance::invoice.amount_breakdown') }}
                            </h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <!-- Taxable Checkbox -->
                            <div class="flex items-center">
                                <input type="checkbox" name="is_taxable" id="is_taxable" value="1"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="is_taxable" class="ml-2 text-sm font-medium text-gray-700">
                                    {{ __('finance::invoice.is_taxable') }}
                                </label>
                            </div>

                            <!-- Tax Rate -->
                            <div id="taxRateContainer">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('finance::invoice.tax_rate') }}
                                </label>
                                <div>
                                    <span id="tax_rate_display"
                                        class="block w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                                        {{ number_format(old('tax_rate', $orgSettings->tax_rate ?? 0), 2) }}%
                                    </span>
                                    <input type="hidden" name="tax_rate" id="tax_rate"
                                        value="{{ old('tax_rate', $orgSettings->tax_rate ?? 0) }}">
                                </div>
                            </div>

                            <!-- Discount Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('finance::invoice.discount_type') }}
                                </label>
                                <select name="discount_type" id="discount_type"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="percentage">{{ __('finance::invoice.discount_types.percentage') }}
                                    <option value="fixed">{{ __('finance::invoice.discount_types.fixed') }}</option>
                                    </option>
                                </select>
                            </div>

                            <!-- Discount Value -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('finance::invoice.discount_value') }}
                                </label>
                                <input type="number" name="discount_value" id="discount_value" step="0.01"
                                    min="0" value="{{ old('discount_value', 0) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <!-- Totals Display -->
                            <div class="pt-4 border-t border-gray-200 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('finance::invoice.subtotal') }}:</span>
                                    <span id="subtotalDisplay" class="font-medium text-gray-900">0.00</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('finance::invoice.discount_amount') }}:</span>
                                    <span id="discountDisplay" class="font-medium text-gray-900">0.00</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('finance::invoice.tax_amount') }}:</span>
                                    <span id="taxDisplay" class="font-medium text-gray-900">0.00</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                                    <span class="text-gray-900">{{ __('finance::invoice.total_amount') }}:</span>
                                    <span id="totalDisplay" class="text-blue-600">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information (Optional) -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-bold text-gray-900">{{ __('finance::invoice.payment_info') }}</h2>
                            <p class="text-sm text-gray-600 mt-1">{{ __('finance::invoice.initial_payment') }}</p>
                        </div>
                        <div class="p-6 space-y-4">
                            <!-- Payment Amount -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('finance::invoice.paid_amount') }}
                                </label>
                                <input type="number" name="paid_amount" id="paid_amount" step="0.01"
                                    min="0" value="{{ old('paid_amount', 0) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            </div>

                            <!-- Account -->
                            <div id="accountContainer" style="display: none;">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('finance::invoice.payment_account') }}
                                </label>
                                <select name="account_id" id="account_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="">{{ __('finance::invoice.select_account') }}</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}">
                                            {{ $account->display_name }} -
                                            {{ number_format($account->current_balance, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>



                    <!-- Submit Button -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <button type="submit" id="createInvoiceBtn"
                            class="w-full py-3 px-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            {{ __('finance::invoice.create_invoice') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>


    </div>

    <!-- Product Selection Side Drawer -->
    <div id="productDrawer" class="fixed inset-0 z-50 hidden">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeProductDrawer()"></div>

        <!-- Drawer -->
        <div class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} top-0 h-full w-full max-w-2xl bg-white shadow-xl transform transition-transform"
            id="drawerContent">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-900">{{ __('finance::invoice.select_product') }}</h3>
                <button type="button" onclick="closeProductDrawer()"
                    class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Filters -->
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-3">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2">{{ __('finance::invoice.search') }}</label>
                        <input type="text" id="productSearch"
                            placeholder="{{ __('finance::invoice.search_by_name_isbn') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <!-- Category -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2">{{ __('finance::invoice.category') }}</label>
                        <select id="categoryFilter"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">{{ __('finance::invoice.all_categories') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sub Category -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2">{{ __('finance::invoice.sub_category') }}</label>
                        <select id="subCategoryFilter"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">{{ __('finance::invoice.all_sub_categories') }}</option>
                            @foreach ($subCategories as $subCategory)
                                <option value="{{ $subCategory->id }}">{{ $subCategory->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Author -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2">{{ __('finance::invoice.author') }}</label>
                        <select id="authorFilter"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">{{ __('finance::invoice.all_authors') }}</option>
                            @foreach ($authors as $author)
                                <option value="{{ $author->id }}">{{ $author->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Product List -->
            <div class="overflow-y-auto" style="height: calc(100vh - 260px);">
                <div id="productList" class="p-6 space-y-3">
                    <!-- Products will be loaded here -->
                </div>
            </div>
        </div>
    </div>


    <!-- Quick Add Party Modal -->
    <div id="quickPartyModal" class="fixed inset-0 z-50 hidden">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeQuickPartyModal()"></div>

        <!-- Modal -->
        <div
            class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-xl shadow-xl">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900">{{ __('finance::party.quick_add_party') }}</h3>
                    <button type="button" onclick="closeQuickPartyModal()"
                        class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <form id="quickPartyForm" class="p-6 space-y-4">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('finance::party.name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="quick_party_name" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('finance::party.type') }} <span class="text-red-500">*</span>
                    </label>
                    <select id="quick_party_type" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="individual">{{ __('finance::party.types.individual') }}</option>
                        <option value="company">{{ __('finance::party.types.company') }}</option>
                        <option value="online">{{ __('finance::party.types.online') }}</option>
                    </select>
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('finance::party.phone') }}
                    </label>
                    <input type="text" id="quick_party_phone"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('finance::party.email') }}
                    </label>
                    <input type="email" id="quick_party_email"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeQuickPartyModal()"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit" id="quickPartySubmitBtn"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        {{ __('finance::party.add_party') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // ═══════════════════════════════════════════════════════════════
            // STATE
            // ═══════════════════════════════════════════════════════════════
            let itemCounter = 0;
            let currentItemId = null;
            const productCache = {}; // id → product object (built lazily from API)
            const stockCache = {}; // `${warehouseId}_${productId}` → qty
            const productsForIsbn = @json($productsForJs); // slim list for ISBN lookup only

            // Drawer state
            let drawerPage = 1;
            let drawerLoading = false;
            let drawerHasMore = true;
            let drawerSearchTimer = null;
            const drawerFilters = {
                q: '',
                category_id: '',
                sub_category_id: '',
                author_id: ''
            };

            // Auto-save
            const AUTOSAVE_KEY = 'invoice_draft_{{ auth()->id() }}';
            const AUTOSAVE_DELAY_MS = 2000;
            let autosaveTimer = null;

            // ═══════════════════════════════════════════════════════════════
            // BOOT
            // ═══════════════════════════════════════════════════════════════
            document.addEventListener('DOMContentLoaded', () => {

                // Seed productCache from slim list so ISBN lookup + draft restore work immediately
                productsForIsbn.forEach(p => {
                    productCache[p.id] = p;
                });

                // First empty item row
                addInvoiceItem();

                // Item buttons
                document.getElementById('addItemBtn').addEventListener('click', addInvoiceItem);
                document.getElementById('floatingAddItemBtn').addEventListener('click', addInvoiceItem);

                // Tax toggle
                document.getElementById('is_taxable').addEventListener('change', function() {
                    document.getElementById('taxRateContainer').style.display = this.checked ? 'block' : 'none';
                    calculateTotals();
                });

                // Totals recalc
                ['discount_type', 'discount_value', 'tax_rate'].forEach(id => {
                    document.getElementById(id).addEventListener('input', calculateTotals);
                    document.getElementById(id).addEventListener('change', calculateTotals);
                });

                // Payment amount → show/hide account selector
                document.getElementById('paid_amount').addEventListener('input', function() {
                    const show = parseFloat(this.value) > 0;
                    document.getElementById('accountContainer').style.display = show ? 'block' : 'none';
                    document.getElementById('account_id').required = show;
                });

                // Warehouse change → bust stock cache + recheck
                document.getElementById('subWarehouseSelect').addEventListener('change', () => {
                    Object.keys(stockCache).forEach(k => delete stockCache[k]);
                    checkAllItemsStock();
                });

                // ISBN quick-add
                document.getElementById('isbnInput').addEventListener('keypress', e => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        addByIsbn();
                    }
                });
                document.getElementById('addByIsbnBtn').addEventListener('click', addByIsbn);

                // Drawer filter listeners
                document.getElementById('productSearch').addEventListener('input', function() {
                    clearTimeout(drawerSearchTimer);
                    drawerFilters.q = this.value.trim();
                    drawerSearchTimer = setTimeout(() => fetchDrawerProducts(true), 350);
                });
                document.getElementById('categoryFilter').addEventListener('change', function() {
                    drawerFilters.category_id = this.value;
                    fetchDrawerProducts(true);
                });
                document.getElementById('subCategoryFilter').addEventListener('change', function() {
                    drawerFilters.sub_category_id = this.value;
                    fetchDrawerProducts(true);
                });
                document.getElementById('authorFilter').addEventListener('change', function() {
                    drawerFilters.author_id = this.value;
                    fetchDrawerProducts(true);
                });

                // Drawer infinite scroll
                document.querySelector('#productDrawer .overflow-y-auto')
                    .addEventListener('scroll', function() {
                        if (this.scrollTop + this.clientHeight >= this.scrollHeight - 120) {
                            fetchDrawerProducts(false);
                        }
                    });

                // Auto-save hooks
                setupAutoSave();

                // Load draft if exists
                loadDraft();

                // Clear draft on valid submit
                document.getElementById('invoiceForm').addEventListener('submit', function() {
                    if (this.checkValidity()) clearDraft();
                });
            });

            // ═══════════════════════════════════════════════════════════════
            // INVOICE ITEMS
            // ═══════════════════════════════════════════════════════════════
            function addInvoiceItem() {
                itemCounter++;
                const id = itemCounter;
                const dir = '{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}';

                document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', `
        <div class="item-row border border-gray-200 rounded-lg p-4 transition-all" data-item="${id}">
            <div class="grid grid-cols-12 gap-4">

                <div class="col-span-12 md:col-span-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        {{ __('finance::invoice.product') }}
                    </label>
                    <input type="hidden" name="items[${id}][product_id]" class="product-id">
                    <button type="button" onclick="openProductDrawer(${id})"
                        class="product-display w-full px-3 py-2 text-sm border border-gray-300 rounded-lg
                               text-${dir} hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <span class="text-gray-400">{{ __('finance::invoice.select_product') }}</span>
                    </button>
                </div>

                <div class="col-span-6 md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        {{ __('finance::invoice.quantity') }}
                    </label>
                    <input type="number" name="items[${id}][quantity]" min="1" value="1" required
                        class="item-quantity w-full px-3 py-2 text-sm border border-gray-300 rounded-lg
                               focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        oninput="handleQuantityChange(${id})" onblur="handleQuantityChange(${id})">
                </div>

                <div class="col-span-6 md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        {{ __('finance::invoice.unit_price') }}
                    </label>
                    <input type="number" name="items[${id}][unit_price]" step="0.01" min="0" value="0" required
                        class="item-price w-full px-3 py-2 text-sm border border-gray-300 rounded-lg
                               focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        oninput="calculateLineTotal(${id})">
                </div>

                <div class="col-span-6 md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        {{ __('finance::invoice.item_discount') }}
                    </label>
                    <input type="number" name="items[${id}][discount_amount]" step="0.01" min="0" value="0"
                        class="item-discount w-full px-3 py-2 text-sm border border-gray-300 rounded-lg
                               focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        oninput="calculateLineTotal(${id})">
                </div>

                <div class="col-span-5 md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        {{ __('finance::invoice.line_total') }}
                    </label>
                    <input type="text" readonly value="0.00"
                        class="item-total w-full px-3 py-2 text-sm bg-gray-50 border border-gray-300
                               rounded-lg font-medium text-gray-900">
                </div>

                <div class="col-span-1 flex items-end">
                    <button type="button" onclick="removeItem(${id})"
                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5
                                   4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>

            </div>
        </div>
    `);
            }

            function removeItem(itemId) {
                if (document.querySelectorAll('.item-row').length <= 1) {
                    alert('{{ __('finance::invoice.at_least_one_item') }}');
                    return;
                }
                document.querySelector(`[data-item="${itemId}"]`).remove();
                calculateTotals();
                checkAllItemsStock();
            }

            // ═══════════════════════════════════════════════════════════════
            // PRODUCT SELECTION
            // ═══════════════════════════════════════════════════════════════
            async function selectProductForItem(itemId, product) {
                // Merge into cache
                productCache[product.id] = product;

                // If this product already exists in another row, merge quantities
                const existingRow = findRowByProductId(product.id, itemId);
                if (existingRow) {
                    const qty = existingRow.querySelector('.item-quantity');
                    qty.value = (parseFloat(qty.value) || 0) + 1;
                    const existingId = existingRow.getAttribute('data-item');
                    await handleQuantityChange(existingId);
                    highlightRow(existingId, 'green');

                    // Remove current row if it's still empty
                    const currentRow = document.querySelector(`[data-item="${itemId}"]`);
                    if (currentRow && !currentRow.querySelector('.product-id').value) {
                        if (document.querySelectorAll('.item-row').length > 1) currentRow.remove();
                    }
                    return;
                }

                const row = document.querySelector(`[data-item="${itemId}"]`);
                if (!row) return;

                row.querySelector('.product-id').value = product.id;
                row.querySelector('.item-price').value = product.price;

                row.querySelector('.product-display').innerHTML = `
        <div class="flex items-center justify-between">
            <div>
                <div class="font-medium text-gray-900">${product.name}</div>
                ${product.isbn ? `<div class="text-xs text-gray-500">ISBN: ${product.isbn}</div>` : ''}
            </div>
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    `;

                calculateLineTotal(itemId);
                await checkProductStock(product.id, parseFloat(row.querySelector('.item-quantity').value) || 1, itemId);
            }

            function findRowByProductId(productId, excludeItemId = null) {
                for (const row of document.querySelectorAll('.item-row')) {
                    if (excludeItemId && row.getAttribute('data-item') == excludeItemId) continue;
                    if (row.querySelector('.product-id').value == productId) return row;
                }
                return null;
            }

            // ═══════════════════════════════════════════════════════════════
            // CALCULATIONS
            // ═══════════════════════════════════════════════════════════════
            function calculateLineTotal(itemId) {
                const row = document.querySelector(`[data-item="${itemId}"]`);
                const qty = parseFloat(row.querySelector('.item-quantity').value) || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                const discount = parseFloat(row.querySelector('.item-discount').value) || 0;
                row.querySelector('.item-total').value = Math.max(0, qty * price - discount).toFixed(2);
                calculateTotals();
            }

            function calculateTotals() {
                let subtotal = 0;
                document.querySelectorAll('.item-total').forEach(el => {
                    subtotal += parseFloat(el.value) || 0;
                });

                const discountType = document.getElementById('discount_type').value;
                const discountValue = parseFloat(document.getElementById('discount_value').value) || 0;
                const discountAmt = discountType === 'percentage' ? (subtotal * discountValue / 100) : discountValue;
                const afterDiscount = subtotal - discountAmt;

                const isTaxable = document.getElementById('is_taxable').checked;
                const taxRate = parseFloat(document.getElementById('tax_rate').value) || 0;
                const taxAmt = isTaxable ? (afterDiscount * taxRate / 100) : 0;
                const total = afterDiscount + taxAmt;

                document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2);
                document.getElementById('discountDisplay').textContent = discountAmt.toFixed(2);
                document.getElementById('taxDisplay').textContent = taxAmt.toFixed(2);
                document.getElementById('totalDisplay').textContent = total.toFixed(2);
            }

            // ═══════════════════════════════════════════════════════════════
            // STOCK CHECKING
            // ═══════════════════════════════════════════════════════════════
            let quantityDebounceTimer = null;

            async function handleQuantityChange(itemId) {
                clearTimeout(quantityDebounceTimer);
                calculateLineTotal(itemId);

                const row = document.querySelector(`[data-item="${itemId}"]`);
                const productId = row.querySelector('.product-id').value;
                const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;

                if (!productId) return;

                if (quantity <= 0) {
                    hideStockWarning();
                    clearRowHighlight(itemId);
                    return;
                }

                quantityDebounceTimer = setTimeout(() => checkProductStock(productId, quantity, itemId), 500);
            }

            async function getStock(productId, warehouseId) {
                const key = `${warehouseId}_${productId}`;
                if (stockCache[key] !== undefined) return stockCache[key];

                try {
                    const res = await fetch(
                        `{{ route('finance.sales-invoices.product-stock') }}?product_id=${productId}&sub_warehouse_id=${warehouseId}`
                    );
                    const data = await res.json();
                    stockCache[key] = data.quantity;
                    return data.quantity;
                } catch {
                    return 0;
                }
            }

            async function checkProductStock(productId, requestedQty, itemId) {
                const warehouseId = document.getElementById('subWarehouseSelect').value;
                if (!warehouseId) {
                    showStockWarning('{{ __('finance::invoice.please_select_sub_warehouse') }}');
                    return false;
                }
                if (!productId) return true;

                const available = await getStock(productId, warehouseId);

                // Sum ALL rows for this product
                let totalRequested = 0;
                document.querySelectorAll('.item-row').forEach(row => {
                    if (row.querySelector('.product-id').value == productId) {
                        totalRequested += parseFloat(row.querySelector('.item-quantity').value) || 0;
                    }
                });

                const product = productCache[productId];
                const name = product ? product.name : `#${productId}`;

                if (totalRequested > available) {
                    showStockWarning(
                        `{{ __('finance::invoice.insufficient_stock_for') }} "${name}". ` +
                        `{{ __('finance::invoice.available') }}: ${available}, ` +
                        `{{ __('finance::invoice.requested') }}: ${totalRequested}`
                    );
                    highlightRow(itemId, 'red');
                    return false;
                }

                hideStockWarning();
                clearRowHighlight(itemId);
                return true;
            }

            async function checkAllItemsStock() {
                hideStockWarning();
                for (const row of document.querySelectorAll('.item-row')) {
                    const productId = row.querySelector('.product-id').value;
                    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                    const itemId = row.getAttribute('data-item');
                    if (productId && quantity > 0) {
                        const valid = await checkProductStock(productId, quantity, itemId);
                        if (!valid) return false;
                    }
                }
                return true;
            }

            function showStockWarning(message) {
                document.getElementById('stockWarningMessage').textContent = message;
                document.getElementById('stockWarningAlert').classList.remove('hidden');
                document.getElementById('stockWarningAlert').scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                const btn = document.getElementById('createInvoiceBtn');
                if (btn) {
                    btn.disabled = true;
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }

            function hideStockWarning() {
                document.getElementById('stockWarningAlert').classList.add('hidden');
                const btn = document.getElementById('createInvoiceBtn');
                if (btn) {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }

            function highlightRow(itemId, color) {
                const row = document.querySelector(`[data-item="${itemId}"]`);
                if (!row) return;
                const isRed = color === 'red';
                const isGreen = color === 'green';
                row.classList.toggle('border-red-500', isRed);
                row.classList.toggle('bg-red-50', isRed);
                row.classList.toggle('border-green-500', isGreen);
                row.classList.toggle('bg-green-50', isGreen);
                row.classList.toggle('border-gray-200', !isRed && !isGreen);
                if (isGreen) {
                    row.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    setTimeout(() => clearRowHighlight(itemId), 2000);
                }
            }

            function clearRowHighlight(itemId) {
                const row = document.querySelector(`[data-item="${itemId}"]`);
                if (!row) return;
                row.classList.remove('border-red-500', 'bg-red-50', 'border-green-500', 'bg-green-50');
                row.classList.add('border-gray-200');
            }

            // ═══════════════════════════════════════════════════════════════
            // ISBN QUICK-ADD
            // ═══════════════════════════════════════════════════════════════
            async function addByIsbn() {
                const isbn = document.getElementById('isbnInput').value.trim();
                if (!isbn) {
                    alert('{{ __('finance::invoice.please_enter_isbn') }}');
                    return;
                }

                const warehouseId = document.getElementById('subWarehouseSelect').value;
                if (!warehouseId) {
                    alert('{{ __('finance::invoice.please_select_sub_warehouse_first') }}');
                    document.getElementById('subWarehouseSelect').focus();
                    return;
                }

                // Try local cache first, then fetch from server
                let product = Object.values(productCache).find(p => p.isbn === isbn);

                if (!product) {
                    // Not in cache → ask server
                    try {
                        const res = await fetch(
                            `{{ route('finance.sales-invoices.search-products') }}?q=${encodeURIComponent(isbn)}&per_page=1`
                        );
                        const data = await res.json();
                        if (data.data && data.data.length > 0) {
                            product = data.data.find(p => p.isbn === isbn) || data.data[0];
                            productCache[product.id] = product;
                        }
                    } catch {
                        /* handled below */
                    }
                }

                if (!product) {
                    alert('{{ __('finance::invoice.product_not_found') }}');
                    document.getElementById('isbnInput').value = '';
                    document.getElementById('isbnInput').focus();
                    return;
                }

                const existingRow = findRowByProductId(product.id);
                if (existingRow) {
                    const qtyInput = existingRow.querySelector('.item-quantity');
                    qtyInput.value = (parseFloat(qtyInput.value) || 0) + 1;
                    await handleQuantityChange(existingRow.getAttribute('data-item'));
                    highlightRow(existingRow.getAttribute('data-item'), 'green');
                    showToast('{{ __('finance::invoice.quantity_increased') }}: ' + product.name);
                } else {
                    addInvoiceItem();
                    await selectProductForItem(itemCounter, product);
                    showToast('{{ __('finance::invoice.product_added') }}: ' + product.name);
                }

                document.getElementById('isbnInput').value = '';
                document.getElementById('isbnInput').focus();
            }

            // ═══════════════════════════════════════════════════════════════
            // PRODUCT DRAWER
            // ═══════════════════════════════════════════════════════════════
            function openProductDrawer(itemId) {
                if (!document.getElementById('subWarehouseSelect').value) {
                    alert('{{ __('finance::invoice.please_select_sub_warehouse_first') }}');
                    document.getElementById('subWarehouseSelect').focus();
                    return;
                }

                currentItemId = itemId;

                // Reset state
                drawerPage = 1;
                drawerHasMore = true;
                Object.keys(drawerFilters).forEach(k => drawerFilters[k] = '');
                document.getElementById('productSearch').value = '';
                document.getElementById('categoryFilter').value = '';
                document.getElementById('subCategoryFilter').value = '';
                document.getElementById('authorFilter').value = '';
                document.getElementById('productList').innerHTML = '';

                document.getElementById('productDrawer').classList.remove('hidden');
                fetchDrawerProducts(true);
                setTimeout(() => document.getElementById('productSearch').focus(), 100);
            }

            function closeProductDrawer() {
                document.getElementById('productDrawer').classList.add('hidden');
                currentItemId = null;
            }

            async function fetchDrawerProducts(reset = false) {
                if (drawerLoading) return;
                if (!reset && !drawerHasMore) return;

                if (reset) {
                    drawerPage = 1;
                    drawerHasMore = true;
                    document.getElementById('productList').innerHTML = '';
                }

                drawerLoading = true;
                showDrawerSpinner();

                const params = new URLSearchParams({
                    q: drawerFilters.q,
                    page: drawerPage,
                    category_id: drawerFilters.category_id,
                    sub_category_id: drawerFilters.sub_category_id,
                    author_id: drawerFilters.author_id,
                });

                try {
                    const res = await fetch(`{{ route('finance.sales-invoices.search-products') }}?${params}`);
                    const json = await res.json();

                    appendDrawerCards(json.data);

                    // Merge into productCache
                    json.data.forEach(p => {
                        productCache[p.id] = p;
                    });

                    drawerHasMore = json.has_more;
                    drawerPage++;
                } catch (e) {
                    console.error('Drawer fetch failed', e);
                } finally {
                    drawerLoading = false;
                    hideDrawerSpinner();
                }
            }

            function appendDrawerCards(list) {
                const container = document.getElementById('productList');

                if (drawerPage === 1 && list.length === 0) {
                    container.innerHTML = `
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-500">{{ __('finance::invoice.no_products_found') }}</p>
            </div>`;
                    return;
                }

                list.forEach(product => {
                    const card = document.createElement('div');
                    card.className =
                        'border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:bg-blue-50 cursor-pointer transition-all';
                    card.onclick = () => {
                        selectProductFromDrawer(product.id);
                    };
                    card.innerHTML = `
            <div class="flex justify-between items-start gap-4">
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-900 truncate">${escHtml(product.name)}</h4>
                    <div class="mt-1 space-y-0.5">
                        ${product.isbn         ? `<p class="text-xs text-gray-500">ISBN: ${escHtml(product.isbn)}</p>` : ''}
                        ${product.sku          ? `<p class="text-xs text-gray-500">SKU: ${escHtml(product.sku)}</p>` : ''}
                        ${product.author_name  ? `<p class="text-xs text-gray-600">{{ __('finance::invoice.author') }}: ${escHtml(product.author_name)}</p>` : ''}
                        ${product.category_name? `<p class="text-xs text-gray-600">{{ __('finance::invoice.category') }}: ${escHtml(product.category_name)}</p>` : ''}
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-lg font-bold text-blue-600">${parseFloat(product.price).toFixed(2)}</p>
                    ${product.stock_quantity != null
                        ? `<p class="text-xs text-gray-500">{{ __('finance::invoice.stock') }}: ${product.stock_quantity}</p>`
                        : ''}
                </div>
            </div>`;
                    container.appendChild(card);
                });
            }

            function selectProductFromDrawer(productId) {
                if (!currentItemId) return;
                const product = productCache[productId];
                if (!product) return;
                selectProductForItem(currentItemId, product);
                closeProductDrawer();
            }

            function showDrawerSpinner() {
                if (document.getElementById('drawerSpinner')) return;
                const el = document.createElement('div');
                el.id = 'drawerSpinner';
                el.className = 'flex justify-center py-6';
                el.innerHTML = `
        <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>`;
                document.getElementById('productList').after(el);
            }

            function hideDrawerSpinner() {
                document.getElementById('drawerSpinner')?.remove();
            }

            // ═══════════════════════════════════════════════════════════════
            // FORM SUBMIT
            // ═══════════════════════════════════════════════════════════════
            document.getElementById('invoiceForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                const btn = document.getElementById('createInvoiceBtn');
                const originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = `
        <svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
        </svg>`;

                const valid = await checkAllItemsStock();
                if (valid) {
                    clearDraft();
                    this.submit();
                } else {
                    alert('{{ __('finance::invoice.please_check_stock_warnings') }}');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            });

            // ═══════════════════════════════════════════════════════════════
            // AUTO-SAVE / DRAFT
            // ═══════════════════════════════════════════════════════════════
            function setupAutoSave() {
                const form = document.getElementById('invoiceForm');
                form.addEventListener('input', () => {
                    clearTimeout(autosaveTimer);
                    autosaveTimer = setTimeout(saveDraft, AUTOSAVE_DELAY_MS);
                });
                form.addEventListener('change', () => {
                    clearTimeout(autosaveTimer);
                    autosaveTimer = setTimeout(saveDraft, AUTOSAVE_DELAY_MS);
                });
                window.addEventListener('beforeunload', saveDraft);
            }

            function saveDraft() {
                const draft = {
                    timestamp: new Date().toISOString(),
                    party_id: document.querySelector('[name="party_id"]')?.value || '',
                    sub_warehouse_id: document.querySelector('[name="sub_warehouse_id"]').value,
                    invoice_date: document.querySelector('[name="invoice_date"]').value,
                    due_date: document.querySelector('[name="due_date"]').value,
                    payment_terms: document.querySelector('[name="payment_terms"]').value,
                    notes: document.querySelector('[name="notes"]').value,
                    is_taxable: document.getElementById('is_taxable').checked,
                    tax_rate: document.getElementById('tax_rate').value,
                    discount_type: document.getElementById('discount_type').value,
                    discount_value: document.getElementById('discount_value').value,
                    paid_amount: document.getElementById('paid_amount').value,
                    account_id: document.getElementById('account_id').value,
                    items: [],
                };

                document.querySelectorAll('.item-row').forEach(row => {
                    const productId = row.querySelector('.product-id').value;
                    if (!productId) return;
                    draft.items.push({
                        product_id: productId,
                        product_name: row.querySelector('.product-display').textContent.trim(),
                        quantity: row.querySelector('.item-quantity').value,
                        unit_price: row.querySelector('.item-price').value,
                        discount_amount: row.querySelector('.item-discount').value,
                    });
                });

                try {
                    localStorage.setItem(AUTOSAVE_KEY, JSON.stringify(draft));
                    showSaveIndicator('saved');
                } catch {
                    showSaveIndicator('error');
                }
            }

            function loadDraft() {
                try {
                    const raw = localStorage.getItem(AUTOSAVE_KEY);
                    if (!raw) return;
                    showRestorePrompt(JSON.parse(raw));
                } catch {
                    clearDraft();
                }
            }

            function showRestorePrompt(draft) {
                document.body.insertAdjacentHTML('beforeend', `
        <div id="restorePrompt" class="fixed top-4 left-1/2 -translate-x-1/2 z-50 bg-white rounded-lg shadow-xl border-2 border-blue-500 p-4 max-w-md w-full mx-4">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <div class="flex-1">
                    <h3 class="text-base font-bold text-gray-900">{{ __('finance::invoice.draft_found') }}</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ __('finance::invoice.draft_saved') }} ${getTimeAgo(new Date(draft.timestamp))}
                        &mdash; ${draft.items.length} {{ __('finance::invoice.items') }}
                    </p>
                    <div class="flex gap-2 mt-3">
                        <button onclick="restoreDraft()"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                            {{ __('finance::invoice.restore_draft') }}
                        </button>
                        <button onclick="discardDraft()"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium">
                            {{ __('finance::invoice.start_fresh') }}
                        </button>
                    </div>
                </div>
                <button onclick="closeRestorePrompt()" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>`);
            }

            async function restoreDraft() {
                try {
                    const draft = JSON.parse(localStorage.getItem(AUTOSAVE_KEY));

                    if (draft.sub_warehouse_id) document.getElementById('subWarehouseSelect').value = draft
                        .sub_warehouse_id;
                    if (draft.invoice_date) document.querySelector('[name="invoice_date"]').value = draft.invoice_date;
                    if (draft.due_date) document.querySelector('[name="due_date"]').value = draft.due_date;
                    if (draft.payment_terms) document.querySelector('[name="payment_terms"]').value = draft.payment_terms;
                    if (draft.notes) document.querySelector('[name="notes"]').value = draft.notes;

                    document.getElementById('is_taxable').checked = draft.is_taxable;
                    document.getElementById('taxRateContainer').style.display = draft.is_taxable ? 'block' : 'none';
                    if (draft.tax_rate) document.getElementById('tax_rate').value = draft.tax_rate;
                    if (draft.discount_type) document.getElementById('discount_type').value = draft.discount_type;
                    if (draft.discount_value) document.getElementById('discount_value').value = draft.discount_value;

                    if (draft.paid_amount && parseFloat(draft.paid_amount) > 0) {
                        document.getElementById('paid_amount').value = draft.paid_amount;
                        document.getElementById('accountContainer').style.display = 'block';
                        document.getElementById('account_id').required = true;
                        if (draft.account_id) document.getElementById('account_id').value = draft.account_id;
                    }

                    // Restore items
                    document.getElementById('itemsContainer').innerHTML = '';
                    itemCounter = 0;

                    if (draft.items?.length) {
                        for (const item of draft.items) {
                            addInvoiceItem();
                            const id = itemCounter;

                            // Fetch product from server if not cached
                            let product = productCache[item.product_id];
                            if (!product) {
                                try {
                                    const res = await fetch(
                                        `{{ route('finance.sales-invoices.search-products') }}?q=${item.product_id}&page=1`
                                    );
                                    const data = await res.json();
                                    product = data.data?.find(p => p.id == item.product_id);
                                    if (product) productCache[product.id] = product;
                                } catch {
                                    /* skip */
                                }
                            }

                            if (product) await selectProductForItem(id, product);

                            const row = document.querySelector(`[data-item="${id}"]`);
                            if (row) {
                                row.querySelector('.item-quantity').value = item.quantity;
                                row.querySelector('.item-price').value = item.unit_price;
                                row.querySelector('.item-discount').value = item.discount_amount;
                                calculateLineTotal(id);
                            }
                        }
                    } else {
                        addInvoiceItem();
                    }

                    calculateTotals();
                    closeRestorePrompt();
                    showToast('{{ __('finance::invoice.draft_restored') }}');
                } catch (e) {
                    console.error('Restore failed', e);
                    clearDraft();
                    closeRestorePrompt();
                }
            }

            function discardDraft() {
                clearDraft();
                closeRestorePrompt();
                showToast('{{ __('finance::invoice.draft_discarded') }}');
            }

            function closeRestorePrompt() {
                document.getElementById('restorePrompt')?.remove();
            }

            function clearDraft() {
                try {
                    localStorage.removeItem(AUTOSAVE_KEY);
                } catch {}
            }

            function showSaveIndicator(status) {
                document.getElementById('autoSaveIndicator')?.remove();
                const el = document.createElement('div');
                el.id = 'autoSaveIndicator';
                const pos = '{{ app()->getLocale() == 'ar' ? 'left-4' : 'right-4' }}';
                el.className = `fixed top-4 ${pos} z-50 px-3 py-2 rounded-lg shadow-lg text-sm font-medium transition-opacity`;
                el.className += status === 'saved' ? ' bg-green-100 text-green-800' : ' bg-red-100 text-red-800';
                el.innerHTML = status === 'saved' ?
                    `<div class="flex items-center gap-2">
               <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                   <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
               </svg>
               {{ __('finance::invoice.draft_saved') }}
           </div>` :
                    `<div class="flex items-center gap-2">
               <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                   <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
               </svg>
               {{ __('finance::invoice.save_failed') }}
           </div>`;
                document.body.appendChild(el);
                setTimeout(() => {
                    el.style.opacity = '0';
                    setTimeout(() => el.remove(), 300);
                }, 2000);
            }

            // ═══════════════════════════════════════════════════════════════
            // QUICK PARTY MODAL
            // ═══════════════════════════════════════════════════════════════
            function openQuickPartyModal() {
                document.getElementById('quickPartyModal').classList.remove('hidden');
                document.getElementById('quick_party_name').focus();
            }

            function closeQuickPartyModal() {
                document.getElementById('quickPartyModal').classList.add('hidden');
                document.getElementById('quickPartyForm').reset();
            }

            document.getElementById('quickPartyForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                const btn = document.getElementById('quickPartySubmitBtn');
                const orig = btn.textContent;
                btn.disabled = true;
                btn.textContent = '{{ __('common.saving') }}...';

                try {
                    const res = await fetch('{{ route('finance.parties.quick-store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            name: document.getElementById('quick_party_name').value,
                            type: document.getElementById('quick_party_type').value,
                            phone: document.getElementById('quick_party_phone').value,
                            email: document.getElementById('quick_party_email').value,
                        }),
                    });
                    const data = await res.json();
                    if (data.success) {
                        const opt = new Option(data.party.text, data.party.id, true, true);
                        $('#partySelect').append(opt).trigger('change');
                        closeQuickPartyModal();
                        showToast(data.message);
                    } else {
                        alert(data.message);
                    }
                } catch {
                    alert('{{ __('common.error_occurred') }}');
                } finally {
                    btn.disabled = false;
                    btn.textContent = orig;
                }
            });

            // ═══════════════════════════════════════════════════════════════
            // HELPERS
            // ═══════════════════════════════════════════════════════════════
            function showToast(message) {
                const pos = '{{ app()->getLocale() == 'ar' ? 'left-4' : 'right-4' }}';
                const toast = document.createElement('div');
                toast.className =
                    `fixed bottom-4 ${pos} bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in-up`;
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.classList.add('animate-fade-out');
                    setTimeout(() => toast.remove(), 300);
                }, 2000);
            }

            function getTimeAgo(date) {
                const s = Math.floor((Date.now() - date) / 1000);
                if (s < 60) return '{{ __('finance::invoice.just_now') }}';
                if (s < 3600) return Math.floor(s / 60) + ' {{ __('finance::invoice.minutes_ago') }}';
                if (s < 86400) return Math.floor(s / 3600) + ' {{ __('finance::invoice.hours_ago') }}';
                return Math.floor(s / 86400) + ' {{ __('finance::invoice.days_ago') }}';
            }

            function escHtml(str) {
                return String(str ?? '').replace(/[&<>"']/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c]));
            }
        </script>
    @endpush
    @push('styles')
        <style>
            @keyframes fade-in-up {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes fade-out {
                from {
                    opacity: 1;
                }

                to {
                    opacity: 0;
                }
            }

            .animate-fade-in-up {
                animation: fade-in-up 0.3s ease-out;
            }

            .animate-fade-out {
                animation: fade-out 0.3s ease-out;
            }
        </style>
    @endpush
</x-dashboard>
