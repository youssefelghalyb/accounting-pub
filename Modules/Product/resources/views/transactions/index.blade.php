<x-dashboard :pageTitle="__('product::transaction.transactions')">
    @php
        // Prepare data array
        $tableData = $transactions->map(function($transaction) {
            return [
                'id' => $transaction->id,
                'contract_author' => $transaction->contract->author->full_name,
                'contract_book' => isset($transaction->contract->book) ?  $transaction->contract->book->product->name : $transaction->contract->book_name,
                'amount' => $transaction->amount,
                'payment_date' => $transaction->payment_date->format('Y-m-d'),
                'notes' => $transaction->notes,
                'model' => $transaction
            ];
        })->toArray();

        // Prepare columns array
        $tableColumns = [
            [
                'label' => __('product::transaction.contract'),
                'field' => 'contract_author',
                'render' => function($row) {
                    $html = '<div>';
                    $html .= '<p class="font-medium text-gray-900">' . e($row['contract_author']) . '</p>';
                    $html .= '<p class="text-xs text-gray-500">' . e($row['contract_book']) . '</p>';
                    $html .= '</div>';
                    return $html;
                }
            ],
            [
                'label' => __('product::transaction.amount'),
                'field' => 'amount',
                'format' => function($value) {
                    return '<span class="font-medium text-green-600">' . number_format($value, 2) . '</span>';
                }
            ],
            [
                'label' => __('product::transaction.payment_date'),
                'field' => 'payment_date',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-600">' . $value . '</span>';
                }
            ],
            [
                'label' => __('product::transaction.notes'),
                'field' => 'notes',
                'render' => function($row) {
                    if ($row['notes']) {
                        $truncated = strlen($row['notes']) > 50 ? substr($row['notes'], 0, 50) . '...' : $row['notes'];
                        return '<span class="text-sm text-gray-600">' . e($truncated) . '</span>';
                    }
                    return '<span class="text-xs text-gray-400">-</span>';
                }
            ]
        ];

        // Prepare actions array
        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('product.transactions.show', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600'
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('product.transactions.edit', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600'
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('product.transactions.destroy', $row['model']),
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
                'name' => 'contract_id',
                'label' => __('product::transaction.all_contracts'),
                'options' => $contracts->map(function($contract) {
                    $bookName = $contract->book?->product?->name ?? $contract->book_name;   
                    return [
                        'value' => $contract->id,
                        'label' => $contract->author->full_name . ' - ' . $bookName 
                    ];
                })->toArray()
            ],
            [
                'type' => 'date',
                'name' => 'date_from',
                'label' => __('product::transaction.date_from'),
            ],
            [
                'type' => 'date',
                'name' => 'date_to',
                'label' => __('product::transaction.date_to'),
            ]
        ];
    @endphp

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Total Transactions -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::transaction.total_transactions') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_transactions'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Amount -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::transaction.total_amount') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_amount'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- This Month -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::transaction.this_month_amount') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['this_month_amount'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- This Year -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::transaction.this_year_amount') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['this_year_amount'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <x-dashboard.packages.data-table
            :title="__('product::transaction.transaction_list')"
            :description="__('product::transaction.total_transactions') . ': ' . $transactions->count()"
            searchable
            :searchRoute="route('product.transactions.index')"
            :searchPlaceholder="__('product::transaction.search')"
            :filters="$tableFilters"
            :data="$tableData"
            :columns="$tableColumns"
            :actions="$tableActions"
            :createRoute="route('product.transactions.create')"
            :createLabel="__('product::transaction.add_transaction')"
            :emptyStateTitle="__('product::transaction.no_transactions')"
            :emptyStateDescription="__('common.no_data')"
            emptyStateIcon="document"
        />
    </div>
</x-dashboard>
