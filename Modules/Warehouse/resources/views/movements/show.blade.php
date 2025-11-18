<x-dashboard :pageTitle="$movement->reference_number">
    <div class="max-w-7xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('warehouse.movements.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('warehouse::movements.movements') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ $movement->reference_number }}</span>
                </li>
            </ol>
        </nav>

        <!-- Header with Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 font-mono">{{ $movement->reference_number }}</h1>
                        <p class="text-sm text-gray-500 mt-1">{{ $movement->movement_date->format('Y-m-d') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($movement->canBeEdited())
                        <a href="{{ route('warehouse.movements.edit', $movement) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            {{ __('common.edit') }}
                        </a>
                        @endif
                        @if($movement->status !== 'completed')
                        <form action="{{ route('warehouse.movements.destroy', $movement) }}" method="POST" onsubmit="return confirm('{{ __('common.are_you_sure') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                {{ __('common.delete') }}
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Movement Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('warehouse::movements.movement_details') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::movements.reference_number') }}</label>
                        <p class="text-gray-900 font-medium font-mono">{{ $movement->reference_number }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::movements.type') }}</label>
                        @php
                            $typeColors = [
                                'in' => 'bg-blue-100 text-blue-800',
                                'out' => 'bg-orange-100 text-orange-800',
                                'transfer' => 'bg-purple-100 text-purple-800',
                                'adjustment' => 'bg-gray-100 text-gray-800',
                            ];
                            $color = $typeColors[$movement->type] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $color }}">
                            {{ $movement->type_label }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::movements.movement_date') }}</label>
                        <p class="text-gray-900">{{ $movement->movement_date->format('Y-m-d') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::movements.status') }}</label>
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'completed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                            ];
                            $statusColor = $statusColors[$movement->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $statusColor }}">
                            {{ __('warehouse::movements.status_' . $movement->status) }}
                        </span>
                    </div>

                    @if($movement->source_warehouse)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::movements.source_warehouse') }}</label>
                        <p class="text-gray-900 font-medium">{{ $movement->source_warehouse }}</p>
                    </div>
                    @endif

                    @if($movement->destination_warehouse)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::movements.destination_warehouse') }}</label>
                        <p class="text-gray-900 font-medium">{{ $movement->destination_warehouse }}</p>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::movements.total_items') }}</label>
                        <p class="text-gray-900 font-bold text-lg">{{ $movement->total_items }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('common.created_at') }}</label>
                        <p class="text-gray-900">{{ $movement->created_at->format('Y-m-d H:i') }}</p>
                    </div>

                    @if($movement->notes)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('warehouse::movements.notes') }}</label>
                        <p class="text-gray-900">{{ $movement->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('warehouse::movements.products') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ $movement->items->count() }} {{ __('warehouse::movements.products_in_movement') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('warehouse::movements.product') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('warehouse::movements.product_type') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('warehouse::movements.quantity') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('warehouse::movements.item_notes') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($movement->items as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $item->product->name }}</p>
                                    @if($item->product->sku)
                                        <p class="text-xs text-gray-500">SKU: {{ $item->product->sku }}</p>
                                    @endif
                                    @if($item->product->type === 'book' && $item->product->book && $item->product->book->author)
                                        <p class="text-xs text-blue-600">{{ $item->product->book->author->name }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $typeColors = [
                                        'book' => 'bg-blue-50 text-blue-700',
                                        'ebook' => 'bg-purple-50 text-purple-700',
                                        'journal' => 'bg-green-50 text-green-700',
                                        'course' => 'bg-orange-50 text-orange-700',
                                        'bundle' => 'bg-pink-50 text-pink-700',
                                    ];
                                    $color = $typeColors[$item->product->type] ?? 'bg-gray-50 text-gray-700';
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded {{ $color }}">
                                    {{ ucfirst($item->product->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-lg font-bold text-gray-900">{{ number_format($item->quantity) }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $item->notes ?? '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-dashboard>
