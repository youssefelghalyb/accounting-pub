@props([
    'paginator',
    'searchRoute'    => '',
    'filters'        => [],
    'showPerPage'    => false,
    'perPageOptions' => [10, 25, 50, 100],
])

@php
    $isRtl       = app()->getLocale() === 'ar';
    $currentPage = $paginator->currentPage();
    $lastPage    = $paginator->lastPage();
    $total       = $paginator->total();
    $from        = $paginator->firstItem() ?? 0;
    $to          = $paginator->lastItem()  ?? 0;
    $perPage     = $paginator->perPage();

    $chevronLeft  = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>';
    $chevronRight = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>';

    // In RTL: previous = chevron right, next = chevron left (reversed reading direction)
    $prevIcon = $isRtl ? $chevronRight : $chevronLeft;
    $nextIcon = $isRtl ? $chevronLeft  : $chevronRight;

    // Build page window: first, last, current ±2
    $window = collect();
    for ($i = 1; $i <= $lastPage; $i++) {
        if ($i === 1 || $i === $lastPage || abs($i - $currentPage) <= 2) {
            $window->push($i);
        }
    }

    // Inject '...' gaps between non-consecutive pages
    $pages = collect();
    $prev  = null;
    foreach ($window as $page) {
        if ($prev !== null && $page - $prev > 1) {
            $pages->push('...');
        }
        $pages->push($page);
        $prev = $page;
    }
@endphp

@if ($paginator->hasPages() || $showPerPage)
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-1">

        {{-- ── Left: results summary + per-page selector ── --}}
        <div class="flex items-center gap-3">

            <p class="text-sm text-gray-500">
                {{ __('common.showing') }}
                <span class="font-semibold text-gray-800">{{ number_format($from) }}</span>
                {{ __('common.to') }}
                <span class="font-semibold text-gray-800">{{ number_format($to) }}</span>
                {{ __('common.of') }}
                <span class="font-semibold text-gray-800">{{ number_format($total) }}</span>
                {{ __('common.results') }}
            </p>

            @if ($showPerPage && $searchRoute)
                <form method="GET" action="{{ $searchRoute }}" class="flex items-center gap-1.5">
                    @if (request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @foreach ($filters as $filter)
                        @if (request($filter['name']))
                            <input type="hidden" name="{{ $filter['name'] }}" value="{{ request($filter['name']) }}">
                        @endif
                    @endforeach

                    <label class="text-xs text-gray-400 whitespace-nowrap">
                        {{ __('common.per_page') }}
                    </label>
                    <select
                        name="per_page"
                        onchange="this.form.submit()"
                        class="h-8 px-2 text-xs font-medium text-gray-700 bg-white border border-gray-200
                               rounded-lg shadow-sm cursor-pointer focus:outline-none focus:ring-2
                               focus:ring-blue-500 focus:border-blue-500 hover:border-gray-300 transition-colors"
                    >
                        @foreach ($perPageOptions as $option)
                            <option
                                value="{{ $option }}"
                                {{ (int) request('per_page', $perPage) === (int) $option ? 'selected' : '' }}
                            >{{ $option }}</option>
                        @endforeach
                    </select>
                </form>
            @endif

        </div>

        {{-- ── Right: page navigation ── --}}
        @if ($paginator->hasPages())
            <nav class="flex items-center gap-1" aria-label="{{ __('common.page') }}">

                {{-- Previous --}}
                @if ($paginator->onFirstPage())
                    <span class="pagination-btn opacity-40 cursor-not-allowed" aria-disabled="true">
                        {!! $prevIcon !!}
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}"
                       class="pagination-btn hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200"
                       aria-label="{{ __('common.previous') }}"
                    >{!! $prevIcon !!}</a>
                @endif

                {{-- Page numbers --}}
                @foreach ($pages as $page)
                    @if ($page === '...')
                        <span class="flex items-center justify-center w-9 h-9 text-sm text-gray-400 select-none">
                            &hellip;
                        </span>
                    @elseif ($page === $currentPage)
                        <span
                            class="flex items-center justify-center w-9 h-9 rounded-lg text-sm font-semibold
                                   bg-blue-600 text-white shadow-sm shadow-blue-200 cursor-default"
                            aria-current="page"
                        >{{ $page }}</span>
                    @else
                        <a href="{{ $paginator->url($page) }}"
                           class="pagination-btn hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200"
                        >{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}"
                       class="pagination-btn hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200"
                       aria-label="{{ __('common.next') }}"
                    >{!! $nextIcon !!}</a>
                @else
                    <span class="pagination-btn opacity-40 cursor-not-allowed" aria-disabled="true">
                        {!! $nextIcon !!}
                    </span>
                @endif

            </nav>
        @endif

    </div>
@endif

@once
    <style>
        .pagination-btn {
            display:         flex;
            align-items:     center;
            justify-content: center;
            width:           2.25rem;
            height:          2.25rem;
            border-radius:   0.5rem;
            font-size:       0.875rem;
            font-weight:     500;
            color:           #374151;
            background:      #ffffff;
            border:          1px solid #e5e7eb;
            transition:      background 150ms, color 150ms, border-color 150ms, box-shadow 150ms;
            text-decoration: none;
        }
        .pagination-btn:focus-visible {
            outline:        2px solid #3b82f6;
            outline-offset: 2px;
        }
    </style>
@endonce