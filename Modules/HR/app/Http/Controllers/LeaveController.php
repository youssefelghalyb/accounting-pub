<?php

namespace Modules\HR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HR\Models\Leave;
use Modules\HR\Models\Employee;
use Modules\HR\Models\LeaveType;
use Modules\HR\Models\Deduction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\HR\Http\Requests\StoreLeaveRequest;
use Modules\HR\Http\Requests\UpdateLeaveRequest;
use Modules\HR\Http\Requests\RejectLeaveRequest;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $query = Leave::with(['employee.department', 'leaveType']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by leave type
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        $leaves = $query->latest()->paginate(15);

        // Calculate statistics
        $stats = [
            'total_leaves' => Leave::count(),
            'pending_leaves' => Leave::where('status', 'pending')->count(),
            'approved_leaves' => Leave::where('status', 'approved')->count(),
            'rejected_leaves' => Leave::where('status', 'rejected')->count(),
        ];

        return view('hr::leaves.index', compact('leaves', 'stats'));
    }

    public function create()
    {
        $employees = Employee::where('status', 'active')->get();
        $leaveTypes = LeaveType::where('is_active', true)->get();

        $formConfig = [
            'groups' => [
                [
                    'title' => __('hr::leaves.leave_information'),
                    'fields' => [
                        [
                            'name' => 'employee_id',
                            'label' => __('hr::leaves.employee'),
                            'type' => 'select',
                            'options' => $employees->map(function($emp) {
                                return [
                                    'value' => $emp->id,
                                    'label' => $emp->first_name . ' ' . $emp->last_name . ' (' . $emp->employee_code . ')'
                                ];
                            })->prepend(['value' => '', 'label' => __('common.select')])->toArray(),
                            'required' => true,
                            'grid' => 6,
                            'borderColor' => '#3b82f6'
                        ],
                        [
                            'name' => 'leave_type_id',
                            'label' => __('hr::leaves.leave_type'),
                            'type' => 'select',
                            'options' => $leaveTypes->map(function($type) {
                                return [
                                    'value' => $type->id,
                                    'label' => $type->name
                                ];
                            })->prepend(['value' => '', 'label' => __('common.select')])->toArray(),
                            'required' => true,
                            'grid' => 6,
                            'borderColor' => '#3b82f6'
                        ],
                        [
                            'name' => 'start_date',
                            'label' => __('hr::leaves.start_date'),
                            'type' => 'date',
                            'required' => true,
                            'grid' => 6,
                            'borderColor' => '#10b981'
                        ],
                        [
                            'name' => 'end_date',
                            'label' => __('hr::leaves.end_date'),
                            'type' => 'date',
                            'required' => true,
                            'grid' => 6,
                            'borderColor' => '#10b981'
                        ],
                        [
                            'name' => 'reason',
                            'label' => __('hr::leaves.reason'),
                            'type' => 'textarea',
                            'rows' => 3,
                            'required' => true,
                            'placeholder' => __('hr::leaves.enter_reason'),
                            'grid' => 12,
                            'borderColor' => '#6366f1'
                        ],
                        [
                            'name' => 'notes',
                            'label' => __('hr::leaves.notes'),
                            'type' => 'textarea',
                            'rows' => 2,
                            'placeholder' => __('hr::leaves.enter_notes'),
                            'grid' => 12,
                            'borderColor' => '#6366f1'
                        ],
                    ]
                ]
            ]
        ];

        return view('hr::leaves.create', compact('formConfig'));
    }

    public function store(StoreLeaveRequest $request)
    {
        $validated = $request->validated();

        // Calculate days
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $days = $startDate->diffInDays($endDate) + 1;

        $validated['total_days'] = $days;
        $validated['status'] = 'pending';

        Leave::create($validated);

        return redirect()->route('hr.leaves.index')
            ->with('success', __('hr::leaves.leave_created'));
    }

    public function show($id)
    {
        $leave = Leave::with(['employee.department', 'leaveType', 'approvedBy', 'deduction'])->findOrFail($id);

        return view('hr::leaves.show', compact('leave'));
    }

    public function edit($id)
    {
        $leave = Leave::findOrFail($id);
        
        if ($leave->status !== 'pending') {
            return redirect()->route('hr.leaves.index')
                ->with('error', __('hr::leaves.cannot_edit_processed'));
        }

        $employees = Employee::where('status', 'active')->get();
        $leaveTypes = LeaveType::where('is_active', true)->get();

        $formConfig = [
            'groups' => [
                [
                    'title' => __('hr::leaves.leave_information'),
                    'fields' => [
                        [
                            'name' => 'employee_id',
                            'label' => __('hr::leaves.employee'),
                            'type' => 'select',
                            'options' => $employees->map(function($emp) {
                                return [
                                    'value' => $emp->id,
                                    'label' => $emp->first_name . ' ' . $emp->last_name . ' (' . $emp->employee_code . ')'
                                ];
                            })->prepend(['value' => '', 'label' => __('common.select')])->toArray(),
                            'required' => true,
                            'value' => $leave->employee_id,
                            'grid' => 6,
                            'borderColor' => '#3b82f6'
                        ],
                        [
                            'name' => 'leave_type_id',
                            'label' => __('hr::leaves.leave_type'),
                            'type' => 'select',
                            'options' => $leaveTypes->map(function($type) {
                                return [
                                    'value' => $type->id,
                                    'label' => $type->name
                                ];
                            })->prepend(['value' => '', 'label' => __('common.select')])->toArray(),
                            'required' => true,
                            'value' => $leave->leave_type_id,
                            'grid' => 6,
                            'borderColor' => '#3b82f6'
                        ],
                        [
                            'name' => 'start_date',
                            'label' => __('hr::leaves.start_date'),
                            'type' => 'date',
                            'required' => true,
                            'value' => $leave->start_date->format('Y-m-d'),
                            'grid' => 6,
                            'borderColor' => '#10b981'
                        ],
                        [
                            'name' => 'end_date',
                            'label' => __('hr::leaves.end_date'),
                            'type' => 'date',
                            'required' => true,
                            'value' => $leave->end_date->format('Y-m-d'),
                            'grid' => 6,
                            'borderColor' => '#10b981'
                        ],
                        [
                            'name' => 'reason',
                            'label' => __('hr::leaves.reason'),
                            'type' => 'textarea',
                            'rows' => 3,
                            'required' => true,
                            'value' => $leave->reason,
                            'placeholder' => __('hr::leaves.enter_reason'),
                            'grid' => 12,
                            'borderColor' => '#6366f1'
                        ],
                        [
                            'name' => 'notes',
                            'label' => __('hr::leaves.notes'),
                            'type' => 'textarea',
                            'rows' => 2,
                            'value' => $leave->notes,
                            'placeholder' => __('hr::leaves.enter_notes'),
                            'grid' => 12,
                            'borderColor' => '#6366f1'
                        ],
                    ]
                ]
            ]
        ];

        return view('hr::leaves.edit', compact('formConfig', 'leave'));
    }

    public function update(UpdateLeaveRequest $request, $id)
    {
        $leave = Leave::findOrFail($id);

        if ($leave->status !== 'pending') {
            return redirect()->route('hr.leaves.index')
                ->with('error', __('hr::leaves.cannot_edit_processed'));
        }

        $validated = $request->validated();

        // Calculate days
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $days = $startDate->diffInDays($endDate) + 1;

        $validated['total_days'] = $days;

        $leave->update($validated);

        return redirect()->route('hr.leaves.index')
            ->with('success', __('hr::leaves.leave_updated'));
    }

    public function destroy($id)
    {
        $leave = Leave::findOrFail($id);
        
        // Check if leave has associated deduction
        if ($leave->deduction) {
            return redirect()->route('hr.leaves.index')
                ->with('error', __('hr::leaves.cannot_delete_with_deduction'));
        }
        
        $leave->delete();

        return redirect()->route('hr.leaves.index')
            ->with('success', __('hr::leaves.leave_deleted'));
    }

    /**
     * Approve a leave request
     */
    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $leave = Leave::with(['employee', 'leaveType'])->findOrFail($id);

            if ($leave->status !== 'pending') {
                return redirect()->back()
                    ->with('error', __('hr::leaves.already_processed'));
            }

            // Check if can be approved (balance check)
            if (!$leave->canBeApproved()) {
                return redirect()->back()
                    ->with('error', __('hr::leaves.insufficient_balance'));
            }

            // Get current user ID (you may need to adjust this based on your auth setup)
            $approvedBy = Auth::id() ?? 1; // Fallback to 1 if no auth

            // Approve the leave
            $leave->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            // Create deduction if leave type is unpaid
            if (!$leave->leaveType->is_paid && !$leave->deduction_applied) {
                Deduction::create([
                    'employee_id' => $leave->employee_id,
                    'type' => 'unpaid_leave',
                    'days' => $leave->total_days,
                    'amount' => $leave->employee->daily_rate * $leave->total_days,
                    'deduction_date' => $leave->start_date,
                    'leave_id' => $leave->id,
                    'reason' => __('hr::deductions.unpaid_leave_deduction', [
                        'type' => $leave->leaveType->name
                    ]),
                    'notes' => __('hr::deductions.leave_period', [
                        'start' => $leave->start_date->format('Y-m-d'),
                        'end' => $leave->end_date->format('Y-m-d')
                    ])
                ]);

                $leave->update(['deduction_applied' => true]);
            }

            DB::commit();

            return redirect()->back()
                ->with('success', __('hr::leaves.leave_approved'));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', __('hr::leaves.approval_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Reject a leave request
     */
    public function reject(RejectLeaveRequest $request, $id)
    {
        $leave = Leave::findOrFail($id);

        if ($leave->status !== 'pending') {
            return redirect()->back()
                ->with('error', __('hr::leaves.already_processed'));
        }

        $validated = $request->validated();

        // Get current user ID
        $rejectedBy = Auth::id() ?? 1;

        $leave->update([
            'status' => 'rejected',
            'approved_by' => $rejectedBy,
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->back()
            ->with('success', __('hr::leaves.leave_rejected'));
    }
}