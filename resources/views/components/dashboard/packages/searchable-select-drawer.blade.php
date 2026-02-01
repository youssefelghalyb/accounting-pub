<!-- Searchable Select Drawer -->
<div id="{{ $drawerId }}" class="fixed inset-0 z-50 hidden" data-drawer-id="{{ $drawerId }}">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black bg-opacity-50 transition-opacity" 
         onclick="window.drawerClose_{{ $drawerId }}()"></div>
    
    <!-- Drawer -->
    <div class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} top-0 h-full w-full max-w-2xl bg-white shadow-xl transform transition-transform" 
         id="{{ $drawerId }}Content">
        
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-900">{{ $title }}</h3>
            <button type="button" onclick="window.drawerClose_{{ $drawerId }}()" 
                    class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Filters -->
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <div class="grid grid-cols-1 {{ count($filters) > 0 ? 'md:grid-cols-3' : '' }} gap-4">
                
                <!-- Search -->
                <div class="{{ count($filters) > 0 ? 'md:col-span-3' : '' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" 
                           id="{{ $drawerId }}_search" 
                           placeholder="{{ $searchPlaceholder }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Dynamic Filters -->
                @foreach($filters as $filter)
                    @if($filter['type'] === 'select')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ $filter['label'] }}</label>
                        <select id="{{ $drawerId }}_filter_{{ $filter['param'] }}" 
                                data-filter-param="{{ $filter['param'] }}"
                                class="drawer-filter w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All {{ $filter['label'] }}</option>
                            @if(isset($filter['options']))
                                @foreach($filter['options'] as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    @endif

                    @if($filter['type'] === 'text')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ $filter['label'] }}</label>
                        <input type="text" 
                               id="{{ $drawerId }}_filter_{{ $filter['param'] }}" 
                               data-filter-param="{{ $filter['param'] }}"
                               placeholder="{{ $filter['placeholder'] ?? '' }}"
                               class="drawer-filter w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Loading State -->
        <div id="{{ $drawerId }}_loading" class="hidden p-6">
            <div class="flex items-center justify-center">
                <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-3 text-gray-600">Loading...</span>
            </div>
        </div>

        <!-- Items List Container -->
        <div id="{{ $drawerId }}_container" class="overflow-y-auto" style="height: calc(100vh - {{ count($filters) > 0 ? '320px' : '200px' }});">
            <div id="{{ $drawerId }}_list" class="p-6 space-y-3">
                <!-- Items will be loaded here -->
            </div>
        </div>

        <!-- Pagination -->
        <div id="{{ $drawerId }}_pagination" class="p-6 border-t border-gray-200 bg-white hidden">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span id="{{ $drawerId }}_showing_from">0</span> to 
                    <span id="{{ $drawerId }}_showing_to">0</span> of 
                    <span id="{{ $drawerId }}_total">0</span> results
                </div>
                <div class="flex gap-2">
                    <button type="button" 
                            id="{{ $drawerId }}_prev_btn"
                            onclick="window.drawerPrevPage_{{ $drawerId }}()"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        Previous
                    </button>
                    <button type="button" 
                            id="{{ $drawerId }}_next_btn"
                            onclick="window.drawerNextPage_{{ $drawerId }}()"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const drawerId = '{{ $drawerId }}';
    const apiUrl = '{{ $apiUrl }}';
    const displayFields = @json($displayFields);
    const onSelectCallback = '{{ $onSelectCallback }}';
    const noResultsText = '{{ $noResultsText }}';
    const perPage = {{ $perPage }};
    const triggerSelector = '{{ $triggerSelector }}';
    const displaySelector = '{{ $displaySelector }}';
    
    let currentPage = 1;
    let lastPage = 1;
    let total = 0;
    let searchTimeout = null;
    let currentContext = null;

    // Validate displayFields
    if (!displayFields || displayFields.length === 0) {
        console.error('SearchableSelectDrawer Error: displayFields is required and cannot be empty');
        return;
    }

    // Setup trigger elements
    if (triggerSelector) {
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest(triggerSelector);
            if (trigger) {
                e.preventDefault();
                
                // Get context from data attribute or closest parent
                const context = trigger.dataset.context || trigger.closest('[data-item]')?.dataset.item || null;
                window['drawerOpen_' + drawerId](context);
            }
        });
    }

    // Open drawer function
    window['drawerOpen_' + drawerId] = function(context = null) {
        document.getElementById(drawerId).classList.remove('hidden');
        document.getElementById(drawerId + '_search').focus();
        
        // Store context if provided
        currentContext = context;
        
        // Reset and load first page
        currentPage = 1;
        loadItems();
    };

    // Close drawer function
    window['drawerClose_' + drawerId] = function() {
        document.getElementById(drawerId).classList.add('hidden');
        
        // Reset filters
        document.getElementById(drawerId + '_search').value = '';
        document.querySelectorAll(`#${drawerId} .drawer-filter`).forEach(filter => {
            filter.value = '';
        });
        
        currentContext = null;
    };

    // Load items from API
    async function loadItems() {
        const loading = document.getElementById(drawerId + '_loading');
        const list = document.getElementById(drawerId + '_list');
        const pagination = document.getElementById(drawerId + '_pagination');
        
        // Show loading
        loading.classList.remove('hidden');
        list.innerHTML = '';
        pagination.classList.add('hidden');
        
        try {
            // Build query parameters
            const params = new URLSearchParams();
            params.append('page', currentPage);
            params.append('per_page', perPage);
            
            // Add search
            const searchValue = document.getElementById(drawerId + '_search').value;
            if (searchValue) {
                params.append('search', searchValue);
            }
            
            // Add filters
            document.querySelectorAll(`#${drawerId} .drawer-filter`).forEach(filter => {
                const value = filter.value;
                const param = filter.dataset.filterParam;
                if (value && param) {
                    params.append(param, value);
                }
            });
            
            // Fetch data
            const response = await fetch(`${apiUrl}?${params.toString()}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            // Hide loading
            loading.classList.add('hidden');
            
            // Render items
            if (data.data && data.data.length > 0) {
                renderItems(data.data);
                updatePagination(data);
            } else {
                renderNoResults();
            }
            
        } catch (error) {
            console.error('Error loading items:', error);
            loading.classList.add('hidden');
            renderError(error.message);
        }
    }

    // Render items
    function renderItems(items) {
        const container = document.getElementById(drawerId + '_list');
        
        if (!items || items.length === 0) {
            renderNoResults();
            return;
        }
        
        container.innerHTML = items.map(item => renderItem(item)).join('');
    }

    // Render single item
    function renderItem(item) {
        // Validate item
        if (!item) {
            console.warn('Invalid item:', item);
            return '';
        }

        // Get primary field (first display field)
        const primaryField = displayFields[0];
        if (!primaryField || !primaryField.field) {
            console.error('Primary display field is not properly configured:', primaryField);
            return '';
        }
        
        const primaryValue = getNestedValue(item, primaryField.field) || 'Unnamed Item';
        
        // Build details HTML from remaining display fields
        let detailsHtml = '';
        if (displayFields.length > 1) {
            displayFields.slice(1).forEach(field => {
                if (!field || !field.field) {
                    console.warn('Invalid display field:', field);
                    return;
                }
                
                const value = getNestedValue(item, field.field);
                
                // Skip if no value
                if (value === null || value === undefined || value === '') {
                    return;
                }
                
                const label = field.label || '';
                const type = field.type || 'text';
                const cssClass = field.class || '';
                
                if (type === 'badge') {
                    const badgeClass = cssClass || 'bg-gray-100 text-gray-800';
                    detailsHtml += `<span class="inline-block px-2 py-1 text-xs rounded-full ${badgeClass}">${value}</span>`;
                } else if (type === 'price') {
                    const priceClass = cssClass || 'text-gray-600';
                    const formattedPrice = parseFloat(value).toFixed(2);
                    detailsHtml += `<p class="text-xs ${priceClass}">${label}: ${formattedPrice}</p>`;
                } else {
                    // Default: text
                    const textClass = cssClass || 'text-gray-500';
                    detailsHtml += `<p class="text-xs ${textClass}">${label}: ${value}</p>`;
                }
            });
        }

        // Get highlight value if exists
        const highlightValue = item.highlight_value || '';
        const highlightClass = item.highlight_class || 'text-blue-600';

        return `
            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:bg-blue-50 cursor-pointer transition-all"
                 onclick="window.drawerSelect_${drawerId}(${item.id || 0})">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900">${escapeHtml(primaryValue)}</h4>
                        ${detailsHtml ? `<div class="mt-1 space-y-1">${detailsHtml}</div>` : ''}
                    </div>
                    ${highlightValue ? `
                    <div class="text-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} {{ app()->getLocale() == 'ar' ? 'ml-4' : 'mr-4' }}">
                        <p class="text-lg font-bold ${highlightClass}">${escapeHtml(highlightValue)}</p>
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
    }

    // Escape HTML to prevent XSS
    function escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') {
            return unsafe;
        }
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Render no results
    function renderNoResults() {
        const container = document.getElementById(drawerId + '_list');
        container.innerHTML = `
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 12h.01M12 12h.01M12 12h.01M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-500">${noResultsText}</p>
            </div>
        `;
    }

    // Render error
    function renderError(message = 'An error occurred') {
        const container = document.getElementById(drawerId + '_list');
        container.innerHTML = `
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-red-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-500 mb-2">Error loading data</p>
                <p class="text-sm text-gray-400 mb-4">${escapeHtml(message)}</p>
                <button onclick="window.drawerOpen_${drawerId}(currentContext)" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Retry
                </button>
            </div>
        `;
    }

    // Update pagination
    function updatePagination(data) {
        const pagination = document.getElementById(drawerId + '_pagination');
        
        currentPage = data.current_page || 1;
        lastPage = data.last_page || 1;
        total = data.total || 0;
        
        document.getElementById(drawerId + '_showing_from').textContent = data.from || 0;
        document.getElementById(drawerId + '_showing_to').textContent = data.to || 0;
        document.getElementById(drawerId + '_total').textContent = total;
        
        // Update button states
        const prevBtn = document.getElementById(drawerId + '_prev_btn');
        const nextBtn = document.getElementById(drawerId + '_next_btn');
        
        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= lastPage;
        
        pagination.classList.remove('hidden');
    }

    // Get nested object value
    function getNestedValue(obj, path) {
        if (!obj || !path) return null;
        return path.split('.').reduce((current, prop) => current?.[prop], obj);
    }

    // Select item function
    window['drawerSelect_' + drawerId] = async function(itemId) {
        if (!itemId) {
            console.error('Item ID is required');
            return;
        }
        
        // Call the callback function
        if (typeof window[onSelectCallback] === 'function') {
            try {
                await window[onSelectCallback](itemId, currentContext);
            } catch (error) {
                console.error('Error in callback:', error);
            }
        } else {
            console.warn(`Callback function "${onSelectCallback}" is not defined`);
        }
        
        window['drawerClose_' + drawerId]();
    };

    // Pagination functions
    window['drawerNextPage_' + drawerId] = function() {
        if (currentPage < lastPage) {
            currentPage++;
            
            // Scroll to top of list
            const container = document.getElementById(drawerId + '_container');
            if (container) container.scrollTop = 0;
            
            loadItems();
        }
    };

    window['drawerPrevPage_' + drawerId] = function() {
        if (currentPage > 1) {
            currentPage--;
            
            // Scroll to top of list
            const container = document.getElementById(drawerId + '_container');
            if (container) container.scrollTop = 0;
            
            loadItems();
        }
    };

    // Search with debounce
    const searchInput = document.getElementById(drawerId + '_search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentPage = 1;
                loadItems();
            }, 500);
        });
    }
    
    // Filter change events
    document.querySelectorAll(`#${drawerId} .drawer-filter`).forEach(filter => {
        filter.addEventListener('change', function() {
            currentPage = 1;
            loadItems();
        });
    });
})();
</script>
@endpush