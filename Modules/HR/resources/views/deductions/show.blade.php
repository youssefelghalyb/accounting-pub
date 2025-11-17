<x-dashboard :pageTitle="__('hr::deductions.deduction_details')">
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('hr.deductions.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('hr::deductions.deductions') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('hr::deductions.deduction_reference') }} #{{ $deduction->id }}</span>
                </li>
            </ol>
        </nav>

        <!-- Deduction Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl bg-red-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $deduction->employee->name }}</h1>
                        <p class="text-sm text-gray-600 mt-1">{{ __('hr::deductions.deduction_date') }}: {{ $deduction->deduction_date->format('Y-m-d') }}</p>
                        <div class="flex items-center gap-3 mt-2">
                            @if($deduction->type === 'days')
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    {{ __('hr::deductions.type_days') }}
                                </span>
                            @elseif($deduction->type === 'amount')
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ __('hr::deductions.type_amount') }}
                                </span>
                            @else
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    {{ __('hr::deductions.type_unpaid_leave') }}
                                </span>
                            @endif
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">
                                {{ number_format($deduction->amount, 2) }} {{ __('common.currency') }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('hr.deductions.edit', $deduction) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        {{ __('common.edit') }}
                    </a>
                    <form action="{{ route('hr.deductions.destroy', $deduction) }}" 
                          method="POST" 
                          class="inline-block"
                          onsubmit="return confirm('{{ __('hr::deductions.confirm_delete') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            {{ __('common.delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Employee Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('hr::deductions.employee_information') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('hr::employee.employee_name') }}</label>
                        <p class="text-base font-semibold text-gray-900">{{ $deduction->employee->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('hr::employee.employee_code') }}</label>
                        <p class="text-base font-semibold text-gray-900">{{ $deduction->employee->employee_code }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('hr::department.department') }}</label>
                        <p class="text-base font-semibold text-gray-900">{{ $deduction->employee->department->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('hr::employee.position') }}</label>
                        <p class="text-base font-semibold text-gray-900">{{ $deduction->employee->position }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deduction Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('hr::deductions.deduction_information') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('hr::deductions.deduction_type') }}</label>
                        @if($deduction->type === 'days')
                            <p class="text-base font-semibold text-gray-900">{{ __('hr::deductions.type_days') }}</p>
                        @elseif($deduction->type === 'amount')
                            <p class="text-base font-semibold text-gray-900">{{ __('hr::deductions.type_amount') }}</p>
                        @else
                            <p class="text-base font-semibold text-gray-900">{{ __('hr::deductions.type_unpaid_leave') }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('hr::deductions.deduction_date') }}</label>
                        <p class="text-base font-semibold text-gray-900">{{ $deduction->deduction_date->format('Y-m-d') }}</p>
                    </div>
                    @if($deduction->days)
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('hr::deductions.days') }}</label>
                            <p class="text-base font-semibold text-gray-900">{{ $deduction->days }} {{ __('hr::deductions.days_label') }}</p>
                        </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('hr::deductions.amount') }}</label>
                        <p class="text-2xl font-bold text-red-600">{{ number_format($deduction->amount, 2) }} {{ __('common.currency') }}</p>
                    </div>
                </div>

                @if($deduction->isFromLeave())
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex items-center gap-3 p-4 bg-purple-50 rounded-lg border border-purple-200">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-purple-900">{{ __('hr::deductions.linked_to_leave') }}</p>
                                <p class="text-xs text-purple-700 mt-1">{{ __('hr::deductions.leave_reference') }} #{{ $deduction->leave_id }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <label class="block text-sm font-medium text-gray-600 mb-2">{{ __('hr::deductions.reason') }}</label>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-900 leading-relaxed">{{ $deduction->reason }}</p>
                    </div>
                </div>

                @if($deduction->notes)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <label class="block text-sm font-medium text-gray-600 mb-2">{{ __('hr::deductions.notes') }}</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900 leading-relaxed">{{ $deduction->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Back Button -->
        <div class="flex justify-start">
            <a href="{{ route('hr.deductions.index') }}" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                {{ __('common.back_to_list') }}
            </a>
        </div>
    </div>
</x-dashboard>