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
                    'value' => $transaction->contract_id,
                    'grid' => 12,
                    'borderColor' => '#3b82f6',
                    'options' => collect($contracts)->map(function($contract) use ($transaction) {
                        $remaining = $contract->id === $transaction->contract_id
                            ? $contract->outstanding_balance + $transaction->amount
                            : $contract->outstanding_balance;
                        return [
                            'value' => $contract->id,
                            'label' => $contract->author->full_name . ' - ' . $contract->book->product->name . ' ('. number_format($remaining, 2) . ' ' . __('product::transaction.remaining') . ')'
                        ];
                    })->prepend(['value' => '', 'label' => __('product::transaction.select_contract')])->toArray()
                ],
                [
                    'name' => 'amount',
                    'type' => 'number',
                    'label' => __('product::transaction.amount'),
                    'placeholder' => __('product::transaction.enter_amount'),
                    'required' => true,
                    'value' => $transaction->amount,
                    'grid' => 6,
                    'borderColor' => '#10b981'
                ],
                [
                    'name' => 'payment_date',
                    'type' => 'date',
                    'label' => __('product::transaction.payment_date'),
                    'required' => true,
                    'value' => $transaction->payment_date ? $transaction->payment_date->format('Y-m-d') : null,
                    'grid' => 6,
                    'borderColor' => '#10b981'
                ],
                [
                    'name' => 'notes',
                    'type' => 'textarea',
                    'label' => __('product::transaction.notes'),
                    'placeholder' => __('product::transaction.enter_notes'),
                    'required' => false,
                    'value' => $transaction->notes,
                    'rows' => 3,
                    'grid' => 12,
                    'borderColor' => '#8b5cf6'
                ],
                [
                    'name' => 'receipt_file',
                    'type' => 'file',
                    'label' => __('product::transaction.receipt_file'),
                    'required' => false,
                    'value' => $transaction->receipt_file,
                    'grid' => 12,
                    'borderColor' => '#8b5cf6',
                    'accept' => '.pdf,.jpg,.jpeg,.png',
                    'helperText' => $transaction->receipt_file
                        ? __('product::transaction.upload_receipt') . ' (' . __('common.current') . ': ' . basename($transaction->receipt_file) . ')'
                        : __('product::transaction.upload_receipt')
                ]
            ]
        ]
    ]
];
@endphp

<x-dashboard :pageTitle="__('product::transaction.edit_transaction')">
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
                    <span class="text-gray-900 font-medium">{{ __('product::transaction.edit_transaction') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('product::transaction.edit_transaction') }}: {{ number_format($transaction->amount, 2) }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
            </div>

            <div class="p-6">
                <x-dashboard.packages.form-builder
                    :action="route('product.transactions.update', $transaction)"
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
