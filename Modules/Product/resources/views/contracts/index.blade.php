<x-dashboard :pageTitle="__('product::contract.contracts')">
    @php
        // Prepare data array
        $tableData = $contracts->map(function($contract) {
            return [
                'id' => $contract->id,
                'author_name' => $contract->author->full_name,
                'book_name' => isset($contract->book) ?  $contract->book->product->name : $contract->book_name,
                'contract_date' => $contract->contract_date->format('Y-m-d'),
                'contract_price' => $contract->contract_price,
                'total_paid' => $contract->total_paid,
                'outstanding_balance' => $contract->outstanding_balance,
                'payment_status' => $contract->payment_status,
                'model' => $contract
            ];
        })->toArray();

        // Prepare columns array
        $tableColumns = [
            [
                'label' => __('product::contract.author'),
                'field' => 'author_name',
                'format' => function($value) {
                    return '<span class="font-medium text-gray-900">' . e($value) . '</span>';
                }
            ],
            [
                'label' => __('product::contract.book'),
                'field' => 'book_name',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-600">' . e($value) . '</span>';
                }
            ],
            [
                'label' => __('product::contract.contract_date'),
                'field' => 'contract_date',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-600">' . $value . '</span>';
                }
            ],
            [
                'label' => __('product::contract.contract_price'),
                'field' => 'contract_price',
                'format' => function($value) {
                    return '<span class="font-medium text-gray-900">' . number_format($value, 2) . '</span>';
                }
            ],
            [
                'label' => __('product::contract.total_paid'),
                'field' => 'total_paid',
                'format' => function($value) {
                    return '<span class="text-sm text-green-600 font-medium">' . number_format($value, 2) . '</span>';
                }
            ],
            [
                'label' => __('product::contract.payment_status'),
                'field' => 'payment_status',
                'render' => function($row) {
                    $colors = [
                        'paid' => 'bg-green-100 text-green-800',
                        'partial' => 'bg-yellow-100 text-yellow-800',
                        'pending' => 'bg-red-100 text-red-800',
                    ];
                    $color = $colors[$row['payment_status']] ?? 'bg-gray-100 text-gray-800';
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $color . '">' . __('product::contract.' . $row['payment_status']) . '</span>';
                }
            ]
        ];

        // Prepare actions array
        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('product.contracts.show', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600'
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('product.contracts.edit', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600'
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('product.contracts.destroy', $row['model']),
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
                'name' => 'author_id',
                'label' => __('product::contract.all_authors'),
                'options' => $authors->map(function($author) {
                    return ['value' => $author->id, 'label' => $author->full_name];
                })->toArray()
            ],
            [
                'type' => 'select',
                'name' => 'book_id',
                'label' => __('product::contract.all_books'),
                'options' => $books->map(function($book) {
                    return ['value' => $book->id, 'label' => $book->product->name];
                })->toArray()
            ]
        ];
    @endphp

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Total Contracts -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::contract.total_contracts') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_contracts'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Value -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::contract.total_value') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_value'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Paid -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::contract.total_paid') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_paid'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Outstanding -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::contract.outstanding') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['outstanding'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <x-dashboard.packages.data-table
            :title="__('product::contract.contract_list')"
            :description="__('product::contract.total_contracts') . ': ' . $contracts->count()"
            searchable
            :searchRoute="route('product.contracts.index')"
            :searchPlaceholder="__('product::contract.search')"
            :filters="$tableFilters"
            :data="$tableData"
            :columns="$tableColumns"
            :actions="$tableActions"
            :createRoute="route('product.contracts.create')"
            :createLabel="__('product::contract.add_contract')"
            :emptyStateTitle="__('product::contract.no_contracts')"
            :emptyStateDescription="__('common.no_data')"
            emptyStateIcon="document"
        />
    </div>
</x-dashboard>
