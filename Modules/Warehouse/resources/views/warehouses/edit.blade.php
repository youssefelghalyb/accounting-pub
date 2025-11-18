@php
    $formConfig = [
        'groups' => [
            [
                'title' => __('warehouse::warehouse.warehouse_details'),
                'fields' => [
                    [
                        'name' => 'name',
                        'type' => 'text',
                        'label' => __('warehouse::warehouse.name'),
                        'placeholder' => __('warehouse::warehouse.enter_name'),
                        'required' => true,
                        'grid' => 12,
                        'borderColor' => '#3b82f6',
                        'value' => $warehouse->name
                    ],
                    [
                        'name' => 'description',
                        'type' => 'textarea',
                        'label' => __('warehouse::warehouse.description'),
                        'placeholder' => __('warehouse::warehouse.enter_description'),
                        'required' => false,
                        'rows' => 4,
                        'grid' => 12,
                        'borderColor' => '#8b5cf6',
                        'value' => $warehouse->description
                    ]
                ]
            ]
        ]
    ];
@endphp

<x-dashboard :pageTitle="__('warehouse::warehouse.edit_warehouse')">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('warehouse.warehouses.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('warehouse::warehouse.warehouses') }}
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
                    <span class="text-gray-900 font-medium">{{ __('warehouse::warehouse.edit_warehouse') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('warehouse::warehouse.edit_warehouse') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
            </div>

            <div class="p-6">
                <x-dashboard.packages.form-builder
                    :action="route('warehouse.warehouses.update', $warehouse)"
                    method="PUT"
                    :formConfig="$formConfig"
                />
            </div>
        </div>
    </div>
</x-dashboard>
