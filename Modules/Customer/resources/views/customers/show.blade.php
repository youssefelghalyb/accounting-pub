<x-dashboard :pageTitle="__('customer::customer.customer_details')">
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('customer.customers.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('customer::customer.customers') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ $customer->name }}</span>
                </li>
            </ol>
        </nav>

        <!-- Customer Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-{{ $customer->type_color }}-500 to-{{ $customer->type_color }}-600 flex items-center justify-center text-white text-2xl font-bold">
                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $customer->name }}</h1>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-{{ $customer->status_color }}-100 text-{{ $customer->status_color }}-800">
                                {{ $customer->status_label }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-{{ $customer->type_color }}-100 text-{{ $customer->type_color }}-800">
                                {{ $customer->type_label }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Toggle Status Button -->
                    <form action="{{ route('customer.customers.toggle-status', $customer) }}" method="POST" class="inline-block">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('{{ $customer->is_active ? __('customer::customer.confirm_deactivate') : __('customer::customer.confirm_activate') }}')"
                                class="inline-flex items-center gap-2 px-4 py-2 {{ $customer->is_active ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-lg transition-colors">
                            @if($customer->is_active)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                </svg>
                                {{ __('customer::customer.deactivate') }}
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ __('customer::customer.activate') }}
                            @endif
                        </button>
                    </form>

                    <!-- Edit Button -->
                    <a href="{{ route('customer.customers.edit', $customer) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        {{ __('common.edit') }}
                    </a>
                </div>
            </div>

            <!-- Customer Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6 pt-6 border-t border-gray-200">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('customer::customer.email') }}</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $customer->email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('customer::customer.phone') }}</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $customer->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('customer::customer.tax_number') }}</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $customer->tax_number ?? '-' }}</p>
                </div>
                <div class="md:col-span-3">
                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('customer::customer.address') }}</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $customer->address ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Customer Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Customer Since -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('customer::customer.customer_since') }}</p>
                        <p class="text-xl font-bold text-gray-900 mt-2">{{ $customer->created_at->format('M d, Y') }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Days Active -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('customer::customer.days_active') }}</p>
                        <p class="text-xl font-bold text-gray-900 mt-2">{{ $customer->created_at->diffInDays(now()) }} {{ __('common.days') }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Last Updated -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('customer::customer.last_updated') }}</p>
                        <p class="text-xl font-bold text-gray-900 mt-2">{{ $customer->updated_at->diffForHumans() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Notes Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('customer::customer.customer_notes') }}</h2>
            </div>
            <div class="p-6">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                {{ __('customer::customer.notes_feature_coming_soon') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('customer::customer.activity_log') }}</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <!-- Current Status -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-{{ $customer->status_color }}-100 rounded-full flex items-center justify-center flex-shrink-0">
                            @if($customer->is_active)
                                <svg class="w-5 h-5 text-{{ $customer->status_color }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-{{ $customer->status_color }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">
                                {{ __('customer::customer.current_status') }}: 
                                <span class="text-{{ $customer->status_color }}-600">{{ $customer->status_label }}</span>
                            </p>
                            <p class="text-xs text-gray-600">{{ __('customer::customer.status_can_be_changed') }}</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 my-4"></div>

                    <!-- Created Event -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ __('customer::customer.customer_created') }}</p>
                            <p class="text-xs text-gray-600">{{ $customer->created_at->format('M d, Y \a\t H:i') }}</p>
                        </div>
                    </div>

                    @if($customer->created_at->ne($customer->updated_at))
                        <!-- Updated Event -->
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ __('customer::customer.customer_updated') }}</p>
                                <p class="text-xs text-gray-600">{{ $customer->updated_at->format('M d, Y \a\t H:i') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-dashboard>