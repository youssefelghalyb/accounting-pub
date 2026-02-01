<x-dashboard :pageTitle="__('finance::receipt.receipt_management')">
    @php
        // Prepare data array
        $tableData = $receipts->map(function($receipt) {
            return [
                'id' => $receipt->id,
                'voucher_number' => $receipt->voucher_number,
                'voucher_date' => $receipt->voucher_date->format('Y-m-d'),
                'party_name' => $receipt->party->name,
                'account_name' => $receipt->account->account_name,
                'invoice_number' => $receipt->salesInvoice?->invoice_number,
                'amount' => $receipt->amount,
                'payment_method' => $receipt->payment_method,
                'payment_method_label' => $receipt->payment_method_label,
                'payment_method_color' => $receipt->payment_method_color,
                'reference_number' => $receipt->reference_number,
                'model' => $receipt
            ];
        })->toArray();
        
        // Prepare columns array
        $tableColumns = [
            [
                'label' => __('finance::receipt.voucher_number'),
                'field' => 'voucher_number',
                'render' => function($row) {
                    $html = '<div>';
                    $html .= '<p class="font-medium text-gray-900">' . e($row['voucher_number']) . '</p>';
                    $html .= '<p class="text-xs text-gray-500">' . e($row['voucher_date']) . '</p>';
                    $html .= '</div>';
                    return $html;
                }
            ],
            [
                'label' => __('finance::receipt.party'),
                'field' => 'party_name',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-900">' . e($value) . '</span>';
                }
            ],
            [
                'label' => __('finance::receipt.account'),
                'field' => 'account_name',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-600">' . e($value) . '</span>';
                }
            ],
            [
                'label' => __('finance::receipt.invoice'),
                'field' => 'invoice_number',
                'format' => function($value) {
                    return $value ? '<span class="text-sm text-blue-600">' . e($value) . '</span>' : '<span class="text-xs text-gray-400">-</span>';
                }
            ],
            [
                'label' => __('finance::receipt.amount'),
                'field' => 'amount',
                'format' => function($value) {
                    return '<span class="font-bold text-lg text-green-600">' . number_format($value, 2) . '</span>';
                }
            ],
            [
                'label' => __('finance::receipt.payment_method'),
                'field' => 'payment_method_label',
                'render' => function($row) {
                    $html = '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-' . $row['payment_method_color'] . '-100 text-' . $row['payment_method_color'] . '-800">' . e($row['payment_method_label']) . '</span>';
                    if ($row['reference_number']) {
                        $html .= '<p class="text-xs text-gray-500 mt-1">' . e($row['reference_number']) . '</p>';
                    }
                    return $html;
                }
            ]
        ];
        
        // Prepare actions array
        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('finance.receipt-vouchers.show', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600'
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('finance.receipt-vouchers.edit', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600'
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('finance.receipt-vouchers.destroy', $row['model']),
                'method' => 'DELETE',
                'confirm' => __('finance::receipt.confirm_delete'),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                'color' => 'text-red-600'
            ]
        ];
        
        // Prepare filters array
        $tableFilters = [
            [
                'type' => 'select',
                'name' => 'party_id',
                'label' => __('finance::receipt.select_party'),
                'options' => $parties->map(function($party) {
                    return [
                        'value' => $party->id,
                        'label' => $party->name
                    ];
                })->toArray()
            ],
            [
                'type' => 'select',
                'name' => 'account_id',
                'label' => __('finance::receipt.select_account'),
                'options' => $accounts->map(function($account) {
                    return [
                        'value' => $account->id,
                        'label' => $account->account_name
                    ];
                })->toArray()
            ],
            [
                'type' => 'select',
                'name' => 'payment_method',
                'label' => __('finance::receipt.select_payment_method'),
                'options' => [
                    ['value' => 'cash', 'label' => __('finance::receipt.payment_methods.cash')],
                    ['value' => 'cheque', 'label' => __('finance::receipt.payment_methods.cheque')],
                    ['value' => 'bank_transfer', 'label' => __('finance::receipt.payment_methods.bank_transfer')],
                    ['value' => 'credit_card', 'label' => __('finance::receipt.payment_methods.credit_card')],
                    ['value' => 'other', 'label' => __('finance::receipt.payment_methods.other')],
                ]
            ]
        ];
    @endphp

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Total Amount -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::receipt.total_amount') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_amount'], 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $stats['total_receipts'] }} {{ __('finance::receipt.receipt_vouchers') }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Today's Receipts -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::receipt.today_receipts') }}</p>
                        <p class="text-3xl font-bold text-blue-600 mt-2">{{ number_format($stats['today_receipts'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- This Month -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::receipt.month_receipts') }}</p>
                        <p class="text-3xl font-bold text-purple-600 mt-2">{{ number_format($stats['this_month_receipts'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Cash Receipts -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::receipt.cash_receipts') }}</p>
                        <p class="text-3xl font-bold text-emerald-600 mt-2">{{ number_format($stats['cash_receipts'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <x-dashboard.packages.data-table
            :title="__('finance::receipt.receipt_list')"
            :description="__('finance::receipt.total_receipts') . ': ' . $receipts->count()"
            searchable
            :searchRoute="route('finance.receipt-vouchers.index')"
            :searchPlaceholder="__('finance::receipt.search_placeholder')"
            :filters="$tableFilters"
            :data="$tableData"
            :columns="$tableColumns"
            :actions="$tableActions"
            :createRoute="route('finance.receipt-vouchers.create')"
            :createLabel="__('finance::receipt.add_receipt')"
            :emptyStateTitle="__('finance::receipt.no_receipts')"
            :emptyStateDescription="__('common.no_data')"
            emptyStateIcon="receipt-refund"
        />
    </div>
</x-dashboard>