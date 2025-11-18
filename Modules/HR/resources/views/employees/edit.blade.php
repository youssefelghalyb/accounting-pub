<x-dashboard :pageTitle="__('hr::employee.edit_employee')">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('hr.employees.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('hr::employee.employees') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('hr::employee.edit_employee') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('hr::employee.edit_employee') }}: {{ $employee->full_name }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
            </div>

            <div class="p-6">
                    
                    <x-dashboard.packages.form-builder 
                        :action="route('hr.employees.update', $employee)" 
                        method="POST"
                        :formConfig="[
                            'groups' => [
                                [
                                    'title' => __('hr::employee.personal_info'),
                                    'fields' => [
                                        [
                                            'name' => 'first_name',
                                            'type' => 'text',
                                            'label' => __('hr::employee.first_name'),
                                            'placeholder' => __('hr::employee.enter_first_name'),
                                            'required' => true,
                                            'value' => $employee->first_name,
                                            'grid' => 6,
                                            'borderColor' => '#3b82f6'
                                        ],
                                        [
                                            'name' => 'last_name',
                                            'type' => 'text',
                                            'label' => __('hr::employee.last_name'),
                                            'placeholder' => __('hr::employee.enter_last_name'),
                                            'required' => true,
                                            'value' => $employee->last_name,
                                            'grid' => 6,
                                            'borderColor' => '#3b82f6'
                                        ],
                                        [
                                            'name' => 'email',
                                            'type' => 'email',
                                            'label' => __('hr::employee.email'),
                                            'placeholder' => __('hr::employee.enter_email'),
                                            'required' => true,
                                            'value' => $employee->email,
                                            'grid' => 6,
                                            'borderColor' => '#3b82f6'
                                        ],
                                        [
                                            'name' => 'phone',
                                            'type' => 'text',
                                            'label' => __('hr::employee.phone'),
                                            'placeholder' => __('hr::employee.enter_phone'),
                                            'required' => false,
                                            'value' => $employee->phone,
                                            'grid' => 6,
                                            'borderColor' => '#3b82f6'
                                        ]
                                    ]
                                ],
                                [
                                    'title' => __('hr::employee.employment_info'),
                                    'fields' => [
                                        [
                                            'name' => 'department_id',
                                            'type' => 'select',
                                            'label' => __('hr::employee.department'),
                                            'required' => true,
                                            'grid' => 6,
                                            'borderColor' => '#10b981',
                                            'options' => collect($departments)->map(function($dept) use ($employee) {
                                                return [
                                                    'value' => $dept->id,
                                                    'label' => $dept->name,
                                                    'selected' => $dept->id == $employee->department_id
                                                ];
                                            })->prepend([
                                                'value' => '',
                                                'label' => __('hr::employee.select_department')
                                            ])->toArray(),
                                            'value' => $employee->department_id
                                        ],
                                        [
                                            'name' => 'position',
                                            'type' => 'text',
                                            'label' => __('hr::employee.position'),
                                            'placeholder' => __('hr::employee.enter_position'),
                                            'required' => false,
                                            'value' => $employee->position,
                                            'grid' => 6,
                                            'borderColor' => '#10b981'
                                        ],
                                        [
                                            'name' => 'hire_date',
                                            'type' => 'date',
                                            'label' => __('hr::employee.hire_date'),
                                            'required' => true,
                                            'value' => $employee->hire_date->format('Y-m-d'),
                                            'grid' => 12,
                                            'borderColor' => '#10b981'
                                        ]
                                    ]
                                ],
                                [
                                    'title' => __('hr::employee.salary_info'),
                                    'fields' => [
                                        [
                                            'name' => 'salary',
                                            'type' => 'number',
                                            'label' => __('hr::employee.salary'),
                                            'placeholder' => __('hr::employee.enter_salary'),
                                            'required' => true,
                                            'value' => $employee->salary,
                                            'grid' => 6,
                                            'borderColor' => '#8b5cf6'
                                        ],
                                        [
                                            'name' => 'daily_rate',
                                            'type' => 'number',
                                            'label' => __('hr::employee.daily_rate'),
                                            'placeholder' => __('hr::employee.daily_rate'),
                                            'required' => false,
                                            'value' => $employee->daily_rate,
                                            'grid' => 6,
                                            'borderColor' => '#8b5cf6',
                                            'helperText' => __('hr::employee.auto_calculated_daily_rate')
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