<x-dashboard :pageTitle="$stock->product->name . ' - ' . $stock->warehouse_name">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('warehouse.stocks.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('warehouse::stocks.stocks') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ $stock->product->name }}</span>
                </li>
            </ol>
        </nav>

        <!-- Header with Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $stock->product->name }}</h1>
                        <p class="text-sm text-gray-500 mt-1">{{ $stock->warehouse_name }}</p>
                        @if($stock->location)
                            <p class="text-sm text-gray-500">{{ __('warehouse::stocks.location') }}: {{ $stock->location }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('warehouse.stocks.edit', $stock) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            {{ __('common.edit') }}
                        </a>
                        <form action="{{ route('warehouse.stocks.destroy', $stock) }}" method="POST" onsubmit="return confirm('{{ __('common.are_you_sure') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                {{ __('common.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.total_quantity') }}</label>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($stock->quantity) }}</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.available_quantity') }}</label>
                <p class="text-3xl font-bold text-green-600">{{ number_format($stock->available_quantity) }}</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.reserved_quantity') }}</label>
                <p class="text-3xl font-bold text-orange-600">{{ number_format($stock->reserved_quantity) }}</p>
            </div>
        </div>

        <!-- Stock Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('warehouse::stocks.stock_details') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.warehouse_name') }}</label>
                        <p class="text-gray-900 font-medium">{{ $stock->warehouse_name }}</p>
                    </div>

                    @if($stock->location)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.location') }}</label>
                        <p class="text-gray-900">{{ $stock->location }}</p>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.minimum_quantity') }}</label>
                        <p class="text-gray-900">{{ number_format($stock->minimum_quantity) }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.stock_level') }}</label>
                        @php
                            $statusClasses = [
                                'out_of_stock' => 'bg-red-100 text-red-800',
                                'low_stock' => 'bg-orange-100 text-orange-800',
                                'in_stock' => 'bg-green-100 text-green-800',
                            ];
                            $class = $statusClasses[$stock->stock_level] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $class }}">
                            {{ __('warehouse::stocks.' . $stock->stock_level) }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.status') }}</label>
                        @php
                            $statusColor = $stock->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $statusColor }}">
                            {{ __('warehouse::stocks.' . $stock->status) }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('common.created_at') }}</label>
                        <p class="text-gray-900">{{ $stock->created_at->format('Y-m-d H:i') }}</p>
                    </div>

                    @if($stock->description)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.description') }}</label>
                        <p class="text-gray-900">{{ $stock->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Product Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('warehouse::stocks.product_details') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.product_name') }}</label>
                        <p class="text-gray-900 font-medium">{{ $stock->product->name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.product_type') }}</label>
                        @php
                            $typeColors = [
                                'book' => 'bg-blue-100 text-blue-800',
                                'ebook' => 'bg-purple-100 text-purple-800',
                                'journal' => 'bg-green-100 text-green-800',
                                'course' => 'bg-orange-100 text-orange-800',
                                'bundle' => 'bg-pink-100 text-pink-800',
                            ];
                            $color = $typeColors[$stock->product->type] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $color }}">
                            {{ ucfirst($stock->product->type) }}
                        </span>
                    </div>

                    @if($stock->product->sku)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.product_sku') }}</label>
                        <p class="text-gray-900">{{ $stock->product->sku }}</p>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.base_price') }}</label>
                        <p class="text-gray-900 font-bold">{{ number_format($stock->product->base_price, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Book Details (if product type is book) -->
        @if($stock->product->type === 'book' && $stock->product->book)
        <div class="bg-white rounded-xl shadow-sm border border-blue-200 mb-6">
            <div class="p-6 border-b border-blue-200 bg-blue-50">
                <h2 class="text-lg font-bold text-blue-900 flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    {{ __('warehouse::stocks.book_details') }}
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($stock->product->book->author)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.author') }}</label>
                        <p class="text-gray-900 font-medium">{{ $stock->product->book->author->name }}</p>
                    </div>
                    @endif

                    @if($stock->product->book->category)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.category') }}</label>
                        <p class="text-gray-900">{{ $stock->product->book->category->name }}</p>
                    </div>
                    @endif

                    @if($stock->product->book->isbn)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.isbn') }}</label>
                        <p class="text-gray-900 font-mono">{{ $stock->product->book->isbn }}</p>
                    </div>
                    @endif

                    @if($stock->product->book->publisher)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.publisher') }}</label>
                        <p class="text-gray-900">{{ $stock->product->book->publisher }}</p>
                    </div>
                    @endif

                    @if($stock->product->book->publication_year)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.publication_year') }}</label>
                        <p class="text-gray-900">{{ $stock->product->book->publication_year }}</p>
                    </div>
                    @endif

                    @if($stock->product->book->language)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.language') }}</label>
                        <p class="text-gray-900">{{ $stock->product->book->language }}</p>
                    </div>
                    @endif

                    @if($stock->product->book->pages)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::stocks.pages') }}</label>
                        <p class="text-gray-900">{{ $stock->product->book->pages }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</x-dashboard>
