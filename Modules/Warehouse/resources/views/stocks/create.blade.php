@php
    $formConfig = [
        'groups' => [
            [
                'title' => __('warehouse::stocks.stock_details'),
                'fields' => [
                    [
                        'name' => 'product_id',
                        'type' => 'select',
                        'label' => __('warehouse::stocks.product'),
                        'required' => true,
                        'grid' => 6,
                        'borderColor' => '#3b82f6',
                        'options' => array_merge(
                            [['value' => '', 'label' => __('warehouse::stocks.select_product')]],
                            $products->map(function($product) {
                                $label = $product->name;
                                if ($product->sku) {
                                    $label .= ' (SKU: ' . $product->sku . ')';
                                }
                                if ($product->book) {
                                    $label .= ' - ' . $product->book->author->name;
                                }
                                return ['value' => $product->id, 'label' => $label];
                            })->toArray()
                        )
                    ],
                    [
                        'name' => 'warehouse_name',
                        'type' => 'text',
                        'label' => __('warehouse::stocks.warehouse_name'),
                        'placeholder' => __('warehouse::stocks.enter_warehouse_name'),
                        'required' => true,
                        'grid' => 6,
                        'borderColor' => '#3b82f6'
                    ],
                    [
                        'name' => 'location',
                        'type' => 'text',
                        'label' => __('warehouse::stocks.location'),
                        'placeholder' => __('warehouse::stocks.enter_location'),
                        'required' => false,
                        'grid' => 6,
                        'borderColor' => '#8b5cf6'
                    ],
                    [
                        'name' => 'status',
                        'type' => 'select',
                        'label' => __('warehouse::stocks.status'),
                        'required' => true,
                        'grid' => 6,
                        'borderColor' => '#10b981',
                        'options' => [
                            ['value' => '', 'label' => __('warehouse::stocks.select_status')],
                            ['value' => 'active', 'label' => __('warehouse::stocks.active')],
                            ['value' => 'inactive', 'label' => __('warehouse::stocks.inactive')],
                        ]
                    ],
                    [
                        'name' => 'quantity',
                        'type' => 'number',
                        'label' => __('warehouse::stocks.quantity'),
                        'placeholder' => __('warehouse::stocks.enter_quantity'),
                        'required' => true,
                        'grid' => 6,
                        'borderColor' => '#10b981'
                    ],
                    [
                        'name' => 'minimum_quantity',
                        'type' => 'number',
                        'label' => __('warehouse::stocks.minimum_quantity'),
                        'placeholder' => __('warehouse::stocks.enter_minimum_quantity'),
                        'required' => true,
                        'grid' => 6,
                        'borderColor' => '#f59e0b'
                    ],
                    [
                        'name' => 'description',
                        'type' => 'textarea',
                        'label' => __('warehouse::stocks.description'),
                        'placeholder' => __('warehouse::stocks.enter_description'),
                        'required' => false,
                        'rows' => 3,
                        'grid' => 12,
                        'borderColor' => '#8b5cf6'
                    ]
                ]
            ]
        ]
    ];
@endphp

<x-dashboard :pageTitle="__('warehouse::stocks.add_stock')">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('warehouse.stocks.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('warehouse::stocks.stocks') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('warehouse::stocks.add_stock') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('warehouse::stocks.add_stock') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
            </div>

            <div class="p-6">
                <x-dashboard.packages.form-builder
                    :action="route('warehouse.stocks.store')"
                    method="POST"
                    :formConfig="$formConfig"
                />
            </div>
        </div>
    </div>
</x-dashboard>
