<x-dashboard :pageTitle="$book->product->name">
    <div class="max-w-5xl mx-auto">
        {{-- Breadcrumb --}}
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('product.books.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('product::book.books') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </li>
                <li><span class="text-gray-900 font-medium">{{ $book->product->name }}</span></li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $book->product->name }}</h1>
                    <p class="text-sm text-gray-500 mt-1">ISBN: {{ $book->isbn }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('product.books.edit', $book) }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        {{ __('common.edit') }}
                    </a>
                    <form action="{{ route('product.books.destroy', $book) }}" method="POST"
                        onsubmit="return confirm('{{ __('common.are_you_sure') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            {{ __('common.delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Product Information --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::book.product_info') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::product.name') }}</label>
                        <p class="text-gray-900 font-medium">{{ $book->product->name }}</p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::product.type') }}</label>
                        @php $color = $book->product->type === 'book' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'; @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $color }}">
                            {{ __('product::product.' . $book->product->type) }}
                        </span>
                    </div>
                    @if ($book->product->sku)
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::product.sku') }}</label>
                            <p class="text-gray-900">{{ $book->product->sku }}</p>
                        </div>
                    @endif
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::product.base_price') }}</label>
                        <p class="text-gray-900 font-bold text-lg">{{ number_format($book->product->base_price, 2) }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::product.status') }}</label>
                        @php $statusColor = $book->product->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $statusColor }}">
                            {{ __('product::product.' . $book->product->status) }}
                        </span>
                    </div>
                    @if ($book->product->description)
                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::product.description') }}</label>
                            <p class="text-gray-900">{{ $book->product->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Book Information --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::book.book_info') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.isbn') }}</label>
                        <p class="text-gray-900 font-medium">{{ $book->isbn }}</p>
                    </div>

                    {{-- Authors — derived via contract pivot --}}
                    @php $bookAuthors = $book->authors; @endphp
                    @if ($bookAuthors->isNotEmpty())
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-600 mb-2">{{ __('product::book.author') }}</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($bookAuthors as $author)
                                    <a href="{{ route('product.authors.show', $author) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium transition
                                           {{ $author->pivot->is_representative ? 'bg-blue-100 text-blue-800 hover:bg-blue-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                        {{ $author->full_name }}
                                        @if ($author->pivot->is_representative)
                                            <span
                                                class="text-xs opacity-70">({{ __('product::contract.representative') }})</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($book->category)
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.category') }}</label>
                            <a href="{{ route('product.categories.show', $book->category) }}"
                                class="text-blue-600 hover:text-blue-700">
                                {{ $book->category->name }}
                            </a>
                        </div>
                    @endif

                    @if ($book->subCategory)
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.sub_category') }}</label>
                            <a href="{{ route('product.categories.show', $book->subCategory) }}"
                                class="text-blue-600 hover:text-blue-700">
                                {{ $book->subCategory->name }}
                            </a>
                        </div>
                    @endif

                    @if ($book->num_of_pages)
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.num_of_pages') }}</label>
                            <p class="text-gray-900">{{ number_format($book->num_of_pages) }}</p>
                        </div>
                    @endif

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.cover_type') }}</label>
                        @php $coverColor = $book->cover_type === 'hard' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $coverColor }}">
                            {{ __('product::book.' . $book->cover_type) }}
                        </span>
                    </div>

                    @if ($book->published_at)
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.published_at') }}</label>
                            <p class="text-gray-900">{{ $book->published_at->format('Y-m-d') }}</p>
                        </div>
                    @endif

                    @if ($book->language)
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.language') }}</label>
                            <p class="text-gray-900">{{ $book->language }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Contract Summary --}}
        @if ($book->contract)
            @php $contract = $book->contract; @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">{{ __('product::contract.contract_details') }}</h2>
                    <a href="{{ route('product.contracts.show', $contract) }}"
                        class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        {{ __('common.view') }} →
                    </a>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">
                                {{ __('product::contract.contract_price') }}</p>
                            <p class="text-lg font-bold text-gray-900 mt-1">
                                {{ number_format($contract->contract_price, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">
                                {{ __('product::contract.total_paid') }}</p>
                            <p class="text-lg font-bold text-green-600 mt-1">
                                {{ number_format($contract->total_paid, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">
                                {{ __('product::contract.outstanding_balance') }}</p>
                            <p class="text-lg font-bold text-orange-600 mt-1">
                                {{ number_format($contract->outstanding_balance, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">
                                {{ __('product::contract.payment_status') }}</p>
                            @php
                                $colors = [
                                    'paid' => 'bg-green-100 text-green-800',
                                    'partial' => 'bg-yellow-100 text-yellow-800',
                                    'pending' => 'bg-red-100 text-red-800',
                                ];
                                $color = $colors[$contract->payment_status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span
                                class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $color }} mt-1">
                                {{ __('product::contract.' . $contract->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Translation Information --}}
        @if ($book->is_translated)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">{{ __('product::book.translation_info') }}</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @if ($book->translated_from)
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.translated_from') }}</label>
                                <p class="text-gray-900">{{ $book->translated_from }}</p>
                            </div>
                        @endif
                        @if ($book->translated_to)
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.translated_to') }}</label>
                                <p class="text-gray-900">{{ $book->translated_to }}</p>
                            </div>
                        @endif
                        @if ($book->translator_name)
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::book.translator_name') }}</label>
                                <p class="text-gray-900">{{ $book->translator_name }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif


        {{--
    Drop at the bottom of show.blade.php
    (after Translation Information, before closing </div> of max-w-5xl)
--}}

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::book.sales_overview') }}</h2>
            </div>

            @if ($salesTotals && $salesTotals->total_orders > 0)

                {{-- Summary cards --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 border-b border-gray-100">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-xs font-medium text-blue-500 uppercase tracking-wide">
                            {{ __('product::book.total_orders') }}
                        </p>
                        <p class="text-2xl font-bold text-blue-700 mt-1">
                            {{ number_format($salesTotals->total_orders) }}
                        </p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-xs font-medium text-green-500 uppercase tracking-wide">
                            {{ __('product::book.total_qty_sold') }}
                        </p>
                        <p class="text-2xl font-bold text-green-700 mt-1">
                            {{ number_format($salesTotals->total_qty) }}
                        </p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <p class="text-xs font-medium text-purple-500 uppercase tracking-wide">
                            {{ __('product::book.total_revenue') }}
                        </p>
                        <p class="text-2xl font-bold text-purple-700 mt-1">
                            {{ number_format($salesTotals->total_revenue, 2) }}
                        </p>
                        <p class="text-xs text-purple-400 mt-1">{{ __('product::book.before_invoice_discount') }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            {{ __('product::book.avg_price') }}
                        </p>
                        <p class="text-2xl font-bold text-gray-700 mt-1">
                            {{ number_format($salesTotals->avg_price, 2) }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ number_format($salesTotals->min_price, 2) }} —
                            {{ number_format($salesTotals->max_price, 2) }}
                        </p>
                    </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-4 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    {{ __('product::book.invoice_number') }}
                                </th>
                                <th
                                    class="px-4 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    {{ __('product::book.date') }}
                                </th>
                                <th
                                    class="px-4 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    {{ __('product::book.customer') }}
                                </th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    {{ __('product::book.qty') }}
                                </th>
                                <th
                                    class="px-4 py-3 text-end text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    {{ __('product::book.unit_price') }}
                                </th>
                                <th
                                    class="px-4 py-3 text-end text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    {{ __('product::book.line_total') }}
                                </th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    {{ __('product::book.invoice_discount_note') }}
                                </th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    {{ __('product::book.status') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($salesItems as $item)
                                @php
                                    $statusColors = [
                                        'paid' => 'bg-green-100 text-green-700',
                                        'partial' => 'bg-yellow-100 text-yellow-700',
                                        'unpaid' => 'bg-red-100 text-red-700',
                                        'pending' => 'bg-gray-100 text-gray-600',
                                        'draft' => 'bg-gray-100 text-gray-500',
                                        'cancelled' => 'bg-red-50 text-red-400',
                                    ];
                                    $statusColor = $statusColors[$item->invoice_status] ?? 'bg-gray-100 text-gray-600';
                                @endphp
                                @php $isGift = (float) $item->line_total === 0.0; @endphp
                                <tr
                                    class="transition-colors {{ $isGift
                                        ? 'bg-amber-50 hover:bg-amber-100'
                                        : ($item->invoice_status === 'cancelled'
                                            ? 'opacity-50 hover:bg-gray-50'
                                            : 'hover:bg-gray-50') }}">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('finance.sales-invoices.show', $item->invoice_id) }}"
                                            class="text-blue-600 hover:text-blue-700 font-medium">
                                            {{ $item->invoice_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($item->invoice_date)->format('Y-m-d') }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $item->customer_name }}
                                        @if ($item->customer_phone)
                                            <span
                                                class="block text-xs text-gray-400">{{ $item->customer_phone }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center font-medium text-gray-700">
                                        {{ number_format($item->quantity) }}
                                    </td>
                                    <td class="px-4 py-3 text-end text-gray-700">
                                        {{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-end font-semibold">
                                        @if ($isGift)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                                🎁 {{ __('product::book.gift') }}
                                            </span>
                                        @else
                                            <span
                                                class="text-gray-900">{{ number_format($item->line_total, 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        {{-- Invoice-level discount is on the whole invoice, not this item --}}
                                        @if ($item->invoice_discount > 0)
                                            <span class="inline-flex items-center gap-1 text-xs text-orange-500"
                                                title="{{ __('product::book.invoice_discount_tooltip') }}">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z" />
                                                </svg>
                                                {{ number_format($item->invoice_discount, 2) }}
                                            </span>
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                            {{ __('finance::invoice.statuses.' . $item->invoice_status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        @if ($salesItems->count() > 1)
                            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                                <tr>
                                    <td colspan="3"
                                        class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">
                                        {{ __('product::book.page_total') }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-gray-700">
                                        {{ number_format($salesItems->sum('quantity')) }}
                                    </td>
                                    <td class="px-4 py-3"></td>
                                    <td class="px-4 py-3 text-end font-bold text-gray-900">
                                        {{ number_format($salesItems->sum('line_total'), 2) }}
                                    </td>
                                    <td colspan="2" class="px-4 py-3"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                @if ($salesItems->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $salesItems->links() }}
                    </div>
                @endif
            @else
                <div class="p-12 text-center">
                    <svg class="mx-auto w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-gray-400 text-sm">{{ __('product::book.no_sales_yet') }}</p>
                </div>
            @endif
        </div>

    </div>
</x-dashboard>
