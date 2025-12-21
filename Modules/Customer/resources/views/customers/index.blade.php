<x-dashboard :pageTitle="__('customer::customer.customer_management')">
    @php
        // Prepare data array with pre-evaluated actions for each row
        $processedData = $customers->map(function($customer) {
            $row = [
                'id' => $customer->id,
                'name' => $customer->name,
                'type' => $customer->type,
                'type_label' => $customer->type_label,
                'type_color' => $customer->type_color,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'tax_number' => $customer->tax_number,
                'address' => $customer->address,
                'is_active' => $customer->is_active,
                'status_label' => $customer->status_label,
                'status_color' => $customer->status_color,
                'model' => $customer
            ];
            
            // Pre-evaluate actions for this specific row
            $row['actions'] = [
                [
                    'type' => 'link',
                    'label' => __('common.view'),
                    'route' => route('customer.customers.show', $customer),
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                    'color' => 'text-blue-600'
                ],
                [
                    'type' => 'link',
                    'label' => __('common.edit'),
                    'route' => route('customer.customers.edit', $customer),
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                    'color' => 'text-green-600'
                ],
                [
                    'type' => 'form',
                    'label' => $customer->is_active ? __('customer::customer.deactivate') : __('customer::customer.activate'),
                    'route' => route('customer.customers.toggle-status', $customer),
                    'method' => 'POST',
                    'confirm' => $customer->is_active ? __('customer::customer.confirm_deactivate') : __('customer::customer.confirm_activate'),
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>',
                    'color' => $customer->is_active ? 'text-orange-600' : 'text-green-600'
                ],
                [
                    'type' => 'form',
                    'label' => __('common.delete'),
                    'route' => route('customer.customers.destroy', $customer),
                    'method' => 'DELETE',
                    'confirm' => __('customer::customer.confirm_delete'),
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                    'color' => 'text-red-600'
                ]
            ];
            
            return $row;
        })->toArray();
        
        // Prepare columns array
        $tableColumns = [
            [
                'label' => __('customer::customer.name'),
                'field' => 'name',
                'render' => function($row) {
                    $initials = strtoupper(substr($row['name'], 0, 2));
                    $html = '<div class="flex items-center gap-3">';
                    $html .= '<div class="w-10 h-10 rounded-full bg-gradient-to-br from-' . $row['type_color'] . '-500 to-' . $row['type_color'] . '-600 flex items-center justify-center text-white font-bold text-sm">' . $initials . '</div>';
                    $html .= '<div>';
                    $html .= '<p class="font-medium text-gray-900">' . e($row['name']) . '</p>';
                    $html .= '<p class="text-xs text-gray-500">' . ($row['phone'] ?? '-') . '</p>';
                    $html .= '</div>';
                    $html .= '</div>';
                    return $html;
                }
            ],
            [
                'label' => __('customer::customer.type'),
                'field' => 'type_label',
                'render' => function($row) {
                    return '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-' . $row['type_color'] . '-100 text-' . $row['type_color'] . '-800">' . e($row['type_label']) . '</span>';
                }
            ],
            [
                'label' => __('customer::customer.email'),
                'field' => 'email',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-600">' . ($value ?? '-') . '</span>';
                }
            ],
            [
                'label' => __('customer::customer.tax_number'),
                'field' => 'tax_number',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-600">' . ($value ?? '-') . '</span>';
                }
            ],
            [
                'label' => __('common.status'),
                'field' => 'status_label',
                'render' => function($row) {
                    return '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-' . $row['status_color'] . '-100 text-' . $row['status_color'] . '-800">' . e($row['status_label']) . '</span>';
                }
            ]
        ];
        
        // Use pre-evaluated actions from each row
        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('customer.customers.show', $row['id']),
                'color' => 'text-blue-600',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>'
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('customer.customers.edit', $row['id']),
                'color' => 'text-green-600',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>'
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('customer.customers.destroy', $row['id']),
                'method' => 'DELETE',
                'color' => 'text-red-600',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>'
            ],
        ];
        
        // Prepare filters array
        $tableFilters = [
            [
                'type' => 'select',
                'name' => 'type',
                'label' => __('customer::customer.all_types'),
                'options' => [
                    ['value' => 'individual', 'label' => __('customer::customer.types.individual')],
                    ['value' => 'company', 'label' => __('customer::customer.types.company')],
                    ['value' => 'online', 'label' => __('customer::customer.types.online')],
                ]
            ],
            [
                'type' => 'select',
                'name' => 'status',
                'label' => __('customer::customer.all_statuses'),
                'options' => [
                    ['value' => 'active', 'label' => __('customer::customer.active')],
                    ['value' => 'inactive', 'label' => __('customer::customer.inactive')],
                ]
            ]
        ];
    @endphp

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <!-- Total Customers -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('customer::customer.total_customers') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_customers'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Customers -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('customer::customer.active_customers') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['active_customers'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Individual Customers -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('customer::customer.individual') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['individual_customers'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Company Customers -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('customer::customer.company') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['company_customers'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Online Customers -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('customer::customer.online') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['online_customers'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <x-dashboard.packages.data-table
            :title="__('customer::customer.customer_list')"
            :description="__('customer::customer.total_customers') . ': ' . $customers->count()"
            searchable
            :searchRoute="route('customer.customers.index')"
            :searchPlaceholder="__('customer::customer.search')"
            :filters="$tableFilters"
            :data="$processedData"
            :columns="$tableColumns"
            :actions="$tableActions"
            :createRoute="route('customer.customers.create')"
            :createLabel="__('customer::customer.add_customer')"
            :emptyStateTitle="__('customer::customer.no_customers')"
            :emptyStateDescription="__('common.no_data')"
            emptyStateIcon="users"
        />
    </div>
</x-dashboard>