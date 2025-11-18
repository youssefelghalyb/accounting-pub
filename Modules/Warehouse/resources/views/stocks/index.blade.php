<x-dashboard :pageTitle="__('warehouse::stocks.module_name')">
    @php
        // Prepare data array
        $tableData = $stocks->map(function($stock) {
            return [
                'id' => $stock->id,
                'product_name' => $stock->product->name,
                'product_sku' => $stock->product->sku,
                'product_type' => $stock->product->type,
                'warehouse_name' => $stock->warehouse_name,
                'location' => $stock->location,
                'quantity' => $stock->quantity,
                'available_quantity' => $stock->available_quantity,
                'minimum_quantity' => $stock->minimum_quantity,
                'stock_level' => $stock->stock_level,
                'status' => $stock->status,
                'model' => $stock
            ];
        })->toArray();

        // Prepare columns array
        $tableColumns = [
            [
                'label' => __('warehouse::stocks.product'),
                'field' => 'product_name',
                'render' => function($row) {
                    $html = '<div>';
                    $html .= '<p class="font-medium text-gray-900">' . e($row['product_name']) . '</p>';
                    if ($row['product_sku']) {
                        $html .= '<p class="text-xs text-gray-500">SKU: ' . e($row['product_sku']) . '</p>';
                    }
                    $typeColors = [
                        'book' => 'bg-blue-50 text-blue-700',
                        'ebook' => 'bg-purple-50 text-purple-700',
                        'journal' => 'bg-green-50 text-green-700',
                        'course' => 'bg-orange-50 text-orange-700',
                        'bundle' => 'bg-pink-50 text-pink-700',
                    ];
                    $color = $typeColors[$row['product_type']] ?? 'bg-gray-50 text-gray-700';
                    $html .= '<span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium rounded ' . $color . '">' . ucfirst($row['product_type']) . '</span>';
                    $html .= '</div>';
                    return $html;
                }
            ],
            [
                'label' => __('warehouse::stocks.warehouse'),
                'field' => 'warehouse_name',
                'render' => function($row) {
                    $html = '<div>';
                    $html .= '<p class="font-medium text-gray-900">' . e($row['warehouse_name']) . '</p>';
                    if ($row['location']) {
                        $html .= '<p class="text-xs text-gray-500">' . e($row['location']) . '</p>';
                    }
                    $html .= '</div>';
                    return $html;
                }
            ],
            [
                'label' => __('warehouse::stocks.quantity'),
                'field' => 'quantity',
                'render' => function($row) {
                    $levelColors = [
                        'out_of_stock' => 'text-red-600 font-bold',
                        'low_stock' => 'text-orange-600 font-semibold',
                        'in_stock' => 'text-green-600'
                    ];
                    $color = $levelColors[$row['stock_level']] ?? 'text-gray-900';
                    $html = '<div>';
                    $html .= '<p class="' . $color . '">' . number_format($row['quantity']) . '</p>';
                    $html .= '<p class="text-xs text-gray-500">Min: ' . number_format($row['minimum_quantity']) . '</p>';
                    $html .= '</div>';
                    return $html;
                }
            ],
            [
                'label' => __('warehouse::stocks.stock_level'),
                'field' => 'stock_level',
                'render' => function($row) {
                    $statusClasses = [
                        'out_of_stock' => 'bg-red-100 text-red-800',
                        'low_stock' => 'bg-orange-100 text-orange-800',
                        'in_stock' => 'bg-green-100 text-green-800',
                    ];
                    $class = $statusClasses[$row['stock_level']] ?? 'bg-gray-100 text-gray-800';
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $class . '">' . __('warehouse::stocks.' . $row['stock_level']) . '</span>';
                }
            ],
            [
                'label' => __('warehouse::stocks.status'),
                'field' => 'status',
                'render' => function($row) {
                    $statusColor = $row['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $statusColor . '">' . __('warehouse::stocks.' . $row['status']) . '</span>';
                }
            ]
        ];

        // Prepare actions array
        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('warehouse.stocks.show', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600'
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('warehouse.stocks.edit', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600'
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('warehouse.stocks.destroy', $row['model']),
                'method' => 'DELETE',
                'confirm' => __('common.are_you_sure'),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                'color' => 'text-red-600'
            ]
        ];

        // Prepare filters array
        $tableFilters = [
            [
                'type' => 'select',
                'name' => 'warehouse',
                'label' => __('warehouse::stocks.all_warehouses'),
                'options' => $warehouses->map(function($w) {
                    return ['value' => $w, 'label' => $w];
                })->toArray()
            ],
            [
                'type' => 'select',
                'name' => 'stock_level',
                'label' => __('warehouse::stocks.all_levels'),
                'options' => [
                    ['value' => 'low', 'label' => __('warehouse::stocks.low_stock')],
                    ['value' => 'out', 'label' => __('warehouse::stocks.out_of_stock')],
                ]
            ],
            [
                'type' => 'select',
                'name' => 'status',
                'label' => __('warehouse::stocks.all_statuses'),
                'options' => [
                    ['value' => 'active', 'label' => __('warehouse::stocks.active')],
                    ['value' => 'inactive', 'label' => __('warehouse::stocks.inactive')],
                ]
            ]
        ];
    @endphp

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <!-- Total Stocks -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('warehouse::stocks.total_stocks') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_stocks'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Stocks -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('warehouse::stocks.active_stocks') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['active_stocks'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Low Stock -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('warehouse::stocks.low_stock_items') }}</p>
                        <p class="text-3xl font-bold text-orange-900 mt-2">{{ $stats['low_stock_items'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Out of Stock -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('warehouse::stocks.out_of_stock_items') }}</p>
                        <p class="text-3xl font-bold text-red-900 mt-2">{{ $stats['out_of_stock_items'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Quantity -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('warehouse::stocks.total_quantity') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_quantity']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <x-dashboard.packages.data-table
            :data="$tableData"
            :columns="$tableColumns"
            :actions="$tableActions"
            :filters="$tableFilters"
            :addButton="[
                'label' => __('warehouse::stocks.add_stock'),
                'route' => route('warehouse.stocks.create')
            ]"
            searchPlaceholder="{{ __('warehouse::stocks.search') }}"
        />
    </div>
</x-dashboard>
