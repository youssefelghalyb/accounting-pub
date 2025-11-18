<?php

namespace Modules\HR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HR\Models\Deduction;
use Modules\HR\Models\Employee;
use Carbon\Carbon;
use Modules\HR\Http\Requests\StoreDeductionRequest;
use Modules\HR\Http\Requests\UpdateDeductionRequest;

class DeductionController extends Controller
{
    public function index(Request $request)
    {
        $query = Deduction::with(['employee.department', 'leave']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('deduction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('deduction_date', '<=', $request->date_to);
        }

        $deductions = $query->latest('deduction_date')->paginate(15);

        // Calculate statistics
        $stats = [
            'total_deductions' => Deduction::count(),
            'total_amount' => Deduction::sum('amount'),
            'days_deductions' => Deduction::where('type', 'days')->count(),
            'amount_deductions' => Deduction::where('type', 'amount')->count(),
            'unpaid_leave_deductions' => Deduction::where('type', 'unpaid_leave')->count(),
            'this_month_amount' => Deduction::whereYear('deduction_date', now()->year)
                                           ->whereMonth('deduction_date', now()->month)
                                           ->sum('amount'),
        ];

        return view('hr::deductions.index', compact('deductions', 'stats'));
    }

    public function create()
    {
        $employees = Employee::where('status', 'active')->get();

        $formConfig = [
            'groups' => [
                [
                    'title' => __('hr::deductions.deduction_information'),
                    'fields' => [
                        [
                            'name' => 'employee_id',
                            'label' => __('hr::deductions.employee'),
                            'type' => 'select',
                            'options' => $employees->map(function($emp) {
                                return [
                                    'value' => $emp->id,
                                    'label' => $emp->name . ' (' . $emp->employee_code . ')'
                                ];
                            })->prepend(['value' => '', 'label' => __('common.select')])->toArray(),
                            'required' => true,
                            'grid' => 6,
                            'borderColor' => '#3b82f6'
                        ],
                        [
                            'name' => 'deduction_date',
                            'label' => __('hr::deductions.deduction_date'),
                            'type' => 'date',
                            'required' => true,
                            'grid' => 6,
                            'borderColor' => '#3b82f6'
                        ],
                        [
                            'name' => 'type',
                            'label' => __('hr::deductions.deduction_type'),
                            'type' => 'radio',
                            'required' => true,
                            'grid' => 12,
                            'layout' => 'row',
                            'options' => [
                                ['value' => 'days', 'label' => __('hr::deductions.type_days')],
                                ['value' => 'amount', 'label' => __('hr::deductions.type_amount')],
                                ['value' => 'unpaid_leave', 'label' => __('hr::deductions.type_unpaid_leave')]
                            ],
                            'borderColor' => '#10b981'
                        ],
                        [
                            'name' => 'days',
                            'label' => __('hr::deductions.days'),
                            'type' => 'number',
                            'placeholder' => __('hr::deductions.enter_days'),
                            'grid' => 6,
                            'borderColor' => '#10b981'
                        ],
                        [
                            'name' => 'amount',
                            'label' => __('hr::deductions.amount'),
                            'type' => 'number',
                            'placeholder' => __('hr::deductions.enter_amount'),
                            'grid' => 6,
                            'borderColor' => '#10b981'
                        ],
                        [
                            'name' => 'reason',
                            'label' => __('hr::deductions.reason'),
                            'type' => 'textarea',
                            'rows' => 3,
                            'required' => true,
                            'placeholder' => __('hr::deductions.enter_reason'),
                            'grid' => 12,
                            'borderColor' => '#6366f1'
                        ],
                        [
                            'name' => 'notes',
                            'label' => __('hr::deductions.notes'),
                            'type' => 'textarea',
                            'rows' => 2,
                            'placeholder' => __('hr::deductions.enter_notes'),
                            'grid' => 12,
                            'borderColor' => '#6366f1'
                        ],
                    ]
                ]
            ]
        ];

        return view('hr::deductions.create', compact('formConfig'));
    }

    public function store(StoreDeductionRequest $request)
    {
        $validated = $request->validated();

        Deduction::create($validated);

        return redirect()->route('hr.deductions.index')
            ->with('success', __('hr::deductions.deduction_created'));
    }

    public function show($id)
    {
        $deduction = Deduction::with(['employee.department', 'leave'])->findOrFail($id);

        return view('hr::deductions.show', compact('deduction'));
    }

    public function edit($id)
    {
        $deduction = Deduction::findOrFail($id);
        $employees = Employee::where('status', 'active')->get();

        $formConfig = [
            'groups' => [
                [
                    'title' => __('hr::deductions.deduction_information'),
                    'fields' => [
                        [
                            'name' => 'employee_id',
                            'label' => __('hr::deductions.employee'),
                            'type' => 'select',
                            'options' => $employees->map(function($emp) {
                                return [
                                    'value' => $emp->id,
                                    'label' => $emp->name . ' (' . $emp->employee_code . ')'
                                ];
                            })->prepend(['value' => '', 'label' => __('common.select')])->toArray(),
                            'required' => true,
                            'value' => $deduction->employee_id,
                            'grid' => 6,
                            'borderColor' => '#3b82f6'
                        ],
                        [
                            'name' => 'deduction_date',
                            'label' => __('hr::deductions.deduction_date'),
                            'type' => 'date',
                            'required' => true,
                            'value' => $deduction->deduction_date->format('Y-m-d'),
                            'grid' => 6,
                            'borderColor' => '#3b82f6'
                        ],
                        [
                            'name' => 'type',
                            'label' => __('hr::deductions.deduction_type'),
                            'type' => 'radio',
                            'required' => true,
                            'grid' => 12,
                            'layout' => 'row',
                            'options' => [
                                ['value' => 'days', 'label' => __('hr::deductions.type_days')],
                                ['value' => 'amount', 'label' => __('hr::deductions.type_amount')],
                                ['value' => 'unpaid_leave', 'label' => __('hr::deductions.type_unpaid_leave')]
                            ],
                            'value' => $deduction->type,
                            'borderColor' => '#10b981'
                        ],
                        [
                            'name' => 'days',
                            'label' => __('hr::deductions.days'),
                            'type' => 'number',
                            'placeholder' => __('hr::deductions.enter_days'),
                            'value' => $deduction->days,
                            'grid' => 6,
                            'borderColor' => '#10b981'
                        ],
                        [
                            'name' => 'amount',
                            'label' => __('hr::deductions.amount'),
                            'type' => 'number',
                            'placeholder' => __('hr::deductions.enter_amount'),
                            'value' => $deduction->amount,
                            'grid' => 6,
                            'borderColor' => '#10b981'
                        ],
                        [
                            'name' => 'reason',
                            'label' => __('hr::deductions.reason'),
                            'type' => 'textarea',
                            'rows' => 3,
                            'required' => true,
                            'value' => $deduction->reason,
                            'placeholder' => __('hr::deductions.enter_reason'),
                            'grid' => 12,
                            'borderColor' => '#6366f1'
                        ],
                        [
                            'name' => 'notes',
                            'label' => __('hr::deductions.notes'),
                            'type' => 'textarea',
                            'rows' => 2,
                            'value' => $deduction->notes,
                            'placeholder' => __('hr::deductions.enter_notes'),
                            'grid' => 12,
                            'borderColor' => '#6366f1'
                        ],
                    ]
                ]
            ]
        ];

        return view('hr::deductions.edit', compact('formConfig', 'deduction'));
    }

    public function update(UpdateDeductionRequest $request, $id)
    {
        $deduction = Deduction::findOrFail($id);

        $validated = $request->validated();

        $deduction->update($validated);

        return redirect()->route('hr.deductions.index')
            ->with('success', __('hr::deductions.deduction_updated'));
    }

    public function destroy($id)
    {
        $deduction = Deduction::findOrFail($id);
        $deduction->delete();

        return redirect()->route('hr.deductions.index')
            ->with('success', __('hr::deductions.deduction_deleted'));
    }
}