<x-dashboard :pageTitle="__('hr::department.edit_department')">
    <div class="max-w-4xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('hr.departments.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('hr::department.departments') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('hr::department.edit_department') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('hr::department.edit_department') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
            </div>

            <div class="p-6">

                    <x-dashboard.packages.form-builder 
                        :action="route('hr.departments.update', $department)" 
                        method="POST"
                        :formConfig="[
                            'fields' => [
                                [
                                    'name' => 'name',
                                    'type' => 'text',
                                    'label' => __('hr::department.name'),
                                    'placeholder' => __('hr::department.enter_name'),
                                    'required' => true,
                                    'value' => $department->name,
                                    'grid' => 12,
                                    'borderColor' => '#3b82f6'
                                ],
                                [
                                    'name' => 'description',
                                    'type' => 'textarea',
                                    'label' => __('hr::department.description'),
                                    'placeholder' => __('hr::department.enter_description'),
                                    'required' => false,
                                    'value' => $department->description,
                                    'rows' => 4,
                                    'maxLength' => 500,
                                    'grid' => 12,
                                    'borderColor' => '#3b82f6'
                                ],
                                [
                                    'name' => 'color',
                                    'type' => 'color',
                                    'label' => __('hr::department.department_color'),
                                    'required' => true,
                                    'value' => $department->color,
                                    'grid' => 12,
                                    'borderColor' => '#3b82f6'
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