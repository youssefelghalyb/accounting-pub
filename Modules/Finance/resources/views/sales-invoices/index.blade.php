<x-dashboard :pageTitle="__('finance::invoice.invoice_management')">
    @php
        // Prepare data array
        $tableData = $invoices
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                    'party_name' => $invoice->party->name,
                    'total_amount' => $invoice->total_amount,
                    'paid_amount' => $invoice->paid_amount,
                    'outstanding_balance' => $invoice->outstanding_balance,
                    'status' => $invoice->status,
                    'status_label' => $invoice->status_label,
                    'status_color' => $invoice->status_color,
                    'is_overdue' => $invoice->is_overdue,
                    'days_overdue' => $invoice->days_overdue,
                    'model' => $invoice,
                ];
            })
            ->toArray();

        // Prepare columns array
        $tableColumns = [
            [
                'label' => __('finance::invoice.invoice_number'),
                'field' => 'invoice_number',
                'render' => function ($row) {
                    $html = '<div>';
                    $html .= '<p class="font-medium text-gray-900">' . e($row['invoice_number']) . '</p>';
                    $html .= '<p class="text-xs text-gray-500">' . e($row['invoice_date']) . '</p>';
                    $html .= '</div>';
                    return $html;
                },
            ],
            [
                'label' => __('finance::invoice.party'),
                'field' => 'party_name',
                'format' => function ($value) {
                    return '<span class="text-sm text-gray-900">' . e($value) . '</span>';
                },
            ],
            [
                'label' => __('finance::invoice.total_amount'),
                'field' => 'total_amount',
                'format' => function ($value) {
                    return '<span class="font-medium text-gray-900">' . number_format($value, 2) . '</span>';
                },
            ],
            [
                'label' => __('finance::invoice.paid_amount'),
                'field' => 'paid_amount',
                'format' => function ($value) {
                    return '<span class="font-medium text-green-600">' . number_format($value, 2) . '</span>';
                },
            ],
            [
                'label' => __('finance::invoice.outstanding_balance'),
                'field' => 'outstanding_balance',
                'render' => function ($row) {
                    $color = $row['outstanding_balance'] > 0 ? 'text-red-600' : 'text-green-600';
                    return '<span class="font-medium ' .
                        $color .
                        '">' .
                        number_format($row['outstanding_balance'], 2) .
                        '</span>';
                },
            ],
            [
                'label' => __('common.status'),
                'field' => 'status_label',
                'render' => function ($row) {
                    $html = '<div class="flex flex-col gap-1">';
                    $html .=
                        '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-' .
                        $row['status_color'] .
                        '-100 text-' .
                        $row['status_color'] .
                        '-800">' .
                        e($row['status_label']) .
                        '</span>';
                    if ($row['is_overdue']) {
                        $html .=
                            '<span class="text-xs text-red-600">' .
                            __('finance::invoice.days_overdue', ['days' => $row['days_overdue']]) .
                            '</span>';
                    }
                    $html .= '</div>';
                    return $html;
                },
            ],
        ];

        // Prepare actions array
        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('finance.sales-invoices.show', $row['model']),
                'icon' =>
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600',
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('finance.sales-invoices.edit', $row['model']),
                'icon' =>
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600',
                'condition' => fn($row) => !in_array($row['status'], ['paid', 'cancelled']),
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('finance.sales-invoices.destroy', $row['model']),
                'method' => 'DELETE',
                'confirm' => __('finance::invoice.confirm_delete'),
                'icon' =>
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                'color' => 'text-red-600',
                'condition' => fn($row) => $row['paid_amount'] == 0,
            ],
        ];

        // Prepare filters array
        $tableFilters = [
            [
                'type' => 'select',
                'name' => 'party_id',
                'label' => __('finance::invoice.select_party'),
                'options' => $parties
                    ->map(function ($party) {
                        return [
                            'value' => $party->id,
                            'label' => $party->name,
                        ];
                    })
                    ->toArray(),
            ],
            [
                'type' => 'select',
                'name' => 'status',
                'label' => __('common.all_status'),
                'options' => [
                    ['value' => 'unpaid', 'label' => __('finance::invoice.statuses.unpaid')],
                    ['value' => 'partial', 'label' => __('finance::invoice.statuses.partial')],
                    ['value' => 'paid', 'label' => __('finance::invoice.statuses.paid')],
                    ['value' => 'cancelled', 'label' => __('finance::invoice.statuses.cancelled')],
                ],
            ],
        ];
    @endphp

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Total Sales -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::invoice.total_sales') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_sales'], 2) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ $stats['total_invoices'] }}
                            {{ __('finance::invoice.sales_invoices') }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Unpaid Invoices -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::invoice.unpaid_invoices') }}</p>
                        <p class="text-3xl font-bold text-red-600 mt-2">{{ $stats['unpaid_invoices'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Overdue Invoices -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::invoice.overdue_invoices') }}</p>
                        <p class="text-3xl font-bold text-orange-600 mt-2">{{ $stats['overdue_invoices'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Outstanding -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('finance::invoice.total_outstanding') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            {{ number_format($stats['total_outstanding'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <x-dashboard.packages.data-table :title="__('finance::invoice.invoice_list')" :description="__('finance::invoice.total_invoices') . ': ' . $invoices->count()" searchable :searchRoute="route('finance.sales-invoices.index')"
            :searchPlaceholder="__('finance::invoice.search_placeholder')" :filters="$tableFilters" :data="$tableData" :columns="$tableColumns" :actions="$tableActions" :createRoute="route('finance.sales-invoices.create')"
            :createLabel="__('finance::invoice.add_invoice')" :emptyStateTitle="__('finance::invoice.no_invoices')" :emptyStateDescription="__('common.no_data')" emptyStateIcon="document-text" :pagination="$invoices"
            showPerPage :perPage="[10, 25, 50, 100]" />
    </div>
</x-dashboard>
