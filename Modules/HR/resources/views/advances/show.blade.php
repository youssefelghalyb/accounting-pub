<x-dashboard :pageTitle="__('hr::advance.advance_details')">
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
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
                    <span class="text-gray-900 font-medium">{{ $advance->advance_code }}</span>
                </li>
            </ol>
        </nav>

        <!-- Advance Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $advance->advance_code }}</h1>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-{{ $advance->status_color }}-100 text-{{ $advance->status_color }}-800">
                            {{ __('hr::advance.statuses.' . $advance->status) }}
                        </span>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-{{ $advance->type_color }}-100 text-{{ $advance->type_color }}-800">
                            {{ __('hr::advance.types.' . $advance->type) }}
                        </span>
                        @if($advance->isOverdue())
                            <span class="inline-flex items-center gap-1 px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293z" clip-rule="evenodd"/>
                                </svg>
                                {{ __('hr::advance.overdue') }} ({{ $advance->overdue_days }} {{ __('common.days') }})
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-600">
                        {{ __('hr::advance.issued_to') }}: 
                        <a href="{{ route('hr.employees.show', $advance->employee) }}" class="font-medium text-blue-600 hover:text-blue-800">
                            {{ $advance->employee->full_name }}
                        </a>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('hr.advances.settlements.create') }}?advance_id={{ $advance->id }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        {{ __('hr::advance.add_settlement') }}
                    </a>
                    <a href="{{ route('hr.advances.edit', $advance) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        {{ __('common.edit') }}
                    </a>
                </div>
            </div>

            <!-- Advance Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6 pt-6 border-t border-gray-200">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('hr::advance.issue_date') }}</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $advance->issue_date->format('Y-m-d') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('hr::advance.expected_settlement') }}</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $advance->expected_settlement_date?->format('Y-m-d') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('hr::advance.actual_settlement') }}</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $advance->actual_settlement_date?->format('Y-m-d') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('hr::advance.issued_by') }}</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $advance->issuedBy?->full_name ?? '-' }}</p>
                </div>
            </div>
        </div>
        <!-- After the main header actions, add conditional action buttons -->
<div class="flex items-center gap-2">
    @if($advance->outstanding_balance > 0 && !$advance->hasDeduction())
        <!-- Convert to Deduction Button -->
        <form action="{{ route('hr.advances.convert-to-deduction', $advance) }}" 
              method="POST" 
              class="inline-block"
              onsubmit="return confirm('{{ __('hr::advance.confirm_convert_to_deduction', ['amount' => number_format($advance->outstanding_balance, 2)]) }}')">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                {{ __('hr::advance.convert_to_deduction') }}
            </button>
        </form>
    @endif

    @if($advance->hasOverpayment())
        <!-- Add to Salary Button -->
        <form action="{{ route('hr.advances.add-to-salary', $advance) }}" 
              method="POST" 
              class="inline-block"
              onsubmit="return confirm('{{ __('hr::advance.confirm_add_to_salary', ['amount' => number_format($advance->overpayment_amount, 2)]) }}')">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ __('hr::advance.add_to_salary') }}
            </button>
        </form>
    @endif

    <a href="{{ route('hr.advances.settlements.create') }}?advance_id={{ $advance->id }}" 
       class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        {{ __('hr::advance.add_settlement') }}
    </a>
    
    <a href="{{ route('hr.advances.edit', $advance) }}" 
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        {{ __('common.edit') }}
    </a>
</div>
<!-- Update status display section -->
<div class="flex items-center gap-3 mb-2">
    <h1 class="text-2xl font-bold text-gray-900">{{ $advance->advance_code }}</h1>
    
    @if($advance->status === 'settled_via_deduction')
        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800">
            {{ __('hr::advance.statuses.settled_via_deduction') }}
        </span>
    @else
        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-{{ $advance->status_color }}-100 text-{{ $advance->status_color }}-800">
            {{ __('hr::advance.statuses.' . $advance->status) }}
        </span>
    @endif
    
    <!-- ... rest of badges -->
</div>

<!-- Show deduction info if converted -->
@if($advance->hasDeduction())
    <div class="mt-4 p-4 bg-purple-50 border-l-4 border-purple-400 rounded-lg">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-purple-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-purple-800">{{ __('hr::advance.converted_to_deduction') }}</h3>
                <p class="text-sm text-purple-700 mt-1">
                    {{ __('hr::advance.deduction_info', [
                        'amount' => number_format($advance->getDeduction()->amount, 2),
                        'date' => $advance->getDeduction()->deduction_date->format('Y-m-d')
                    ]) }}
                </p>
                <a href="{{ route('hr.deductions.show', $advance->getDeduction()->id) }}" 
                   class="text-sm text-purple-600 hover:text-purple-800 font-medium mt-2 inline-block">
                    {{ __('hr::advance.view_deduction') }} →
                </a>
            </div>
        </div>
    </div>
@endif

        <!-- Financial Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 rounded-xl shadow-sm border border-blue-200 p-6">
                <p class="text-sm font-medium text-blue-600">{{ __('hr::advance.advance_amount') }}</p>
                <p class="text-3xl font-bold text-blue-900 mt-2">{{ number_format($advance->amount, 2) }}</p>
            </div>

            <div class="bg-green-50 rounded-xl shadow-sm border border-green-200 p-6">
                <p class="text-sm font-medium text-green-600">{{ __('hr::advance.cash_returned') }}</p>
                <p class="text-3xl font-bold text-green-900 mt-2">{{ number_format($advance->total_cash_returned, 2) }}</p>
            </div>

            <div class="bg-purple-50 rounded-xl shadow-sm border border-purple-200 p-6">
                <p class="text-sm font-medium text-purple-600">{{ __('hr::advance.amount_spent') }}</p>
                <p class="text-3xl font-bold text-purple-900 mt-2">{{ number_format($advance->total_spent, 2) }}</p>
            </div>

            <div class="bg-{{ $advance->outstanding_balance > 0 ? 'red' : 'gray' }}-50 rounded-xl shadow-sm border border-{{ $advance->outstanding_balance > 0 ? 'red' : 'gray' }}-200 p-6">
                <p class="text-sm font-medium text-{{ $advance->outstanding_balance > 0 ? 'red' : 'gray' }}-600">{{ __('hr::advance.outstanding') }}</p>
                <p class="text-3xl font-bold text-{{ $advance->outstanding_balance > 0 ? 'red' : 'gray' }}-900 mt-2">{{ number_format($advance->outstanding_balance, 2) }}</p>
            </div>
        </div>

        <!-- Purpose & Notes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('hr::advance.purpose') }}</h2>
            <p class="text-gray-700 leading-relaxed">{{ $advance->purpose }}</p>

            @if($advance->notes)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ __('hr::advance.notes') }}</h3>
                    <p class="text-sm text-gray-600">{{ $advance->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Settlements History -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">{{ __('hr::advance.settlements_history') }}</h2>
                    <span class="text-sm text-gray-600">
                        {{ $advance->settlements->count() }} {{ __('hr::advance.settlements') }}
                    </span>
                </div>
            </div>
            
            @if($settlementsData->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                @foreach($settlementsColumns as $column)
                                    <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">
                                        {{ $column['label'] }}
                                    </th>
                                @endforeach
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">
                                    {{ __('common.actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($settlementsData as $settlement)
                                <tr class="hover:bg-gray-50">
                                    @foreach($settlementsColumns as $column)
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if(isset($column['format']))
                                                {!! $column['format']($settlement[$column['field']], $settlement) !!}
                                            @elseif(isset($column['render']))
                                                {!! $column['render']($settlement) !!}
                                            @else
                                                {{ $settlement[$column['field']] }}
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            @foreach($settlementsActions as $action)
                                                @php
                                                    $show = !isset($action['show']) || $action['show']($settlement);
                                                @endphp
                                                
                                                @if($show)
                                                    @if($action['type'] === 'link')
                                                        <a href="{{ $action['route']($settlement) }}" 
                                                           class="inline-flex items-center justify-center w-8 h-8 {{ $action['color'] }} hover:bg-{{ str_replace('text-', '', $action['color']) }}-50 rounded-lg transition-colors"
                                                           title="{{ $action['label'] }}">
                                                            {!! $action['icon'] !!}
                                                        </a>
                                                    @elseif($action['type'] === 'form')
                                                        <form action="{{ $action['route']($settlement) }}" 
                                                              method="POST" 
                                                              class="inline-block"
                                                              onsubmit="return confirm('{{ __('common.are_you_sure') }}')">
                                                            @csrf
                                                            @method($action['method'])
                                                            <button type="submit" 
                                                                    class="inline-flex items-center justify-center w-8 h-8 {{ $action['color'] }} hover:bg-{{ str_replace('text-', '', $action['color']) }}-50 rounded-lg transition-colors"
                                                                    title="{{ $action['label'] }}">
                                                                {!! $action['icon'] !!}
                                                            </button>
                                                        </form>
                                                    @elseif($action['type'] === 'button')
                                                        <button type="button"
                                                                onclick="{{ $action['onclick']($settlement) }}"
                                                                class="inline-flex items-center justify-center w-8 h-8 {{ $action['color'] }} hover:bg-{{ str_replace('text-', '', $action['color']) }}-50 rounded-lg transition-colors"
                                                                title="{{ $action['label'] }}">
                                                            {!! $action['icon'] !!}
                                                        </button>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('hr::advance.no_settlements') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('hr::advance.no_settlements_description') }}</p>
                    <div class="mt-6">
                        <a href="{{ route('hr.advances.settlements.create') }}?advance_id={{ $advance->id }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            {{ __('hr::advance.add_settlement') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-dashboard>