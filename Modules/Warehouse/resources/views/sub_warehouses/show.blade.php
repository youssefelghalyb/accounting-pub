<x-dashboard :pageTitle="$subWarehouse->name">
    <div class="max-w-6xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('warehouse.sub_warehouses.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('warehouse::sub_warehouse.sub_warehouses') }}
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
                    <span class="text-gray-900 font-medium">{{ $subWarehouse->name }}</span>
                </li>
            </ol>
        </nav>

        <!-- Header with Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $subWarehouse->name }}</h1>
                            <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800">
                                {{ __('warehouse::sub_warehouse.' . $subWarehouse->type) }}
                            </span>
                        </div>
                        <p class="text-gray-600">
                            <strong>{{ __('warehouse::sub_warehouse.warehouse') }}:</strong>
                            <a href="{{ route('warehouse.warehouses.show', $subWarehouse->warehouse) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $subWarehouse->warehouse->name }}
                            </a>
                        </p>
                        @if($subWarehouse->address)
                            <p class="text-gray-600 mt-1"><strong>{{ __('warehouse::sub_warehouse.address') }}:</strong> {{ $subWarehouse->address }}</p>
                        @endif
                        @if($subWarehouse->country)
                            <p class="text-gray-600 mt-1"><strong>{{ __('warehouse::sub_warehouse.country') }}:</strong> {{ $subWarehouse->country }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('warehouse.sub_warehouses.add-stock', $subWarehouse) }}"
                           class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            {{ __('warehouse::sub_warehouse.add_stock') }}
                        </a>

                        <a href="{{ route('warehouse.sub_warehouses.edit', $subWarehouse) }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            {{ __('common.edit') }}
                        </a>

                        <form action="{{ route('warehouse.sub_warehouses.destroy', $subWarehouse) }}" method="POST"
                              onsubmit="return confirm('{{ __('common.are_you_sure') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                {{ __('common.delete') }}
                            </button>
                        </form>
                    </div>
                </div>

                @if($subWarehouse->notes)
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-700"><strong>{{ __('warehouse::sub_warehouse.notes') }}:</strong> {{ $subWarehouse->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Stock Products Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('warehouse::sub_warehouse.stock_details') }}</h2>
            </div>

            <div class="p-6">
                @if($subWarehouse->products->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('warehouse::sub_warehouse.product') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('warehouse::sub_warehouse.book_details') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('warehouse::sub_warehouse.quantity') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($subWarehouse->products as $warehouseProduct)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $warehouseProduct->product->name }}</div>
                                            @if($warehouseProduct->product->sku)
                                                <div class="text-xs text-gray-500">SKU: {{ $warehouseProduct->product->sku }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($warehouseProduct->product->book)
                                                <div class="text-sm text-gray-900">
                                                    @if($warehouseProduct->product->book->author)
                                                        <p><strong>{{ __('warehouse::sub_warehouse.author') }}:</strong> {{ $warehouseProduct->product->book->author->name }}</p>
                                                    @endif
                                                    @if($warehouseProduct->product->book->category)
                                                        <p><strong>{{ __('warehouse::sub_warehouse.category') }}:</strong> {{ $warehouseProduct->product->book->category->name }}</p>
                                                    @endif
                                                    @if($warehouseProduct->product->book->isbn)
                                                        <p class="text-xs text-gray-500">{{ __('warehouse::sub_warehouse.isbn') }}: {{ $warehouseProduct->product->book->isbn }}</p>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-sm">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 text-sm font-bold rounded-full bg-green-100 text-green-800">
                                                {{ number_format($warehouseProduct->quantity) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="2" class="px-6 py-4 text-right text-sm font-bold text-gray-900">
                                        {{ __('warehouse::sub_warehouse.total_quantity') }}:
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-sm font-bold rounded-full bg-blue-100 text-blue-800">
                                            {{ number_format($subWarehouse->products->sum('quantity')) }}
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('warehouse::sub_warehouse.no_products') }}</h3>
                        <div class="mt-6">
                            <a href="{{ route('warehouse.sub_warehouses.add-stock', $subWarehouse) }}"
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                {{ __('warehouse::sub_warehouse.add_stock') }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-dashboard>
