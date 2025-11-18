<x-dashboard :pageTitle="__('product::book.books')">
    @php
        // Prepare data array
        $tableData = $books->map(function($book) {
            return [
                'id' => $book->id,
                'name' => $book->product->name,
                'isbn' => $book->isbn,
                'author_name' => $book->author?->full_name,
                'category_name' => $book->category?->name,
                'num_of_pages' => $book->num_of_pages,
                'cover_type' => $book->cover_type,
                'base_price' => $book->product->base_price,
                'is_translated' => $book->is_translated,
                'model' => $book
            ];
        })->toArray();

        // Prepare columns array
        $tableColumns = [
            [
                'label' => __('product::product.name'),
                'field' => 'name',
                'render' => function($row) {
                    $html = '<div>';
                    $html .= '<p class="font-medium text-gray-900">' . e($row['name']) . '</p>';
                    $html .= '<p class="text-xs text-gray-500">ISBN: ' . e($row['isbn']) . '</p>';
                    $html .= '</div>';
                    return $html;
                }
            ],
            [
                'label' => __('product::book.author'),
                'field' => 'author_name',
                'format' => function($value) {
                    return $value ? '<span class="text-sm text-gray-600">' . e($value) . '</span>' : '<span class="text-sm text-gray-400">-</span>';
                }
            ],
            [
                'label' => __('product::book.category'),
                'field' => 'category_name',
                'format' => function($value) {
                    return $value ? '<span class="text-sm text-gray-600">' . e($value) . '</span>' : '<span class="text-sm text-gray-400">-</span>';
                }
            ],
            [
                'label' => __('product::book.num_of_pages'),
                'field' => 'num_of_pages',
                'format' => function($value) {
                    return $value ? '<span class="text-sm text-gray-600">' . number_format($value) . '</span>' : '<span class="text-sm text-gray-400">-</span>';
                }
            ],
            [
                'label' => __('product::book.cover_type'),
                'field' => 'cover_type',
                'render' => function($row) {
                    $color = $row['cover_type'] === 'hard' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800';
                    return '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $color . '">' . __('product::book.' . $row['cover_type']) . '</span>';
                }
            ],
            [
                'label' => __('product::product.base_price'),
                'field' => 'base_price',
                'format' => function($value) {
                    return '<span class="font-medium text-gray-900">' . number_format($value, 2) . '</span>';
                }
            ]
        ];

        // Prepare actions array
        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('product.books.show', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
                'color' => 'text-blue-600'
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('product.books.edit', $row['model']),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>',
                'color' => 'text-green-600'
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('product.books.destroy', $row['model']),
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
                'label' => __('product::book.all_authors'),
                'options' => $authors->map(function($author) {
                    return [
                        'value' => $author->id,
                        'label' => $author->full_name
                    ];
                })->toArray()
            ],
            [
                'type' => 'select',
                'name' => 'category_id',
                'label' => __('product::book.all_categories'),
                'options' => $categories->map(function($cat) {
                    return [
                        'value' => $cat->id,
                        'label' => $cat->name
                    ];
                })->toArray()
            ]
        ];
    @endphp

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Books -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::book.total_books') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_books'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Pages -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::book.total_pages') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_pages']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Translated Books -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::book.translated_books') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['translated_books'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <x-dashboard.packages.data-table
            :title="__('product::book.book_list')"
            :description="__('product::book.total_books') . ': ' . $books->count()"
            searchable
            :searchRoute="route('product.books.index')"
            :searchPlaceholder="__('product::book.search')"
            :filters="$tableFilters"
            :data="$tableData"
            :columns="$tableColumns"
            :actions="$tableActions"
            :createRoute="route('product.books.create')"
            :createLabel="__('product::book.add_book')"
            :emptyStateTitle="__('product::book.no_books')"
            :emptyStateDescription="__('common.no_data')"
            emptyStateIcon="document"
        />
    </div>
</x-dashboard>
