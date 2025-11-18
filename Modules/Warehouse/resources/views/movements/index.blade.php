<x-dashboard :pageTitle="__('warehouse::movements.module_name')">
    @php
        // Prepare data array
        $tableData = $movements->map(function($movement) {
            return [
                'id' => $movement->id,
                'reference_number' => $movement->reference_number,
                'type' => $movement->type,
                'movement_date' => $movement->movement_date->format('Y-m-d'),
                'source_warehouse' => $movement->source_warehouse,
                'destination_warehouse' => $movement->destination_warehouse,
                'total_items' => $movement->total_items,
                'status' => $movement->status,
                'model' => $movement
            ];
        })->toArray();

        // Prepare columns array
        $tableColumns = [
            [
                'label' => __('warehouse::movements.reference_number'),
                'field' => 'reference_number',
                'render' => function($row) {
                    return '<p class="font-medium text-gray-900 font-mono">' . e($row['reference_number']) . '</p>';
                }
            ],
            [
                'label' => __('warehouse::movements.type'),
                'field' => 'type',
                'render' => function($row) {
                    $colors = [
                        'in' => 'bg-blue-100 text-blue-800',
                        'out' => 'bg-orange-100 text-orange-800',
                        'transfer' => 'bg-purple-100 text-purple-800',
                        'adjustment' => 'bg-gray-100 text-gray-800',
                    ];
                    $color = $colors[$row['type']] ?? 'bg-gray-100 text-gray-800';
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $color . '">' . __('warehouse::movements.type_' . $row['type']) . '</span>';
                }
            ],
            [
                'label' => __('warehouse::movements.warehouses'),
                'field' => 'source_warehouse',
                'render' => function($row) {
                    $html = '<div class="text-sm">';
                    if ($row['source_warehouse']) {
                        $html .= '<p class="text-gray-600">From: <span class="font-medium text-gray-900">' . e($row['source_warehouse']) . '</span></p>';
                    }
                    if ($row['destination_warehouse']) {
                        $html .= '<p class="text-gray-600">To: <span class="font-medium text-gray-900">' . e($row['destination_warehouse']) . '</span></p>';
                    }
                    $html .= '</div>';
                    return $html;
                }
            ],
            [
                'label' => __('warehouse::movements.total_items'),
                'field' => 'total_items',
                'render' => function($row) {
                    return '<span class="font-medium text-gray-900">' . $row['total_items'] . '</span>';
                }
            ],
            [
                'label' => __('warehouse::movements.movement_date'),
                'field' => 'movement_date',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-600">' . $value . '</span>';
                }
            ],
            [
                'label' => __('warehouse::movements.status'),
                'field' => 'status',
                'render' => function($row) {
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'completed' => 'bg-green-100 text-green-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                    ];
                    $color = $statusColors[$row['status']] ?? 'bg-gray-100 text-gray-800';
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $color . '">' . __('warehouse::movements.status_' . $row['status']) . '</span>';
                }
            ]
        ];

        // Prepare actions array
        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('warehouse.movements.show', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600'
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('warehouse.movements.edit', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600',
                'condition' => fn($row) => $row['status'] === 'pending'
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('warehouse.movements.destroy', $row['model']),
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
                'label' => __('warehouse::movements.all_types'),
                'options' => [
                    ['value' => 'in', 'label' => __('warehouse::movements.type_in')],
                    ['value' => 'out', 'label' => __('warehouse::movements.type_out')],
                    ['value' => 'transfer', 'label' => __('warehouse::movements.type_transfer')],
                    ['value' => 'adjustment', 'label' => __('warehouse::movements.type_adjustment')],
                ]
            ],
            [
                'type' => 'select',
                'name' => 'status',
                'label' => __('warehouse::movements.all_statuses'),
                'options' => [
                    ['value' => 'pending', 'label' => __('warehouse::movements.status_pending')],
                    ['value' => 'completed', 'label' => __('warehouse::movements.status_completed')],
                    ['value' => 'cancelled', 'label' => __('warehouse::movements.status_cancelled')],
                ]
            ]
        ];
    @endphp

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Total Movements -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('warehouse::movements.total_movements') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_movements'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Pending Movements -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('warehouse::movements.pending_movements') }}</p>
                        <p class="text-3xl font-bold text-yellow-900 mt-2">{{ $stats['pending_movements'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Completed Movements -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('warehouse::movements.completed_movements') }}</p>
                        <p class="text-3xl font-bold text-green-900 mt-2">{{ $stats['completed_movements'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Items Moved -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('warehouse::movements.total_items_moved') }}</p>
                        <p class="text-3xl font-bold text-purple-900 mt-2">{{ number_format($stats['total_items_moved']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
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
                'label' => __('warehouse::movements.add_movement'),
                'route' => route('warehouse.movements.create')
            ]"
            searchPlaceholder="{{ __('warehouse::movements.search') }}"
        />
    </div>
</x-dashboard>
