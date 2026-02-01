<x-dashboard :pageTitle="__('finance::party.party_management')">
    @php
        // Prepare data array
        $tableData = $parties
            ->map(function ($party) {
                return [
                    'id' => $party->id,
                    'name' => $party->name,
                    'type' => $party->type,
                    'type_label' => $party->type_label,
                    'type_color' => $party->type_color,
                    'email' => $party->email,
                    'phone' => $party->phone,
                    'tax_number' => $party->tax_number,
                    'role_label' => $party->role_label,
                    'customer_balance' => $party->customer_balance,
                    'vendor_balance' => $party->vendor_balance,
                    'is_active' => $party->is_active,
                    'model' => $party,
                ];
            })
            ->toArray();

        // Prepare columns array
        $tableColumns = [
            [
                'label' => __('finance::party.name'),
                'field' => 'name',
                'render' => function ($row) {
                    $initials = strtoupper(substr($row['name'], 0, 2));
                    $html = '<div class="flex items-center gap-3">';
                    $html .=
                        '<div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">' .
                        $initials .
                        '</div>';
                    $html .= '<div>';
                    $html .= '<p class="font-medium text-gray-900">' . e($row['name']) . '</p>';
                    $html .= '<p class="text-xs text-gray-500">' . ($row['phone'] ?? '-') . '</p>';
                    $html .= '</div>';
                    $html .= '</div>';
                    return $html;
                },
            ],
            [
                'label' => __('finance::party.type'),
                'field' => 'type_label',
                'render' => function ($row) {
                    return '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-' .
                        $row['type_color'] .
                        '-100 text-' .
                        $row['type_color'] .
                        '-800">' .
                        e($row['type_label']) .
                        '</span>';
                },
            ],
            [
                'label' => __('finance::party.role'),
                'field' => 'role_label',
                'format' => function ($value) {
                    return '<span class="text-sm text-gray-600">' . e($value) . '</span>';
                },
            ],
            [
                'label' => __('finance::party.email'),
                'field' => 'email',
                'format' => function ($value) {
                    return '<span class="text-sm text-gray-600">' . ($value ?? '-') . '</span>';
                },
            ],
            [
                'label' => __('finance::party.customer_balance'),
                'field' => 'customer_balance',
                'render' => function ($row) {
                    $balance = (float) $row['customer_balance'];

                    $color = $balance < 0 ? 'text-red-600' : ($balance == 0 ? 'text-yellow-600' : 'text-green-600');

                    return '<span class="font-medium ' . $color . '">' . number_format($balance, 2) . '</span>';
                },
            ],
            [
                'label' => __('common.status'),
                'field' => 'is_active',
                'render' => function ($row) {
                    if ($row['is_active']) {
                        return '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">' .
                            __('common.active') .
                            '</span>';
                    } else {
                        return '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">' .
                            __('common.inactive') .
                            '</span>';
                    }
                },
            ],
        ];

        // Prepare actions array
        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('finance.parties.show', $row['model']),
                'icon' =>
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600',
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('finance.parties.edit', $row['model']),
                'icon' =>
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600',
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('finance.parties.destroy', $row['model']),
                'method' => 'DELETE',
                'confirm' => __('finance::party.confirm_delete'),
                'icon' =>
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                'color' => 'text-red-600',
            ],
        ];

        // Prepare filters array
        $tableFilters = [
            [
                'type' => 'select',
                'name' => 'type',
                'label' => __('finance::party.all_types'),
                'options' => [
                    ['value' => 'individual', 'label' => __('finance::party.types.individual')],
                    ['value' => 'company', 'label' => __('finance::party.types.company')],
                    ['value' => 'online', 'label' => __('finance::party.types.online')],
                ],
            ],
            [
                'type' => 'select',
                'name' => 'status',
                'label' => __('common.all_status'),
                'options' => [
                    ['value' => 'active', 'label' => __('common.active')],
                    ['value' => 'inactive', 'label' => __('common.inactive')],
                ],
            ],
        ];
    @endphp

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Total Parties -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::party.total_parties') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_parties'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Customers -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::party.total_customers') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_customers'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Parties -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::party.active_parties') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['active_parties'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Receivables -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::party.total_receivables') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            {{ number_format($stats['total_receivables'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <x-dashboard.packages.data-table :title="__('finance::party.party_list')" :description="__('finance::party.total_parties') . ': ' . $parties->count()" searchable :searchRoute="route('finance.parties.index')"
            :searchPlaceholder="__('finance::party.search_placeholder')" :filters="$tableFilters" :data="$tableData" :columns="$tableColumns" :actions="$tableActions" :createRoute="route('finance.parties.create')"
            :createLabel="__('finance::party.add_party')" :emptyStateTitle="__('finance::party.no_parties')" :emptyStateDescription="__('common.no_data')" emptyStateIcon="users" />
    </div>
</x-dashboard>
