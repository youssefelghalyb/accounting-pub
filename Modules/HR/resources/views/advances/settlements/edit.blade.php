<x-dashboard :pageTitle="__('hr::advance.edit_settlement')">
    <div class="mb-6">
        <nav aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                @if($settlement->advance)
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
                        <a href="{{ route('hr.advances.show', $settlement->advance) }}" class="text-gray-500 hover:text-gray-700">
                            {{ $settlement->advance->advance_code }}
                        </a>
                    </li>
                @else
                    <li>
                        <a href="{{ route('hr.employees.index') }}" class="text-gray-500 hover:text-gray-700">
                            {{ __('hr::employee.employees') }}
                        </a>
                    </li>
                    <li>
                        <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </li>
                    <li>
                        <a href="{{ route('hr.employees.show', $settlement->employee) }}" class="text-gray-500 hover:text-gray-700">
                            {{ $settlement->employee->full_name }}
                        </a>
                    </li>
                @endif
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('common.edit') }}</span>
                </li>
            </ol>
        </nav>
    </div>

    <x-dashboard.packages.form-builder 
        :formConfig="$formConfig" 
        :action="route('hr.advances.settlements.update', $settlement)" 
        method="POST"
    />

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('dynamicForm');
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            form.appendChild(methodInput);
        });
    </script>
    @endpush
</x-dashboard>