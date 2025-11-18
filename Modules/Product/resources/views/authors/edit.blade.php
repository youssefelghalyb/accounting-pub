@php
$formConfig = [
    'groups' => [
        [
            'title' => __('product::author.personal_info'),
            'fields' => [
                [
                    'name' => 'full_name',
                    'type' => 'text',
                    'label' => __('product::author.full_name'),
                    'placeholder' => __('product::author.enter_full_name'),
                    'required' => true,
                    'value' => $author->full_name,
                    'grid' => 6,
                    'borderColor' => '#3b82f6'
                ],
                [
                    'name' => 'nationality',
                    'type' => 'text',
                    'label' => __('product::author.nationality'),
                    'placeholder' => __('product::author.enter_nationality'),
                    'required' => false,
                    'value' => $author->nationality,
                    'grid' => 6,
                    'borderColor' => '#3b82f6'
                ],
                [
                    'name' => 'country_of_residence',
                    'type' => 'text',
                    'label' => __('product::author.country_of_residence'),
                    'placeholder' => __('product::author.enter_country'),
                    'required' => false,
                    'value' => $author->country_of_residence,
                    'grid' => 6,
                    'borderColor' => '#3b82f6'
                ],
                [
                    'name' => 'occupation',
                    'type' => 'text',
                    'label' => __('product::author.occupation'),
                    'placeholder' => __('product::author.enter_occupation'),
                    'required' => false,
                    'value' => $author->occupation,
                    'grid' => 6,
                    'borderColor' => '#3b82f6'
                ],
                [
                    'name' => 'bio',
                    'type' => 'textarea',
                    'label' => __('product::author.bio'),
                    'placeholder' => __('product::author.enter_bio'),
                    'required' => false,
                    'value' => $author->bio,
                    'rows' => 4,
                    'grid' => 12,
                    'borderColor' => '#3b82f6'
                ]
            ]
        ],
        [
            'title' => __('product::author.contact_info'),
            'fields' => [
                [
                    'name' => 'email',
                    'type' => 'email',
                    'label' => __('product::author.email'),
                    'placeholder' => __('product::author.enter_email'),
                    'required' => false,
                    'value' => $author->email,
                    'grid' => 6,
                    'borderColor' => '#10b981'
                ],
                [
                    'name' => 'phone_number',
                    'type' => 'text',
                    'label' => __('product::author.phone_number'),
                    'placeholder' => __('product::author.enter_phone'),
                    'required' => false,
                    'value' => $author->phone_number,
                    'grid' => 6,
                    'borderColor' => '#10b981'
                ],
                [
                    'name' => 'whatsapp_number',
                    'type' => 'text',
                    'label' => __('product::author.whatsapp_number'),
                    'placeholder' => __('product::author.enter_whatsapp'),
                    'required' => false,
                    'value' => $author->whatsapp_number,
                    'grid' => 12,
                    'borderColor' => '#10b981'
                ]
            ]
        ],
        [
            'title' => __('product::author.additional_info'),
            'fields' => [
                [
                    'name' => 'id_image',
                    'type' => 'file',
                    'label' => __('product::author.id_image'),
                    'accept' => 'image/*',
                    'helperText' => __('product::author.upload_id_image'),
                    'required' => false,
                    'value' => $author->id_image ? asset('storage/' . $author->id_image) : null,
                    'grid' => 12,
                    'borderColor' => '#8b5cf6'
                ]
            ]
        ]
    ]
];
@endphp

<x-dashboard :pageTitle="__('product::author.edit_author')">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('product.authors.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('product::author.authors') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('product::author.edit_author') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('product::author.edit_author') }}: {{ $author->full_name }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
            </div>

            <div class="p-6">
                <x-dashboard.packages.form-builder
                    :action="route('product.authors.update', $author)"
                    method="POST"
                    :formConfig="$formConfig"
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
