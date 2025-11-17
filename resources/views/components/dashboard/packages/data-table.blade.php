@props([
    'title' => '',
    'description' => '',
    'data' => [],
    'columns' => [],
    'actions' => [],
    'bulkActions' => [],
    'searchable' => false,
    'searchPlaceholder' => __('common.search'),
    'searchRoute' => '',
    'filters' => [],
    'createRoute' => '',
    'createLabel' => __('common.add'),
    'emptyStateTitle' => __('common.no_data'),
    'emptyStateDescription' => __('common.no_results'),
    'emptyStateIcon' => 'default',
    'pagination' => null,
    'perPage' => [10, 25, 50, 100],
    'showPerPage' => false,
    'striped' => true,
    'bordered' => true,
    'hover' => true,
    'compact' => false,
    'rtl' => null,
])

@php
    $isRtl = $rtl ?? (app()->getLocale() == 'ar');
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <!-- Header -->
    <div class="p-6 border-b border-gray-200">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Title and Description -->
            <div>
                @if($title)
                    <h2 class="text-xl font-bold text-gray-900">{{ $title }}</h2>
                @endif
                @if($description)
                    <p class="text-sm text-gray-600 mt-1">{{ $description }}</p>
                @endif
            </div>

            <!-- Actions Bar -->
            <div class="flex flex-col sm:flex-row gap-3">
                <!-- Search -->
                @if($searchable && $searchRoute)
                    <form method="GET" action="{{ $searchRoute }}" class="flex gap-2">
                        @foreach($filters as $filter)
                            @if(request($filter['name']))
                                <input type="hidden" name="{{ $filter['name'] }}" value="{{ request($filter['name']) }}">
                            @endif
                        @endforeach
                        
                        <div class="relative">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="{{ $searchPlaceholder }}"
                                   class="w-full sm:w-64 px-4 py-2 {{ $isRtl ? 'pl-10' : 'pr-10' }} border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <button type="submit" class="absolute {{ $isRtl ? 'left-3' : 'right-3' }} top-2.5 text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                @endif

                <!-- Filters -->
                @foreach($filters as $filter)
                    @if($filter['type'] === 'select')
                        <form method="GET" action="{{ $searchRoute }}" class="flex gap-2">
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            
                            @foreach($filters as $otherFilter)
                                @if($otherFilter['name'] !== $filter['name'] && request($otherFilter['name']))
                                    <input type="hidden" name="{{ $otherFilter['name'] }}" value="{{ request($otherFilter['name']) }}">
                                @endif
                            @endforeach
                            
                            <select name="{{ $filter['name'] }}" 
                                    onchange="this.form.submit()"
                                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">{{ $filter['label'] }}</option>
                                @foreach($filter['options'] as $option)
                                    <option value="{{ $option['value'] }}" {{ request($filter['name']) == $option['value'] ? 'selected' : '' }}>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                @endforeach

                <!-- Create Button -->
                @if($createRoute)
                    <a href="{{ $createRoute }}" 
                       class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors whitespace-nowrap text-sm font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        {{ $createLabel }}
                    </a>
                @endif
            </div>
        </div>

        <!-- Bulk Actions -->
        @if(count($bulkActions) > 0)
            <div class="mt-4 pt-4 border-t border-gray-200" id="bulkActionsBar" style="display: none;">
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-600" id="selectedCount">0 {{ __('common.selected') }}</span>
                    <select id="bulkActionSelect" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                        <option value="">{{ __('common.select_action') }}</option>
                        @foreach($bulkActions as $action)
                            <option value="{{ $action['value'] }}">{{ $action['label'] }}</option>
                        @endforeach
                    </select>
                    <button type="button" 
                            onclick="executeBulkAction()"
                            class="px-4 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        {{ __('common.apply') }}
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        @if(count($data) > 0)
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        @if(count($bulkActions) > 0)
                            <th class="px-6 py-3 text-center w-12">
                                <input type="checkbox" 
                                       id="selectAll"
                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                       onchange="toggleAllCheckboxes(this)">
                            </th>
                        @endif
                        
                        @foreach($columns as $column)
                            <th class="px-6 py-3 text-{{ $column['align'] ?? 'start' }} text-xs font-semibold text-gray-600 uppercase tracking-wider {{ $column['class'] ?? '' }}">
                                {{ $column['label'] }}
                            </th>
                        @endforeach
                        
                        @if(count($actions) > 0)
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                {{ __('common.actions') }}
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($data as $index => $row)
                        <tr class="{{ $hover ? 'hover:bg-gray-50' : '' }} transition-colors">
                            @if(count($bulkActions) > 0)
                                <td class="px-6 py-4 text-center">
                                    <input type="checkbox" 
                                           class="row-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                           value="{{ $row['id'] ?? $index }}"
                                           onchange="updateBulkActionsBar()">
                                </td>
                            @endif
                            
                            @foreach($columns as $column)
                                <td class="px-6 py-4 {{ $column['nowrap'] ?? true ? 'whitespace-nowrap' : '' }} text-{{ $column['align'] ?? 'start' }}">
                                    @php
                                        if (isset($column['render'])) {
                                            echo $column['render']($row);
                                        } else {
                                            $value = data_get($row, $column['field']);
                                            if (isset($column['format'])) {
                                                echo $column['format']($value, $row);
                                            } else {
                                                echo e($value);
                                            }
                                        }
                                    @endphp
                                </td>
                            @endforeach
                            
                            @if(count($actions) > 0)
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @foreach($actions as $action)
                                            @php
                                                $show = !isset($action['show']) || $action['show']($row);
                                            @endphp
                                            
                                            @if($show)
                                                @if($action['type'] === 'link')
                                                    <a href="{{ $action['route']($row) }}" 
                                                       class="inline-flex items-center justify-center w-8 h-8 {{ $action['color'] ?? 'text-blue-600' }} hover:bg-{{ str_replace('text-', '', $action['color'] ?? 'blue') }}-50 rounded-lg transition-colors"
                                                       title="{{ $action['label'] }}">
                                                        @php echo $action['icon']; @endphp
                                                    </a>
                                                @elseif($action['type'] === 'form')
                                                    <form action="{{ $action['route']($row) }}" 
                                                          method="POST" 
                                                          class="inline-block"
                                                          onsubmit="return confirm('{{ $action['confirm'] ?? __('common.are_you_sure') }}')">
                                                        @csrf
                                                        @if($action['method'] ?? 'POST' !== 'POST')
                                                            @method($action['method'])
                                                        @endif
                                                        <button type="submit" 
                                                                class="inline-flex items-center justify-center w-8 h-8 {{ $action['color'] ?? 'text-red-600' }} hover:bg-{{ str_replace('text-', '', $action['color'] ?? 'red') }}-50 rounded-lg transition-colors"
                                                                title="{{ $action['label'] }}">
                                                            @php echo $action['icon']; @endphp
                                                        </button>
                                                    </form>
                                                @elseif($action['type'] === 'button')
                                                    <button type="button"
                                                            onclick="{{ $action['onclick']($row) }}"
                                                            class="inline-flex items-center justify-center w-8 h-8 {{ $action['color'] ?? 'text-gray-600' }} hover:bg-gray-50 rounded-lg transition-colors"
                                                            title="{{ $action['label'] }}">
                                                        @php echo $action['icon']; @endphp
                                                    </button>
                                                @endif
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            @if($pagination)
                <div class="px-6 py-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-gray-600">
                            {{ __('common.showing') }} 
                            <span class="font-medium">{{ $pagination->firstItem() }}</span> 
                            {{ __('common.to') }} 
                            <span class="font-medium">{{ $pagination->lastItem() }}</span> 
                            {{ __('common.of') }} 
                            <span class="font-medium">{{ $pagination->total() }}</span> 
                            {{ __('common.results') }}
                        </div>
                        
                        <div>
                            {{ $pagination->links() }}
                        </div>
                    </div>
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                @if($emptyStateIcon === 'users')
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                @elseif($emptyStateIcon === 'document')
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                @elseif($emptyStateIcon === 'folder')
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                @elseif($emptyStateIcon === 'calendar')
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                @else
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                @endif
                
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $emptyStateTitle }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ $emptyStateDescription }}</p>
                
                @if($createRoute)
                    <div class="mt-6">
                        <a href="{{ $createRoute }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            {{ $createLabel }}
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

@if(count($bulkActions) > 0)
    @push('scripts')
    <script>
        function toggleAllCheckboxes(source) {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = source.checked;
            });
            updateBulkActionsBar();
        }

        function updateBulkActionsBar() {
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            const bulkActionsBar = document.getElementById('bulkActionsBar');
            const selectedCount = document.getElementById('selectedCount');
            
            if (checkboxes.length > 0) {
                bulkActionsBar.style.display = 'block';
                selectedCount.textContent = checkboxes.length + ' {{ __('common.selected') }}';
            } else {
                bulkActionsBar.style.display = 'none';
            }
            
            // Update select all checkbox
            const selectAll = document.getElementById('selectAll');
            const allCheckboxes = document.querySelectorAll('.row-checkbox');
            selectAll.checked = checkboxes.length === allCheckboxes.length;
        }

        function executeBulkAction() {
            const action = document.getElementById('bulkActionSelect').value;
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            const ids = Array.from(checkboxes).map(cb => cb.value);
            
            if (!action) {
                alert('{{ __('common.select_action') }}');
                return;
            }
            
            if (ids.length === 0) {
                alert('{{ __('common.no_items_selected') }}');
                return;
            }
            
            // You can emit an event or call a function here
            window.dispatchEvent(new CustomEvent('bulk-action', {
                detail: { action, ids }
            }));
        }
    </script>
    @endpush
@endif