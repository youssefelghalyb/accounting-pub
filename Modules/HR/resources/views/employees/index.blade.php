<x-dashboard :pageTitle="__('hr::employee.employee_management')">
    @php
        // Prepare data array
        $tableData = $employees->map(function($emp) {
            return [
                'id' => $emp->id,
                'first_name' => $emp->first_name,
                'last_name' => $emp->last_name,
                'full_name' => $emp->full_name,
                'email' => $emp->email,
                'phone' => $emp->phone,
                'department_name' => $emp->department->name,
                'department_color' => $emp->department->color,
                'position' => $emp->position,
                'salary' => $emp->salary,
                'hire_date' => $emp->hire_date->format('Y-m-d'),
                'model' => $emp
            ];
        })->toArray();
        
        // Prepare columns array
        $tableColumns = [
            [
                'label' => __('hr::employee.full_name'),
                'field' => 'full_name',
                'render' => function($row) {
                    $initials = strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1));
                    $html = '<div class="flex items-center gap-3">';
                    $html .= '<div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">' . $initials . '</div>';
                    $html .= '<div>';
                    $html .= '<p class="font-medium text-gray-900">' . e($row['full_name']) . '</p>';
                    $html .= '<p class="text-xs text-gray-500">' . ($row['phone'] ?? '-') . '</p>';
                    $html .= '</div>';
                    $html .= '</div>';
                    return $html;
                }
            ],
            [
                'label' => __('hr::employee.email'),
                'field' => 'email',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-600">' . e($value) . '</span>';
                }
            ],
            [
                'label' => __('hr::employee.department'),
                'field' => 'department_name',
                'render' => function($row) {
                    $html = '<div class="flex items-center gap-2">';
                    $html .= '<div class="w-3 h-3 rounded-full" style="background-color: ' . $row['department_color'] . '"></div>';
                    $html .= '<span class="text-sm text-gray-900">' . e($row['department_name']) . '</span>';
                    $html .= '</div>';
                    return $html;
                }
            ],
            [
                'label' => __('hr::employee.position'),
                'field' => 'position',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-600">' . ($value ?? '-') . '</span>';
                }
            ],
            [
                'label' => __('hr::employee.salary'),
                'field' => 'salary',
                'format' => function($value) {
                    return '<span class="font-medium text-gray-900">' . number_format($value, 2) . '</span>';
                }
            ],
            [
                'label' => __('hr::employee.hire_date'),
                'field' => 'hire_date',
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
                'route' => fn($row) => route('hr.employees.show', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600'
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('hr.employees.edit', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600'
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('hr.employees.destroy', $row['model']),
                'method' => 'DELETE',
                'confirm' => __('hr::employee.confirm_delete'),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                'color' => 'text-red-600'
            ]
        ];
        
        // Prepare filters array
        $tableFilters = [
            [
                'type' => 'select',
                'name' => 'department_id',
                'label' => __('hr::employee.all_departments'),
                'options' => $departments->map(function($dept) {
                    return [
                        'value' => $dept->id,
                        'label' => $dept->name
                    ];
                })->toArray()
            ]
        ];
    @endphp

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Total Employees -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('hr::employee.total_employees') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_employees'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Salary -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('hr::employee.total_salary_cost') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_salary'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Average Salary -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('hr::employee.average_salary') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['average_salary'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Departments -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('hr::department.total_departments') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['departments_count'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <x-dashboard.packages.data-table
            :title="__('hr::employee.employee_list')"
            :description="__('hr::employee.total_employees') . ': ' . $employees->count()"
            searchable
            :searchRoute="route('hr.employees.index')"
            :searchPlaceholder="__('hr::employee.search')"
            :filters="$tableFilters"
            :data="$tableData"
            :columns="$tableColumns"
            :actions="$tableActions"
            :createRoute="route('hr.employees.create')"
            :createLabel="__('hr::employee.add_employee')"
            :emptyStateTitle="__('hr::employee.no_employees')"
            :emptyStateDescription="__('common.no_data')"
            emptyStateIcon="users"
        />
    </div>
</x-dashboard>