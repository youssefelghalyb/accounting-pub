<x-dashboard :pageTitle="__('product::contract.contracts')">
    @php
        $tableData = $contracts->map(function ($contract) {
            return [
                'id'                 => $contract->id,
                'authors_names'      => $contract->authors_names,
                'book_name'          => isset($contract->book) ? $contract->book->product->name : $contract->book_name,
                'contract_date'      => $contract->contract_date->format('Y-m-d'),
                'contract_price'     => $contract->contract_price,
                'total_paid'         => $contract->total_paid,
                'outstanding_balance'=> $contract->outstanding_balance,
                'payment_status'     => $contract->payment_status,
                'model'              => $contract,
            ];
        })->toArray();

        $tableColumns = [
            [
                'label' => __('product::contract.authors'),
                'field' => 'authors_names',
                'format' => fn($value) => '<span class="font-medium text-gray-900">' . e($value) . '</span>',
            ],
            [
                'label' => __('product::contract.book'),
                'field' => 'book_name',
                'format' => fn($value) => '<span class="text-sm text-gray-600">' . e($value) . '</span>',
            ],
            [
                'label' => __('product::contract.contract_date'),
                'field' => 'contract_date',
                'format' => fn($value) => '<span class="text-sm text-gray-600">' . $value . '</span>',
            ],
            [
                'label' => __('product::contract.contract_price'),
                'field' => 'contract_price',
                'format' => fn($value) => '<span class="font-medium text-gray-900">' . number_format($value, 2) . '</span>',
            ],
            [
                'label' => __('product::contract.total_paid'),
                'field' => 'total_paid',
                'format' => fn($value) => '<span class="text-sm text-green-600 font-medium">' . number_format($value, 2) . '</span>',
            ],
            [
                'label' => __('product::contract.payment_status'),
                'field' => 'payment_status',
                'render' => function ($row) {
                    $colors = [
                        'paid'    => 'bg-green-100 text-green-800',
                        'partial' => 'bg-yellow-100 text-yellow-800',
                        'pending' => 'bg-red-100 text-red-800',
                    ];
                    $color = $colors[$row['payment_status']] ?? 'bg-gray-100 text-gray-800';
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $color . '">'
                        . __('product::contract.' . $row['payment_status'])
                        . '</span>';
                },
            ],
        ];

        $tableActions = [
            [
                'type'  => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('product.contracts.show', $row['model']),
                'icon'  => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
                'color' => 'text-blue-600',
            ],
            [
                'type'  => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('product.contracts.edit', $row['model']),
                'icon'  => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
                'color' => 'text-green-600',
            ],
            [
                'type'    => 'form',
                'label'   => __('common.delete'),
                'route'   => fn($row) => route('product.contracts.destroy', $row['model']),
                'method'  => 'DELETE',
                'confirm' => __('common.are_you_sure'),
                'icon'    => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>',
                'color'   => 'text-red-600',
            ],
        ];

        $tableFilters = [
            [
                'type'    => 'select',
                'name'    => 'author_id',
                'label'   => __('product::contract.all_authors'),
                'options' => $authors->map(fn($a) => ['value' => $a->id, 'label' => $a->full_name])->toArray(),
            ],
            [
                'type'    => 'select',
                'name'    => 'book_id',
                'label'   => __('product::contract.all_books'),
                'options' => $books->map(fn($b) => ['value' => $b->id, 'label' => $b->product->name])->toArray(),
            ],
        ];
    @endphp

    <div class="space-y-6">
        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-sm font-medium text-gray-600">{{ __('product::contract.total_contracts') }}</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_contracts'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-sm font-medium text-gray-600">{{ __('product::contract.total_value') }}</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_value'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-sm font-medium text-gray-600">{{ __('product::contract.total_paid') }}</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_paid'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-sm font-medium text-gray-600">{{ __('product::contract.outstanding') }}</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['outstanding'], 2) }}</p>
            </div>
        </div>

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