<x-dashboard :pageTitle="__('hr::leaveTypes.leave_type_management')">
    @php
        // Prepare data
        $tableData = $leaveTypes
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'description' => $type->description,
                    'max_days' => $type->max_days_per_year,
                    'is_paid' => $type->is_paid,
                    'color' => $type->color,
                    'leaves_count' => $type->leaves_count,
                    'model' => $type,
                ];
            })
            ->toArray();

        // Prepare columns
        $tableColumns = [
            [
                'label' => __('hr::leaveTypes.type_name'),
                'field' => 'name',
                'render' => function ($row) {
                    $html = '<div class="flex items-center gap-3">';
                    $html .= '<div class="w-3 h-3 rounded-full" style="background-color: ' . $row['color'] . '"></div>';
                    $html .= '<span class="font-medium text-gray-900">' . e($row['name']) . '</span>';
                    $html .= '</div>';
                    return $html;
                },
            ],
            [
                'label' => __('hr::leaveTypes.description'),
                'field' => 'description',
                'format' => function ($value) {
                    return '<span class="text-sm text-gray-600">' . (Str::limit($value, 50) ?? '-') . '</span>';
                },
                'nowrap' => false,
            ],
            [
                'label' => __('hr::leaveTypes.days_allowed'),
                'field' => 'max_days',
                'align' => 'center',
                'render' => function ($row) {
                    if ($row['max_days']) {
                        return '<span class="font-medium text-gray-900">' . $row['max_days'] . '</span>';
                    }
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">' .
                        __('common.unlimited') .
                        '</span>';
                },
            ],
            [
                'label' => __('hr::leaveTypes.leave_type'),
                'field' => 'is_paid',
                'align' => 'center',
                'render' => function ($row) {
                    if ($row['is_paid']) {
                        return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">' .
                            __('hr::leaveTypes.paid') .
                            '</span>';
                    }
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">' .
                        __('hr::leaveTypes.unpaid') .
                        '</span>';
                },
            ],
            [
                'label' => __('hr::leaveTypes.total_leaves'),
                'field' => 'leaves_count',
                'align' => 'center',
                'format' => function ($value) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">' .
                        $value .
                        '</span>';
                },
            ],
        ];

        // Prepare actions
        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('hr.leave-types.show', $row['model']),
                'icon' =>
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600',
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('hr.leave-types.edit', $row['model']),
                'icon' =>
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600',
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('hr.leave-types.destroy', $row['model']),
                'method' => 'DELETE',
                'confirm' => __('hr::leaveTypes.confirm_delete_type'),
                'icon' =>
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                'color' => 'text-red-600',
            ],
        ];
    @endphp

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('hr::leaveTypes.total_leave_types') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_leave_types'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('hr::leaveTypes.paid_types') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['paid_types'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('hr::leaveTypes.unpaid_types') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['unpaid_types'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('hr::leaveTypes.unlimited_types') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['unlimited_types'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <x-dashboard.packages.data-table :title="__(key: 'hr::leaveTypes.leave_type_list')" :description="__('hr::leaveTypes.total_leave_types') . ': ' . $leaveTypes->count()" searchable :searchRoute="route('hr.leave-types.index')"
            :searchPlaceholder="__('hr::leaveTypes.search_leave_types')" :data="$tableData" :columns="$tableColumns" :actions="$tableActions" :createRoute="route('hr.leave-types.create')" :createLabel="__('hr::leaveTypes.add_leave_type')"
            :emptyStateTitle="__('hr::leaveTypes.no_leave_types')" :emptyStateDescription="__('common.no_data')" emptyStateIcon="calendar" />
    </div>
</x-dashboard>
