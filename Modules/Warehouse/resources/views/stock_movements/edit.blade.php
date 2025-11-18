@php
    $formConfig = [
        'groups' => [
            [
                'title' => __('warehouse::stock_movement.stock_movement_details'),
                'fields' => [
                    [
                        'name' => 'movement_type',
                        'type' => 'select',
                        'label' => __('warehouse::stock_movement.movement_type'),
                        'placeholder' => __('warehouse::stock_movement.select_type'),
                        'required' => true,
                        'grid' => 4,
                        'borderColor' => '#3b82f6',
                        'options' => [
                            ['value' => 'transfer', 'label' => __('warehouse::stock_movement.transfer')],
                            ['value' => 'inbound', 'label' => __('warehouse::stock_movement.inbound')],
                            ['value' => 'outbound', 'label' => __('warehouse::stock_movement.outbound')]
                        ],
                        'value' => $movement->movement_type,
                        'disabled' => true
                    ],
                    [
                        'name' => 'product_id',
                        'type' => 'select',
                        'label' => __('warehouse::stock_movement.product'),
                        'placeholder' => __('warehouse::stock_movement.select_product'),
                        'required' => true,
                        'grid' => 4,
                        'borderColor' => '#10b981',
                        'options' => $products->map(function($product) {
                            return [
                                'value' => $product->id,
                                'label' => $product->name . ($product->sku ? ' (SKU: ' . $product->sku . ')' : '')
                            ];
                        })->toArray(),
                        'value' => $movement->product_id,
                        'disabled' => true
                    ],
                    [
                        'name' => 'quantity',
                        'type' => 'number',
                        'label' => __('warehouse::stock_movement.quantity'),
                        'placeholder' => __('warehouse::stock_movement.enter_quantity'),
                        'required' => true,
                        'grid' => 4,
                        'borderColor' => '#8b5cf6',
                        'min' => 1,
                        'value' => $movement->quantity,
                        'disabled' => true
                    ],
                    [
                        'name' => 'from_sub_warehouse_id',
                        'type' => 'select',
                        'label' => __('warehouse::stock_movement.from_sub_warehouse'),
                        'placeholder' => __('warehouse::stock_movement.select_from_warehouse'),
                        'required' => false,
                        'grid' => 6,
                        'borderColor' => '#ef4444',
                        'options' => $subWarehouses->map(function($subWarehouse) {
                            return [
                                'value' => $subWarehouse->id,
                                'label' => $subWarehouse->name . ' (' . $subWarehouse->warehouse->name . ')'
                            ];
                        })->toArray(),
                        'value' => $movement->from_sub_warehouse_id,
                        'disabled' => true
                    ],
                    [
                        'name' => 'to_sub_warehouse_id',
                        'type' => 'select',
                        'label' => __('warehouse::stock_movement.to_sub_warehouse'),
                        'placeholder' => __('warehouse::stock_movement.select_to_warehouse'),
                        'required' => false,
                        'grid' => 6,
                        'borderColor' => '#10b981',
                        'options' => $subWarehouses->map(function($subWarehouse) {
                            return [
                                'value' => $subWarehouse->id,
                                'label' => $subWarehouse->name . ' (' . $subWarehouse->warehouse->name . ')'
                            ];
                        })->toArray(),
                        'value' => $movement->to_sub_warehouse_id,
                        'disabled' => true
                    ],
                    [
                        'name' => 'reason',
                        'type' => 'text',
                        'label' => __('warehouse::stock_movement.reason'),
                        'placeholder' => __('warehouse::stock_movement.enter_reason'),
                        'required' => false,
                        'grid' => 6,
                        'borderColor' => '#f59e0b',
                        'value' => $movement->reason
                    ],
                    [
                        'name' => 'reference_id',
                        'type' => 'text',
                        'label' => __('warehouse::stock_movement.reference_id'),
                        'placeholder' => __('warehouse::stock_movement.enter_reference_id'),
                        'required' => false,
                        'grid' => 6,
                        'borderColor' => '#06b6d4',
                        'value' => $movement->reference_id
                    ],
                    [
                        'name' => 'notes',
                        'type' => 'textarea',
                        'label' => __('warehouse::stock_movement.notes'),
                        'placeholder' => __('warehouse::stock_movement.enter_notes'),
                        'required' => false,
                        'rows' => 3,
                        'grid' => 12,
                        'borderColor' => '#6366f1',
                        'value' => $movement->notes
                    ]
                ]
            ]
        ]
    ];
@endphp

<x-dashboard :pageTitle="__('warehouse::stock_movement.edit_stock_movement')">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('warehouse.stock_movements.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('warehouse::stock_movement.stock_movements') }}
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
                    <span class="text-gray-900 font-medium">{{ __('warehouse::stock_movement.edit_stock_movement') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Warning Notice -->
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex">
                <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-yellow-800">{{ __('common.notice') }}</h3>
                    <p class="text-sm text-yellow-700 mt-1">
                        {{ __('common.critical_fields_disabled') }}
                        You can only edit the reason, reference ID, and notes fields. Movement type, product, quantity, and warehouses cannot be changed after creation.
                    </p>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('warehouse::stock_movement.edit_stock_movement') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
            </div>

            <div class="p-6">
                <x-dashboard.packages.form-builder
                    :action="route('warehouse.stock_movements.update', $movement)"
                    method="PUT"
                    :formConfig="$formConfig"
                />
            </div>
        </div>
    </div>
</x-dashboard>
