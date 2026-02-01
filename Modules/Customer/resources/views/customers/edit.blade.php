<x-dashboard :pageTitle="__('customer::customer.edit_customer')">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('customer.customers.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('customer::customer.customers') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('customer::customer.edit_customer') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('customer::customer.edit_customer') }}: {{ $customer->name }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
            </div>

            <div class="p-6">
                <x-dashboard.packages.form-builder 
                    :action="route('customer.customers.update', $customer)" 
                    method="POST"
                    :formConfig="[
                        'groups' => [
                            [
                                'title' => __('customer::customer.basic_info'),
                                'fields' => [
                                    [
                                        'name' => 'name',
                                        'type' => 'text',
                                        'label' => __('customer::customer.name'),
                                        'placeholder' => __('customer::customer.enter_name'),
                                        'required' => true,
                                        'value' => $customer->name,
                                        'grid' => 6,
                                        'borderColor' => '#3b82f6'
                                    ],
                                    [
                                        'name' => 'type',
                                        'type' => 'select',
                                        'label' => __('customer::customer.type'),
                                        'required' => true,
                                        'grid' => 6,
                                        'borderColor' => '#3b82f6',
                                        'options' => [
                                            ['value' => '', 'label' => __('customer::customer.select_type')],
                                            ['value' => 'individual', 'label' => __('customer::customer.types.individual'), 'selected' => $customer->type === 'individual'],
                                            ['value' => 'company', 'label' => __('customer::customer.types.company'), 'selected' => $customer->type === 'company'],
                                            ['value' => 'online', 'label' => __('customer::customer.types.online'), 'selected' => $customer->type === 'online'],
                                        ],
                                        'value' => $customer->type
                                    ]
                                ]
                            ],
                            [
                                'title' => __('customer::customer.contact_info'),
                                'fields' => [
                                    [
                                        'name' => 'phone',
                                        'type' => 'text',
                                        'label' => __('customer::customer.phone'),
                                        'placeholder' => __('customer::customer.enter_phone'),
                                        'required' => false,
                                        'value' => $customer->phone,
                                        'grid' => 6,
                                        'borderColor' => '#10b981'
                                    ],
                                    [
                                        'name' => 'email',
                                        'type' => 'email',
                                        'label' => __('customer::customer.email'),
                                        'placeholder' => __('customer::customer.enter_email'),
                                        'required' => false,
                                        'value' => $customer->email,
                                        'grid' => 6,
                                        'borderColor' => '#10b981'
                                    ],
                                    [
                                        'name' => 'address',
                                        'type' => 'textarea',
                                        'label' => __('customer::customer.address'),
                                        'placeholder' => __('customer::customer.enter_address'),
                                        'required' => false,
                                        'value' => $customer->address,
                                        'grid' => 12,
                                        'borderColor' => '#10b981',
                                        'rows' => 3
                                    ]
                                ]
                            ],
                            [
                                'title' => __('customer::customer.tax_info'),
                                'fields' => [
                                    [
                                        'name' => 'tax_number',
                                        'type' => 'text',
                                        'label' => __('customer::customer.tax_number'),
                                        'placeholder' => __('customer::customer.enter_tax_number'),
                                        'required' => false,
                                        'value' => $customer->tax_number,
                                        'grid' => 6,
                                        'borderColor' => '#8b5cf6'
                                    ],
                                    [
                                        'name' => 'is_active',
                                        'type' => 'select',
                                        'label' => __('customer::customer.status'),
                                        'required' => true,
                                        'grid' => 6,
                                        'borderColor' => '#8b5cf6',
                                        'options' => [
                                            ['value' => '1', 'label' => __('customer::customer.active'), 'selected' => $customer->is_active],
                                            ['value' => '0', 'label' => __('customer::customer.inactive'), 'selected' => !$customer->is_active],
                                        ],
                                        'value' => $customer->is_active ? '1' : '0'
                                    ]
                                ]
                            ]
                        ]
                    ]"
                />
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Add hidden method field for PUT request
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('dynamicForm');
            if (form) {
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'PUT';
                form.insertBefore(methodField, form.firstChild);
            }
        });
    </script>
    @endpush
</x-dashboard>