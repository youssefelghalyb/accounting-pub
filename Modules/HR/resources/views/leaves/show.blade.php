<x-dashboard :pageTitle="__('hr::leaves.leave_details')">
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('hr.leaves.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('hr::leaves.leaves') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ __('hr::leaves.leave_reference') }} #{{ $leave->id }}</span>
                </li>
            </ol>
        </nav>

        <!-- Leave Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl flex items-center justify-center" style="background-color: {{ $leave->leaveType->color }}20;">
                        <div class="w-8 h-8 rounded-full" style="background-color: {{ $leave->leaveType->color }};"></div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $leave->employee->first_name }} {{ $leave->employee->last_name }}</h1>
                        <p class="text-sm text-gray-600 mt-1">{{ $leave->leaveType->name }}</p>
                        <div class="flex items-center gap-3 mt-2">
                            @if($leave->status === 'pending')
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    {{ __('hr::leaves.status_pending') }}
                                </span>
                            @elseif($leave->status === 'approved')
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ __('hr::leaves.status_approved') }}
                                </span>
                            @else
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    {{ __('hr::leaves.status_rejected') }}
                                </span>
                            @endif
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                {{ $leave->total_days }} {{ __('hr::leaves.days_label') }}
                            </span>
                            @if(!$leave->leaveType->is_paid)
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800">
                                    {{ __('hr::leaves.unpaid') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if($leave->status === 'pending')
                        <!-- Approve Button -->
                        <form action="{{ route('hr.leaves.approve', $leave) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit" 
                                    onclick="return confirm('{{ __('hr::leaves.confirm_approve') }}')"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('hr::leaves.approve') }}
                            </button>
                        </form>
                        
                        <!-- Reject Button (opens modal) -->
                        <button type="button" 
                                onclick="openRejectModal()"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            {{ __('hr::leaves.reject') }}
                        </button>

                        <!-- Edit Button -->
                        <a href="{{ route('hr.leaves.edit', $leave) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            {{ __('common.edit') }}
                        </a>
                    @endif

                    <!-- Delete Button (only for pending) -->
                    @if($leave->status === 'pending' || !$leave->deduction)
                        <form action="{{ route('hr.leaves.destroy', $leave) }}" 
                              method="POST" 
                              class="inline-block"
                              onsubmit="return confirm('{{ __('hr::leaves.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                {{ __('common.delete') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Rejection Reason Alert (if rejected) -->
        @if($leave->status === 'rejected' && $leave->rejection_reason)
            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-red-900">{{ __('hr::leaves.rejection_reason') }}</h3>
                        <p class="text-sm text-red-800 mt-1">{{ $leave->rejection_reason }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Deduction Alert (if deduction applied) -->
        @if($leave->deduction)
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-orange-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-orange-900">{{ __('hr::leaves.deduction_applied') }}</h3>
                            <p class="text-sm text-orange-800 mt-1">
                                {{ __('hr::leaves.deduction_amount') }}: 
                                <span class="font-bold">{{ number_format($leave->deduction->amount, 2) }} {{ __('common.currency') }}</span>
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('hr.deductions.show', $leave->deduction) }}" 
                       class="inline-flex items-center gap-2 px-3 py-1.5 bg-orange-600 text-white text-sm rounded-lg hover:bg-orange-700 transition-colors">
                        {{ __('hr::leaves.view_deduction') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        @endif

        <!-- Employee Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('hr::leaves.employee_information') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('hr::employee.employee_name') }}</label>
                        <p class="text-base font-semibold text-gray-900">{{ $leave->employee->first_name }} {{ $leave->employee->last_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('hr::employee.employee_code') }}</label>
                        <p class="text-base font-semibold text-gray-900">{{ $leave->employee->employee_code }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('hr::department.department') }}</label>
                        <p class="text-base font-semibold text-gray-900">{{ $leave->employee->department->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('hr::employee.position') }}</label>
                        <p class="text-base font-semibold text-gray-900">{{ $leave->employee->position }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leave Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('hr::leaves.leave_information') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('hr::leaves.leave_type') }}</label>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $leave->leaveType->color }};"></div>
                            <p class="text-base font-semibold text-gray-900">{{ $leave->leaveType->name }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('hr::leaves.duration') }}</label>
                        <p class="text-base font-semibold text-gray-900">{{ $leave->total_days }} {{ __('hr::leaves.days_label') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('hr::leaves.start_date') }}</label>
                        <p class="text-base font-semibold text-gray-900">{{ $leave->start_date->format('Y-m-d') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('hr::leaves.end_date') }}</label>
                        <p class="text-base font-semibold text-gray-900">{{ $leave->end_date->format('Y-m-d') }}</p>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <label class="block text-sm font-medium text-gray-600 mb-2">{{ __('hr::leaves.reason') }}</label>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-900 leading-relaxed">{{ $leave->reason }}</p>
                    </div>
                </div>

                @if($leave->notes)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <label class="block text-sm font-medium text-gray-600 mb-2">{{ __('hr::leaves.notes') }}</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900 leading-relaxed">{{ $leave->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Approval Information -->
        @if($leave->status !== 'pending')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">{{ __('hr::leaves.approval_information') }}</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($leave->approved_by)
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">
                                    {{ $leave->status === 'approved' ? __('hr::leaves.approved_by') : __('hr::leaves.rejected_by') }}
                                </label>
                                <p class="text-base font-semibold text-gray-900">
                                    {{ $leave->approvedBy ? $leave->approvedBy->first_name . ' ' . $leave->approvedBy->last_name : __('hr::leaves.not_available') }}
                                </p>
                            </div>
                        @endif
                        @if($leave->approved_at)
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('hr::leaves.approval_date') }}</label>
                                <p class="text-base font-semibold text-gray-900">{{ $leave->approved_at->format('Y-m-d H:i') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Back Button -->
        <div class="flex justify-start">
            <a href="{{ route('hr.leaves.index') }}" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                {{ __('common.back_to_list') }}
            </a>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
            <div class="flex flex-col">
                <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">{{ __('hr::leaves.reject_leave') }}</h3>
                    <button type="button" 
                            onclick="closeRejectModal()"
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form action="{{ route('hr.leaves.reject', $leave) }}" method="POST" class="mt-4">
                    @csrf
                    <div class="mb-4">
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('hr::leaves.rejection_reason') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea name="rejection_reason" 
                                  id="rejection_reason" 
                                  rows="4" 
                                  required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                  placeholder="{{ __('hr::leaves.enter_rejection_reason') }}"></textarea>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="submit" 
                                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            {{ __('hr::leaves.reject') }}
                        </button>
                        <button type="button" 
                                onclick="closeRejectModal()"
                                class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            {{ __('common.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            document.getElementById('rejection_reason').value = '';
        }

        // Close modal on outside click
        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });
    </script>
    @endpush
</x-dashboard>