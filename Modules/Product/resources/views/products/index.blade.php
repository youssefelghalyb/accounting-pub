<x-dashboard :pageTitle="__('product::product.module_name')">
    @php
        // Prepare data array
        $tableData = $products->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'type' => $product->type,
                'sku' => $product->sku,
                'base_price' => $product->base_price,
                'status' => $product->status,
                'created_at' => $product->created_at->format('Y-m-d'),
                'model' => $product
            ];
        })->toArray();

        // Prepare columns array
        $tableColumns = [
            [
                'label' => __('product::product.name'),
                'field' => 'name',
                'render' => function($row) {
                    $html = '<div>';
                    $html .= '<p class="font-medium text-gray-900">' . e($row['name']) . '</p>';
                    if ($row['sku']) {
                        $html .= '<p class="text-xs text-gray-500">SKU: ' . e($row['sku']) . '</p>';
                    }
                    $html .= '</div>';
                    return $html;
                }
            ],
            [
                'label' => __('product::product.type'),
                'field' => 'type',
                'render' => function($row) {
                    $colors = [
                        'book' => 'bg-blue-100 text-blue-800',
                        'ebook' => 'bg-purple-100 text-purple-800',
                        'journal' => 'bg-green-100 text-green-800',
                        'course' => 'bg-orange-100 text-orange-800',
                        'bundle' => 'bg-pink-100 text-pink-800',
                    ];
                    $color = $colors[$row['type']] ?? 'bg-gray-100 text-gray-800';
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $color . '">' . __('product::product.' . $row['type']) . '</span>';
                }
            ],
            [
                'label' => __('product::product.base_price'),
                'field' => 'base_price',
                'format' => function($value) {
                    return '<span class="font-medium text-gray-900">' . number_format($value, 2) . '</span>';
                }
            ],
            [
                'label' => __('product::product.status'),
                'field' => 'status',
                'render' => function($row) {
                    $statusColor = $row['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $statusColor . '">' . __('product::product.' . $row['status']) . '</span>';
                }
            ],
            [
                'label' => __('common.created_at'),
                'field' => 'created_at',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-600">' . $value . '</span>';
                }
            ]
        ];

        // Prepare actions array
        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('product.products.show', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600'
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('product.products.edit', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600'
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('product.products.destroy', $row['model']),
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
                'name' => 'type',
                'label' => __('product::product.all_types'),
                'options' => [
                    ['value' => 'book', 'label' => __('product::product.book')],
                    ['value' => 'ebook', 'label' => __('product::product.ebook')],
                    ['value' => 'journal', 'label' => __('product::product.journal')],
                    ['value' => 'course', 'label' => __('product::product.course')],
                    ['value' => 'bundle', 'label' => __('product::product.bundle')],
                ]
            ],
            [
                'type' => 'select',
                'name' => 'status',
                'label' => __('product::product.all_statuses'),
                'options' => [
                    ['value' => 'active', 'label' => __('product::product.active')],
                    ['value' => 'inactive', 'label' => __('product::product.inactive')],
                ]
            ]
        ];
    @endphp

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Products -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::product.total_products') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_products'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Products -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::product.active_products') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['active_products'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Value -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::product.total_value') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_value'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <x-dashboard.packages.data-table
            :title="__('product::product.product_list')"
            :description="__('product::product.total_products') . ': ' . $products->count()"
            searchable
            :searchRoute="route('product.products.index')"
            :searchPlaceholder="__('product::product.search')"
            :filters="$tableFilters"
            :data="$tableData"
            :columns="$tableColumns"
            :actions="$tableActions"
            :createRoute="route('product.products.create')"
            :createLabel="__('product::product.add_product')"
            :emptyStateTitle="__('product::product.no_products')"
            :emptyStateDescription="__('common.no_data')"
            emptyStateIcon="document"
        />
    </div>
</x-dashboard>
