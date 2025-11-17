<x-dashboard :pageTitle="__('hr::leaves.leave_management')">
    @php
        // Prepare data
        $tableData = $leaves->map(function($leave) {
            return [
                'id' => $leave->id,
                'employee_name' => $leave->employee->first_name . ' ' . $leave->employee->last_name,
                'employee_code' => $leave->employee->employee_code,
                'department' => $leave->employee->department->name,
                'leave_type' => $leave->leaveType->name,
                'leave_type_color' => $leave->leaveType->color,
                'start_date' => $leave->start_date->format('Y-m-d'),
                'end_date' => $leave->end_date->format('Y-m-d'),
                'days' => $leave->days,
                'status' => $leave->status,
                'model' => $leave
            ];
        })->toArray();
        
        // Prepare columns
        $tableColumns = [
            [
                'label' => __('hr::employee.employee_name'),
                'field' => 'employee_name',
                'render' => function($row) {
                    return '<div class="flex flex-col">'
                        . '<span class="font-medium text-gray-900">' . e($row['employee_name']) . '</span>'
                        . '<span class="text-xs text-gray-500">' . e($row['employee_code']) . '</span>'
                        . '</div>';
                }
            ],
            [
                'label' => __('hr::department.name'),
                'field' => 'department',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-600">' . e($value) . '</span>';
                }
            ],
            [
                'label' => __('hr::leaves.leave_type'),
                'field' => 'leave_type',
                'render' => function($row) {
                    return '<div class="flex items-center gap-2">'
                        . '<div class="w-3 h-3 rounded-full" style="background-color: ' . $row['leave_type_color'] . '"></div>'
                        . '<span class="text-sm font-medium text-gray-900">' . e($row['leave_type']) . '</span>'
                        . '</div>';
                }
            ],
            [
                'label' => __('hr::leaves.start_date'),
                'field' => 'start_date',
                'align' => 'center',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-900">' . $value . '</span>';
                }
            ],
            [
                'label' => __('hr::leaves.end_date'),
                'field' => 'end_date',
                'align' => 'center',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-900">' . $value . '</span>';
                }
            ],
            [
                'label' => __('hr::leaves.days'),
                'field' => 'days',
                'align' => 'center',
                'render' => function($row) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">'
                        . $row['days'] . ' ' . __('hr::leaves.days_label')
                        . '</span>';
                }
            ],
            [
                'label' => __('hr::leaves.status'),
                'field' => 'status',
                'align' => 'center',
                'render' => function($row) {
                    if ($row['status'] === 'pending') {
                        return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">' . __('hr::leaves.status_pending') . '</span>';
                    } elseif ($row['status'] === 'approved') {
                        return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">' . __('hr::leaves.status_approved') . '</span>';
                    } else {
                        return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">' . __('hr::leaves.status_rejected') . '</span>';
                    }
                }
            ]
        ];
        
        // Prepare actions
        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('hr.leaves.show', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600'
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('hr.leaves.edit', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600',
                'show' => fn($row) => $row['status'] === 'pending'
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('hr.leaves.destroy', $row['model']),
                'method' => 'DELETE',
                'confirm' => __('hr::leaves.confirm_delete'),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                'color' => 'text-red-600'
            ]
        ];
        
        // Prepare filters
        $filterOptions = [];
        foreach (\Modules\HR\Models\LeaveType::all() as $type) {
            $filterOptions[] = ['value' => $type->id, 'label' => $type->name];
        }
        
        $filters = [
            [
                'type' => 'select',
                'name' => 'status',
                'label' => __('hr::leaves.filter_by_status'),
                'options' => [
                    ['value' => 'pending', 'label' => __('hr::leaves.status_pending')],
                    ['value' => 'approved', 'label' => __('hr::leaves.status_approved')],
                    ['value' => 'rejected', 'label' => __('hr::leaves.status_rejected')]
                ]
            ],
            [
                'type' => 'select',
                'name' => 'leave_type_id',
                'label' => __('hr::leaves.filter_by_type'),
                'options' => $filterOptions
            ]
        ];
    @endphp

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('hr::leaves.total_leaves') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_leaves'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('hr::leaves.pending_leaves') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['pending_leaves'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('hr::leaves.approved_leaves') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['approved_leaves'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('hr::leaves.rejected_leaves') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['rejected_leaves'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <x-dashboard.packages.data-table
            :title="__('hr::leaves.leave_list')"
            :description="__('hr::leaves.manage_leaves')"
            searchable
            :searchRoute="route('hr.leaves.index')"
            :searchPlaceholder="__('hr::leaves.search_leaves')"
            :data="$tableData"
            :columns="$tableColumns"
            :actions="$tableActions"
            :filters="$filters"
            :createRoute="route('hr.leaves.create')"
            :createLabel="__('hr::leaves.add_leave')"
            :pagination="$leaves"
            :emptyStateTitle="__('hr::leaves.no_leaves')"
            :emptyStateDescription="__('hr::leaves.no_leaves_description')"
            emptyStateIcon="calendar"
        />
    </div>
</x-dashboard>