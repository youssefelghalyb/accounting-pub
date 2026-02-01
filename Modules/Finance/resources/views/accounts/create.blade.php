<x-dashboard :pageTitle="__('finance::account.add_account')">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('finance.accounts.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('finance::account.accounts') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('finance::account.add_account') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('finance::account.add_account') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
            </div>

            <div class="p-6">
                <x-dashboard.packages.form-builder 
                    :action="route('finance.accounts.store')" 
                    method="POST"
                    :formConfig="[
                        'groups' => [
                            [
                                'title' => __('finance::account.basic_info'),
                                'fields' => [
                                    [
                                        'name' => 'account_name',
                                        'type' => 'text',
                                        'label' => __('finance::account.account_name'),
                                        'placeholder' => __('finance::account.enter_account_name'),
                                        'required' => true,
                                        'grid' => 6,
                                        'borderColor' => '#3b82f6'
                                    ],
                                    [
                                        'name' => 'account_type',
                                        'type' => 'select',
                                        'label' => __('finance::account.account_type'),
                                        'required' => true,
                                        'grid' => 6,
                                        'borderColor' => '#3b82f6',
                                        'options' => [
                                            ['value' => '', 'label' => __('finance::account.select_type')],
                                            ['value' => 'cash', 'label' => __('finance::account.types.cash')],
                                            ['value' => 'bank', 'label' => __('finance::account.types.bank')],
                                        ]
                                    ],
                                    [
                                        'name' => 'opening_balance',
                                        'type' => 'number',
                                        'label' => __('finance::account.opening_balance'),
                                        'placeholder' => __('finance::account.enter_opening_balance'),
                                        'required' => true,
                                        'grid' => 6,
                                        'borderColor' => '#3b82f6'
                                    ],
                                    [
                                        'name' => 'currency',
                                        'type' => 'text',
                                        'label' => __('finance::account.currency'),
                                        'value' => $orgSettings->currency ?? 'USD',
                                        'required' => false,
                                        'grid' => 6,
                                        'borderColor' => '#3b82f6'
                                    ]
                                ]
                            ],
                            [
                                'title' => __('finance::account.bank_info'),
                                'fields' => [
                                    [
                                        'name' => 'account_number',
                                        'type' => 'text',
                                        'label' => __('finance::account.account_number'),
                                        'placeholder' => __('finance::account.enter_account_number'),
                                        'required' => false,
                                        'grid' => 6,
                                        'borderColor' => '#10b981'
                                    ],
                                    [
                                        'name' => 'bank_name',
                                        'type' => 'text',
                                        'label' => __('finance::account.bank_name'),
                                        'placeholder' => __('finance::account.enter_bank_name'),
                                        'required' => false,
                                        'grid' => 6,
                                        'borderColor' => '#10b981'
                                    ],
                                    [
                                        'name' => 'branch_name',
                                        'type' => 'text',
                                        'label' => __('finance::account.branch_name'),
                                        'placeholder' => __('finance::account.enter_branch_name'),
                                        'required' => false,
                                        'grid' => 6,
                                        'borderColor' => '#10b981'
                                    ],
                                    [
                                        'name' => 'swift_code',
                                        'type' => 'text',
                                        'label' => __('finance::account.swift_code'),
                                        'placeholder' => __('finance::account.enter_swift_code'),
                                        'required' => false,
                                        'grid' => 6,
                                        'borderColor' => '#10b981'
                                    ],
                                    [
                                        'name' => 'iban',
                                        'type' => 'text',
                                        'label' => __('finance::account.iban'),
                                        'placeholder' => __('finance::account.enter_iban'),
                                        'required' => false,
                                        'grid' => 12,
                                        'borderColor' => '#10b981'
                                    ]
                                ]
                            ],
                            [
                                'title' => __('common.additional_information'),
                                'fields' => [
                                    [
                                        'name' => 'notes',
                                        'type' => 'textarea',
                                        'label' => __('finance::account.notes'),
                                        'placeholder' => __('finance::account.enter_notes'),
                                        'required' => false,
                                        'rows' => 3,
                                        'grid' => 12,
                                        'borderColor' => '#8b5cf6'
                                    ]
                                ]
                            ]
                        ]
                    ]"
                />
            </div>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h4 class="text-sm font-semibold text-blue-900">{{ __('common.note') }}</h4>
                    <p class="text-sm text-blue-800 mt-1">{{ __('finance::account.bank_info_optional') }}</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const accountTypeSelect = document.querySelector('select[name="account_type"]');
            const bankFields = ['account_number', 'bank_name', 'branch_name', 'swift_code', 'iban'];
            
            function toggleBankFields() {
                const isCash = accountTypeSelect.value === 'cash';
                
                bankFields.forEach(fieldName => {
                    const field = document.querySelector(`[name="${fieldName}"]`);
                    const fieldWrapper = field?.closest('.mb-6');
                    
                    if (fieldWrapper) {
                        if (isCash) {
                            fieldWrapper.style.opacity = '0.5';
                            field.disabled = true;
                        } else {
                            fieldWrapper.style.opacity = '1';
                            field.disabled = false;
                        }
                    }
                });
            }
            
            accountTypeSelect?.addEventListener('change', toggleBankFields);
            toggleBankFields(); // Initial state
        });
    </script>
    @endpush
</x-dashboard>