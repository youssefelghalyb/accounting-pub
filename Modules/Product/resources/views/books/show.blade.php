<x-dashboard :pageTitle="$book->product->name">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('product.books.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('product::book.books') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ $book->product->name }}</span>
                </li>
            </ol>
        </nav>

        <!-- Header with Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $book->product->name }}</h1>
                        <p class="text-sm text-gray-500 mt-1">ISBN: {{ $book->isbn }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('product.books.edit', $book) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            {{ __('common.edit') }}
                        </a>
                        <form action="{{ route('product.books.destroy', $book) }}" method="POST" onsubmit="return confirm('{{ __('common.are_you_sure') }}')">
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

        <!-- Product Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::book.product_info') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::product.name') }}</label>
                        <p class="text-gray-900 font-medium">{{ $book->product->name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::product.type') }}</label>
                        @php
                            $color = $book->product->type === 'book' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800';
                        @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $color }}">
                            {{ __('product::product.' . $book->product->type) }}
                        </span>
                    </div>

                    @if($book->product->sku)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::product.sku') }}</label>
                        <p class="text-gray-900">{{ $book->product->sku }}</p>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::product.base_price') }}</label>
                        <p class="text-gray-900 font-bold text-lg">{{ number_format($book->product->base_price, 2) }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::product.status') }}</label>
                        @php
                            $statusColor = $book->product->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $statusColor }}">
                            {{ __('product::product.' . $book->product->status) }}
                        </span>
                    </div>

                    @if($book->product->description)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::product.description') }}</label>
                        <p class="text-gray-900">{{ $book->product->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Book Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::book.book_info') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.isbn') }}</label>
                        <p class="text-gray-900 font-medium">{{ $book->isbn }}</p>
                    </div>

                    @if($book->author)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.author') }}</label>
                        <a href="{{ route('product.authors.show', $book->author) }}" class="text-blue-600 hover:text-blue-700 font-medium">
                            {{ $book->author->full_name }}
                        </a>
                    </div>
                    @endif

                    @if($book->category)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.category') }}</label>
                        <a href="{{ route('product.categories.show', $book->category) }}" class="text-blue-600 hover:text-blue-700">
                            {{ $book->category->name }}
                        </a>
                    </div>
                    @endif

                    @if($book->subCategory)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.sub_category') }}</label>
                        <a href="{{ route('product.categories.show', $book->subCategory) }}" class="text-blue-600 hover:text-blue-700">
                            {{ $book->subCategory->name }}
                        </a>
                    </div>
                    @endif

                    @if($book->num_of_pages)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.num_of_pages') }}</label>
                        <p class="text-gray-900">{{ number_format($book->num_of_pages) }}</p>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.cover_type') }}</label>
                        @php
                            $coverColor = $book->cover_type === 'hard' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800';
                        @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $coverColor }}">
                            {{ __('product::book.' . $book->cover_type) }}
                        </span>
                    </div>

                    @if($book->published_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.published_at') }}</label>
                        <p class="text-gray-900">{{ $book->published_at->format('Y-m-d') }}</p>
                    </div>
                    @endif

                    @if($book->language)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.language') }}</label>
                        <p class="text-gray-900">{{ $book->language }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($book->is_translated)
        <!-- Translation Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::book.translation_info') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @if($book->translated_from)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.translated_from') }}</label>
                        <p class="text-gray-900">{{ $book->translated_from }}</p>
                    </div>
                    @endif

                    @if($book->translated_to)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.translated_to') }}</label>
                        <p class="text-gray-900">{{ $book->translated_to }}</p>
                    </div>
                    @endif

                    @if($book->translator_name)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.translator_name') }}</label>
                        <p class="text-gray-900">{{ $book->translator_name }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</x-dashboard>
