<x-dashboard :pageTitle="__('finance::party.add_party')">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('finance.parties.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('finance::party.parties') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('finance::party.add_party') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('finance::party.add_party') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
            </div>

            <div class="p-6">
                <x-dashboard.packages.form-builder 
                    :action="route('finance.parties.store')" 
                    method="POST"
                    :formConfig="[
                        'groups' => [
                            [
                                'title' => __('finance::party.basic_info'),
                                'fields' => [
                                    [
                                        'name' => 'name',
                                        'type' => 'text',
                                        'label' => __('finance::party.name'),
                                        'placeholder' => __('finance::party.enter_name'),
                                        'required' => true,
                                        'grid' => 12,
                                        'borderColor' => '#3b82f6'
                                    ],
                                    [
                                        'name' => 'type',
                                        'type' => 'select',
                                        'label' => __('finance::party.type'),
                                        'required' => true,
                                        'grid' => 6,
                                        'borderColor' => '#3b82f6',
                                        'options' => [
                                            ['value' => '', 'label' => __('finance::party.select_type')],
                                            ['value' => 'individual', 'label' => __('finance::party.types.individual')],
                                            ['value' => 'company', 'label' => __('finance::party.types.company')],
                                            ['value' => 'online', 'label' => __('finance::party.types.online')],
                                        ]
                                    ],
                                    [
                                        'name' => 'tax_number',
                                        'type' => 'text',
                                        'label' => __('finance::party.tax_number'),
                                        'placeholder' => __('finance::party.enter_tax_number'),
                                        'required' => false,
                                        'grid' => 6,
                                        'borderColor' => '#3b82f6'
                                    ]
                                ]
                            ],
                            [
                                'title' => __('finance::party.contact_info'),
                                'fields' => [
                                    [
                                        'name' => 'email',
                                        'type' => 'email',
                                        'label' => __('finance::party.email'),
                                        'placeholder' => __('finance::party.enter_email'),
                                        'required' => false,
                                        'grid' => 6,
                                        'borderColor' => '#10b981'
                                    ],
                                    [
                                        'name' => 'phone',
                                        'type' => 'text',
                                        'label' => __('finance::party.phone'),
                                        'placeholder' => __('finance::party.enter_phone'),
                                        'required' => false,
                                        'grid' => 6,
                                        'borderColor' => '#10b981'
                                    ],
                                    [
                                        'name' => 'address',
                                        'type' => 'textarea',
                                        'label' => __('finance::party.address'),
                                        'placeholder' => __('finance::party.enter_address'),
                                        'required' => false,
                                        'rows' => 3,
                                        'grid' => 12,
                                        'borderColor' => '#10b981'
                                    ]
                                ]
                            ]
                        ]
                    ]"
                />
            </div>
        </div>
    </div>
</x-dashboard>