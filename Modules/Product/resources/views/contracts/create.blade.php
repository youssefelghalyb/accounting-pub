<x-dashboard :pageTitle="__('product::contract.add_contract')">
    <div class="max-w-5xl mx-auto" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

        {{-- Breadcrumb --}}
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('product.contracts.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('product::contract.contracts') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                </li>
                <li><span class="text-gray-900 font-medium">{{ __('product::contract.add_contract') }}</span></li>
            </ol>
        </nav>

        {{-- Form Card --}}
        <form action="{{ route('product.contracts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">{{ __('product::contract.create_contract') }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
                </div>

                <div class="p-6 space-y-8">

                    {{-- ── Authors Section ─────────────────────────────── --}}
                    <div
                        x-data="{
                            selectedAuthors: [],
                            representativeId: null,
                            allAuthors: {{ $authors->map(fn($a) => ['id' => $a->id, 'name' => $a->full_name])->toJson() }},
                            searchQuery: '',
                            open: false,
                            get filteredAuthors() {
                                const q = this.searchQuery.toLowerCase();
                                return this.allAuthors.filter(a =>
                                    !this.selectedAuthors.find(s => s.id === a.id) &&
                                    a.name.toLowerCase().includes(q)
                                );
                            },
                            addAuthor(author) {
                                this.selectedAuthors.push(author);
                                // First author added becomes the representative by default
                                if (this.selectedAuthors.length === 1) {
                                    this.representativeId = author.id;
                                }
                                this.searchQuery = '';
                                this.open = false;
                            },
                            removeAuthor(id) {
                                this.selectedAuthors = this.selectedAuthors.filter(a => a.id !== id);
                                if (this.representativeId === id) {
                                    this.representativeId = this.selectedAuthors[0]?.id ?? null;
                                }
                            },
                            setRepresentative(id) {
                                this.representativeId = id;
                            }
                        }"
                        class="border border-blue-100 rounded-xl p-5 bg-blue-50/30"
                    >
                        <h3 class="text-base font-semibold text-gray-800 mb-4">
                            {{ __('product::contract.authors') }}
                            <span class="text-red-500">*</span>
                        </h3>

                        {{-- Search & Add Author --}}
                        <div class="relative mb-4">
                            <input
                                type="text"
                                x-model="searchQuery"
                                @focus="open = true"
                                @click.outside="open = false"
                                placeholder="{{ __('product::contract.search_add_author') }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                            {{-- Dropdown --}}
                            <div
                                x-show="open && filteredAuthors.length > 0"
                                x-cloak
                                class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto"
                            >
                                <template x-for="author in filteredAuthors" :key="author.id">
                                    <button
                                        type="button"
                                        @click="addAuthor(author)"
                                        class="w-full text-start px-4 py-2.5 text-sm hover:bg-blue-50 transition"
                                        x-text="author.name"
                                    ></button>
                                </template>
                            </div>
                        </div>

                        {{-- Empty state --}}
                        <p x-show="selectedAuthors.length === 0" class="text-sm text-gray-500 italic">
                            {{ __('product::contract.no_authors_selected') }}
                        </p>

                        {{-- Selected authors list --}}
                        <div class="space-y-2">
                            <template x-for="author in selectedAuthors" :key="author.id">
                                <div class="flex items-center justify-between bg-white border border-gray-200 rounded-lg px-4 py-3 shadow-sm">
                                    <div class="flex items-center gap-3">
                                        {{-- Representative radio --}}
                                        <input
                                            type="radio"
                                            :value="author.id"
                                            x-model="representativeId"
                                            :name="'representative_id_display'"
                                            class="text-blue-600 focus:ring-blue-500"
                                            :title="'{{ __('product::contract.set_as_representative') }}'"
                                        />
                                        <span class="text-sm font-medium text-gray-800" x-text="author.name"></span>
                                        <span
                                            x-show="representativeId === author.id"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700"
                                        >
                                            {{ __('product::contract.representative') }}
                                        </span>
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeAuthor(author.id)"
                                        class="text-red-400 hover:text-red-600 transition"
                                        :title="'{{ __('product::contract.remove_author') }}'"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>

                                    {{-- Hidden inputs for form submission --}}
                                    <input type="hidden" :name="'author_ids[]'" :value="author.id" />
                                </div>
                            </template>
                        </div>

                        {{-- Hidden representative_id for form submission --}}
                        <input type="hidden" name="representative_id" :value="representativeId" />

                        @error('author_ids')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('representative_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- ── Book & Contract Details ──────────────────────── --}}
                    <div class="grid grid-cols-12 gap-5">

                        {{-- Book (existing) --}}
                        <div class="col-span-12 md:col-span-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                {{ __('product::contract.book') }}
                            </label>
                            <select name="book_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500">
                                <option value="">{{ __('product::contract.select_book') }}</option>
                                @foreach ($books as $book)
                                    <option value="{{ $book->id }}" {{ old('book_id', $selectedBook) == $book->id ? 'selected' : '' }}>
                                        {{ $book->product->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('book_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Book name (free text) --}}
                        <div class="col-span-12 md:col-span-8">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                {{ __('product::contract.book_name') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="book_name"
                                value="{{ old('book_name') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 @error('book_name') border-red-500 @enderror"
                            />
                            @error('book_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Contract date --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                {{ __('product::contract.contract_date') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                name="contract_date"
                                value="{{ old('contract_date') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 @error('contract_date') border-red-500 @enderror"
                            />
                            @error('contract_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Contract price --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                {{ __('product::contract.contract_price') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                name="contract_price"
                                value="{{ old('contract_price') }}"
                                min="0"
                                step="0.01"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 @error('contract_price') border-red-500 @enderror"
                            />
                            @error('contract_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Profit percentage --}}
                        <div class="col-span-12">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                {{ __('product::contract.percentage_from_book_profit') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                name="percentage_from_book_profit"
                                value="{{ old('percentage_from_book_profit', 0) }}"
                                min="0"
                                max="100"
                                step="0.01"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-green-500 @error('percentage_from_book_profit') border-red-500 @enderror"
                            />
                            @error('percentage_from_book_profit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Contract file --}}
                        <div class="col-span-12">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                {{ __('product::contract.contract_file') }}
                            </label>
                            <input
                                type="file"
                                name="contract_file"
                                accept=".pdf,.doc,.docx"
                                class="w-full text-sm text-gray-500 file:me-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100"
                            />
                            <p class="mt-1 text-xs text-gray-500">{{ __('product::contract.upload_contract_file') }}</p>
                            @error('contract_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3 rounded-b-xl">
                    <a href="{{ route('product.contracts.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        {{ __('common.cancel') }}
                    </a>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        {{ __('product::contract.save_contract') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-dashboard>