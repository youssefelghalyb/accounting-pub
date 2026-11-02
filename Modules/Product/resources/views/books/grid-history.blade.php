<x-dashboard :pageTitle="__('product::book_grid.history.title')">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ __('product::book_grid.history.title') }}</h1>
                <p class="text-sm text-gray-500">{{ __('product::book_grid.history.subtitle') }}</p>
            </div>
            <a href="{{ route('product.book-grid.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">← {{ __('product::book_grid.history.back_to_grid') }}</a>
        </div>

        @include('product::books.partials.book-grid-history')
    </div>

    @push('head-scripts')
        <script>window.__BOOK_GRID_HISTORY = @json($config);</script>
    @endpush
    @push('scripts')
        <script src="{{ asset('js/book-grid-history.js') }}?v={{ filemtime(public_path('js/book-grid-history.js')) }}"></script>
    @endpush
</x-dashboard>
