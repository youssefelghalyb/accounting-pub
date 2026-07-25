<x-dashboard :pageTitle="__('product::contractor.contractors')">
    @php
        $tableData = $contractors->map(function ($contractor) {
            return [
                'id' => $contractor->id,
                'name' => $contractor->name,
                'email' => $contractor->email,
                'phone' => $contractor->phone,
                'nationality' => $contractor->nationality,
                'contractor_books_count' => $contractor->contractor_books_count,
                'model' => $contractor,
            ];
        })->toArray();

        $tableColumns = [
            [
                'label' => __('product::contractor.name'),
                'field' => 'name',
                'render' => function ($row) {
                    $initials = strtoupper(mb_substr($row['name'], 0, 2));
                    $html = '<div class="flex items-center gap-3">';
                    $html .= '<div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm">' . $initials . '</div>';
                    $html .= '<p class="font-medium text-gray-900">' . e($row['name']) . '</p>';
                    $html .= '</div>';
                    return $html;
                },
            ],
            [
                'label' => __('product::contractor.email'),
                'field' => 'email',
                'format' => fn($v) => $v ? '<span class="text-sm text-gray-600">' . e($v) . '</span>' : '<span class="text-sm text-gray-400">-</span>',
            ],
            [
                'label' => __('product::contractor.phone'),
                'field' => 'phone',
                'format' => fn($v) => $v ? '<span class="text-sm text-gray-600">' . e($v) . '</span>' : '<span class="text-sm text-gray-400">-</span>',
            ],
            [
                'label' => __('product::contractor.nationality'),
                'field' => 'nationality',
                'format' => fn($v) => $v ? '<span class="text-sm text-gray-600">' . e($v) . '</span>' : '<span class="text-sm text-gray-400">-</span>',
            ],
            [
                'label' => __('product::contractor.total_books'),
                'field' => 'contractor_books_count',
                'format' => fn($v) => '<span class="px-2 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-800">' . $v . '</span>',
            ],
        ];

        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('product.contractors.show', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
                'color' => 'text-blue-600',
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('product.contractors.edit', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
                'color' => 'text-green-600',
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('product.contractors.destroy', $row['model']),
                'method' => 'DELETE',
                'confirm' => __('common.are_you_sure'),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>',
                'color' => 'text-red-600',
            ],
        ];
    @endphp

    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm font-medium text-gray-600">{{ __('product::contractor.total_contractors') }}</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_contractors'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm font-medium text-gray-600">{{ __('product::contractor.total_contracted_books') }}</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_contracted_books'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm font-medium text-gray-600">{{ __('product::contractor.total_royalty_accrued') }}</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_royalty_accrued'], 2) }}</p>
            </div>
        </div>

        <x-dashboard.packages.data-table
            :title="__('product::contractor.contractor_list')"
            :description="__('product::contractor.total_contractors') . ': ' . $contractors->total()"
            searchable
            :searchRoute="route('product.contractors.index')"
            :searchPlaceholder="__('product::contractor.search')"
            :data="$tableData"
            :columns="$tableColumns"
            :actions="$tableActions"
            :createRoute="route('product.contractors.create')"
            :createLabel="__('product::contractor.add_contractor')"
            :emptyStateTitle="__('product::contractor.no_contractors')"
            :emptyStateDescription="__('common.no_data')"
            emptyStateIcon="users"
            :pagination="$contractors"
            showPerPage :perPage="[10, 25, 50, 100]"
        />
    </div>
</x-dashboard>
