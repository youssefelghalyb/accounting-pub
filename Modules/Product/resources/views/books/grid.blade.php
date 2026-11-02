<x-dashboard :pageTitle="__('product::book_grid.title')">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ __('product::book_grid.title') }}</h1>
                <p class="text-sm text-gray-500">{{ __('product::book_grid.ui.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('product.book-grid.history') }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50" data-t="history-link">🕘 {{ __('product::book_grid.history.history_link') }}</a>
                <a href="{{ route('product.books.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">← {{ __('product::book.books') }}</a>
            </div>
        </div>

        @include('product::books.partials.book-grid')
    </div>

    @push('head-scripts')
        <script>window.__BOOK_GRID_CONFIG = @json($config);</script>
    @endpush
    @push('scripts')
        <script src="{{ asset('js/book-grid.js') }}?v={{ filemtime(public_path('js/book-grid.js')) }}"></script>
    @endpush
</x-dashboard>
