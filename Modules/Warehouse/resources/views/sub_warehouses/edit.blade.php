@php
    $formConfig = [
        'groups' => [
            [
                'title' => __('warehouse::sub_warehouse.sub_warehouse_details'),
                'fields' => [
                    [
                        'name' => 'warehouse_id',
                        'type' => 'select',
                        'label' => __('warehouse::sub_warehouse.warehouse'),
                        'placeholder' => __('warehouse::sub_warehouse.select_warehouse'),
                        'required' => true,
                        'grid' => 6,
                        'borderColor' => '#3b82f6',
                        'options' => $warehouses->map(function($warehouse) {
                            return [
                                'value' => $warehouse->id,
                                'label' => $warehouse->name
                            ];
                        })->toArray(),
                        'value' => $subWarehouse->warehouse_id
                    ],
                    [
                        'name' => 'name',
                        'type' => 'text',
                        'label' => __('warehouse::sub_warehouse.name'),
                        'placeholder' => __('warehouse::sub_warehouse.enter_name'),
                        'required' => true,
                        'grid' => 6,
                        'borderColor' => '#10b981',
                        'value' => $subWarehouse->name
                    ],
                    [
                        'name' => 'type',
                        'type' => 'select',
                        'label' => __('warehouse::sub_warehouse.type'),
                        'placeholder' => __('warehouse::sub_warehouse.select_type'),
                        'required' => true,
                        'grid' => 6,
                        'borderColor' => '#8b5cf6',
                        'options' => [
                            ['value' => 'main', 'label' => __('warehouse::sub_warehouse.main')],
                            ['value' => 'branch', 'label' => __('warehouse::sub_warehouse.branch')],
                            ['value' => 'book_fair', 'label' => __('warehouse::sub_warehouse.book_fair')],
                            ['value' => 'temporary', 'label' => __('warehouse::sub_warehouse.temporary')],
                            ['value' => 'other', 'label' => __('warehouse::sub_warehouse.other')]
                        ],
                        'value' => $subWarehouse->type
                    ],
                    [
                        'name' => 'country',
                        'type' => 'text',
                        'label' => __('warehouse::sub_warehouse.country'),
                        'placeholder' => __('warehouse::sub_warehouse.enter_country'),
                        'required' => false,
                        'grid' => 6,
                        'borderColor' => '#f59e0b',
                        'value' => $subWarehouse->country
                    ],
                    [
                        'name' => 'address',
                        'type' => 'textarea',
                        'label' => __('warehouse::sub_warehouse.address'),
                        'placeholder' => __('warehouse::sub_warehouse.enter_address'),
                        'required' => false,
                        'rows' => 3,
                        'grid' => 12,
                        'borderColor' => '#06b6d4',
                        'value' => $subWarehouse->address
                    ],
                    [
                        'name' => 'notes',
                        'type' => 'textarea',
                        'label' => __('warehouse::sub_warehouse.notes'),
                        'placeholder' => __('warehouse::sub_warehouse.enter_notes'),
                        'required' => false,
                        'rows' => 3,
                        'grid' => 12,
                        'borderColor' => '#6366f1',
                        'value' => $subWarehouse->notes
                    ]
                ]
            ]
        ]
    ];
@endphp

<x-dashboard :pageTitle="__('warehouse::sub_warehouse.edit_sub_warehouse')">
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
                    <span class="text-gray-900 font-medium">{{ __('warehouse::sub_warehouse.edit_sub_warehouse') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('warehouse::sub_warehouse.edit_sub_warehouse') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
            </div>

            <div class="p-6">
                <x-dashboard.packages.form-builder
                    :action="route('warehouse.sub_warehouses.update', $subWarehouse)"
                    method="PUT"
                    :formConfig="$formConfig"
                />
            </div>
        </div>
    </div>
</x-dashboard>
