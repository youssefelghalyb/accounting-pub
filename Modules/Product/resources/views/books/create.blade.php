@php
    $formConfig = [
        'groups' => [
            [
                'title' => __('product::book.product_info'),
                'fields' => [
                    [
                        'name' => 'name',
                        'type' => 'text',
                        'label' => __('product::product.name'),
                        'placeholder' => __('product::product.enter_name'),
                        'required' => true,
                        'grid' => 6,
                        'borderColor' => '#3b82f6',
                    ],
                    [
                        'name' => 'type',
                        'type' => 'select',
                        'label' => __('product::product.type'),
                        'required' => true,
                        'grid' => 6,
                        'borderColor' => '#3b82f6',
                        'options' => [
                            ['value' => '', 'label' => __('product::product.select_type')],
                            ['value' => 'book', 'label' => __('product::product.book')],
                            ['value' => 'ebook', 'label' => __('product::product.ebook')],
                        ],
                    ],
                    [
                        'name' => 'sku',
                        'type' => 'text',
                        'label' => __('product::product.sku'),
                        'placeholder' => __('product::product.enter_sku'),
                        'required' => false,
                        'grid' => 6,
                        'borderColor' => '#3b82f6',
                    ],
                    [
                        'name' => 'base_price',
                        'type' => 'number',
                        'label' => __('product::product.base_price'),
                        'placeholder' => __('product::product.enter_base_price'),
                        'required' => true,
                        'grid' => 6,
                        'borderColor' => '#3b82f6',
                    ],
                    [
                        'name' => 'status',
                        'type' => 'select',
                        'label' => __('product::product.status'),
                        'required' => true,
                        'grid' => 12,
                        'borderColor' => '#3b82f6',
                        'options' => [
                            ['value' => '', 'label' => __('product::product.select_status')],
                            ['value' => 'active', 'label' => __('product::product.active')],
                            ['value' => 'inactive', 'label' => __('product::product.inactive')],
                        ],
                    ],
                    [
                        'name' => 'description',
                        'type' => 'textarea',
                        'label' => __('product::product.description'),
                        'placeholder' => __('product::product.enter_description'),
                        'required' => false,
                        'rows' => 3,
                        'grid' => 12,
                        'borderColor' => '#3b82f6',
                    ],
                ],
            ],
            [
                'title' => __('product::book.book_info'),
                'fields' => [
                    [
                        'name' => 'isbn',
                        'type' => 'text',
                        'label' => __('product::book.isbn'),
                        'placeholder' => __('product::book.enter_isbn'),
                        'required' => true,
                        'grid' => 6,
                        'borderColor' => '#10b981',
                    ],
                    [
                        'name' => 'author_id',
                        'type' => 'searchable_select',
                        'label' => __('product::book.author'),
                        'placeholder' => __('product::book.select_author'),
                        'required' => false,
                        'grid' => 6,
                        'borderColor' => '#10b981',
                        'searchRoute' => route('product.authors.search'),
                        'quickAddModal' => 'openQuickAuthorModal',
                        'quickAddLabel' => __('product::author.quick_add'),
                    ],
                    [
                        'name' => 'category_id',
                        'type' => 'select',
                        'label' => __('product::book.category'),
                        'required' => false,
                        'grid' => 6,
                        'borderColor' => '#10b981',
                        'options' => collect($categories)
                            ->map(function ($cat) {
                                return ['value' => $cat->id, 'label' => $cat->name];
                            })
                            ->prepend(['value' => ' ', 'label' => __('product::book.select_category')])
                            ->toArray(),
                    ],
                    [
                        'name' => 'sub_category_id',
                        'type' => 'select',
                        'label' => __('product::book.sub_category'),
                        'required' => false,
                        'grid' => 6,
                        'borderColor' => '#10b981',
                        'options' => collect($subCategories)
                            ->map(function ($cat) {
                                return ['value' => $cat->id, 'label' => $cat->name];
                            })
                            ->prepend(['value' => ' ', 'label' => __('product::book.select_sub_category')])
                            ->toArray(),
                    ],
                    [
                        'name' => 'num_of_pages',
                        'type' => 'number',
                        'label' => __('product::book.num_of_pages'),
                        'placeholder' => __('product::book.enter_pages'),
                        'required' => false,
                        'grid' => 6,
                        'borderColor' => '#10b981',
                    ],
                    [
                        'name' => 'cover_type',
                        'type' => 'select',
                        'label' => __('product::book.cover_type'),
                        'required' => true,
                        'grid' => 6,
                        'borderColor' => '#10b981',
                        'options' => [
                            ['value' => '', 'label' => __('product::book.select_cover_type')],
                            ['value' => 'hard', 'label' => __('product::book.hard')],
                            ['value' => 'soft', 'label' => __('product::book.soft')],
                        ],
                    ],
                    [
                        'name' => 'published_at',
                        'type' => 'date',
                        'label' => __('product::book.published_at'),
                        'required' => false,
                        'grid' => 6,
                        'borderColor' => '#10b981',
                    ],
                    [
                        'name' => 'language',
                        'type' => 'text',
                        'label' => __('product::book.language'),
                        'placeholder' => __('product::book.enter_language'),
                        'required' => false,
                        'grid' => 6,
                        'borderColor' => '#10b981',
                    ],
                ],
            ],
            [
                'title' => __('product::book.translation_info'),
                'fields' => [
                    [
                        'name' => 'is_translated',
                        'type' => 'radio',
                        'label' => __('product::book.is_translated'),
                        'options' => [
                            ['value' => '1', 'label' => __('common.yes')],
                            ['value' => '0', 'label' => __('common.no')],
                        ],
                        'grid' => 12,
                        'borderColor' => '#8b5cf6',
                    ],
                    [
                        'name' => 'translated_from',
                        'type' => 'text',
                        'label' => __('product::book.translated_from'),
                        'placeholder' => __('product::book.enter_translated_from'),
                        'required' => false,
                        'grid' => 4,
                        'borderColor' => '#8b5cf6',
                    ],
                    [
                        'name' => 'translated_to',
                        'type' => 'text',
                        'label' => __('product::book.translated_to'),
                        'placeholder' => __('product::book.enter_translated_to'),
                        'required' => false,
                        'grid' => 4,
                        'borderColor' => '#8b5cf6',
                    ],
                    [
                        'name' => 'translator_name',
                        'type' => 'text',
                        'label' => __('product::book.translator_name'),
                        'placeholder' => __('product::book.enter_translator_name'),
                        'required' => false,
                        'grid' => 4,
                        'borderColor' => '#8b5cf6',
                    ],
                ],
            ],
        ],
    ];
@endphp

<x-dashboard :pageTitle="__('product::book.add_book')">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('product.books.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('product::book.books') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('product::book.add_book') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">{{ __('product::book.register_book') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('common.required_fields') }}</p>
            </div>

            <div class="p-6">
                <x-dashboard.packages.form-builder :action="route('product.books.store')" method="POST" :formConfig="$formConfig" />
            </div>
        </div>
    </div>
    <!-- Quick Add Author Modal -->
<div id="quickAuthorModal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeQuickAuthorModal()"></div>

    <!-- Modal -->
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-xl shadow-xl">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-900">{{ __('product::author.quick_add_author') }}</h3>
                <button type="button" onclick="closeQuickAuthorModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <form id="quickAuthorForm" class="p-6 space-y-4">
            <!-- Full Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('product::author.full_name') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" id="quick_author_name" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('product::author.email') }}
                </label>
                <input type="email" id="quick_author_email"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Phone -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('product::author.phone') }}
                </label>
                <input type="text" id="quick_author_phone"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Nationality -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('product::author.nationality') }}
                </label>
                <input type="text" id="quick_author_nationality"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeQuickAuthorModal()"
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    {{ __('common.cancel') }}
                </button>
                <button type="submit" id="quickAuthorSubmitBtn"
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    {{ __('product::author.add_author') }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Quick Author Modal Functions
    function openQuickAuthorModal() {
        document.getElementById('quickAuthorModal').classList.remove('hidden');
        document.getElementById('quick_author_name').focus();
    }

    function closeQuickAuthorModal() {
        document.getElementById('quickAuthorModal').classList.add('hidden');
        document.getElementById('quickAuthorForm').reset();
    }

    // Quick Author Form Submit
    document.getElementById('quickAuthorForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('quickAuthorSubmitBtn');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = '{{ __('common.saving') }}...';

        const formData = {
            full_name: document.getElementById('quick_author_name').value,
            email: document.getElementById('quick_author_email').value,
            phone: document.getElementById('quick_author_phone').value,
            nationality: document.getElementById('quick_author_nationality').value,
            _token: '{{ csrf_token() }}'
        };

        try {
            const response = await fetch('{{ route('product.authors.quick-store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                // Add new option to select2
                const newOption = new Option(data.author.text, data.author.id, true, true);
                $('#author_id').append(newOption).trigger('change');

                closeQuickAuthorModal();
                
                // Show success message
                showToast(data.message);
            } else {
                alert(data.message);
            }
        } catch (error) {
            alert('{{ __('common.error_occurred') }}');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });

    // Toast notification function (if not already present)
    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 {{ app()->getLocale() == 'ar' ? 'left-4' : 'right-4' }} bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in-up';
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('animate-fade-out');
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    }
</script>
@endpush
</x-dashboard>
