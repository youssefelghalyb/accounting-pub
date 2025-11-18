<x-dashboard :pageTitle="$product->name">
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
                    <span class="text-gray-900 font-medium">{{ $product->name }}</span>
                </li>
            </ol>
        </nav>

        <!-- Header with Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
                        @if($product->sku)
                            <p class="text-sm text-gray-500 mt-1">SKU: {{ $product->sku }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('product.products.edit', $product) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            {{ __('common.edit') }}
                        </a>
                        <form action="{{ route('product.products.destroy', $product) }}" method="POST" onsubmit="return confirm('{{ __('common.are_you_sure') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                {{ __('common.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::product.product_details') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::product.name') }}</label>
                        <p class="text-gray-900 font-medium">{{ $product->name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::product.type') }}</label>
                        @php
                            $colors = [
                                'book' => 'bg-blue-100 text-blue-800',
                                'ebook' => 'bg-purple-100 text-purple-800',
                                'journal' => 'bg-green-100 text-green-800',
                                'course' => 'bg-orange-100 text-orange-800',
                                'bundle' => 'bg-pink-100 text-pink-800',
                            ];
                            $color = $colors[$product->type] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $color }}">
                            {{ __('product::product.' . $product->type) }}
                        </span>
                    </div>

                    @if($product->sku)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::product.sku') }}</label>
                        <p class="text-gray-900">{{ $product->sku }}</p>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::product.base_price') }}</label>
                        <p class="text-gray-900 font-bold text-lg">{{ number_format($product->base_price, 2) }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::product.status') }}</label>
                        @php
                            $statusColor = $product->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $statusColor }}">
                            {{ __('product::product.' . $product->status) }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('common.created_at') }}</label>
                        <p class="text-gray-900">{{ $product->created_at->format('Y-m-d H:i') }}</p>
                    </div>

                    @if($product->description)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::product.description') }}</label>
                        <p class="text-gray-900">{{ $product->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-dashboard>
