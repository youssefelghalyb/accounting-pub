@php
$formConfig = [
    'groups' => [
        [
            'title' => __('product::transaction.transaction_details'),
            'fields' => [
                [
                    'name' => 'contract_id',
                    'type' => 'select',
                    'label' => __('product::transaction.contract'),
                    'required' => true,
                    'grid' => 12,
                    'borderColor' => '#3b82f6',
                    'options' => collect($contracts)->map(function($contract) {
                            $bookName = $contract->book?->product?->name ?? $contract->book_name;

                        return [
                            'value' => $contract->id,
                            'label' => $contract->author->full_name . ' - ' .  
                            $bookName .
                             ' ('. number_format($contract->outstanding_balance, 2) . ' ' . __('product::transaction.remaining') . ')'
                        ];
                    })->prepend(['value' => '', 'label' => __('product::transaction.select_contract')])->toArray()
                ],
                [
                    'name' => 'amount',
                    'type' => 'number',
                    'label' => __('product::transaction.amount'),
                    'placeholder' => __('product::transaction.enter_amount'),
                    'required' => true,
                    'grid' => 6,
                    'borderColor' => '#10b981'
                ],
                [
                    'name' => 'payment_date',
                    'type' => 'date',
                    'label' => __('product::transaction.payment_date'),
                    'required' => true,
                    'grid' => 6,
                    'borderColor' => '#10b981'
                ],
                [
                    'name' => 'notes',
                    'type' => 'textarea',
                    'label' => __('product::transaction.notes'),
                    'placeholder' => __('product::transaction.enter_notes'),
                    'required' => false,
                    'rows' => 3,
                    'grid' => 12,
                    'borderColor' => '#8b5cf6'
                ],
                [
                    'name' => 'receipt_file',
                    'type' => 'file',
                    'label' => __('product::transaction.receipt_file'),
                    'required' => false,
                    'grid' => 12,
                    'borderColor' => '#8b5cf6',
                    'accept' => '.pdf,.jpg,.jpeg,.png',
                    'helperText' => __('product::transaction.upload_receipt')
                ]
            ]
        ]
    ]
];
@endphp

<x-dashboard :pageTitle="__('product::transaction.add_transaction')">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('product.transactions.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('product::transaction.transactions') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('product::transaction.add_transaction') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('product::transaction.record_payment') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
            </div>

            <div class="p-6">
                <x-dashboard.packages.form-builder
                    :action="route('product.transactions.store')"
                    method="POST"
                    :formConfig="$formConfig"
                />
            </div>
        </div>
    </div>
</x-dashboard>
