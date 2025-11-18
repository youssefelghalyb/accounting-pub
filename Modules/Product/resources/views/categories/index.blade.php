<x-dashboard :pageTitle="__('product::category.categories')">
    @php
        // Prepare data array
        $tableData = $categories->map(function($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'parent_name' => $category->parent?->name,
                'children_count' => $category->children()->count(),
                'books_count' => $category->books()->count(),
                'created_at' => $category->created_at->format('Y-m-d'),
                'model' => $category
            ];
        })->toArray();

        // Prepare columns array
        $tableColumns = [
            [
                'label' => __('product::category.name'),
                'field' => 'name',
                'render' => function($row) {
                    $html = '<div>';
                    $html .= '<p class="font-medium text-gray-900">' . e($row['name']) . '</p>';
                    if ($row['parent_name']) {
                        $html .= '<p class="text-xs text-gray-500">' . __('common.parent') . ': ' . e($row['parent_name']) . '</p>';
                    }
                    $html .= '</div>';
                    return $html;
                }
            ],
            [
                'label' => __('product::category.parent_category'),
                'field' => 'parent_name',
                'format' => function($value) {
                    return $value ? '<span class="text-sm text-gray-600">' . e($value) . '</span>' : '<span class="text-sm text-gray-400">-</span>';
                }
            ],
            [
                'label' => __('product::category.sub_categories'),
                'field' => 'children_count',
                'format' => function($value) {
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">' . $value . '</span>';
                }
            ],
            [
                'label' => __('product::category.total_books'),
                'field' => 'books_count',
                'format' => function($value) {
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">' . $value . '</span>';
                }
            ],
            [
                'label' => __('common.created_at'),
                'field' => 'created_at',
                'format' => function($value) {
                    return '<span class="text-sm text-gray-600">' . $value . '</span>';
                }
            ]
        ];

        // Prepare actions array
        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('product.categories.show', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600'
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('product.categories.edit', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600'
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('product.categories.destroy', $row['model']),
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
                'name' => 'filter',
                'label' => __('product::category.filter_all'),
                'options' => [
                    ['value' => 'parent', 'label' => __('product::category.filter_parent')],
                    ['value' => 'child', 'label' => __('product::category.filter_child')],
                ]
            ]
        ];
    @endphp

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Categories -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::category.total_categories') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_categories'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Parent Categories -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::category.parent_categories') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['parent_categories'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Sub Categories -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::category.sub_categories') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['sub_categories'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <x-dashboard.packages.data-table
            :title="__('product::category.category_list')"
            :description="__('product::category.total_categories') . ': ' . $categories->count()"
            searchable
            :searchRoute="route('product.categories.index')"
            :searchPlaceholder="__('product::category.search')"
            :filters="$tableFilters"
            :data="$tableData"
            :columns="$tableColumns"
            :actions="$tableActions"
            :createRoute="route('product.categories.create')"
            :createLabel="__('product::category.add_category')"
            :emptyStateTitle="__('product::category.no_categories')"
            :emptyStateDescription="__('common.no_data')"
            emptyStateIcon="folder"
        />
    </div>
</x-dashboard>
