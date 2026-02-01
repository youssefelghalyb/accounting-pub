<x-dashboard :pageTitle="__('finance::purchase.create_invoice')">
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
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('finance::purchase.create_invoice') }}</span>
                </li>
            </ol>
        </nav>

        <form action="{{ route('finance.purchase-invoices.store') }}" method="POST" id="invoiceForm">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Invoice Details -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900">{{ __('finance::purchase.invoice_info') }}</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Vendor with Quick Add -->
                                <div class="md:col-span-2">
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            {{ __('finance::purchase.vendor') }} <span class="text-red-500">*</span>
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
                                        <option value="">{{ __('finance::purchase.select_vendor') }}</option>
                                        @foreach ($parties as $party)
                                            <option value="{{ $party->id }}"
                                                {{ $selectedParty == $party->id ? 'selected' : '' }}>
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
                                        {{ __('finance::purchase.due_date') }}
                                    </label>
                                    <input type="date" name="due_date" value="{{ old('due_date') }}"
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
                                    <input type="text" name="reference_number" value="{{ old('reference_number') }}"
                                        placeholder="{{ __('finance::purchase.vendor_invoice_number') }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
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
                                <h3 class="text-lg font-bold text-gray-900">
                                    {{ __('finance::purchase.quick_add_isbn') }}</h3>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">{{ __('finance::purchase.scan_or_enter_isbn') }}</p>
                            <div class="flex gap-3">
                                <input type="text" id="isbnInput"
                                    placeholder="{{ __('finance::purchase.enter_isbn_placeholder') }}"
                                    class="flex-1 px-4 py-3 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                                    autocomplete="off">
                                <button type="button" id="addByIsbnBtn"
                                    class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    {{ __('finance::purchase.add') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Manual Amount for Non-Product Invoices -->
                    <div
                        class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl shadow-sm border border-purple-200">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-3">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                                <h3 class="text-lg font-bold text-gray-900">
                                    {{ __('finance::purchase.service_expense_invoice') }}</h3>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">
                                {{ __('finance::purchase.manual_amount_description') }}</p>
                            <div class="flex items-center gap-4">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('finance::purchase.manual_amount') }}
                                    </label>
                                    <input type="number" name="manual_amount" id="manual_amount" step="0.01"
                                        min="0" value="{{ old('manual_amount', 0) }}"
                                        class="w-full px-4 py-3 border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white">
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ __('finance::purchase.leave_zero_for_items') }}</p>
                                </div>
                                <div class="pt-7">
                                    <button type="button" id="clearManualAmount"
                                        class="px-4 py-3 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                        {{ __('common.clear') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Invoice Items -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900">{{ __('finance::purchase.items') }}
                                    </h2>
                                    <p class="text-sm text-gray-600 mt-1">{{ __('finance::purchase.items_optional') }}
                                    </p>
                                </div>
                                <button type="button" id="addItemBtn"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    {{ __('finance::purchase.add_item') }}
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            <div id="itemsContainer" class="space-y-4">
                                <!-- Items will be added here dynamically -->
                                <div class="text-center py-8 text-gray-500" id="noItemsMessage">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                        </path>
                                    </svg>
                                    {{ __('finance::purchase.no_items_yet') }}
                                </div>
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Tax & Discount -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-bold text-gray-900">{{ __('finance::purchase.amount_breakdown') }}
                            </h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <!-- Taxable Checkbox -->
                            <div class="flex items-center">
                                <input type="checkbox" name="is_taxable" id="is_taxable" value="1"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="is_taxable" class="ml-2 text-sm font-medium text-gray-700">
                                    {{ __('finance::purchase.is_taxable') }}
                                </label>
                            </div>

                            <!-- Tax Rate (shown only when taxable) -->
                            <div id="taxRateContainer" style="display: none;">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('finance::purchase.tax_rate') }}
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

                            <!-- Discount Amount -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('finance::purchase.discount_amount') }}
                                </label>
                                <input type="number" name="discount_amount" id="discount_amount" step="0.01"
                                    min="0" value="{{ old('discount_amount', 0) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <!-- Totals Display -->
                            <div class="pt-4 border-t border-gray-200 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('finance::purchase.subtotal') }}:</span>
                                    <span id="subtotalDisplay" class="font-medium text-gray-900">0.00</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('finance::purchase.discount_amount') }}:</span>
                                    <span id="discountDisplay" class="font-medium text-gray-900">0.00</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('finance::purchase.tax_amount') }}:</span>
                                    <span id="taxDisplay" class="font-medium text-gray-900">0.00</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                                    <span class="text-gray-900">{{ __('finance::purchase.total_amount') }}:</span>
                                    <span id="totalDisplay" class="text-blue-600">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information (Optional) -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-bold text-gray-900">{{ __('finance::purchase.payment_info') }}
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">{{ __('finance::purchase.initial_payment') }}</p>
                        </div>
                        <div class="p-6 space-y-4">
                            <!-- Payment Amount -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('finance::purchase.paid_amount') }}
                                </label>
                                <input type="number" name="paid_amount" id="paid_amount" step="0.01"
                                    min="0" value="{{ old('paid_amount', 0) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            </div>

                            <!-- Account -->
                            <div id="accountContainer" style="display: none;">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('finance::purchase.payment_account') }}
                                </label>
                                <select name="account_id" id="account_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="">{{ __('finance::purchase.select_account') }}</option>
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
                        <button type="submit"
                            class="w-full py-3 px-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            {{ __('finance::purchase.create_invoice') }}
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
                <h3 class="text-xl font-bold text-gray-900">{{ __('finance::purchase.select_product') }}</h3>
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
                            class="block text-sm font-medium text-gray-700 mb-2">{{ __('finance::purchase.search') }}</label>
                        <input type="text" id="productSearch"
                            placeholder="{{ __('finance::purchase.search_by_name_isbn') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <!-- Category -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2">{{ __('finance::purchase.category') }}</label>
                        <select id="categoryFilter"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">{{ __('finance::purchase.all_categories') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sub Category -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2">{{ __('finance::purchase.sub_category') }}</label>
                        <select id="subCategoryFilter"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">{{ __('finance::purchase.all_sub_categories') }}</option>
                            @foreach ($subCategories as $subCategory)
                                <option value="{{ $subCategory->id }}">{{ $subCategory->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Author -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2">{{ __('finance::purchase.author') }}</label>
                        <select id="authorFilter"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">{{ __('finance::purchase.all_authors') }}</option>
                            @foreach ($authors as $author)
                                <option value="{{ $author->id }}">{{ $author->name }}</option>
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
                    <h3 class="text-xl font-bold text-gray-900">{{ __('finance::party.quick_add_vendor') }}</h3>
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
                        {{ __('finance::party.add_vendor') }}
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
                    url: '{{ route('finance.purchase-parties.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
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
                placeholder: '{{ __('finance::purchase.select_vendor') }}',
                allowClear: true,
                minimumInputLength: 0,
                language: {
                    inputTooShort: function() {
                        return '{{ __('finance::purchase.type_to_search') }}';
                    },
                    searching: function() {
                        return '{{ __('finance::purchase.searching') }}...';
                    },
                    noResults: function() {
                        return '{{ __('finance::purchase.no_results') }}';
                    }
                },
                dir: '{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}',
                width: '100%'
            });

            let itemCounter = 0;
            let currentItemId = null;
            const products = @json($productsForJs);
            const allProducts = @json($allProductsWithBooks);

            document.addEventListener('DOMContentLoaded', function() {
                // Add item button
                document.getElementById('addItemBtn').addEventListener('click', addInvoiceItem);

                // Recalculate on discount/tax changes
                // Tax checkbox toggle
                document.getElementById('is_taxable').addEventListener('change', function() {
                    document.getElementById('taxRateContainer').style.display = this.checked ? 'block' : 'none';
                    calculateTotals();
                });

                // Recalculate on discount/tax changes
                document.getElementById('discount_amount').addEventListener('input', calculateTotals);


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

                // ISBN Quick Add
                const isbnInput = document.getElementById('isbnInput');
                isbnInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        addByIsbn();
                    }
                });
                document.getElementById('addByIsbnBtn').addEventListener('click', addByIsbn);

                // Product drawer filters
                document.getElementById('productSearch').addEventListener('input', filterProducts);
                document.getElementById('categoryFilter').addEventListener('change', filterProducts);
                document.getElementById('subCategoryFilter').addEventListener('change', filterProducts);
                document.getElementById('authorFilter').addEventListener('change', filterProducts);
                // Manual amount handling
                const manualAmountInput = document.getElementById('manual_amount');
                const itemsSection = document.querySelector(
                    '.bg-white.rounded-xl.shadow-sm.border.border-gray-200:has(#itemsContainer)');
                const isbnSection = document.querySelector('.bg-gradient-to-r.from-blue-50');

                manualAmountInput.addEventListener('input', function() {
                    const manualAmount = parseFloat(this.value) || 0;

                    if (manualAmount > 0) {
                        // Disable items section
                        itemsSection.classList.add('opacity-50', 'pointer-events-none');
                        isbnSection.classList.add('opacity-50', 'pointer-events-none');

                        // Remove all items
                        document.querySelectorAll('.item-row').forEach(row => row.remove());
                        updateNoItemsMessage();

                        // Update totals based on manual amount
                        calculateManualTotals(manualAmount);

                        // Show info message
                        showManualAmountInfo();
                    } else {
                        // Enable items section
                        itemsSection.classList.remove('opacity-50', 'pointer-events-none');
                        isbnSection.classList.remove('opacity-50', 'pointer-events-none');

                        // Hide info message
                        hideManualAmountInfo();

                        // Recalculate from items
                        calculateTotals();
                    }
                });

                // Clear manual amount button
                document.getElementById('clearManualAmount').addEventListener('click', function() {
                    manualAmountInput.value = 0;
                    manualAmountInput.dispatchEvent(new Event('input'));
                });

                // Initial product list load
                renderProductList(allProducts);
            });

            function calculateManualTotals(manualAmount) {
                const discountAmount = parseFloat(document.getElementById('discount_amount').value) || 0;
                const amountAfterDiscount = manualAmount - discountAmount;

                // Calculate tax only if taxable
                let taxAmount = 0;
                const isTaxable = document.getElementById('is_taxable').checked;
                if (isTaxable) {
                    const taxRate = parseFloat(document.getElementById('tax_rate').value) || 0;
                    taxAmount = (amountAfterDiscount * taxRate) / 100;
                }

                const total = amountAfterDiscount + taxAmount;

                document.getElementById('subtotalDisplay').textContent = manualAmount.toFixed(2);
                document.getElementById('discountDisplay').textContent = discountAmount.toFixed(2);
                document.getElementById('taxDisplay').textContent = taxAmount.toFixed(2);
                document.getElementById('totalDisplay').textContent = total.toFixed(2);
            }

            function calculateTotals() {
                // Check if manual amount is set
                const manualAmount = parseFloat(document.getElementById('manual_amount').value) || 0;

                if (manualAmount > 0) {
                    calculateManualTotals(manualAmount);
                    return;
                }

                // Original items-based calculation
                let subtotal = 0;
                document.querySelectorAll('.item-total').forEach(input => {
                    subtotal += parseFloat(input.value) || 0;
                });

                const discountAmount = parseFloat(document.getElementById('discount_amount').value) || 0;
                const amountAfterDiscount = subtotal - discountAmount;

                // Calculate tax only if taxable
                let taxAmount = 0;
                const isTaxable = document.getElementById('is_taxable').checked;
                if (isTaxable) {
                    const taxRate = parseFloat(document.getElementById('tax_rate').value) || 0;
                    taxAmount = (amountAfterDiscount * taxRate) / 100;
                }

                const total = amountAfterDiscount + taxAmount;

                document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2);
                document.getElementById('discountDisplay').textContent = discountAmount.toFixed(2);
                document.getElementById('taxDisplay').textContent = taxAmount.toFixed(2);
                document.getElementById('totalDisplay').textContent = total.toFixed(2);
            }

            function showManualAmountInfo() {
                const existing = document.getElementById('manualAmountInfo');
                if (existing) return;

                const info = document.createElement('div');
                info.id = 'manualAmountInfo';
                info.className = 'bg-purple-50 border-l-4 border-purple-500 rounded-lg p-4 mb-6';
                info.innerHTML = `
        <div class="flex items-start">
            <svg class="w-5 h-5 text-purple-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-purple-800">
                    {{ __('finance::purchase.manual_amount_mode') }}
                </h3>
                <p class="mt-1 text-sm text-purple-700">
                    {{ __('finance::purchase.items_disabled_info') }}
                </p>
            </div>
        </div>
    `;

                const isbnSection = document.querySelector('.bg-gradient-to-r.from-blue-50');
                isbnSection.insertAdjacentElement('beforebegin', info);
            }

            function hideManualAmountInfo() {
                const info = document.getElementById('manualAmountInfo');
                if (info) {
                    info.remove();
                }
            }

            // Update form submission validation
            document.getElementById('invoiceForm').addEventListener('submit', function(e) {
                const manualAmount = parseFloat(document.getElementById('manual_amount').value) || 0;
                const items = document.querySelectorAll('.item-row');

                if (manualAmount === 0 && items.length === 0) {
                    e.preventDefault();
                    alert('{{ __('finance::purchase.items_or_amount_required') }}');
                    return false;
                }
            });

            function addByIsbn() {
                const isbn = document.getElementById('isbnInput').value.trim();

                if (!isbn) {
                    alert('{{ __('finance::purchase.please_enter_isbn') }}');
                    return;
                }

                const product = allProducts.find(p => p.isbn === isbn);

                if (!product) {
                    alert('{{ __('finance::purchase.product_not_found') }}');
                    document.getElementById('isbnInput').value = '';
                    document.getElementById('isbnInput').focus();
                    return;
                }

                const existingRow = findExistingProductRow(product.id);

                if (existingRow) {
                    const quantityInput = existingRow.querySelector('.item-quantity');
                    const currentQuantity = parseFloat(quantityInput.value) || 0;
                    quantityInput.value = currentQuantity + 1;
                    const existingItemId = existingRow.getAttribute('data-item');
                    calculateLineTotal(existingItemId);
                    highlightUpdatedRow(existingItemId);
                    showToast('{{ __('finance::purchase.quantity_increased') }}: ' + product.name);
                } else {
                    addInvoiceItem();
                    const newItemId = itemCounter;
                    selectProductForItem(newItemId, product);
                    showToast('{{ __('finance::purchase.product_added') }}: ' + product.name);
                }

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
                currentItemId = itemId;
                document.getElementById('productDrawer').classList.remove('hidden');
                document.getElementById('productSearch').focus();
            }

            function closeProductDrawer() {
                document.getElementById('productDrawer').classList.add('hidden');
                currentItemId = null;
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

                    if (search) {
                        matches = matches && (
                            product.name.toLowerCase().includes(search) ||
                            (product.isbn && product.isbn.toLowerCase().includes(search)) ||
                            (product.sku && product.sku.toLowerCase().includes(search))
                        );
                    }

                    if (categoryId) {
                        matches = matches && product.category_id == categoryId;
                    }

                    if (subCategoryId) {
                        matches = matches && product.sub_category_id == subCategoryId;
                    }

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
                        <p class="text-gray-500">{{ __('finance::purchase.no_products_found') }}</p>
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
                                ${product.author_name ? `<p class="text-xs text-gray-600">{{ __('finance::purchase.author') }}: ${product.author_name}</p>` : ''}
                                ${product.category_name ? `<p class="text-xs text-gray-600">{{ __('finance::purchase.category') }}: ${product.category_name}</p>` : ''}
                            </div>
                        </div>
                        <div class="text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} {{ app()->getLocale() == 'ar' ? 'ml-4' : 'mr-4' }}">
                            <p class="text-lg font-bold text-blue-600">${parseFloat(product.price).toFixed(2)}</p>
                            ${product.stock_quantity !== null ? `<p class="text-xs text-gray-500">{{ __('finance::purchase.stock') }}: ${product.stock_quantity}</p>` : ''}
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

            function selectProductForItem(itemId, product) {
                const existingRow = findExistingProductRow(product.id, itemId);

                if (existingRow) {
                    const quantityInput = existingRow.querySelector('.item-quantity');
                    const currentQuantity = parseFloat(quantityInput.value) || 0;
                    quantityInput.value = currentQuantity + 1;
                    const existingItemId = existingRow.getAttribute('data-item');
                    calculateLineTotal(existingItemId);

                    const currentRow = document.querySelector(`[data-item="${itemId}"]`);
                    const currentProductId = currentRow.querySelector('.product-id').value;
                    if (!currentProductId) {
                        const allRows = document.querySelectorAll('.item-row');
                        if (allRows.length > 1) {
                            currentRow.remove();
                            updateNoItemsMessage();
                        }
                    }

                    highlightUpdatedRow(existingItemId);
                    return;
                }

                const row = document.querySelector(`[data-item="${itemId}"]`);
                if (!row) return;

                row.querySelector('.product-id').value = product.id;

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

                const priceInput = row.querySelector('.item-price');
                priceInput.value = product.price;

                calculateLineTotal(itemId);
            }

            function findExistingProductRow(productId, excludeItemId = null) {
                const rows = document.querySelectorAll('.item-row');
                for (const row of rows) {
                    const rowItemId = row.getAttribute('data-item');
                    const rowProductId = row.querySelector('.product-id').value;

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

                row.classList.add('bg-green-50', 'border-green-500');
                row.classList.remove('border-gray-200');

                row.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                setTimeout(() => {
                    row.classList.remove('bg-green-50', 'border-green-500');
                    row.classList.add('border-gray-200');
                }, 2000);
            }

            function addInvoiceItem() {
                itemCounter++;
                const container = document.getElementById('itemsContainer');

                // Hide no items message
                const noItemsMsg = document.getElementById('noItemsMessage');
                if (noItemsMsg) {
                    noItemsMsg.style.display = 'none';
                }

                const itemHtml = `
                <div class="item-row border border-gray-200 rounded-lg p-4 transition-all" data-item="${itemCounter}">
                    <div class="grid grid-cols-12 gap-4">
                        <!-- Product -->
                        <div class="col-span-12 md:col-span-4">
                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('finance::purchase.product') }}</label>
                            <input type="hidden" name="items[${itemCounter}][product_id]" class="product-id" required>
                            <button type="button" onclick="openProductDrawer(${itemCounter})"
                                class="product-display w-full px-3 py-2 text-sm border border-gray-300 rounded-lg text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <span class="text-gray-400">{{ __('finance::purchase.select_product') }}</span>
                            </button>
                        </div>

                        <!-- Quantity -->
                        <div class="col-span-6 md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('finance::purchase.quantity') }}</label>
                            <input type="number" name="items[${itemCounter}][quantity]" min="1" value="1" required
                                class="item-quantity w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                oninput="calculateLineTotal(${itemCounter})">
                        </div>

                        <!-- Unit Price -->
                        <div class="col-span-6 md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('finance::purchase.unit_price') }}</label>
                            <input type="number" name="items[${itemCounter}][unit_price]" step="0.01" min="0" value="0" required
                                class="item-price w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                oninput="calculateLineTotal(${itemCounter})">
                        </div>

                        <!-- Discount -->
                        <div class="col-span-6 md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('finance::purchase.item_discount') }}</label>
                            <input type="number" name="items[${itemCounter}][discount_amount]" step="0.01" min="0" value="0"
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


                    </div>
                </div>
            `;

                container.insertAdjacentHTML('beforeend', itemHtml);
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
                document.querySelector(`[data-item="${itemId}"]`).remove();
                updateNoItemsMessage();
                calculateTotals();
            }

            function updateNoItemsMessage() {
                const items = document.querySelectorAll('.item-row');
                const noItemsMsg = document.getElementById('noItemsMessage');
                if (items.length === 0 && noItemsMsg) {
                    noItemsMsg.style.display = 'block';
                }
            }

            // Quick Party Modal Functions
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

                const submitBtn = document.getElementById('quickPartySubmitBtn');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = '{{ __('common.saving') }}...';

                const formData = {
                    name: document.getElementById('quick_party_name').value,
                    type: document.getElementById('quick_party_type').value,
                    phone: document.getElementById('quick_party_phone').value,
                    email: document.getElementById('quick_party_email').value,
                    is_vendor: true,
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
