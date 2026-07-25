@php
$formConfig = [
    'groups' => [
        [
            'title' => __('product::contractor.personal_info'),
            'fields' => [
                [
                    'name' => 'name',
                    'type' => 'text',
                    'label' => __('product::contractor.name'),
                    'placeholder' => __('product::contractor.enter_name'),
                    'required' => true,
                    'grid' => 6,
                    'borderColor' => '#10b981'
                ],
                [
                    'name' => 'nationality',
                    'type' => 'text',
                    'label' => __('product::contractor.nationality'),
                    'placeholder' => __('product::contractor.enter_nationality'),
                    'required' => false,
                    'grid' => 6,
                    'borderColor' => '#10b981'
                ],
                [
                    'name' => 'address',
                    'type' => 'textarea',
                    'label' => __('product::contractor.address'),
                    'placeholder' => __('product::contractor.enter_address'),
                    'required' => false,
                    'rows' => 3,
                    'grid' => 12,
                    'borderColor' => '#10b981'
                ]
            ]
        ],
        [
            'title' => __('product::contractor.contact_info'),
            'fields' => [
                [
                    'name' => 'email',
                    'type' => 'email',
                    'label' => __('product::contractor.email'),
                    'placeholder' => __('product::contractor.enter_email'),
                    'required' => false,
                    'grid' => 6,
                    'borderColor' => '#3b82f6'
                ],
                [
                    'name' => 'secondary_email',
                    'type' => 'email',
                    'label' => __('product::contractor.secondary_email'),
                    'required' => false,
                    'grid' => 6,
                    'borderColor' => '#3b82f6'
                ],
                [
                    'name' => 'phone',
                    'type' => 'text',
                    'label' => __('product::contractor.phone'),
                    'placeholder' => __('product::contractor.enter_phone'),
                    'required' => false,
                    'grid' => 6,
                    'borderColor' => '#3b82f6'
                ],
                [
                    'name' => 'secondary_phone',
                    'type' => 'text',
                    'label' => __('product::contractor.secondary_phone'),
                    'required' => false,
                    'grid' => 6,
                    'borderColor' => '#3b82f6'
                ]
            ]
        ],
        [
            'title' => __('product::contractor.additional_info'),
            'fields' => [
                [
                    'name' => 'national_id_file',
                    'type' => 'file',
                    'label' => __('product::contractor.national_id_file'),
                    'accept' => 'image/*,.pdf',
                    'helperText' => __('product::contractor.upload_national_id'),
                    'required' => false,
                    'grid' => 12,
                    'borderColor' => '#8b5cf6'
                ]
            ]
        ]
    ]
];
@endphp

<x-dashboard :pageTitle="__('product::contractor.add_contractor')">
    <div class="max-w-5xl mx-auto">
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('product.contractors.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('product::contractor.contractors') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('product::contractor.add_contractor') }}</span>
                </li>
            </ol>
        </nav>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('product::contractor.add_contractor') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
            </div>

            <div class="p-6">
                <x-dashboard.packages.form-builder
                    :action="route('product.contractors.store')"
                    method="POST"
                    :formConfig="$formConfig"
                />
            </div>
        </div>
    </div>
</x-dashboard>
