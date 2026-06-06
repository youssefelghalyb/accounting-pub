<x-dashboard :pageTitle="__('product::book.books')">
    @php
        // Prepare data array
        $tableData = $books
            ->map(function ($book) {
                return [
                    'id' => $book->id,
                    'name' => $book->product->name,
                    'isbn' => $book->isbn,
                    'author_name' => $book->author?->full_name,
                    'category_name' => $book->category?->name,
                    'num_of_pages' => $book->num_of_pages,
                    'cover_type' => $book->cover_type,
                    'base_price' => $book->product->base_price,
                    'is_translated' => $book->is_translated,
                    'model' => $book,
                ];
            })
            ->toArray();

        function limitWords(string $text, int $maxWords = 3, string $end = '...'): string
        {
            $text = trim($text);

            if ($text === '') {
                return '';
            }

            $words = preg_split('/\s+/', $text);
            $limited = implode(' ', array_slice($words, 0, $maxWords));

            return count($words) > $maxWords ? $limited . $end : $limited;
        }

        $tableColumns = [
            [
                'label' => __('product::product.name'),
                'field' => 'name',
                'render' => function ($row) {
                    $html = '<div>';
                    $html .= '<p class="font-medium text-gray-900">' . e(limitWords($row['name'], 7)) . '</p>';
                    $html .= '<p class="text-xs text-gray-500">ISBN: ' . e($row['isbn']) . '</p>';
                    $html .= '</div>';
                    return $html;
                },
            ],
            [
                'label' => __('product::book.author'),
                'field' => 'author_name',
                'format' => function ($value) {
                    return $value
                        ? '<span class="text-sm text-gray-600">' . e(limitWords($value, 5)) . '</span>'
                        : '<span class="text-sm text-gray-400">-</span>';
                },
            ],
            [
                'label' => __('product::book.category'),
                'field' => 'category_name',
                'format' => function ($value) {
                    return $value
                        ? '<span class="text-sm text-gray-600">' . e($value) . '</span>'
                        : '<span class="text-sm text-gray-400">-</span>';
                },
            ],
            [
                'label' => __('product::book.num_of_pages'),
                'field' => 'num_of_pages',
                'format' => function ($value) {
                    return $value
                        ? '<span class="text-sm text-gray-600">' . number_format($value) . '</span>'
                        : '<span class="text-sm text-gray-400">-</span>';
                },
            ],
            [
                'label' => __('product::book.cover_type'),
                'field' => 'cover_type',
                'render' => function ($row) {
                    $color =
                        $row['cover_type'] === 'hard' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800';
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full ' .
                        $color .
                        '">' .
                        __('product::book.' . $row['cover_type']) .
                        '</span>';
                },
            ],
            [
                'label' => __('product::product.base_price'),
                'field' => 'base_price',
                'format' => function ($value) {
                    return '<span class="font-medium text-gray-900">' . number_format($value, 2) . '</span>';
                },
            ],
        ];

        // Prepare actions array
        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('product.books.show', $row['model']),
                'icon' =>
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600',
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('product.books.edit', $row['model']),
                'icon' =>
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600',
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('product.books.destroy', $row['model']),
                'method' => 'DELETE',
                'confirm' => __('common.are_you_sure'),
                'icon' =>
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                'color' => 'text-red-600',
            ],
        ];

        // Prepare filters array
        $tableFilters = [
            [
                'type' => 'select',
                'name' => 'author_id',
                'label' => __('product::book.all_authors'),
                'options' => $authors
                    ->map(function ($author) {
                        return [
                            'value' => $author->id,
                            'label' => $author->full_name,
                        ];
                    })
                    ->toArray(),
            ],
            [
                'type' => 'select',
                'name' => 'category_id',
                'label' => __('product::book.all_categories'),
                'options' => $categories
                    ->map(function ($cat) {
                        return [
                            'value' => $cat->id,
                            'label' => $cat->name,
                        ];
                    })
                    ->toArray(),
            ],
        ];
    @endphp

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Books -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::book.total_books') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_books'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Pages -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::book.total_pages') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_pages']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Translated Books -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::book.translated_books') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['translated_books'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <button onclick="window.dispatchEvent(new CustomEvent('open'))"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-500 hover:bg-indigo-600 rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ __('product::book.bulk_price_update') }}
        </button>
        <x-dashboard.packages.data-table :title="__('product::book.book_list')" :description="__('product::book.total_books') . ': ' . $books->count()" searchable :searchRoute="route('product.books.index')"
            :searchPlaceholder="__('product::book.search')" :filters="$tableFilters" :data="$tableData" :columns="$tableColumns" :actions="$tableActions" :createRoute="route('product.books.create')"
            :createLabel="__('product::book.add_book')" :emptyStateTitle="__('product::book.no_books')" :emptyStateDescription="__('common.no_data')" emptyStateIcon="document" :pagination="$books"
            showPerPage :perPage="[10, 25, 50, 100]" />
    </div>
    <div id="bulk-price-modal" x-data="bulkPriceUpdate()" x-on:open.window="open = true" x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" x-show="open"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="!processing && close()">
        </div>

        {{-- Modal Panel --}}
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg" x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4">
            {{-- Header --}}
            <div class="px-6 pt-6 pb-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('product::book.bulk_price_update') }}
                            </h3>
                            <p class="text-xs text-gray-500">{{ __('product::book.bulk_price_desc') }}</p>
                        </div>
                    </div>
                    <button @click="close()" :disabled="processing"
                        class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 space-y-5">

                {{-- ── FORM (visible when idle) ── --}}
                <template x-if="!processing && !done">
                    <div class="space-y-4">

                        {{-- Operation Toggle --}}
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2">{{ __('product::book.operation') }}</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="form.operation = 'increment'"
                                    :class="form.operation === 'increment' ?
                                        'bg-emerald-500 text-white border-emerald-500 shadow-sm' :
                                        'bg-white text-gray-600 border-gray-200 hover:border-emerald-300 hover:text-emerald-600'"
                                    class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border-2 text-sm font-medium transition-all duration-150">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    {{ __('product::book.increment') }}
                                </button>
                                <button type="button" @click="form.operation = 'decrement'"
                                    :class="form.operation === 'decrement' ?
                                        'bg-rose-500 text-white border-rose-500 shadow-sm' :
                                        'bg-white text-gray-600 border-gray-200 hover:border-rose-300 hover:text-rose-600'"
                                    class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border-2 text-sm font-medium transition-all duration-150">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 12H4" />
                                    </svg>
                                    {{ __('product::book.decrement') }}
                                </button>
                            </div>
                        </div>

                        {{-- Type Toggle --}}
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2">{{ __('product::book.update_type') }}</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="form.type = 'fixed'"
                                    :class="form.type === 'fixed' ?
                                        'bg-indigo-500 text-white border-indigo-500 shadow-sm' :
                                        'bg-white text-gray-600 border-gray-200 hover:border-indigo-300 hover:text-indigo-600'"
                                    class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border-2 text-sm font-medium transition-all duration-150">
                                    <span class="font-bold text-base leading-none">$</span>
                                    {{ __('product::book.fixed_amount') }}
                                </button>
                                <button type="button" @click="form.type = 'percentage'"
                                    :class="form.type === 'percentage' ?
                                        'bg-indigo-500 text-white border-indigo-500 shadow-sm' :
                                        'bg-white text-gray-600 border-gray-200 hover:border-indigo-300 hover:text-indigo-600'"
                                    class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border-2 text-sm font-medium transition-all duration-150">
                                    <span class="font-bold text-base leading-none">%</span>
                                    {{ __('product::book.percentage') }}
                                </button>
                            </div>
                        </div>

                        {{-- Amount Input --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                <span
                                    x-text="form.type === 'fixed' ? '{{ __('product::book.amount') }}' : '{{ __('product::book.percentage_amount') }}'"></span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                    <span x-text="form.type === 'fixed' ? '$' : '%'"
                                        class="text-gray-500 font-semibold text-sm"></span>
                                </div>
                                <input type="number" x-model="form.amount" min="0.01" step="0.01"
                                    placeholder="0.00"
                                    class="w-full ps-9 pe-4 py-2.5 rounded-lg border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 text-gray-900 text-sm transition-all outline-none"
                                    :class="formError ? 'border-red-400 focus:border-red-400 focus:ring-red-100' : ''">
                            </div>
                            <template x-if="formError">
                                <p class="mt-1 text-xs text-red-500" x-text="formError"></p>
                            </template>
                        </div>

                        {{-- Preview banner --}}
                        <template x-if="form.amount > 0">
                            <div class="rounded-lg px-4 py-3 text-sm flex items-center gap-2"
                                :class="form.operation === 'increment' ?
                                    'bg-emerald-50 text-emerald-700 border border-emerald-100' :
                                    'bg-rose-50 text-rose-700 border border-rose-100'">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span x-text="previewText()"></span>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- ── PROGRESS (visible when processing) ── --}}
                <template x-if="processing">
                    <div class="space-y-4">
                        <div class="text-center space-y-1">
                            <p class="text-sm font-medium text-gray-700">{{ __('product::book.updating_prices') }}</p>
                            <p class="text-xs text-gray-500" x-text="currentBookName"></p>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-xs text-gray-500">
                                <span x-text="`${progress.current} / ${progress.total}`"></span>
                                <span x-text="`${progress.percent}%`"></span>
                            </div>
                            <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-indigo-500 to-indigo-400 rounded-full transition-all duration-300 ease-out"
                                    :style="`width: ${progress.percent}%`"></div>
                            </div>
                        </div>

                        {{-- Spinner + label --}}
                        <div class="flex items-center justify-center gap-2 text-sm text-indigo-600">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            <span>{{ __('product::book.please_wait') }}</span>
                        </div>
                    </div>
                </template>

                {{-- ── RESULTS (visible when done) ── --}}
                <template x-if="done">
                    <div class="space-y-4">
                        {{-- Success banner --}}
                        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-100">
                            <div
                                class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-emerald-800">
                                    {{ __('product::book.update_complete') }}</p>
                                <p class="text-xs text-emerald-600 mt-0.5">
                                    <span x-text="results.updated"></span>
                                    {{ __('product::book.books_updated_successfully') }}
                                </p>
                            </div>
                        </div>

                        {{-- Stats grid --}}
                        <div class="grid grid-cols-3 gap-3">
                            <div class="text-center p-3 rounded-lg bg-gray-50 border border-gray-100">
                                <p class="text-2xl font-bold text-gray-900" x-text="results.total"></p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ __('product::book.total') }}</p>
                            </div>
                            <div class="text-center p-3 rounded-lg bg-emerald-50 border border-emerald-100">
                                <p class="text-2xl font-bold text-emerald-600" x-text="results.updated"></p>
                                <p class="text-xs text-emerald-600 mt-0.5">{{ __('product::book.updated') }}</p>
                            </div>
                            <div class="text-center p-3 rounded-lg border"
                                :class="results.failed > 0 ? 'bg-rose-50 border-rose-100' : 'bg-gray-50 border-gray-100'">
                                <p class="text-2xl font-bold"
                                    :class="results.failed > 0 ? 'text-rose-600' : 'text-gray-400'"
                                    x-text="results.failed"></p>
                                <p class="text-xs mt-0.5"
                                    :class="results.failed > 0 ? 'text-rose-600' : 'text-gray-400'">
                                    {{ __('product::book.failed') }}</p>
                            </div>
                        </div>

                        {{-- Errors accordion --}}
                        <template x-if="results.errors && results.errors.length > 0">
                            <div class="rounded-xl border border-rose-200 overflow-hidden">
                                <button type="button" @click="showErrors = !showErrors"
                                    class="w-full flex items-center justify-between px-4 py-3 bg-rose-50 text-rose-700 text-sm font-medium hover:bg-rose-100 transition-colors">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                        </svg>
                                        <span
                                            x-text="`${results.errors.length} {{ __('product::book.errors_found') }}`"></span>
                                    </span>
                                    <svg class="w-4 h-4 transition-transform duration-200"
                                        :class="showErrors ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div x-show="showErrors" x-collapse
                                    class="divide-y divide-rose-100 max-h-40 overflow-y-auto">
                                    <template x-for="err in results.errors" :key="err.book_id">
                                        <div class="px-4 py-2.5">
                                            <p class="text-xs font-medium text-rose-700" x-text="err.book_name"></p>
                                            <p class="text-xs text-rose-500 mt-0.5" x-text="err.reason"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Footer --}}
            <div class="px-6 pb-6 flex items-center justify-end gap-3">
                <template x-if="!processing && !done">
                    <div class="flex gap-3">
                        <button type="button" @click="close()"
                            class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            {{ __('common.cancel') }}
                        </button>
                        <button type="button" @click="submit()" :disabled="!form.amount || form.amount <= 0"
                            class="px-5 py-2 text-sm font-medium text-white rounded-lg transition-all disabled:opacity-40 disabled:cursor-not-allowed"
                            :class="form.operation === 'increment' ?
                                'bg-emerald-500 hover:bg-emerald-600 shadow-emerald-200 shadow-sm hover:shadow-md' :
                                'bg-rose-500 hover:bg-rose-600 shadow-rose-200 shadow-sm hover:shadow-md'">
                            {{ __('product::book.apply_update') }}
                        </button>
                    </div>
                </template>
                <template x-if="done">
                    <div class="flex gap-3">
                        <button type="button" @click="reset()"
                            class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            {{ __('product::book.update_again') }}
                        </button>
                        <button type="button" @click="closeAndReload()"
                            class="px-5 py-2 text-sm font-medium text-white bg-indigo-500 hover:bg-indigo-600 rounded-lg transition-colors shadow-sm">
                            {{ __('common.done') }}
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>


    {{-- ── ALPINE COMPONENT ──────────────────────────────────────────────────── --}}
    @push('scripts')
        <script>
            function bulkPriceUpdate() {
                return {
                    open: false,
                    processing: false,
                    done: false,
                    showErrors: false,
                    currentBookName: '',
                    formError: null,

                    form: {
                        operation: 'increment',
                        type: 'fixed',
                        amount: '',
                    },

                    progress: {
                        current: 0,
                        total: 0,
                        percent: 0,
                    },

                    results: {
                        total: 0,
                        updated: 0,
                        failed: 0,
                        errors: [],
                    },

                    previewText() {
                        const symbol = this.form.type === 'fixed' ? '$' : '%';
                        const verb = this.form.operation === 'increment' ? 'Increase' : 'Decrease';
                        const amount = parseFloat(this.form.amount) || 0;
                        return `${verb} all book prices by ${symbol}${amount.toFixed(2)}`;
                    },

                    close() {
                        if (this.processing) return;
                        this.open = false;
                    },

                    closeAndReload() {
                        this.close();
                        setTimeout(() => window.location.reload(), 250);
                    },

                    reset() {
                        this.processing = false;
                        this.done = false;
                        this.showErrors = false;
                        this.formError = null;
                        this.currentBookName = '';
                        this.form = {
                            operation: 'increment',
                            type: 'fixed',
                            amount: ''
                        };
                        this.progress = {
                            current: 0,
                            total: 0,
                            percent: 0
                        };
                        this.results = {
                            total: 0,
                            updated: 0,
                            failed: 0,
                            errors: []
                        };
                    },

                    async submit() {
                        this.formError = null;

                        const amount = parseFloat(this.form.amount);
                        if (!amount || amount <= 0) {
                            this.formError = 'Please enter a valid amount greater than 0.';
                            return;
                        }

                        this.processing = true;
                        this.done = false;

                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

                        const response = await fetch('{{ route('product.books.bulk-price-update') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/x-ndjson',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                operation: this.form.operation,
                                amount: amount,
                                type: this.form.type,
                            }),
                        });

                        if (!response.ok) {
                            this.processing = false;
                            this.formError = 'Server error. Please try again.';
                            return;
                        }

                        const reader = response.body.getReader();
                        const decoder = new TextDecoder();
                        let buffer = '';

                        while (true) {
                            const {
                                value,
                                done
                            } = await reader.read();
                            if (done) break;

                            buffer += decoder.decode(value, {
                                stream: true
                            });
                            const lines = buffer.split('\n');
                            buffer = lines.pop(); // keep incomplete last line

                            for (const line of lines) {
                                if (!line.trim()) continue;
                                try {
                                    const data = JSON.parse(line);

                                    if (data.type === 'progress') {
                                        this.progress.current = data.current;
                                        this.progress.total = data.total;
                                        this.progress.percent = data.percent;
                                        this.currentBookName = data.book_name;

                                    } else if (data.type === 'done') {
                                        this.results.total = data.total;
                                        this.results.updated = data.updated;
                                        this.results.failed = data.failed;
                                        this.results.errors = data.errors || [];
                                        this.processing = false;
                                        this.done = true;
                                    }
                                } catch (_) {
                                    /* ignore malformed lines */ }
                            }
                        }
                    },
                };
            }

            // Global helper to open the modal from anywhere (e.g. a button outside x-data scope)
            window.openBulkPriceModal = () => window.dispatchEvent(new CustomEvent('open'));
        </script>
    @endpush
</x-dashboard>
