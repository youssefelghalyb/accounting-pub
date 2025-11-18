<x-dashboard :pageTitle="__('hr::employee.employee_details')">
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
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
                    <span class="text-gray-900 font-medium">{{ $employee->full_name }}</span>
                </li>
            </ol>
        </nav>

        <!-- Employee Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">
                        {{ strtoupper(substr($employee->first_name, 0, 1)) }}{{ strtoupper(substr($employee->last_name, 0, 1)) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $employee->full_name }}</h1>
                        <p class="text-sm text-gray-600 mt-1">{{ $employee->position ?? __('hr::employee.position') }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $employee->department->color }}"></div>
                            <span class="text-sm font-medium text-gray-700">{{ $employee->department->name }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('hr.employees.edit', $employee) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        {{ __('common.edit') }}
                    </a>
                </div>
            </div>

            <!-- Employee Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6 pt-6 border-t border-gray-200">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('hr::employee.email') }}</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $employee->email }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('hr::employee.phone') }}</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $employee->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('hr::employee.hire_date') }}</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $employee->hire_date->format('Y-m-d') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('hr::employee.years_of_service') }}</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $employee->years_of_service }} {{ __('common.years') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('hr::employee.salary') }}</p>
                    <p class="text-sm text-gray-900 mt-1">{{ number_format($employee->salary, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('hr::employee.daily_rate') }}</p>
                    <p class="text-sm text-gray-900 mt-1">{{ number_format($employee->daily_rate, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Salary Breakdown Card -->
<!-- Salary Breakdown Card -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-bold text-gray-900">{{ __('hr::employee.monthly_breakdown') }} - {{ now()->format('F Y') }}</h2>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Gross Salary -->
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <p class="text-sm font-medium text-blue-600">{{ __('hr::employee.gross_salary') }}</p>
                <p class="text-2xl font-bold text-blue-900 mt-2">{{ number_format($salaryBreakdown['gross_salary'], 2) }}</p>
            </div>

            <!-- Total Deductions -->
            <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                <p class="text-sm font-medium text-red-600">{{ __('hr::employee.total_deductions') }}</p>
                <p class="text-2xl font-bold text-red-900 mt-2">-{{ number_format($salaryBreakdown['total_deductions'], 2) }}</p>
            </div>

            <!-- Bonuses -->
            @if($salaryBreakdown['total_bonuses'] > 0)
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <p class="text-sm font-medium text-green-600">{{ __('hr::employee.bonuses') }}</p>
                    <p class="text-2xl font-bold text-green-900 mt-2">+{{ number_format($salaryBreakdown['total_bonuses'], 2) }}</p>
                </div>
            @endif

            <!-- Net Salary -->
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                <p class="text-sm font-medium text-purple-600">{{ __('hr::employee.net_salary') }}</p>
                <p class="text-2xl font-bold text-purple-900 mt-2">{{ number_format($salaryBreakdown['net_salary'], 2) }}</p>
            </div>
        </div>

        <!-- Show bonuses breakdown if exists -->
        @if($salaryBreakdown['bonuses']->count() > 0)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ __('hr::employee.bonuses_this_month') }}</h3>
                <div class="space-y-2">
                    @foreach($salaryBreakdown['bonuses'] as $bonus)
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $bonus->reason }}</p>
                                <p class="text-xs text-gray-600">{{ $bonus->deduction_date->format('Y-m-d') }}</p>
                            </div>
                            <span class="text-sm font-bold text-green-600">+{{ number_format(abs($bonus->amount), 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Leave Statistics -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900">{{ __('hr::leaves.leave_statistics') }}</h2>
                        <a href="{{ route('hr.leaves.create') }}?employee={{ $employee->id }}" 
                           class="text-sm text-blue-600 hover:text-blue-800">
                            {{ __('hr::leaves.request_leave') }}
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-900">{{ $leaveStats['total_leaves'] }}</p>
                            <p class="text-xs text-gray-600 mt-1">{{ __('hr::leaves.total_leaves') }}</p>
                        </div>
                        <div class="text-center p-4 bg-yellow-50 rounded-lg">
                            <p class="text-2xl font-bold text-yellow-900">{{ $leaveStats['pending_leaves'] }}</p>
                            <p class="text-xs text-yellow-700 mt-1">{{ __('hr::leaves.pending') }}</p>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <p class="text-2xl font-bold text-green-900">{{ $leaveStats['approved_leaves'] }}</p>
                            <p class="text-xs text-green-700 mt-1">{{ __('hr::leaves.approved') }}</p>
                        </div>
                        <div class="text-center p-4 bg-red-50 rounded-lg">
                            <p class="text-2xl font-bold text-red-900">{{ $leaveStats['rejected_leaves'] }}</p>
                            <p class="text-xs text-red-700 mt-1">{{ __('hr::leaves.rejected') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">{{ __('common.quick_links') }}</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <a href="{{ route('hr.leaves.create') }}?employee={{ $employee->id }}" 
                           class="flex items-center gap-3 p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ __('hr::leaves.request_leave') }}</p>
                                <p class="text-xs text-gray-600">{{ __('hr::leaves.my_leaves') }}</p>
                            </div>
                        </a>

                        <a href="{{ route('hr.deductions.create') }}?employee={{ $employee->id }}" 
                           class="flex items-center gap-3 p-3 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                            <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ __('hr::deductions.add_deduction') }}</p>
                                <p class="text-xs text-gray-600">{{ __('hr::deductions.employee_deductions') }}</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Leaves -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('hr::leaves.leave_history') }}</h2>
            </div>
            <div class="overflow-x-auto">
                @if($recentLeaves->count() > 0)
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('hr::leaves.leave_type') }}</th>
                                <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('hr::leaves.start_date') }}</th>
                                <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('hr::leaves.end_date') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('hr::leaves.total_days') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('common.status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($recentLeaves as $leave)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-medium text-gray-900">{{ $leave->leaveType->name }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $leave->start_date->format('Y-m-d') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $leave->end_date->format('Y-m-d') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-sm font-medium text-gray-900">{{ $leave->total_days }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($leave->status === 'pending')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                {{ __('hr::leaves.pending') }}
                                            </span>
                                        @elseif($leave->status === 'approved')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ __('hr::leaves.approved') }}
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                {{ __('hr::leaves.rejected') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500">{{ __('hr::leaves.no_leaves') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Deductions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('hr::deductions.deduction_summary') }}</h2>
            </div>
            <div class="overflow-x-auto">
                @if($recentDeductions->count() > 0)
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('hr::deductions.type') }}</th>
                                <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('hr::deductions.reason') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('hr::deductions.days') }}</th>
                                <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('hr::deductions.amount') }}</th>
                                <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('common.date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($recentDeductions as $deduction)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $deduction->type === 'days' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $deduction->type === 'amount' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $deduction->type === 'unpaid_leave' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ $deduction->type_name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ Str::limit($deduction->reason, 50) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">{{ $deduction->days ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">-{{ number_format($deduction->amount, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $deduction->deduction_date->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500">{{ __('hr::deductions.no_deductions') }}</p>
                    </div>
                @endif
            </div>
        </div>
        <!-- Employee Advances Summary -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">{{ __('hr::advance.employee_advances_summary') }}</h2>
                <a href="{{ route('hr.advances.create') }}?employee_id={{ $employee->id }}" 
                   class="text-sm text-blue-600 hover:text-blue-800">
                    {{ __('hr::advance.add_advance') }}
                </a>
            </div>
        </div>
        <div class="p-6">
            @php
                $employeeAdvances = $employee->advances;
                $totalAdvances = $employeeAdvances->sum('amount');
                $totalOutstanding = $employeeAdvances->sum('outstanding_balance');
            @endphp
            
            <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <p class="text-2xl font-bold text-blue-900">{{ $employeeAdvances->count() }}</p>
                    <p class="text-xs text-blue-700 mt-1">{{ __('hr::advance.total_advances') }}</p>
                </div>
                <div class="text-center p-4 bg-red-50 rounded-lg">
                    <p class="text-2xl font-bold text-red-900">{{ number_format($totalOutstanding, 2) }}</p>
                    <p class="text-xs text-red-700 mt-1">{{ __('hr::advance.total_outstanding') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">{{ __('hr::advance.recent_settlements') }}</h2>
                <a href="{{ route('hr.advances.settlements.create') }}?employee_id={{ $employee->id }}" 
                   class="text-sm text-green-600 hover:text-green-800">
                    {{ __('hr::advance.add_settlement') }}
                </a>
            </div>
        </div>
        <div class="p-6">
            @php
                $recentSettlements = $employee->advanceSettlements()->latest()->take(3)->get();
            @endphp
            
            @if($recentSettlements->count() > 0)
                <div class="space-y-3">
                    @foreach($recentSettlements as $settlement)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $settlement->settlement_code }}</p>
                                <p class="text-xs text-gray-600">{{ $settlement->settlement_date->format('Y-m-d') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-green-600">{{ number_format($settlement->total_accounted, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 text-sm">{{ __('hr::advance.no_settlements') }}</p>
            @endif
        </div>
    </div>
</div>

<!-- Recent Advances Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-bold text-gray-900">{{ __('hr::advance.recent_advances') }}</h2>
    </div>
    <div class="overflow-x-auto">
        @if($employeeAdvances->count() > 0)
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('hr::advance.advance_code') }}</th>
                        <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('hr::advance.amount') }}</th>
                        <th class="px-6 py-3 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-xs font-semibold text-gray-600 uppercase">{{ __('hr::advance.outstanding') }}</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('common.status') }}</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($employeeAdvances->take(5) as $advance)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-gray-900">{{ $advance->advance_code }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($advance->amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $advance->outstanding_balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($advance->outstanding_balance, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-{{ $advance->status_color }}-100 text-{{ $advance->status_color }}-800">
                                    {{ __('hr::advance.statuses.' . $advance->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="{{ route('hr.advances.show', $advance) }}" 
                                   class="text-blue-600 hover:text-blue-800">
                                    {{ __('common.view') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="text-center py-8">
                <p class="text-gray-500">{{ __('hr::advance.no_advances') }}</p>
            </div>
        @endif
    </div>
</div>
    </div>
</x-dashboard>