<x-dashboard :pageTitle="__('warehouse::stock_movement.module_name')">
    @php
        $tableData = $movements->map(function($movement) {
            return [
                'id' => $movement->id,
                'product_name' => $movement->product->name,
                'from_warehouse' => $movement->fromSubWarehouse ? $movement->fromSubWarehouse->name : '-',
                'to_warehouse' => $movement->toSubWarehouse ? $movement->toSubWarehouse->name : '-',
                'quantity' => $movement->quantity,
                'movement_type' => $movement->movement_type,
                'reason' => $movement->reason,
                'created_at' => $movement->created_at->format('Y-m-d H:i'),
                'model' => $movement
            ];
        })->toArray();

        $tableColumns = [
            [
                'label' => __('warehouse::stock_movement.product'),
                'field' => 'product_name',
                'render' => function($row) {
                    return '<p class="font-medium text-gray-900">' . e($row['product_name']) . '</p>';
                }
            ],
            [
                'label' => __('warehouse::stock_movement.movement_type'),
                'field' => 'movement_type',
                'render' => function($row) {
                    $colors = [
                        'transfer' => 'blue',
                        'inbound' => 'green',
                        'outbound' => 'red',
                    ];
                    $color = $colors[$row['movement_type']] ?? 'gray';
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-' . $color . '-100 text-' . $color . '-800">' . __('warehouse::stock_movement.' . $row['movement_type']) . '</span>';
                }
            ],
            [
                'label' => __('warehouse::stock_movement.from_sub_warehouse'),
                'field' => 'from_warehouse',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-600">' . e($value) . '</span>';
                }
            ],
            [
                'label' => __('warehouse::stock_movement.to_sub_warehouse'),
                'field' => 'to_warehouse',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-600">' . e($value) . '</span>';
                }
            ],
            [
                'label' => __('warehouse::stock_movement.quantity'),
                'field' => 'quantity',
                'format' => function($value) {
                    return '<span class="font-medium text-gray-900">' . number_format($value) . '</span>';
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
                'route' => fn($row) => route('warehouse.stock_movements.show', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600'
            ]
        ];

        $tableFilters = [
            [
                'type' => 'search',
                'name' => 'search',
                'placeholder' => __('warehouse::stock_movement.search'),
                'value' => request('search')
            ]
        ];

        $tableStats = [
            [
                'label' => __('warehouse::stock_movement.total_movements'),
                'value' => $stats['total_movements'],
                'color' => 'blue'
            ],
            [
                'label' => __('warehouse::stock_movement.total_transfers'),
                'value' => $stats['total_transfers'],
                'color' => 'purple'
            ],
            [
                'label' => __('warehouse::stock_movement.total_inbound'),
                'value' => number_format($stats['total_inbound']),
                'color' => 'green'
            ],
            [
                'label' => __('warehouse::stock_movement.total_outbound'),
                'value' => number_format($stats['total_outbound']),
                'color' => 'red'
            ]
        ];
    @endphp

    <x-dashboard.packages.data-table
        :columns="$tableColumns"
        :data="$tableData"
        :actions="$tableActions"
        :filters="$tableFilters"
        :stats="$tableStats"
        :createRoute="route('warehouse.stock_movements.create')"
        :createLabel="__('warehouse::stock_movement.create_movements')"
    />
</x-dashboard>
