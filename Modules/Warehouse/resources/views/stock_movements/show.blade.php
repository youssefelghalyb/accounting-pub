<x-dashboard :pageTitle="__('warehouse::stock_movement.view_stock_movement')">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('warehouse.stock_movements.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('warehouse::stock_movement.stock_movements') }}
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
                    <span class="text-gray-900 font-medium">{{ __('warehouse::stock_movement.view_stock_movement') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Header with Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ __('warehouse::stock_movement.stock_movement_details') }}</h1>
                        <p class="text-sm text-gray-600 mt-1">{{ __('common.created_at') }}: {{ $movement->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('warehouse.stock_movements.edit', $movement) }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            {{ __('common.edit') }}
                        </a>

                        <form action="{{ route('warehouse.stock_movements.destroy', $movement) }}" method="POST"
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

        <!-- Movement Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Movement Type & Quantity -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('warehouse::stock_movement.movement_type') }}</h2>
                    <div class="space-y-4">
                        <div>
                            <span class="text-sm text-gray-600">{{ __('warehouse::stock_movement.movement_type') }}</span>
                            <div class="mt-1">
                                @php
                                    $typeColors = [
                                        'transfer' => 'blue',
                                        'inbound' => 'green',
                                        'outbound' => 'red'
                                    ];
                                    $color = $typeColors[$movement->movement_type] ?? 'gray';
                                @endphp
                                <span class="px-3 py-1 text-sm font-medium rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                                    {{ __('warehouse::stock_movement.' . $movement->movement_type) }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <span class="text-sm text-gray-600">{{ __('warehouse::stock_movement.quantity') }}</span>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($movement->quantity) }}</p>
                        </div>

                        @if($movement->user)
                            <div>
                                <span class="text-sm text-gray-600">{{ __('warehouse::stock_movement.user') }}</span>
                                <p class="text-sm font-medium text-gray-900 mt-1">{{ $movement->user->name }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Product Details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('warehouse::stock_movement.product') }}</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-lg font-medium text-gray-900">{{ $movement->product->name }}</p>
                            @if($movement->product->sku)
                                <p class="text-sm text-gray-500">SKU: {{ $movement->product->sku }}</p>
                            @endif
                        </div>

                        @if($movement->product->book)
                            <div class="pt-3 border-t border-gray-200">
                                <h3 class="text-sm font-medium text-gray-900 mb-2">{{ __('warehouse::sub_warehouse.book_details') }}</h3>
                                @if($movement->product->book->author)
                                    <p class="text-sm text-gray-600"><strong>{{ __('warehouse::sub_warehouse.author') }}:</strong> {{ $movement->product->book->author->name }}</p>
                                @endif
                                @if($movement->product->book->category)
                                    <p class="text-sm text-gray-600"><strong>{{ __('warehouse::sub_warehouse.category') }}:</strong> {{ $movement->product->book->category->name }}</p>
                                @endif
                                @if($movement->product->book->isbn)
                                    <p class="text-sm text-gray-600"><strong>{{ __('warehouse::sub_warehouse.isbn') }}:</strong> {{ $movement->product->book->isbn }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Warehouse Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('warehouse::stock_movement.from_sub_warehouse') }} / {{ __('warehouse::stock_movement.to_sub_warehouse') }}</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- From Warehouse -->
                    @if($movement->fromSubWarehouse)
                        <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                            <h3 class="text-sm font-medium text-red-900 mb-2">{{ __('warehouse::stock_movement.from_sub_warehouse') }}</h3>
                            <p class="font-medium text-gray-900">
                                <a href="{{ route('warehouse.sub_warehouses.show', $movement->fromSubWarehouse) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $movement->fromSubWarehouse->name }}
                                </a>
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ __('warehouse::sub_warehouse.warehouse') }}: {{ $movement->fromSubWarehouse->warehouse->name }}
                            </p>
                            @if($movement->fromSubWarehouse->address)
                                <p class="text-sm text-gray-600 mt-1">{{ $movement->fromSubWarehouse->address }}</p>
                            @endif
                        </div>
                    @else
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-500 text-center">-</p>
                        </div>
                    @endif

                    <!-- To Warehouse -->
                    @if($movement->toSubWarehouse)
                        <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                            <h3 class="text-sm font-medium text-green-900 mb-2">{{ __('warehouse::stock_movement.to_sub_warehouse') }}</h3>
                            <p class="font-medium text-gray-900">
                                <a href="{{ route('warehouse.sub_warehouses.show', $movement->toSubWarehouse) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $movement->toSubWarehouse->name }}
                                </a>
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ __('warehouse::sub_warehouse.warehouse') }}: {{ $movement->toSubWarehouse->warehouse->name }}
                            </p>
                            @if($movement->toSubWarehouse->address)
                                <p class="text-sm text-gray-600 mt-1">{{ $movement->toSubWarehouse->address }}</p>
                            @endif
                        </div>
                    @else
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-500 text-center">-</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('common.additional_information') }}</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($movement->reason)
                        <div>
                            <span class="text-sm text-gray-600">{{ __('warehouse::stock_movement.reason') }}</span>
                            <p class="text-sm font-medium text-gray-900 mt-1">{{ $movement->reason }}</p>
                        </div>
                    @endif

                    @if($movement->reference_id)
                        <div>
                            <span class="text-sm text-gray-600">{{ __('warehouse::stock_movement.reference_id') }}</span>
                            <p class="text-sm font-medium text-gray-900 mt-1">{{ $movement->reference_id }}</p>
                        </div>
                    @endif

                    @if($movement->notes)
                        <div class="md:col-span-2">
                            <span class="text-sm text-gray-600">{{ __('warehouse::stock_movement.notes') }}</span>
                            <p class="text-sm text-gray-900 mt-1 p-3 bg-gray-50 rounded-lg">{{ $movement->notes }}</p>
                        </div>
                    @endif

                    <div>
                        <span class="text-sm text-gray-600">{{ __('common.created_at') }}</span>
                        <p class="text-sm font-medium text-gray-900 mt-1">{{ $movement->created_at->format('Y-m-d H:i:s') }}</p>
                    </div>

                    @if($movement->creator)
                        <div>
                            <span class="text-sm text-gray-600">{{ __('common.created_by') }}</span>
                            <p class="text-sm font-medium text-gray-900 mt-1">{{ $movement->creator->name }}</p>
                        </div>
                    @endif

                    @if($movement->updated_at != $movement->created_at)
                        <div>
                            <span class="text-sm text-gray-600">{{ __('common.updated_at') }}</span>
                            <p class="text-sm font-medium text-gray-900 mt-1">{{ $movement->updated_at->format('Y-m-d H:i:s') }}</p>
                        </div>

                        @if($movement->editor)
                            <div>
                                <span class="text-sm text-gray-600">{{ __('common.edited_by') }}</span>
                                <p class="text-sm font-medium text-gray-900 mt-1">{{ $movement->editor->name }}</p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-dashboard>
