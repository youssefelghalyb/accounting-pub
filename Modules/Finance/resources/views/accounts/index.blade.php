<x-dashboard :pageTitle="__('finance::account.account_management')">
    @php
        // Prepare data array
        $tableData = $accounts->map(function($account) {
            return [
                'id' => $account->id,
                'account_name' => $account->account_name,
                'account_number' => $account->account_number,
                'account_type' => $account->account_type,
                'type_label' => $account->type_label,
                'type_color' => $account->type_color,
                'bank_name' => $account->bank_name,
                'current_balance' => $account->current_balance,
                'balance_color' => $account->balance_color,
                'currency' => $account->currency,
                'is_active' => $account->is_active,
                'model' => $account
            ];
        })->toArray();
        
        // Prepare columns array
        $tableColumns = [
            [
                'label' => __('finance::account.account_name'),
                'field' => 'account_name',
                'render' => function($row) {
                    $icon = $row['account_type'] === 'cash' 
                        ? '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>'
                        : '<svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>';
                    
                    $html = '<div class="flex items-center gap-3">';
                    $html .= '<div class="w-10 h-10 bg-' . $row['type_color'] . '-100 rounded-lg flex items-center justify-center">' . $icon . '</div>';
                    $html .= '<div>';
                    $html .= '<p class="font-medium text-gray-900">' . e($row['account_name']) . '</p>';
                    if ($row['account_number']) {
                        $html .= '<p class="text-xs text-gray-500">' . e($row['account_number']) . '</p>';
                    }
                    $html .= '</div>';
                    $html .= '</div>';
                    return $html;
                }
            ],
            [
                'label' => __('finance::account.account_type'),
                'field' => 'type_label',
                'render' => function($row) {
                    return '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-' . $row['type_color'] . '-100 text-' . $row['type_color'] . '-800">' . e($row['type_label']) . '</span>';
                }
            ],
            [
                'label' => __('finance::account.bank_name'),
                'field' => 'bank_name',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-600">' . ($value ?? '-') . '</span>';
                }
            ],
            [
                'label' => __('finance::account.current_balance'),
                'field' => 'current_balance',
                'render' => function($row) {
                    $color = 'text-' . $row['balance_color'] . '-600';
                    return '<span class="font-bold text-lg ' . $color . '">' . number_format($row['current_balance'], 2) . ' ' . e($row['currency']) . '</span>';
                }
            ],
            [
                'label' => __('common.status'),
                'field' => 'is_active',
                'render' => function($row) {
                    if ($row['is_active']) {
                        return '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">' . __('common.active') . '</span>';
                    } else {
                        return '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">' . __('common.inactive') . '</span>';
                    }
                }
            ]
        ];
        
        // Prepare actions array
        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('finance.accounts.show', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600'
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('finance.accounts.edit', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600'
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('finance.accounts.destroy', $row['model']),
                'method' => 'DELETE',
                'confirm' => __('finance::account.confirm_delete'),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                'color' => 'text-red-600'
            ]
        ];
        
        // Prepare filters array
        $tableFilters = [
            [
                'type' => 'select',
                'name' => 'type',
                'label' => __('finance::account.all_types'),
                'options' => [
                    ['value' => 'cash', 'label' => __('finance::account.types.cash')],
                    ['value' => 'bank', 'label' => __('finance::account.types.bank')],
                ]
            ],
            [
                'type' => 'select',
                'name' => 'status',
                'label' => __('common.all_status'),
                'options' => [
                    ['value' => 'active', 'label' => __('common.active')],
                    ['value' => 'inactive', 'label' => __('common.inactive')],
                ]
            ]
        ];
    @endphp

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Total Accounts -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::account.total_accounts') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_accounts'] }}</p>
                        <div class="flex items-center gap-4 mt-2">
                            <span class="text-xs text-green-600">{{ $stats['cash_accounts'] }} {{ __('finance::account.types.cash') }}</span>
                            <span class="text-xs text-blue-600">{{ $stats['bank_accounts'] }} {{ __('finance::account.types.bank') }}</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Balance -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::account.total_balance') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_balance'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Cash Balance -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::account.cash_balance') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_cash_balance'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Bank Balance -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::account.bank_balance') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_bank_balance'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <x-dashboard.packages.data-table
            :title="__('finance::account.account_list')"
            :description="__('finance::account.total_accounts') . ': ' . $accounts->count()"
            searchable
            :searchRoute="route('finance.accounts.index')"
            :searchPlaceholder="__('finance::account.search_placeholder')"
            :filters="$tableFilters"
            :data="$tableData"
            :columns="$tableColumns"
            :actions="$tableActions"
            :createRoute="route('finance.accounts.create')"
            :createLabel="__('finance::account.add_account')"
            :emptyStateTitle="__('finance::account.no_accounts')"
            :emptyStateDescription="__('common.no_data')"
            emptyStateIcon="credit-card"
        />
    </div>
</x-dashboard>