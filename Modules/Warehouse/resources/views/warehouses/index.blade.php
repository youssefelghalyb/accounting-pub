<x-dashboard :pageTitle="__('warehouse::warehouse.module_name')">
    @php
        $tableData = $warehouses->map(function($warehouse) {
            return [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'description' => $warehouse->description,
                'sub_warehouses_count' => $warehouse->subWarehouses->count(),
                'created_at' => $warehouse->created_at->format('Y-m-d'),
                'model' => $warehouse
            ];
        })->toArray();

        $tableColumns = [
            [
                'label' => __('warehouse::warehouse.name'),
                'field' => 'name',
                'render' => function($row) {
                    $html = '<div>';
                    $html .= '<p class="font-medium text-gray-900">' . e($row['name']) . '</p>';
                    if ($row['description']) {
                        $html .= '<p class="text-xs text-gray-500">' . e(Str::limit($row['description'], 50)) . '</p>';
                    }
                    $html .= '</div>';
                    return $html;
                }
            ],
            [
                'label' => __('warehouse::warehouse.total_sub_warehouses'),
                'field' => 'sub_warehouses_count',
                'format' => function($value) {
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">' . $value . '</span>';
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

        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('warehouse.warehouses.show', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600'
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('warehouse.warehouses.edit', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600'
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('warehouse.warehouses.destroy', $row['model']),
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
                'placeholder' => __('warehouse::warehouse.search'),
                'value' => request('search')
            ]
        ];

        $tableStats = [
            [
                'label' => __('warehouse::warehouse.total_warehouses'),
                'value' => $stats['total_warehouses'],
                'color' => 'blue'
            ],
            [
                'label' => __('warehouse::warehouse.total_sub_warehouses'),
                'value' => $stats['total_sub_warehouses'],
                'color' => 'green'
            ],
            [
                'label' => __('warehouse::warehouse.total_stock'),
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
        :createRoute="route('warehouse.warehouses.create')"
        :createLabel="__('warehouse::warehouse.add_warehouse')"
    />
</x-dashboard>
