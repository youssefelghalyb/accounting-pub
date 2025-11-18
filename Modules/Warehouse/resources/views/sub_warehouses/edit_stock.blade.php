@php
    $formConfig = [
        'groups' => [
            [
                'title' => __('warehouse::sub_warehouse.stock_details'),
                'fields' => [
                    [
                        'name' => 'product_name',
                        'type' => 'text',
                        'label' => __('warehouse::sub_warehouse.product'),
                        'value' => $warehouseProduct->product->name . ($warehouseProduct->product->sku ? ' (SKU: ' . $warehouseProduct->product->sku . ')' : ''),
                        'disabled' => true,
                        'grid' => 12,
                        'borderColor' => '#3b82f6'
                    ],
                    [
                        'name' => 'current_quantity',
                        'type' => 'number',
                        'label' => __('warehouse::sub_warehouse.current_quantity'),
                        'value' => $warehouseProduct->quantity,
                        'disabled' => true,
                        'grid' => 6,
                        'borderColor' => '#10b981'
                    ],
                    [
                        'name' => 'quantity',
                        'type' => 'number',
                        'label' => __('warehouse::sub_warehouse.new_quantity'),
                        'placeholder' => __('warehouse::sub_warehouse.enter_quantity'),
                        'required' => true,
                        'min' => 0,
                        'value' => $warehouseProduct->quantity,
                        'grid' => 6,
                        'borderColor' => '#8b5cf6'
                    ]
                ]
            ]
        ]
    ];
@endphp

<x-dashboard :pageTitle="__('warehouse::sub_warehouse.edit_stock')">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('warehouse.sub_warehouses.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('warehouse::sub_warehouse.sub_warehouses') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}"
                         fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                              clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <a href="{{ route('warehouse.sub_warehouses.show', $subWarehouse) }}" class="text-gray-500 hover:text-gray-700">
                        {{ $subWarehouse->name }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}"
                         fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                              clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('warehouse::sub_warehouse.edit_stock') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Info Notice -->
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex">
                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-blue-800">{{ __('common.notice') }}</h3>
                    <p class="text-sm text-blue-700 mt-1">
                        {{ __('warehouse::sub_warehouse.edit_stock_notice') }}
                    </p>
                </div>
            </div>
        </div>

        @if($warehouseProduct->product->book)
        <!-- Book Details Display -->
        <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
            <h3 class="font-medium text-gray-900 mb-3">{{ __('warehouse::sub_warehouse.book_details') }}</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                @if($warehouseProduct->product->book->isbn)
                <div>
                    <span class="text-gray-700 font-medium">{{ __('warehouse::sub_warehouse.isbn') }}:</span>
                    <span class="text-gray-900">{{ $warehouseProduct->product->book->isbn }}</span>
                </div>
                @endif
                @if($warehouseProduct->product->book->author)
                <div>
                    <span class="text-gray-700 font-medium">{{ __('warehouse::sub_warehouse.author') }}:</span>
                    <span class="text-gray-900">{{ $warehouseProduct->product->book->author->name }}</span>
                </div>
                @endif
                @if($warehouseProduct->product->book->category)
                <div>
                    <span class="text-gray-700 font-medium">{{ __('warehouse::sub_warehouse.category') }}:</span>
                    <span class="text-gray-900">{{ $warehouseProduct->product->book->category->name }}</span>
                </div>
                @endif
                @if($warehouseProduct->product->book->pages)
                <div>
                    <span class="text-gray-700 font-medium">{{ __('warehouse::sub_warehouse.pages') }}:</span>
                    <span class="text-gray-900">{{ $warehouseProduct->product->book->pages }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('warehouse::sub_warehouse.edit_stock') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
            </div>

            <div class="p-6">
                <x-dashboard.packages.form-builder
                    :action="route('warehouse.sub_warehouses.update-stock', [$subWarehouse, $warehouseProduct])"
                    method="PUT"
                    :formConfig="$formConfig"
                />
            </div>
        </div>
    </div>
</x-dashboard>
