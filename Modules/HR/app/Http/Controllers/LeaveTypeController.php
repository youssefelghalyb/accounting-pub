<?php

namespace Modules\HR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HR\Models\LeaveType;
use Modules\HR\Http\Requests\StoreLeaveTypeRequest;
use Modules\HR\Http\Requests\UpdateLeaveTypeRequest;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of leave types.
     */
    public function index(Request $request)
    {
        $query = LeaveType::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $leaveTypes = $query->withCount('leaves')->orderBy('created_at', 'desc')->get();

        // Calculate statistics
        $stats = [
            'total_leave_types' => LeaveType::count(),
            'paid_types' => LeaveType::where('is_paid', true)->count(),
            'unpaid_types' => LeaveType::where('is_paid', false)->count(),
            'unlimited_types' => LeaveType::whereNull('max_days_per_year')->count(),
        ];

        return view('hr::leave-types.index', compact('leaveTypes', 'stats'));
    }

    /**
     * Show the form for creating a new leave type.
     */
    public function create()
    {
        return view('hr::leave-types.create');
    }

    /**
     * Store a newly created leave type.
     */
    public function store(StoreLeaveTypeRequest $request)
    {
        $validated = $request->validated();

        LeaveType::create($validated);

        return redirect()
            ->route('hr.leave-types.index')
            ->with('success', __('hr::leave.leave_type_added'));
    }

    /**
     * Display the specified leave type.
     */
    public function show($id)
    {
        $leaveType = LeaveType::findOrFail($id);
        $leaveType->load(['leaves.employee']);
        
        // Get usage statistics
        $currentYear = now()->year;
        $stats = [
            'total_leaves' => $leaveType->leaves()->count(),
            'pending_leaves' => $leaveType->leaves()->pending()->count(),
            'approved_leaves' => $leaveType->leaves()->approved()->count(),
            'rejected_leaves' => $leaveType->leaves()->rejected()->count(),
            'current_year_leaves' => $leaveType->leaves()->forYear($currentYear)->count(),
            'total_days_used_this_year' => $leaveType->getTotalDaysUsedThisYear(),
        ];
        
        // Recent leaves
        $recentLeaves = $leaveType->leaves()
            ->with('employee')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('hr::leave-types.show', compact('leaveType', 'stats', 'recentLeaves'));
    }

    /**
     * Show the form for editing the specified leave type.
     */
    public function edit($id)
    {
        $leaveType = LeaveType::findOrFail($id);

        return view('hr::leave-types.edit', compact('leaveType'));
    }

    /**
     * Update the specified leave type.
     */
    public function update(UpdateLeaveTypeRequest $request, LeaveType $leaveType)
    {
        $validated = $request->validated();

        $leaveType->update($validated);

        return redirect()
            ->route('hr.leave-types.index')
            ->with('success', __('hr::leave.leave_type_updated'));
    }

    /**
     * Remove the specified leave type.
     */
    public function destroy(LeaveType $leaveType)
    {
        // Check if leave type has associated leaves
        if ($leaveType->leaves()->count() > 0) {
            return redirect()
                ->route('hr.leave-types.index')
                ->with('error', __('hr::leave.cannot_delete_type_with_leaves'));
        }

        $leaveType->delete();

        return redirect()
            ->route('hr.leave-types.index')
            ->with('success', __('hr::leave.leave_type_deleted'));
    }
}