<x-dashboard :pageTitle="__('hr::deductions.deduction_management')">
    @php
        // Prepare data
        $orgSettings = $orgSettings;

        $tableData = $deductions->map(function($deduction) {
            return [
                'id' => $deduction->id,
                'employee_name' => $deduction->employee->name,
                'employee_code' => $deduction->employee->employee_code,
                'department' => $deduction->employee->department->name,
                'type' => $deduction->type,
                'days' => $deduction->days,
                'amount' => $deduction->amount,
                'deduction_date' => $deduction->deduction_date->format('Y-m-d'),
                'is_from_leave' => $deduction->isFromLeave(),
                'model' => $deduction
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
                'label' => __('hr::department.department'),
                'field' => 'department',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-600">' . e($value) . '</span>';
                }
            ],
            [
                'label' => __('hr::deductions.deduction_type'),
                'field' => 'type',
                'align' => 'center',
                'render' => function($row) {
                    if ($row['type'] === 'days') {
                        return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">' . __('hr::deductions.type_days') . '</span>';
                    } elseif ($row['type'] === 'amount') {
                        return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">' . __('hr::deductions.type_amount') . '</span>';
                    } else {
                        return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">' . __('hr::deductions.type_unpaid_leave') . '</span>';
                    }
                }
            ],
            [
                'label' => __('hr::deductions.days'),
                'field' => 'days',
                'align' => 'center',
                'render' => function($row) {
                    return $row['days'] ? '<span class="font-medium text-gray-900">' . $row['days'] . '</span>' : '<span class="text-gray-400">-</span>';
                }
            ],
            [
                'label' => __('hr::deductions.amount'),
                'field' => 'amount',
                'align' => 'center',
                'render' => function($row) use ($orgSettings) {
                    return '<span class="font-semibold text-gray-900">' . number_format($row['amount'], 2) . ' ' . $orgSettings->currency . '</span>';
                }
            ],
            [
                'label' => __('hr::deductions.deduction_date'),
                'field' => 'deduction_date',
                'align' => 'center',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-900">' . $value . '</span>';
                }
            ],
            [
                'label' => __('hr::deductions.source'),
                'field' => 'is_from_leave',
                'align' => 'center',
                'render' => function($row) {
                    if ($row['is_from_leave']) {
                        return '<span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">'
                            . '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>'
                            . __('hr::deductions.from_leave')
                            . '</span>';
                    }
                    return '<span class="text-gray-400 text-xs">' . __('hr::deductions.manual') . '</span>';
                }
            ]
        ];
        
        // Prepare actions
        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('hr.deductions.show', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600'
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('hr.deductions.edit', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600'
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('hr.deductions.destroy', $row['model']),
                'method' => 'DELETE',
                'confirm' => __('hr::deductions.confirm_delete'),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                'color' => 'text-red-600'
            ]
        ];
        
        // Prepare filters
        $filters = [
            [
                'type' => 'select',
                'name' => 'type',
                'label' => __('hr::deductions.filter_by_type'),
                'options' => [
                    ['value' => 'days', 'label' => __('hr::deductions.type_days')],
                    ['value' => 'amount', 'label' => __('hr::deductions.type_amount')],
                    ['value' => 'unpaid_leave', 'label' => __('hr::deductions.type_unpaid_leave')]
                ]
            ]
        ];
    @endphp

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('hr::deductions.total_deductions') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_deductions'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('hr::deductions.total_amount') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_amount'], 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{$orgSettings->currency}}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('hr::deductions.unpaid_leave_count') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['unpaid_leave_deductions'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('hr::deductions.this_month') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['this_month_amount'], 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{$orgSettings->currency}}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <x-dashboard.packages.data-table
            :title="__('hr::deductions.deduction_list')"
            :description="__('hr::deductions.manage_deductions')"
            searchable
            :searchRoute="route('hr.deductions.index')"
            :searchPlaceholder="__('hr::deductions.search_deductions')"
            :data="$tableData"
            :columns="$tableColumns"
            :actions="$tableActions"
            :filters="$filters"
            :createRoute="route('hr.deductions.create')"
            :createLabel="__('hr::deductions.add_deduction')"
            :pagination="$deductions"
            :emptyStateTitle="__('hr::deductions.no_deductions')"
            :emptyStateDescription="__('hr::deductions.no_deductions_description')"
            emptyStateIcon="document"
        />
    </div>
</x-dashboard>