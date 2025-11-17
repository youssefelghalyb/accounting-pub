<?php

namespace Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\HR\Models\Advance;
use Modules\HR\Models\AdvanceSettlement;
use Modules\HR\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\HR\Models\Deduction;

class AdvanceController extends Controller
{
    // ============================================
    // ADVANCES SECTION
    // ============================================

    public function index(Request $request)
    {
        $query = Advance::with(['employee', 'issuedBy']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('advance_code', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($eq) use ($search) {
                        $eq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $advances = $query->latest()->paginate(15);

        // Prepare table data
        $tableData = $advances->map(function ($advance) {
            return [
                'id' => $advance->id,
                'advance_code' => $advance->advance_code,
                'employee_name' => $advance->employee->full_name,
                'amount' => $advance->amount,
                'issue_date' => $advance->issue_date->format('Y-m-d'),
                'outstanding' => $advance->outstanding_balance,
                'type' => $advance->type,
                'type_color' => $advance->type_color,
                'status' => $advance->status,
                'status_color' => $advance->status_color,
                'is_overdue' => $advance->isOverdue(),
            ];
        });

        $tableColumns = [
            ['label' => __('hr::advance.advance_code'), 'field' => 'advance_code'],
            ['label' => __('hr::employee.employee'), 'field' => 'employee_name'],
            [
                'label' => __('hr::advance.amount'),
                'field' => 'amount',
                'format' => fn($value) => number_format($value, 2)
            ],
            ['label' => __('hr::advance.issue_date'), 'field' => 'issue_date'],
            [
                'label' => __('hr::advance.outstanding'),
                'field' => 'outstanding',
                'format' => fn($value, $row) =>
                '<span class="font-medium ' . ($value > 0 ? 'text-red-600' : 'text-green-600') . '">' .
                    number_format($value, 2) .
                    '</span>'
            ],
            [
                'label' => __('hr::advance.type'),
                'field' => 'type',
                'render' => fn($row) =>
                '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-' . $row['type_color'] . '-100 text-' . $row['type_color'] . '-800">' .
                    __('hr::advance.types.' . $row['type']) .
                    '</span>'
            ],
            [
                'label' => __('common.status'),
                'field' => 'status',
                'render' => fn($row) =>
                '<span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-' . $row['status_color'] . '-100 text-' . $row['status_color'] . '-800">' .
                    ($row['is_overdue'] ? '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293z" clip-rule="evenodd"/></svg>' : '') .
                    __('hr::advance.statuses.' . $row['status']) .
                    '</span>'
            ],
        ];

        $tableActions = [
            [
                'type' => 'link',
                'label' => __('common.view'),
                'route' => fn($row) => route('hr.advances.show', $row['id']),
                'color' => 'text-blue-600',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>'
            ],
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('hr.advances.edit', $row['id']),
                'color' => 'text-green-600',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>'
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('hr.advances.destroy', $row['id']),
                'method' => 'DELETE',
                'color' => 'text-red-600',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>'
            ],
        ];

        $filters = [
            [
                'type' => 'select',
                'name' => 'status',
                'label' => __('hr::advance.filter_status'),
                'options' => [
                    ['value' => 'pending', 'label' => __('hr::advance.statuses.pending')],
                    ['value' => 'partial_settlement', 'label' => __('hr::advance.statuses.partial_settlement')],
                    ['value' => 'settled', 'label' => __('hr::advance.statuses.settled')],
                ]
            ],
            [
                'type' => 'select',
                'name' => 'type',
                'label' => __('hr::advance.filter_type'),
                'options' => [
                    ['value' => 'cash', 'label' => __('hr::advance.types.cash')],
                    ['value' => 'salary_advance', 'label' => __('hr::advance.types.salary_advance')],
                    ['value' => 'petty_cash', 'label' => __('hr::advance.types.petty_cash')],
                    ['value' => 'travel', 'label' => __('hr::advance.types.travel')],
                    ['value' => 'purchase', 'label' => __('hr::advance.types.purchase')],
                ]
            ],
        ];

        return view('hr::advances.index', compact('tableData', 'tableColumns', 'tableActions', 'filters', 'advances'));
    }

    public function create(Request $request)
    {
        $employees = Employee::orderBy('first_name')->get();
        $selectedEmployee = $request->employee_id ? Employee::findOrFail($request->employee_id) : null;

        $formConfig = [
            'groups' => [
                [
                    'title' => __('hr::advance.advance_details'),
                    'fields' => [
                        [
                            'name' => 'employee_id',
                            'label' => __('hr::employee.employee'),
                            'type' => 'select',
                            'grid' => 6,
                            'required' => true,
                            'value' => $selectedEmployee?->id,
                            'options' => $employees->map(fn($e) => [
                                'value' => $e->id,
                                'label' => $e->full_name . ' - ' . $e->department->name
                            ])->toArray()
                        ],
                        [
                            'name' => 'amount',
                            'label' => __('hr::advance.amount'),
                            'type' => 'number',
                            'grid' => 6,
                            'required' => true,
                            'placeholder' => '0.00'
                        ],
                        [
                            'name' => 'issue_date',
                            'label' => __('hr::advance.issue_date'),
                            'type' => 'date',
                            'grid' => 6,
                            'required' => true,
                            'value' => now()->format('Y-m-d')
                        ],
                        [
                            'name' => 'expected_settlement_date',
                            'label' => __('hr::advance.expected_settlement_date'),
                            'type' => 'date',
                            'grid' => 6,
                        ],
                        [
                            'name' => 'type',
                            'label' => __('hr::advance.type'),
                            'type' => 'select',
                            'grid' => 12,
                            'required' => true,
                            'options' => [
                                ['value' => 'cash', 'label' => __('hr::advance.types.cash')],
                                ['value' => 'salary_advance', 'label' => __('hr::advance.types.salary_advance')],
                                ['value' => 'petty_cash', 'label' => __('hr::advance.types.petty_cash')],
                                ['value' => 'travel', 'label' => __('hr::advance.types.travel')],
                                ['value' => 'purchase', 'label' => __('hr::advance.types.purchase')],
                            ]
                        ],
                        [
                            'name' => 'purpose',
                            'label' => __('hr::advance.purpose'),
                            'type' => 'textarea',
                            'grid' => 12,
                            'required' => true,
                            'rows' => 3
                        ],
                        [
                            'name' => 'notes',
                            'label' => __('hr::advance.notes'),
                            'type' => 'textarea',
                            'grid' => 12,
                            'rows' => 2
                        ],
                    ]
                ]
            ]
        ];

        return view('hr::advances.create', compact('formConfig'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0.01',
            'issue_date' => 'required|date',
            'expected_settlement_date' => 'nullable|date|after:issue_date',
            'type' => 'required|in:cash,salary_advance,petty_cash,travel,purchase',
            'purpose' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $validated['issued_by'] = Auth::id();

        Advance::create($validated);

        return redirect()->route('hr.advances.index')
            ->with('success', __('hr::advance.created_successfully'));
    }

    public function show($id)
    {
        $advance = Advance::with(['employee', 'issuedBy', 'settlements.receivedBy'])
            ->findOrFail($id);

        // Prepare settlements table
        $settlementsData = $advance->settlements->map(function ($settlement) {
            return [
                'id' => $settlement->id,
                'settlement_code' => $settlement->settlement_code,
                'settlement_date' => $settlement->settlement_date->format('Y-m-d'),
                'cash_returned' => $settlement->cash_returned,
                'amount_spent' => $settlement->amount_spent,
                'total_accounted' => $settlement->total_accounted,
                'received_by' => $settlement->receivedBy?->full_name ?? '-',
                'has_receipt' => !is_null($settlement->receipt_file),
                'receipt_file' => $settlement->receipt_file,
            ];
        });

        $settlementsColumns = [
            ['label' => __('hr::advance.settlement_code'), 'field' => 'settlement_code'],
            ['label' => __('hr::advance.settlement_date'), 'field' => 'settlement_date'],
            [
                'label' => __('hr::advance.cash_returned'),
                'field' => 'cash_returned',
                'format' => fn($value) => '<span class="text-green-600 font-medium">' . number_format($value, 2) . '</span>'
            ],
            [
                'label' => __('hr::advance.amount_spent'),
                'field' => 'amount_spent',
                'format' => fn($value) => '<span class="text-blue-600 font-medium">' . number_format($value, 2) . '</span>'
            ],
            [
                'label' => __('hr::advance.total_accounted'),
                'field' => 'total_accounted',
                'format' => fn($value) => '<span class="font-bold">' . number_format($value, 2) . '</span>'
            ],
            ['label' => __('hr::advance.received_by'), 'field' => 'received_by'],
        ];

        $settlementsActions = [
            [
                'type' => 'link',
                'label' => __('common.edit'),
                'route' => fn($row) => route('hr.advances.settlements.edit', $row['id']),
                'color' => 'text-green-600',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>'
            ],
            [
                'type' => 'button',
                'label' => __('hr::advance.view_receipt'),
                'onclick' => fn($row) => $row['has_receipt'] ? "window.open('" . Storage::url($row['receipt_file']) . "', '_blank')" : "alert('" . __('hr::advance.no_receipt') . "')",
                'color' => 'text-blue-600',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
                'show' => fn($row) => $row['has_receipt']
            ],
            [
                'type' => 'form',
                'label' => __('common.delete'),
                'route' => fn($row) => route('hr.advances.settlements.destroy', $row['id']),
                'method' => 'DELETE',
                'color' => 'text-red-600',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>'
            ],
        ];

        return view('hr::advances.show', compact('advance', 'settlementsData', 'settlementsColumns', 'settlementsActions'));
    }

    public function edit($id)
    {
        $advance = Advance::findOrFail($id);
        $employees = Employee::orderBy('first_name')->get();

        $formConfig = [
            'groups' => [
                [
                    'title' => __('hr::advance.advance_details'),
                    'fields' => [
                        [
                            'name' => 'employee_id',
                            'label' => __('hr::employee.employee'),
                            'type' => 'select',
                            'grid' => 6,
                            'required' => true,
                            'value' => $advance->employee_id,
                            'options' => $employees->map(fn($e) => [
                                'value' => $e->id,
                                'label' => $e->full_name . ' - ' . $e->department->name
                            ])->toArray()
                        ],
                        [
                            'name' => 'amount',
                            'label' => __('hr::advance.amount'),
                            'type' => 'number',
                            'grid' => 6,
                            'required' => true,
                            'value' => $advance->amount
                        ],
                        [
                            'name' => 'issue_date',
                            'label' => __('hr::advance.issue_date'),
                            'type' => 'date',
                            'grid' => 6,
                            'required' => true,
                            'value' => $advance->issue_date->format('Y-m-d')
                        ],
                        [
                            'name' => 'expected_settlement_date',
                            'label' => __('hr::advance.expected_settlement_date'),
                            'type' => 'date',
                            'grid' => 6,
                            'value' => $advance->expected_settlement_date?->format('Y-m-d')
                        ],
                        [
                            'name' => 'type',
                            'label' => __('hr::advance.type'),
                            'type' => 'select',
                            'grid' => 12,
                            'required' => true,
                            'value' => $advance->type,
                            'options' => [
                                ['value' => 'cash', 'label' => __('hr::advance.types.cash')],
                                ['value' => 'salary_advance', 'label' => __('hr::advance.types.salary_advance')],
                                ['value' => 'petty_cash', 'label' => __('hr::advance.types.petty_cash')],
                                ['value' => 'travel', 'label' => __('hr::advance.types.travel')],
                                ['value' => 'purchase', 'label' => __('hr::advance.types.purchase')],
                            ]
                        ],
                        [
                            'name' => 'purpose',
                            'label' => __('hr::advance.purpose'),
                            'type' => 'textarea',
                            'grid' => 12,
                            'required' => true,
                            'value' => $advance->purpose,
                            'rows' => 3
                        ],
                        [
                            'name' => 'notes',
                            'label' => __('hr::advance.notes'),
                            'type' => 'textarea',
                            'grid' => 12,
                            'value' => $advance->notes,
                            'rows' => 2
                        ],
                    ]
                ]
            ]
        ];

        return view('hr::advances.edit', compact('formConfig', 'advance'));
    }

    public function update(Request $request, $id)
    {
        $advance = Advance::findOrFail($id);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0.01',
            'issue_date' => 'required|date',
            'expected_settlement_date' => 'nullable|date|after:issue_date',
            'type' => 'required|in:cash,salary_advance,petty_cash,travel,purchase',
            'purpose' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $advance->update($validated);

        return redirect()->route('hr.advances.show', $advance->id)
            ->with('success', __('hr::advance.updated_successfully'));
    }

    public function destroy($id)
    {
        $advance = Advance::findOrFail($id);
        $advance->delete();

        return redirect()->route('hr.advances.index')
            ->with('success', __('hr::advance.deleted_successfully'));
    }

    // ============================================
    // SETTLEMENTS SECTION
    // ============================================

    public function createSettlement(Request $request)
    {
        $employees = Employee::orderBy('first_name')->get();

        // Get advance if provided
        $advance = $request->advance_id ? Advance::findOrFail($request->advance_id) : null;

        // If advance is provided, filter for that employee
        $selectedEmployee = $advance ? $advance->employee : ($request->employee_id ? Employee::findOrFail($request->employee_id) : null);

        // Get available advances for selected employee (pending or partial)
        $availableAdvances = $selectedEmployee
            ? Advance::where('employee_id', $selectedEmployee->id)
            ->whereIn('status', ['pending', 'partial_settlement'])
            ->get()
            : collect();

        $formConfig = [
            'groups' => [
                [
                    'title' => __('hr::advance.settlement_details'),
                    'fields' => [
                        [
                            'name' => 'employee_id',
                            'label' => __('hr::employee.employee'),
                            'type' => 'select',
                            'grid' => 12,
                            'required' => true,
                            'value' => $selectedEmployee?->id,
                            'options' => $employees->map(fn($e) => [
                                'value' => $e->id,
                                'label' => $e->full_name . ' - ' . $e->department->name
                            ])->toArray()
                        ],
                        [
                            'name' => 'advance_id',
                            'label' => __('hr::advance.linked_advance') . ' (' . __('hr::advance.optional') . ')',
                            'type' => 'select',
                            'grid' => 12,
                            'value' => $advance?->id,
                            'options' => array_merge(
                                [['value' => '', 'label' => __('hr::advance.standalone_settlement')]],
                                $availableAdvances->map(fn($a) => [
                                    'value' => $a->id,
                                    'label' => $a->advance_code . ' - ' . number_format($a->outstanding_balance, 2) . ' ' . __('hr::advance.outstanding')
                                ])->toArray()
                            )
                        ],
                        [
                            'name' => 'cash_returned',
                            'label' => __('hr::advance.cash_returned'),
                            'type' => 'number',
                            'grid' => 6,
                            'value' => '0.00',
                            'placeholder' => '0.00'
                        ],
                        [
                            'name' => 'amount_spent',
                            'label' => __('hr::advance.amount_spent'),
                            'type' => 'number',
                            'grid' => 6,
                            'value' => '0.00',
                            'placeholder' => '0.00'
                        ],
                        [
                            'name' => 'settlement_date',
                            'label' => __('hr::advance.settlement_date'),
                            'type' => 'date',
                            'grid' => 6,
                            'required' => true,
                            'value' => now()->format('Y-m-d')
                        ],
                        [
                            'name' => 'receipt_file',
                            'label' => __('hr::advance.receipt_file'),
                            'type' => 'file',
                            'grid' => 6,
                            'accept' => '.pdf,.jpg,.jpeg,.png'
                        ],
                        [
                            'name' => 'settlement_notes',
                            'label' => __('hr::advance.settlement_notes'),
                            'type' => 'textarea',
                            'grid' => 12,
                            'rows' => 3
                        ],
                    ]
                ]
            ]
        ];

        return view('hr::advances.settlements.create', compact('formConfig', 'advance'));
    }

    public function storeSettlement(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'advance_id' => 'nullable|exists:employee_advances,id',
            'cash_returned' => 'required|numeric|min:0',
            'amount_spent' => 'required|numeric|min:0',
            'settlement_date' => 'required|date',
            'settlement_notes' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);


        // Validate that at least one amount is > 0
        if ($validated['cash_returned'] == 0 && $validated['amount_spent'] == 0) {
            return back()->withErrors(['cash_returned' => __('hr::advance.at_least_one_amount')])->withInput();
        }

        // Validate against advance amount if linked to an advance
        if ($validated['advance_id']) {
            $advance = Advance::findOrFail($validated['advance_id']);
            $outstandingBalance = $advance->outstanding_balance;

            // Check if cash_returned exceeds the advance amount
            if ($validated['cash_returned'] > $advance->amount) {
                return back()->withErrors(['cash_returned' => __('hr::advance.cash_returned_exceeds_advance')])->withInput();
            }

            // Check if amount_spent exceeds the advance amount
            if ($validated['amount_spent'] > $advance->amount) {
                return back()->withErrors(['amount_spent' => __('hr::advance.amount_spent_exceeds_advance')])->withInput();
            }

            // Check if total accounted exceeds the outstanding balance
            $totalAccounted = $validated['cash_returned'] + $validated['amount_spent'];
            if ($totalAccounted > $advance->amount) {
                return back()->withErrors([
                    'cash_returned' => __('hr::advance.total_exceeds_advance', ['amount' => number_format($advance->amount, 2)])
                ])->withInput();
            }
        }

        // Handle file upload
        if ($request->hasFile('receipt_file')) {
            $validated['receipt_file'] = $request->file('receipt_file')->store('settlements', 'public');
        }

        $validated['received_by'] = Auth::id();

        AdvanceSettlement::create($validated);

        if ($validated['advance_id']) {
            return redirect()->route('hr.advances.show', $validated['advance_id'])
                ->with('success', __('hr::advance.settlement_created_successfully'));
        }

        return redirect()->route('hr.employees.show', $validated['employee_id'])
            ->with('success', __('hr::advance.settlement_created_successfully'));
    }

    public function editSettlement($id)
    {
        $settlement = AdvanceSettlement::with('advance', 'employee')->findOrFail($id);
        $employees = Employee::orderBy('first_name')->get();

        // Get available advances for this employee
        $availableAdvances = Advance::where('employee_id', $settlement->employee_id)
            ->whereIn('status', ['pending', 'partial_settlement'])
            ->orWhere('id', $settlement->advance_id)
            ->get();

        $formConfig = [
            'groups' => [
                [
                    'title' => __('hr::advance.settlement_details'),
                    'fields' => [
                        [
                            'name' => 'employee_id',
                            'label' => __('hr::employee.employee'),
                            'type' => 'select',
                            'grid' => 12,
                            'required' => true,
                            'value' => $settlement->employee_id,
                            'options' => $employees->map(fn($e) => [
                                'value' => $e->id,
                                'label' => $e->full_name . ' - ' . $e->department->name
                            ])->toArray()
                        ],
                        [
                            'name' => 'advance_id',
                            'label' => __('hr::advance.linked_advance') . ' (' . __('hr::advance.optional') . ')',
                            'type' => 'select',
                            'grid' => 12,
                            'value' => $settlement->advance_id,
                            'options' => array_merge(
                                [['value' => '', 'label' => __('hr::advance.standalone_settlement')]],
                                $availableAdvances->map(fn($a) => [
                                    'value' => $a->id,
                                    'label' => $a->advance_code . ' - ' . number_format($a->outstanding_balance, 2) . ' ' . __('hr::advance.outstanding')
                                ])->toArray()
                            )
                        ],
                        [
                            'name' => 'cash_returned',
                            'label' => __('hr::advance.cash_returned'),
                            'type' => 'number',
                            'grid' => 6,
                            'value' => $settlement->cash_returned,
                            'placeholder' => '0.00'
                        ],
                        [
                            'name' => 'amount_spent',
                            'label' => __('hr::advance.amount_spent'),
                            'type' => 'number',
                            'grid' => 6,
                            'value' => $settlement->amount_spent,
                            'placeholder' => '0.00'
                        ],
                        [
                            'name' => 'settlement_date',
                            'label' => __('hr::advance.settlement_date'),
                            'type' => 'date',
                            'grid' => 6,
                            'required' => true,
                            'value' => $settlement->settlement_date->format('Y-m-d')
                        ],
                        [
                            'name' => 'receipt_file',
                            'label' => __('hr::advance.receipt_file'),
                            'type' => 'file',
                            'grid' => 6,
                            'accept' => '.pdf,.jpg,.jpeg,.png'
                        ],
                        [
                            'name' => 'settlement_notes',
                            'label' => __('hr::advance.settlement_notes'),
                            'type' => 'textarea',
                            'grid' => 12,
                            'value' => $settlement->settlement_notes,
                            'rows' => 3
                        ],
                    ]
                ]
            ]
        ];

        return view('hr::advances.settlements.edit', compact('formConfig', 'settlement'));
    }

    public function updateSettlement(Request $request, $id)
    {
        $settlement = AdvanceSettlement::findOrFail($id);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'advance_id' => 'nullable|exists:employee_advances,id',
            'cash_returned' => 'required|numeric|min:0',
            'amount_spent' => 'required|numeric|min:0',
            'settlement_date' => 'required|date',
            'settlement_notes' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Validate that at least one amount is > 0
        if ($validated['cash_returned'] == 0 && $validated['amount_spent'] == 0) {
            return back()->withErrors(['cash_returned' => __('hr::advance.at_least_one_amount')])->withInput();
        }

        // Validate against advance amount if linked to an advance
        if ($validated['advance_id']) {
            $advance = Advance::findOrFail($validated['advance_id']);

            // Calculate what was previously accounted (excluding current settlement being updated)
            $previouslyAccounted = $advance->settlements()
                ->where('id', '!=', $settlement->id)
                ->get()
                ->sum(function($s) {
                    return $s->cash_returned + $s->amount_spent;
                });

            // Check if cash_returned exceeds the advance amount
            if ($validated['cash_returned'] > $advance->amount) {
                return back()->withErrors(['cash_returned' => __('hr::advance.cash_returned_exceeds_advance')])->withInput();
            }

            // Check if amount_spent exceeds the advance amount
            if ($validated['amount_spent'] > $advance->amount) {
                return back()->withErrors(['amount_spent' => __('hr::advance.amount_spent_exceeds_advance')])->withInput();
            }

            // Check if total accounted (including this settlement) exceeds the advance amount
            $totalAccounted = $previouslyAccounted + $validated['cash_returned'] + $validated['amount_spent'];
            if ($totalAccounted > $advance->amount) {
                $availableAmount = $advance->amount - $previouslyAccounted;
                return back()->withErrors([
                    'cash_returned' => __('hr::advance.total_exceeds_advance', ['amount' => number_format($advance->amount, 2)])
                ])->withInput();
            }
        }

        // Handle file upload
        if ($request->hasFile('receipt_file')) {
            // Delete old file
            if ($settlement->receipt_file) {
                Storage::disk('public')->delete($settlement->receipt_file);
            }
            $validated['receipt_file'] = $request->file('receipt_file')->store('settlements', 'public');
        }

        $settlement->update($validated);

        if ($settlement->advance_id) {
            return redirect()->route('hr.advances.show', $settlement->advance_id)
                ->with('success', __('hr::advance.settlement_updated_successfully'));
        }

        return redirect()->route('hr.employees.show', $settlement->employee_id)
            ->with('success', __('hr::advance.settlement_updated_successfully'));
    }

    public function destroySettlement($id)
    {
        $settlement = AdvanceSettlement::findOrFail($id);
        $advanceId = $settlement->advance_id;
        $employeeId = $settlement->employee_id;

        // Delete file if exists
        if ($settlement->receipt_file) {
            Storage::disk('public')->delete($settlement->receipt_file);
        }

        $settlement->delete();

        if ($advanceId) {
            return redirect()->route('hr.advances.show', $advanceId)
                ->with('success', __('hr::advance.settlement_deleted_successfully'));
        }

        return redirect()->route('hr.employees.show', $employeeId)
            ->with('success', __('hr::advance.settlement_deleted_successfully'));
    }
    /**
     * Convert outstanding advance to deduction
     */
    public function convertToDeduction($id)
    {
        $advance = Advance::findOrFail($id);

        if ($advance->outstanding_balance <= 0) {
            return back()->with('error', __('hr::advance.no_outstanding_balance'));
        }

        if ($advance->hasDeduction()) {
            return back()->with('error', __('hr::advance.already_converted_to_deduction'));
        }

        $deduction = $advance->convertToDeduction();

        return redirect()->route('hr.advances.show', $advance->id)
            ->with('success', __('hr::advance.converted_to_deduction_success', [
                'amount' => number_format($deduction->amount, 2)
            ]));
    }

    /**
     * Add overpayment to salary (bonus)
     */
    public function addToSalary($id)
    {
        $advance = Advance::findOrFail($id);


        if (!$advance->hasOverpayment()) {
            return back()->with('error', __('hr::advance.no_overpayment'));
        }

        // Create a salary adjustment record (you might want to create a SalaryAdjustment model)
        // handle case where overpayment is alrady  added to salary as a bonus
        $isItAlreadyAdded = Deduction::where('advance_id', $advance->id)
            ->where('amount', -$advance->overpayment_amount)
            ->exists();

        if ($isItAlreadyAdded) {
            return back()->with('error', __('hr::advance.overpayment_already_added_to_salary'));
        }




        // For now, we'll create a negative deduction (bonus)
        $bonus = Deduction::create([
            'employee_id' => $advance->employee_id,
            'type' => 'amount',
            'amount' => -$advance->overpayment_amount, // Negative = addition
            'deduction_date' => now(),
            'advance_id' => $advance->id,
            'reason' => __('hr::advance.bonus_from_overpayment', ['code' => $advance->advance_code]),
            'notes' => __('hr::advance.added_to_salary'),
        ]);

        // Mark advance as settled
        $advance->update([
            'status' => 'settled',
            'actual_settlement_date' => now(),
        ]);

        return redirect()->route('hr.advances.show', $advance->id)
            ->with('success', __('hr::advance.added_to_salary_success', [
                'amount' => number_format($bonus->amount, 2)
            ]));
    }
}
