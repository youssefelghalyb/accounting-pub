<x-dashboard :pageTitle="__('product::product.edit_product')">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('product.products.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('product::product.products') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('product::product.edit_product') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('product::product.edit_product') }}: {{ $product->name }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
            </div>

            <div class="p-6">
                <x-dashboard.packages.form-builder
                    :action="route('product.products.update', $product)"
                    method="POST"
                    :formConfig="[
                        'groups' => [
                            [
                                'title' => __('product::product.product_details'),
                                'fields' => [
                                    [
                                        'name' => 'name',
                                        'type' => 'text',
                                        'label' => __('product::product.name'),
                                        'placeholder' => __('product::product.enter_name'),
                                        'required' => true,
                                        'value' => $product->name,
                                        'grid' => 6,
                                        'borderColor' => '#3b82f6'
                                    ],
                                    [
                                        'name' => 'type',
                                        'type' => 'select',
                                        'label' => __('product::product.type'),
                                        'required' => true,
                                        'value' => $product->type,
                                        'grid' => 6,
                                        'borderColor' => '#3b82f6',
                                        'options' => [
                                            ['value' => '', 'label' => __('product::product.select_type')],
                                            ['value' => 'book', 'label' => __('product::product.book')],
                                            ['value' => 'ebook', 'label' => __('product::product.ebook')],
                                            ['value' => 'journal', 'label' => __('product::product.journal')],
                                            ['value' => 'course', 'label' => __('product::product.course')],
                                            ['value' => 'bundle', 'label' => __('product::product.bundle')],
                                        ]
                                    ],
                                    [
                                        'name' => 'sku',
                                        'type' => 'text',
                                        'label' => __('product::product.sku'),
                                        'placeholder' => __('product::product.enter_sku'),
                                        'required' => false,
                                        'value' => $product->sku,
                                        'grid' => 6,
                                        'borderColor' => '#3b82f6'
                                    ],
                                    [
                                        'name' => 'base_price',
                                        'type' => 'number',
                                        'label' => __('product::product.base_price'),
                                        'placeholder' => __('product::product.enter_base_price'),
                                        'required' => true,
                                        'value' => $product->base_price,
                                        'grid' => 6,
                                        'borderColor' => '#3b82f6'
                                    ],
                                    [
                                        'name' => 'status',
                                        'type' => 'select',
                                        'label' => __('product::product.status'),
                                        'required' => true,
                                        'value' => $product->status,
                                        'grid' => 12,
                                        'borderColor' => '#10b981',
                                        'options' => [
                                            ['value' => '', 'label' => __('product::product.select_status')],
                                            ['value' => 'active', 'label' => __('product::product.active')],
                                            ['value' => 'inactive', 'label' => __('product::product.inactive')],
                                        ]
                                    ],
                                    [
                                        'name' => 'description',
                                        'type' => 'textarea',
                                        'label' => __('product::product.description'),
                                        'placeholder' => __('product::product.enter_description'),
                                        'required' => false,
                                        'value' => $product->description,
                                        'rows' => 4,
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
