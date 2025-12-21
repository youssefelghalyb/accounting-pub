<x-dashboard :pageTitle="__('warehouse::sub_warehouse.module_name')">
    @php
        $tableData = $subWarehouses->map(function($subWarehouse) {
            return [
                'id' => $subWarehouse->id,
                'name' => $subWarehouse->name,
                'warehouse_name' => $subWarehouse->warehouse->name,
                'type' => $subWarehouse->type,
                'address' => $subWarehouse->address,
                'country' => $subWarehouse->country,
                'products_count' => $subWarehouse->products->count(),
                'created_at' => $subWarehouse->created_at->format('Y-m-d'),
                'model' => $subWarehouse
            ];
        })->toArray();

        $tableColumns = [
            [
                'label' => __('warehouse::sub_warehouse.name'),
                'field' => 'name',
                'render' => function($row) {
                    $html = '<div>';
                    $html .= '<p class="font-medium text-gray-900">' . e($row['name']) . '</p>';
                    $html .= '<p class="text-xs text-gray-500">' . e($row['warehouse_name']) . '</p>';
                    $html .= '</div>';
                    return $html;
                }
            ],
            [
                'label' => __('warehouse::sub_warehouse.type'),
                'field' => 'type',
                'render' => function($row) {
                    $colors = [
                        'main' => 'blue',
                        'branch' => 'green',
                        'book_fair' => 'purple',
                        'temporary' => 'yellow',
                        'other' => 'gray',
                    ];
                    $color = $colors[$row['type']] ?? 'gray';
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-' . $color . '-100 text-' . $color . '-800">' . __('warehouse::sub_warehouse.' . $row['type']) . '</span>';
                }
            ],
            [
                'label' => __('warehouse::sub_warehouse.total_products'),
                'field' => 'products_count',
                'format' => function($value) {
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">' . $value . '</span>';
                }
            ],
            [
                'label' => __('warehouse::sub_warehouse.country'),
                'field' => 'country',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-600">' . ($value ?: '-') . '</span>';
                }
            ]
        ];

        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('warehouse.sub_warehouses.show', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600'
            ],
            // [
            //     'type' => 'link',
            //     'label' => __('warehouse::sub_warehouse.add_stock'),
            //     'route' => fn($row) => route('warehouse.sub_warehouses.add_stock', $row['model']),
            //     'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>',
            //     'color' => 'text-purple-600'
            // ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('warehouse.sub_warehouses.edit', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600'
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('warehouse.sub_warehouses.destroy', $row['model']),
                'method' => 'DELETE',
                'confirm' => __('common.are_you_sure'),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                'color' => 'text-red-600'
            ]
        ];

        $tableFilters = [
            [
                'type' => 'search',
                'name' => 'search',
                'placeholder' => __('warehouse::sub_warehouse.search'),
                'value' => request('search')
            ]
        ];

        $tableStats = [
            [
                'label' => __('warehouse::sub_warehouse.total_sub_warehouses'),
                'value' => $stats['total_sub_warehouses'],
                'color' => 'blue'
            ],
            [
                'label' => __('warehouse::sub_warehouse.total_products'),
                'value' => $stats['total_products'],
                'color' => 'green'
            ],
            [
                'label' => __('warehouse::sub_warehouse.total_quantity'),
                'value' => number_format($stats['total_stock']),
                'color' => 'purple'
            ]
        ];
    @endphp

    <x-dashboard.packages.data-table
        :columns="$tableColumns"
        :data="$tableData"
        :actions="$tableActions"
        :filters="$tableFilters"
        :stats="$tableStats"
        :createRoute="route('warehouse.sub_warehouses.create')"
        :createLabel="__('warehouse::sub_warehouse.add_sub_warehouse')"
    />
</x-dashboard>
