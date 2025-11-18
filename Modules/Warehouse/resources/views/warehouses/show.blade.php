<x-dashboard :pageTitle="$warehouse->name">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('warehouse.warehouses.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('warehouse::warehouse.warehouses') }}
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
                    <span class="text-gray-900 font-medium">{{ $warehouse->name }}</span>
                </li>
            </ol>
        </nav>

        <!-- Header with Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $warehouse->name }}</h1>
                        @if($warehouse->description)
                            <p class="text-gray-600 mt-1">{{ $warehouse->description }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('warehouse.warehouses.edit', $warehouse) }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            {{ __('common.edit') }}
                        </a>

                        <form action="{{ route('warehouse.warehouses.destroy', $warehouse) }}" method="POST"
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
            </div>
        </div>

        <!-- Sub-Warehouses Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">{{ __('warehouse::warehouse.total_sub_warehouses') }}: {{ $warehouse->subWarehouses->count() }}</h2>
                <a href="{{ route('warehouse.sub_warehouses.create', ['warehouse_id' => $warehouse->id]) }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ __('warehouse::sub_warehouse.add_sub_warehouse') }}
                </a>
            </div>

            <div class="p-6">
                @if($warehouse->subWarehouses->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($warehouse->subWarehouses as $subWarehouse)
                            <a href="{{ route('warehouse.sub_warehouses.show', $subWarehouse) }}"
                               class="block p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:shadow-md transition">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="font-medium text-gray-900">{{ $subWarehouse->name }}</h3>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $subWarehouse->type_color }}-100 text-{{ $subWarehouse->type_color }}-800">
                                        {{ __('warehouse::sub_warehouse.' . $subWarehouse->type) }}
                                    </span>
                                </div>
                                @if($subWarehouse->address)
                                    <p class="text-sm text-gray-600 mb-1">{{ Str::limit($subWarehouse->address, 50) }}</p>
                                @endif
                                @if($subWarehouse->country)
                                    <p class="text-xs text-gray-500">{{ $subWarehouse->country }}</p>
                                @endif
                                <div class="mt-3 pt-3 border-t border-gray-100">
                                    <p class="text-xs text-gray-600">
                                        {{ __('warehouse::sub_warehouse.total_products') }}: <span class="font-medium">{{ $subWarehouse->products->count() }}</span>
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('warehouse::warehouse.no_sub_warehouses') }}</h3>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-dashboard>
