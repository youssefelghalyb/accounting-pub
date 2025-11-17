<x-dashboard :pageTitle="__('hr::advance.add_settlement')">
    <div class="mb-6">
        <nav aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                @if($advance)
                    <li>
                        <a href="{{ route('hr.advances.index') }}" class="text-gray-500 hover:text-gray-700">
                            {{ __('hr::advance.employee_advances') }}
                        </a>
                    </li>
                    <li>
                        <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </li>
                    <li>
                        <a href="{{ route('hr.advances.show', $advance) }}" class="text-gray-500 hover:text-gray-700">
                            {{ $advance->advance_code }}
                        </a>
                    </li>
                @else
                    <li>
                        <a href="{{ route('hr.employees.index') }}" class="text-gray-500 hover:text-gray-700">
                            {{ __('hr::employee.employees') }}
                        </a>
                    </li>
                @endif
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('hr::advance.add_settlement') }}</span>
                </li>
            </ol>
        </nav>
    </div>

    @if($advance)
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-medium text-blue-900">{{ __('hr::advance.settlement_for_advance') }}: {{ $advance->advance_code }}</p>
                    <p class="text-xs text-blue-700 mt-1">
                        {{ __('hr::advance.outstanding') }}: <span class="font-bold">{{ number_format($advance->outstanding_balance, 2) }}</span>
                    </p>
                </div>
            </div>
        </div>
    @endif

    <x-dashboard.packages.form-builder 
        :formConfig="$formConfig" 
        :action="route('hr.advances.settlements.store')" 
        method="POST"
    />
</x-dashboard>