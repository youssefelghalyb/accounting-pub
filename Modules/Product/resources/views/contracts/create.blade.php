@php
$formConfig = [
    'groups' => [
        [
            'title' => __('product::contract.contract_details'),
            'fields' => [
                [
                    'name' => 'author_id',
                    'type' => 'select',
                    'label' => __('product::contract.author'),
                    'required' => true,
                    'grid' =>4,
                    'borderColor' => '#3b82f6',
                    'options' => collect($authors)->map(function($author) {
                        return ['value' => $author->id, 'label' => $author->full_name];
                    })->prepend(['value' => '', 'label' => __('product::contract.select_author')])->toArray()
                ],
                [
                    'name' => 'book_id',
                    'type' => 'select',
                    'label' => __('product::contract.book'),
                    'required' => false,
                    'grid' =>4,
                    'borderColor' => '#3b82f6',
                    'options' => collect($books)->map(function($book) {
                        return ['value' => $book->id, 'label' => $book->product->name];
                    })->prepend(['value' => ' ', 'label' => __('product::contract.select_book')])->toArray()
                ],
                                [
                    'name' => 'book name', 
                    'type' => 'text',
                    'label' => __('product::contract.book_name'),
                    'required' => false,
                    'grid' => 4,
                    'borderColor' => '#3b82f6',
                ],
                [
                    'name' => 'contract_date',
                    'type' => 'date',
                    'label' => __('product::contract.contract_date'),
                    'required' => true,
                    'grid' => 6,
                    'borderColor' => '#3b82f6'
                ],
                [
                    'name' => 'contract_price',
                    'type' => 'number',
                    'label' => __('product::contract.contract_price'),
                    'placeholder' => __('product::contract.enter_contract_price'),
                    'required' => true,
                    'grid' => 6,
                    'borderColor' => '#3b82f6'
                ],
                [
                    'name' => 'percentage_from_book_profit',
                    'type' => 'number',
                    'label' => __('product::contract.percentage_from_book_profit'),
                    'placeholder' => __('product::contract.enter_percentage'),
                    'required' => true,
                    'grid' => 12,
                    'borderColor' => '#10b981'
                ],
                [
                    'name' => 'contract_file',
                    'type' => 'file',
                    'label' => __('product::contract.contract_file'),
                    'required' => false,
                    'grid' => 12,
                    'borderColor' => '#8b5cf6',
                    'accept' => '.pdf,.doc,.docx',
                    'helperText' => __('product::contract.upload_contract_file')
                ]
            ]
        ]
    ]
];
@endphp

<x-dashboard :pageTitle="__('product::contract.add_contract')">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('product.contracts.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('product::contract.contracts') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('product::contract.add_contract') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('product::contract.create_contract') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
            </div>

            <div class="p-6">
                <x-dashboard.packages.form-builder
                    :action="route('product.contracts.store')"
                    method="POST"
                    :formConfig="$formConfig"
                />
            </div>
        </div>
    </div>
</x-dashboard>
