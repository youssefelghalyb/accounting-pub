<x-dashboard :pageTitle="__('product::author.authors')">
    @php
        // Prepare data array
        $tableData = $authors->map(function($author) {
            return [
                'id' => $author->id,
                'full_name' => $author->full_name,
                'email' => $author->email,
                'phone_number' => $author->phone_number,
                'nationality' => $author->nationality,
                'occupation' => $author->occupation,
                'books_count' => $author->books()->count(),
                'contracts_count' => $author->contracts()->count(),
                'model' => $author
            ];
        })->toArray();

        // Prepare columns array
        $tableColumns = [
            [
                'label' => __('product::author.full_name'),
                'field' => 'full_name',
                'render' => function($row) {
                    $initials = strtoupper(substr($row['full_name'], 0, 2));
                    $html = '<div class="flex items-center gap-3">';
                    $html .= '<div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">' . $initials . '</div>';
                    $html .= '<div>';
                    $html .= '<p class="font-medium text-gray-900">' . e($row['full_name']) . '</p>';
                    if ($row['occupation']) {
                        $html .= '<p class="text-xs text-gray-500">' . e($row['occupation']) . '</p>';
                    }
                    $html .= '</div>';
                    $html .= '</div>';
                    return $html;
                }
            ],
            [
                'label' => __('product::author.email'),
                'field' => 'email',
                'format' => function($value) {
                    return $value ? '<span class="text-sm text-gray-600">' . e($value) . '</span>' : '<span class="text-sm text-gray-400">-</span>';
                }
            ],
            [
                'label' => __('product::author.phone_number'),
                'field' => 'phone_number',
                'format' => function($value) {
                    return $value ? '<span class="text-sm text-gray-600">' . e($value) . '</span>' : '<span class="text-sm text-gray-400">-</span>';
                }
            ],
            [
                'label' => __('product::author.nationality'),
                'field' => 'nationality',
                'format' => function($value) {
                    return $value ? '<span class="text-sm text-gray-600">' . e($value) . '</span>' : '<span class="text-sm text-gray-400">-</span>';
                }
            ],
            [
                'label' => __('product::author.total_books'),
                'field' => 'books_count',
                'format' => function($value) {
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">' . $value . '</span>';
                }
            ],
            [
                'label' => __('product::author.total_contracts'),
                'field' => 'contracts_count',
                'format' => function($value) {
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">' . $value . '</span>';
                }
            ]
        ];

        // Prepare actions array
        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('product.authors.show', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600'
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('product.authors.edit', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600'
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('product.authors.destroy', $row['model']),
                'method' => 'DELETE',
                'confirm' => __('common.are_you_sure'),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                'color' => 'text-red-600'
            ]
        ];
    @endphp

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Authors -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::author.total_authors') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_authors'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Contracts -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::author.total_contracts') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_contracts'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Contract Value -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::author.total_contract_value') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_contract_value'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <x-dashboard.packages.data-table
            :title="__('product::author.author_list')"
            :description="__('product::author.total_authors') . ': ' . $authors->count()"
            searchable
            :searchRoute="route('product.authors.index')"
            :searchPlaceholder="__('product::author.search')"
            :data="$tableData"
            :columns="$tableColumns"
            :actions="$tableActions"
            :createRoute="route('product.authors.create')"
            :createLabel="__('product::author.add_author')"
            :emptyStateTitle="__('product::author.no_authors')"
            :emptyStateDescription="__('common.no_data')"
            emptyStateIcon="users"
        />
    </div>
</x-dashboard>
