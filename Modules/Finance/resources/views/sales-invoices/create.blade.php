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
                                <div class="md:col-span-2">
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            {{ __('finance::invoice.party') }} <span class="text-red-500">*</span>
                                        </label>
                                        <button type="button" onclick="openQuickPartyModal()"
                                            class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            {{ __('finance::party.quick_add') }}
                                        </button>
                                    </div>
                                    <select name="party_id" id="partySelect" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">{{ __('finance::invoice.select_party') }}</option>
                                        @foreach ($parties as $party)
                                            <option value="{{ $party->id }}"
                                                {{ $selectedParty == $party->id ? 'selected' : '' }}
                                                data-balance="{{ $party->customer_balance }}">
                                                {{ $party->name }} - {{ number_format($party->customer_balance, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('party_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

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
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl shadow-sm border border-blue-200">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-3">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                                    </path>
                                </svg>
                                <h3 class="text-lg font-bold text-gray-900">{{ __('finance::invoice.quick_add_isbn') }}
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
            // Initialize Select2 for Party selection
            $('#partySelect').select2({
                ajax: {
                    url: '{{ route('finance.parties.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            page: params.page || 1
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results,
                            pagination: data.pagination
                        };
                    },
                    cache: true
                },
                placeholder: '{{ __('finance::invoice.select_party') }}',
                allowClear: true,
                minimumInputLength: 0, // Show results immediately
                language: {
                    inputTooShort: function() {
                        return '{{ __('finance::invoice.type_to_search') }}';
                    },
                    searching: function() {
                        return '{{ __('finance::invoice.searching') }}...';
                    },
                    noResults: function() {
                        return '{{ __('finance::invoice.no_results') }}';
                    }
                },
                dir: '{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}', // RTL support
                width: '100%'
            });

            let itemCounter = 0;
            let currentItemId = null;
            const products = @json($productsForJs);
            const allProducts = @json($allProductsWithBooks);
            const productStockCache = {}; // Cache for product stock levels


            const AUTOSAVE_KEY = 'invoice_draft_{{ auth()->id() }}';
            const AUTOSAVE_INTERVAL = 2000; // Save every 2 seconds
            let autosaveTimeout;
            document.addEventListener('DOMContentLoaded', function() {
                // Add first item automatically
                addInvoiceItem();

                // Add item button
                document.getElementById('addItemBtn').addEventListener('click', addInvoiceItem);
                document.getElementById('floatingAddItemBtn').addEventListener('click', addInvoiceItem);

                // Tax checkbox toggle
                document.getElementById('is_taxable').addEventListener('change', function() {
                    document.getElementById('taxRateContainer').style.display = this.checked ? 'block' : 'none';
                    calculateTotals();
                });

                // Recalculate on discount/tax changes
                document.getElementById('discount_type').addEventListener('change', calculateTotals);
                document.getElementById('discount_value').addEventListener('input', calculateTotals);
                document.getElementById('tax_rate').addEventListener('input', calculateTotals);

                // Show account selector when payment amount > 0
                document.getElementById('paid_amount').addEventListener('input', function() {
                    const accountContainer = document.getElementById('accountContainer');
                    const accountSelect = document.getElementById('account_id');

                    if (parseFloat(this.value) > 0) {
                        accountContainer.style.display = 'block';
                        accountSelect.required = true;
                    } else {
                        accountContainer.style.display = 'none';
                        accountSelect.required = false;
                    }
                });

                // Sub-warehouse change - clear cache and recheck all items
                document.getElementById('subWarehouseSelect').addEventListener('change', function() {
                    for (let key in productStockCache) {
                        delete productStockCache[key];
                    }
                    checkAllItemsStock();
                });

                // ISBN Quick Add
                const isbnInput = document.getElementById('isbnInput');

                // Handle Enter key
                isbnInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        addByIsbn();
                    }
                });

                // Handle button click
                document.getElementById('addByIsbnBtn').addEventListener('click', addByIsbn);

                // Product drawer filters
                document.getElementById('productSearch').addEventListener('input', filterProducts);
                document.getElementById('categoryFilter').addEventListener('change', filterProducts);
                document.getElementById('subCategoryFilter').addEventListener('change', filterProducts);
                document.getElementById('authorFilter').addEventListener('change', filterProducts);

                // Initial product list load
                renderProductList(allProducts);

                loadDraft();

                // Setup auto-save listeners
                setupAutoSave();
                // Clear draft on successful submit
                document.getElementById('invoiceForm').addEventListener('submit', function(e) {
                    // Only clear if validation passes
                    if (this.checkValidity()) {
                        clearDraft();
                    }
                });

            });

            // Auto-save functions
            function setupAutoSave() {
                // Save on any input change
                document.getElementById('invoiceForm').addEventListener('input', function() {
                    clearTimeout(autosaveTimeout);
                    autosaveTimeout = setTimeout(saveDraft, AUTOSAVE_INTERVAL);
                });

                // Save on select change
                document.getElementById('invoiceForm').addEventListener('change', function() {
                    clearTimeout(autosaveTimeout);
                    autosaveTimeout = setTimeout(saveDraft, AUTOSAVE_INTERVAL);
                });

                // Save before page unload
                window.addEventListener('beforeunload', function() {
                    saveDraft();
                });
            }

            function saveDraft() {
                const formData = {
                    timestamp: new Date().toISOString(),
                    party_id: document.querySelector('[name="party_id"]').value,
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
                    items: []
                };

                // Save all items
                document.querySelectorAll('.item-row').forEach(row => {
                    const itemId = row.getAttribute('data-item');
                    const item = {
                        itemId: itemId,
                        product_id: row.querySelector('.product-id').value,
                        product_name: row.querySelector('.product-display').textContent.trim(),
                        quantity: row.querySelector('.item-quantity').value,
                        unit_price: row.querySelector('.item-price').value,
                        discount_amount: row.querySelector('.item-discount').value
                    };

                    // Only save items with product selected
                    if (item.product_id) {
                        formData.items.push(item);
                    }
                });

                try {
                    localStorage.setItem(AUTOSAVE_KEY, JSON.stringify(formData));
                    showAutoSaveIndicator('saved');
                } catch (e) {
                    console.error('Failed to save draft:', e);
                    showAutoSaveIndicator('error');
                }
            }

            function loadDraft() {
                try {
                    const savedData = localStorage.getItem(AUTOSAVE_KEY);

                    if (!savedData) return;

                    const draft = JSON.parse(savedData);

                    // Show restore prompt
                    showRestorePrompt(draft);

                } catch (e) {
                    console.error('Failed to load draft:', e);
                    clearDraft();
                }
            }

            function showRestorePrompt(draft) {
                const draftTime = new Date(draft.timestamp);
                const timeAgo = getTimeAgo(draftTime);

                const promptHtml = `
            <div id="restorePrompt" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 bg-white rounded-lg shadow-xl border-2 border-blue-500 p-4 max-w-md">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900">{{ __('finance::invoice.draft_found') }}</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ __('finance::invoice.draft_saved') }} ${timeAgo}
                            <br>
                            ${draft.items.length} {{ __('finance::invoice.items') }}
                        </p>
                        <div class="flex gap-2 mt-3">
                            <button type="button" onclick="restoreDraft()" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                {{ __('finance::invoice.restore_draft') }}
                            </button>
                            <button type="button" onclick="discardDraft()" 
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium">
                                {{ __('finance::invoice.start_fresh') }}
                            </button>
                        </div>
                    </div>
                    <button type="button" onclick="closeRestorePrompt()" 
                        class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `;

                document.body.insertAdjacentHTML('beforeend', promptHtml);
            }

            function restoreDraft() {
                try {
                    const savedData = localStorage.getItem(AUTOSAVE_KEY);
                    const draft = JSON.parse(savedData);

                    // Restore basic fields
                    if (draft.party_id) {
                        $('#partySelect').val(draft.party_id).trigger('change');
                    }
                    if (draft.sub_warehouse_id) {
                        document.getElementById('subWarehouseSelect').value = draft.sub_warehouse_id;
                    }
                    if (draft.invoice_date) {
                        document.querySelector('[name="invoice_date"]').value = draft.invoice_date;
                    }
                    if (draft.due_date) {
                        document.querySelector('[name="due_date"]').value = draft.due_date;
                    }
                    if (draft.payment_terms) {
                        document.querySelector('[name="payment_terms"]').value = draft.payment_terms;
                    }
                    if (draft.notes) {
                        document.querySelector('[name="notes"]').value = draft.notes;
                    }

                    // Restore tax and discount
                    document.getElementById('is_taxable').checked = draft.is_taxable;
                    document.getElementById('taxRateContainer').style.display = draft.is_taxable ? 'block' : 'none';
                    if (draft.tax_rate) {
                        document.getElementById('tax_rate').value = draft.tax_rate;
                    }
                    if (draft.discount_type) {
                        document.getElementById('discount_type').value = draft.discount_type;
                    }
                    if (draft.discount_value) {
                        document.getElementById('discount_value').value = draft.discount_value;
                    }

                    // Restore payment
                    if (draft.paid_amount) {
                        document.getElementById('paid_amount').value = draft.paid_amount;
                        if (parseFloat(draft.paid_amount) > 0) {
                            document.getElementById('accountContainer').style.display = 'block';
                            document.getElementById('account_id').required = true;
                            if (draft.account_id) {
                                document.getElementById('account_id').value = draft.account_id;
                            }
                        }
                    }

                    // Clear existing items
                    document.getElementById('itemsContainer').innerHTML = '';
                    itemCounter = 0;

                    // Restore items
                    if (draft.items && draft.items.length > 0) {
                        draft.items.forEach(item => {
                            addInvoiceItem();
                            const currentItemId = itemCounter;

                            // Find product
                            const product = allProducts.find(p => p.id == item.product_id);
                            if (product) {
                                selectProductForItem(currentItemId, product);
                            }

                            // Set values
                            const row = document.querySelector(`[data-item="${currentItemId}"]`);
                            if (row) {
                                row.querySelector('.item-quantity').value = item.quantity;
                                row.querySelector('.item-price').value = item.unit_price;
                                row.querySelector('.item-discount').value = item.discount_amount;
                                calculateLineTotal(currentItemId);
                            }
                        });
                    } else {
                        addInvoiceItem(); // Add at least one item
                    }

                    calculateTotals();
                    closeRestorePrompt();
                    showToast('{{ __('finance::invoice.draft_restored') }}');

                } catch (e) {
                    console.error('Failed to restore draft:', e);
                    alert('{{ __('finance::invoice.failed_to_restore') }}');
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
                const prompt = document.getElementById('restorePrompt');
                if (prompt) {
                    prompt.remove();
                }
            }

            function clearDraft() {
                try {
                    localStorage.removeItem(AUTOSAVE_KEY);
                } catch (e) {
                    console.error('Failed to clear draft:', e);
                }
            }

            function showAutoSaveIndicator(status) {
                // Remove existing indicator
                const existing = document.getElementById('autoSaveIndicator');
                if (existing) existing.remove();

                const indicator = document.createElement('div');
                indicator.id = 'autoSaveIndicator';
                indicator.className =
                    'fixed top-4 {{ app()->getLocale() == 'ar' ? 'left-4' : 'right-4' }} z-50 px-3 py-2 rounded-lg shadow-lg text-sm font-medium transition-all';

                if (status === 'saved') {
                    indicator.className += ' bg-green-100 text-green-800';
                    indicator.innerHTML = `
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ __('finance::invoice.draft_saved') }}
                </div>
            `;
                } else if (status === 'error') {
                    indicator.className += ' bg-red-100 text-red-800';
                    indicator.innerHTML = `
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    {{ __('finance::invoice.save_failed') }}
                </div>
            `;
                }

                document.body.appendChild(indicator);

                // Auto-hide after 2 seconds
                setTimeout(() => {
                    indicator.style.opacity = '0';
                    setTimeout(() => indicator.remove(), 300);
                }, 2000);
            }

            function getTimeAgo(date) {
                const seconds = Math.floor((new Date() - date) / 1000);

                if (seconds < 60) return '{{ __('finance::invoice.just_now') }}';
                if (seconds < 3600) return Math.floor(seconds / 60) + ' {{ __('finance::invoice.minutes_ago') }}';
                if (seconds < 86400) return Math.floor(seconds / 3600) + ' {{ __('finance::invoice.hours_ago') }}';
                return Math.floor(seconds / 86400) + ' {{ __('finance::invoice.days_ago') }}';
            }

            async function getProductStock(productId, subWarehouseId) {
                const cacheKey = `${subWarehouseId}_${productId}`;

                // Check cache first
                if (productStockCache[cacheKey] !== undefined) {
                    return productStockCache[cacheKey];
                }

                // Fetch from server
                try {
                    const response = await fetch(
                        `{{ route('finance.sales-invoices.product-stock') }}?product_id=${productId}&sub_warehouse_id=${subWarehouseId}`
                    );
                    const data = await response.json();
                    productStockCache[cacheKey] = data.quantity;
                    return data.quantity;
                } catch (error) {
                    console.error('Error fetching stock:', error);
                    return 0;
                }
            }

            async function checkProductStock(productId, requestedQuantity, currentItemId) {
                const subWarehouseId = document.getElementById('subWarehouseSelect').value;

                if (!subWarehouseId) {
                    showStockWarning('{{ __('finance::invoice.please_select_sub_warehouse') }}');
                    return false;
                }

                if (!productId) {
                    return true;
                }

                // Get available stock
                const availableStock = await getProductStock(productId, subWarehouseId);

                // Calculate total quantity for this product across all items
                let totalQuantity = 0;
                document.querySelectorAll('.item-row').forEach(row => {
                    const rowProductId = row.querySelector('.product-id').value;
                    if (rowProductId == productId) {
                        const rowQuantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                        totalQuantity += rowQuantity;
                    }
                });

                const product = allProducts.find(p => p.id == productId);
                const productName = product ? product.name : 'Unknown Product';

                if (totalQuantity > availableStock) {
                    showStockWarning(
                        `{{ __('finance::invoice.insufficient_stock_for') }} "${productName}". ` +
                        `{{ __('finance::invoice.available') }}: ${availableStock}, ` +
                        `{{ __('finance::invoice.requested') }}: ${totalQuantity}`
                    );

                    // Highlight the problematic item
                    highlightItemWithIssue(currentItemId);
                    return false;
                } else {
                    hideStockWarning();
                    removeItemHighlight(currentItemId);
                    return true;
                }
            }

            function highlightItemWithIssue(itemId) {
                const row = document.querySelector(`[data-item="${itemId}"]`);
                if (row) {
                    row.classList.add('border-red-500', 'bg-red-50');
                    row.classList.remove('border-gray-200');
                }
            }

            function removeItemHighlight(itemId) {
                const row = document.querySelector(`[data-item="${itemId}"]`);
                if (row) {
                    row.classList.remove('border-red-500', 'bg-red-50');
                    row.classList.add('border-gray-200');
                }
            }

            function showStockWarning(message) {
                const alert = document.getElementById('stockWarningAlert');
                const messageEl = document.getElementById('stockWarningMessage');
                messageEl.textContent = message;
                alert.classList.remove('hidden');

                // Scroll to alert
                alert.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                // disable create invoice button
                const submitBtn = document.getElementById('createInvoiceBtn');
                console.log(submitBtn, 'Disabling submit button');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }

            function hideStockWarning() {
                document.getElementById('stockWarningAlert').classList.add('hidden');

                // Re-enable submit button
                const submitBtn = document.getElementById('createInvoiceBtn');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }

                // Remove all highlights
                document.querySelectorAll('.item-row').forEach(row => {
                    row.classList.remove('border-red-500', 'bg-red-50');
                    row.classList.add('border-gray-200');
                });
            }

            async function checkAllItemsStock() {
                let allValid = true;
                hideStockWarning();

                for (const row of document.querySelectorAll('.item-row')) {
                    const productId = row.querySelector('.product-id').value;
                    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                    const itemId = row.getAttribute('data-item');

                    if (productId && quantity > 0) {
                        const isValid = await checkProductStock(productId, quantity, itemId);
                        if (!isValid) {
                            allValid = false;
                            break; // Stop at first invalid item
                        }
                    }
                }

                return allValid;
            }

            async function addByIsbn() {
                const isbn = document.getElementById('isbnInput').value.trim();

                if (!isbn) {
                    alert('{{ __('finance::invoice.please_enter_isbn') }}');
                    return;
                }

                const subWarehouseId = document.getElementById('subWarehouseSelect').value;
                if (!subWarehouseId) {
                    alert('{{ __('finance::invoice.please_select_sub_warehouse_first') }}');
                    document.getElementById('subWarehouseSelect').focus();
                    return;
                }

                // Find product by ISBN
                const product = allProducts.find(p => p.isbn === isbn);

                if (!product) {
                    alert('{{ __('finance::invoice.product_not_found') }}');
                    document.getElementById('isbnInput').value = '';
                    document.getElementById('isbnInput').focus();
                    return;
                }

                // Check if product already exists, if so increase quantity instead
                const existingRow = findExistingProductRow(product.id);

                if (existingRow) {
                    // Increase quantity of existing row
                    const quantityInput = existingRow.querySelector('.item-quantity');
                    const currentQuantity = parseFloat(quantityInput.value) || 0;
                    quantityInput.value = currentQuantity + 1;

                    const existingItemId = existingRow.getAttribute('data-item');
                    await handleQuantityChange(existingItemId);

                    // Highlight the updated row
                    highlightUpdatedRow(existingItemId);

                    showToast('{{ __('finance::invoice.quantity_increased') }}: ' + product.name);
                } else {
                    // Add new item with this product
                    addInvoiceItem();
                    const newItemId = itemCounter;
                    await selectProductForItem(newItemId, product);

                    showToast('{{ __('finance::invoice.product_added') }}: ' + product.name);
                }

                // Clear input and focus for next scan
                document.getElementById('isbnInput').value = '';
                document.getElementById('isbnInput').focus();
            }

            function showToast(message) {
                const toast = document.createElement('div');
                toast.className =
                    'fixed bottom-4 {{ app()->getLocale() == 'ar' ? 'left-4' : 'right-4' }} bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in-up';
                toast.textContent = message;
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.classList.add('animate-fade-out');
                    setTimeout(() => toast.remove(), 300);
                }, 2000);
            }

            function openProductDrawer(itemId) {
                const subWarehouseId = document.getElementById('subWarehouseSelect').value;
                if (!subWarehouseId) {
                    alert('{{ __('finance::invoice.please_select_sub_warehouse_first') }}');
                    document.getElementById('subWarehouseSelect').focus();
                    return;
                }

                currentItemId = itemId;
                document.getElementById('productDrawer').classList.remove('hidden');
                document.getElementById('productSearch').focus();
            }

            function closeProductDrawer() {
                document.getElementById('productDrawer').classList.add('hidden');
                currentItemId = null;
                // Reset filters
                document.getElementById('productSearch').value = '';
                document.getElementById('categoryFilter').value = '';
                document.getElementById('subCategoryFilter').value = '';
                document.getElementById('authorFilter').value = '';
                renderProductList(allProducts);
            }

            function filterProducts() {
                const search = document.getElementById('productSearch').value.toLowerCase();
                const categoryId = document.getElementById('categoryFilter').value;
                const subCategoryId = document.getElementById('subCategoryFilter').value;
                const authorId = document.getElementById('authorFilter').value;

                let filtered = allProducts.filter(product => {
                    let matches = true;

                    // Search filter
                    if (search) {
                        matches = matches && (
                            product.name.toLowerCase().includes(search) ||
                            (product.isbn && product.isbn.toLowerCase().includes(search)) ||
                            (product.sku && product.sku.toLowerCase().includes(search))
                        );
                    }

                    // Category filter
                    if (categoryId) {
                        matches = matches && product.category_id == categoryId;
                    }

                    // Sub category filter
                    if (subCategoryId) {
                        matches = matches && product.sub_category_id == subCategoryId;
                    }

                    // Author filter
                    if (authorId) {
                        matches = matches && product.author_id == authorId;
                    }

                    return matches;
                });

                renderProductList(filtered);
            }

            function renderProductList(productsList) {
                const container = document.getElementById('productList');

                if (productsList.length === 0) {
                    container.innerHTML = `
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 12h.01M12 12h.01M12 12h.01M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-gray-500">{{ __('finance::invoice.no_products_found') }}</p>
                </div>
            `;
                    return;
                }

                container.innerHTML = productsList.map(product => `
            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:bg-blue-50 cursor-pointer transition-all"
                onclick="selectProductFromDrawer(${product.id})">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900">${product.name}</h4>
                        <div class="mt-1 space-y-1">
                            ${product.isbn ? `<p class="text-xs text-gray-500">ISBN: ${product.isbn}</p>` : ''}
                            ${product.sku ? `<p class="text-xs text-gray-500">SKU: ${product.sku}</p>` : ''}
                            ${product.author_name ? `<p class="text-xs text-gray-600">{{ __('finance::invoice.author') }}: ${product.author_name}</p>` : ''}
                            ${product.category_name ? `<p class="text-xs text-gray-600">{{ __('finance::invoice.category') }}: ${product.category_name}</p>` : ''}
                        </div>
                    </div>
                    <div class="text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} {{ app()->getLocale() == 'ar' ? 'ml-4' : 'mr-4' }}">
                        <p class="text-lg font-bold text-blue-600">${parseFloat(product.price).toFixed(2)}</p>
                        ${product.stock_quantity !== null ? `<p class="text-xs text-gray-500">{{ __('finance::invoice.stock') }}: ${product.stock_quantity}</p>` : ''}
                    </div>
                </div>
            </div>
        `).join('');
            }

            function selectProductFromDrawer(productId) {
                if (!currentItemId) return;

                const product = allProducts.find(p => p.id == productId);
                if (!product) return;

                selectProductForItem(currentItemId, product);
                closeProductDrawer();
            }

            async function selectProductForItem(itemId, product) {
                // Check if product already exists in another row
                const existingRow = findExistingProductRow(product.id, itemId);

                if (existingRow) {
                    // Product exists, increase its quantity
                    const quantityInput = existingRow.querySelector('.item-quantity');
                    const currentQuantity = parseFloat(quantityInput.value) || 0;
                    quantityInput.value = currentQuantity + 1;

                    // Get the existing row's item ID
                    const existingItemId = existingRow.getAttribute('data-item');

                    // Trigger quantity change handler
                    await handleQuantityChange(existingItemId);

                    // Remove the current empty row if it's empty
                    const currentRow = document.querySelector(`[data-item="${itemId}"]`);
                    const currentProductId = currentRow.querySelector('.product-id').value;
                    if (!currentProductId) {
                        // Only remove if there are other items
                        const allRows = document.querySelectorAll('.item-row');
                        if (allRows.length > 1) {
                            currentRow.remove();
                        }
                    }

                    // Highlight the updated row briefly
                    highlightUpdatedRow(existingItemId);

                    return;
                }

                const row = document.querySelector(`[data-item="${itemId}"]`);
                if (!row) return;

                // Set hidden product ID
                row.querySelector('.product-id').value = product.id;

                // Update display button
                const displayBtn = row.querySelector('.product-display');
                displayBtn.innerHTML = `
            <div class="flex items-center justify-between">
                <div>
                    <div class="font-medium text-gray-900">${product.name}</div>
                    ${product.isbn ? `<div class="text-xs text-gray-500">ISBN: ${product.isbn}</div>` : ''}
                </div>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        `;

                // Set price
                const priceInput = row.querySelector('.item-price');
                priceInput.value = product.price;

                calculateLineTotal(itemId);

                // Check stock for current quantity
                const quantity = parseFloat(row.querySelector('.item-quantity').value) || 1;
                await checkProductStock(product.id, quantity, itemId);
            }

            function findExistingProductRow(productId, excludeItemId = null) {
                const rows = document.querySelectorAll('.item-row');
                for (const row of rows) {
                    const rowItemId = row.getAttribute('data-item');
                    const rowProductId = row.querySelector('.product-id').value;

                    // Skip the current row we're trying to update
                    if (excludeItemId && rowItemId == excludeItemId) {
                        continue;
                    }

                    if (rowProductId == productId) {
                        return row;
                    }
                }
                return null;
            }

            function highlightUpdatedRow(itemId) {
                const row = document.querySelector(`[data-item="${itemId}"]`);
                if (!row) return;

                // Add highlight
                row.classList.add('bg-green-50', 'border-green-500');
                row.classList.remove('border-gray-200');

                // Scroll to the row
                row.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                // Remove highlight after 2 seconds
                setTimeout(() => {
                    row.classList.remove('bg-green-50', 'border-green-500');
                    row.classList.add('border-gray-200');
                }, 2000);
            }

            function addInvoiceItem() {
                itemCounter++;
                const container = document.getElementById('itemsContainer');

                const itemHtml = `
            <div class="item-row border border-gray-200 rounded-lg p-4 transition-all" data-item="${itemCounter}">
                <div class="grid grid-cols-12 gap-4">
                    <!-- Product -->
                    <div class="col-span-12 md:col-span-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('finance::invoice.product') }}</label>
                        <input type="hidden" name="items[${itemCounter}][product_id]" class="product-id" required>
                        <button type="button" onclick="openProductDrawer(${itemCounter})"
                            class="product-display w-full px-3 py-2 text-sm border border-gray-300 rounded-lg text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <span class="text-gray-400">{{ __('finance::invoice.select_product') }}</span>
                        </button>
                    </div>

                    <!-- Quantity -->
                    <div class="col-span-6 md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('finance::invoice.quantity') }}</label>
                        <input type="number" name="items[${itemCounter}][quantity]" min="1" value="1" required
                            class="item-quantity w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            oninput="handleQuantityChange(${itemCounter})" onblur="handleQuantityChange(${itemCounter})">
                    </div>

                    <!-- Unit Price -->
                    <div class="col-span-6 md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('finance::invoice.unit_price') }}</label>
                        <input type="number" name="items[${itemCounter}][unit_price]" step="0.01" min="0" value="0" required
                            class="item-price w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            oninput="calculateLineTotal(${itemCounter})">
                    </div>

                    <!-- Discount -->
                    <div class="col-span-6 md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('finance::invoice.item_discount') }}</label>
                        <input type="number" name="items[${itemCounter}][discount_amount]" step="0.01" min="0" value="0"
                            class="item-discount w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            oninput="calculateLineTotal(${itemCounter})">
                    </div>

                    <!-- Line Total -->
                    <div class="col-span-5 md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('finance::invoice.line_total') }}</label>
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

                </div>
            </div>
        `;

                container.insertAdjacentHTML('beforeend', itemHtml);
            }

            // Debounce function to avoid too many API calls
            let quantityCheckTimeout;
            async function handleQuantityChange(itemId) {
                // Clear previous timeout
                if (quantityCheckTimeout) {
                    clearTimeout(quantityCheckTimeout);
                }

                // Calculate line total immediately
                calculateLineTotal(itemId);

                const row = document.querySelector(`[data-item="${itemId}"]`);
                const productId = row.querySelector('.product-id').value;
                const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;

                // Only check stock if product is selected and quantity > 0
                if (productId && quantity > 0) {
                    // Debounce the stock check (wait 500ms after user stops typing)
                    quantityCheckTimeout = setTimeout(async () => {
                        await checkProductStock(productId, quantity, itemId);
                    }, 500);
                } else if (quantity === 0) {
                    hideStockWarning();
                    removeItemHighlight(itemId);
                }
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
                    alert('{{ __('finance::invoice.at_least_one_item') }}');
                    return;
                }

                document.querySelector(`[data-item="${itemId}"]`).remove();
                calculateTotals();

                // Recheck remaining items stock
                checkAllItemsStock();
            }

            function calculateTotals() {
                // Calculate subtotal
                let subtotal = 0;
                document.querySelectorAll('.item-total').forEach(input => {
                    subtotal += parseFloat(input.value) || 0;
                });

                // Calculate discount
                const discountType = document.getElementById('discount_type').value;
                const discountValue = parseFloat(document.getElementById('discount_value').value) || 0;
                let discountAmount = 0;

                if (discountType === 'percentage') {
                    discountAmount = (subtotal * discountValue) / 100;
                } else {
                    discountAmount = discountValue;
                }

                const amountAfterDiscount = subtotal - discountAmount;

                // Calculate tax
                let taxAmount = 0;
                const isTaxable = document.getElementById('is_taxable').checked;
                if (isTaxable) {
                    const taxRate = parseFloat(document.getElementById('tax_rate').value) || 0;
                    taxAmount = (amountAfterDiscount * taxRate) / 100;
                }

                // Calculate total
                const total = amountAfterDiscount + taxAmount;

                // Update displays
                document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2);
                document.getElementById('discountDisplay').textContent = discountAmount.toFixed(2);
                document.getElementById('taxDisplay').textContent = taxAmount.toFixed(2);
                document.getElementById('totalDisplay').textContent = total.toFixed(2);
            }

            // Validate before submit
            document.getElementById('invoiceForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                // Show loading state
                const submitBtn = document.getElementById('createInvoiceBtn');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
            <svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        `;

                const isStockValid = await checkAllItemsStock();

                if (isStockValid) {
                    this.submit();
                } else {
                    alert('{{ __('finance::invoice.please_check_stock_warnings') }}');

                    // Restore button
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });

            // Quick Party Modal Functions
            function openQuickPartyModal() {
                document.getElementById('quickPartyModal').classList.remove('hidden');
                document.getElementById('quick_party_name').focus();
            }

            function closeQuickPartyModal() {
                document.getElementById('quickPartyModal').classList.add('hidden');
                document.getElementById('quickPartyForm').reset();
            }

            // Quick Party Form Submit
            document.getElementById('quickPartyForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                const submitBtn = document.getElementById('quickPartySubmitBtn');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = '{{ __('common.saving') }}...';

                const formData = {
                    name: document.getElementById('quick_party_name').value,
                    type: document.getElementById('quick_party_type').value,
                    phone: document.getElementById('quick_party_phone').value,
                    email: document.getElementById('quick_party_email').value,
                    _token: '{{ csrf_token() }}'
                };

                try {
                    const response = await fetch('{{ route('finance.parties.quick-store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Add new option to select2
                        const newOption = new Option(data.party.text, data.party.id, true, true);
                        $('#partySelect').append(newOption).trigger('change');

                        closeQuickPartyModal();
                        showToast(data.message);
                    } else {
                        alert(data.message);
                    }
                } catch (error) {
                    alert('{{ __('common.error_occurred') }}');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
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
