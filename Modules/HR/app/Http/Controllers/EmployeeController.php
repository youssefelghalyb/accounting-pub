<?php

namespace Modules\HR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HR\Models\Employee;
use Modules\HR\Models\Department;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index(Request $request)
    {
        $query = Employee::with('department');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $employees = $query->orderBy('created_at', 'desc')->get();
        $departments = Department::all();

        // Calculate statistics
        $stats = [
            'total_employees' => Employee::count(),
            'total_salary' => Employee::sum('salary'),
            'average_salary' => Employee::avg('salary'),
            'departments_count' => Department::count(),
        ];

        return view('hr::employees.index', compact('employees', 'departments', 'stats'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(Request $request)
    {
        $departments = Department::all();
        $selectedDepartment = $request->get('department');
        
        return view('hr::employees.create', compact('departments', 'selectedDepartment'));
    }

    /**
     * Store a newly created employee.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'hire_date' => 'required|date',
            'salary' => 'required|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'position' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ], [
            'first_name.required' => __('hr::employee.first_name_required'),
            'last_name.required' => __('hr::employee.last_name_required'),
            'email.required' => __('hr::employee.email_required'),
            'email.email' => __('hr::employee.email_invalid'),
            'email.unique' => __('hr::employee.email_unique'),
            'hire_date.required' => __('hr::employee.hire_date_required'),
            'salary.required' => __('hr::employee.salary_required'),
            'salary.min' => __('hr::employee.salary_positive'),
            'department_id.required' => __('hr::employee.department_required'),
        ]);

        // Auto-calculate daily rate if not provided
        if (!$validated['daily_rate']) {
            $validated['daily_rate'] = round($validated['salary'] / 30, 2);
        }
        $validated['employee_code'] = 'EMP' . str_pad((Employee::max('id') + 1), 5, '0', STR_PAD_LEFT);

        Employee::create($validated);

        return redirect()
            ->route('hr.employees.index')
            ->with('success', __('hr::employee.employee_added'));
    }

    /**
     * Display the specified employee.
     */
public function show($id)
{
    $employee = Employee::findOrFail($id);
    $employee->load(['department', 'leaves.leaveType', 'deductions', 'advances']);
    
    // Get current month/year
    $currentYear = now()->year;
    $currentMonth = now()->month;
    
    // Get all deductions including bonuses (negative amounts)
    $monthDeductions = $employee->deductions()
        ->whereYear('deduction_date', $currentYear)
        ->whereMonth('deduction_date', $currentMonth)
        ->get();
    
    // Separate deductions and bonuses
    $actualDeductions = $monthDeductions->where('amount', '>', 0)->sum('amount');
    $bonuses = abs($monthDeductions->where('amount', '<', 0)->sum('amount'));
    
    // Calculate salary breakdown for current month
    $salaryBreakdown = [
        'gross_salary' => $employee->salary,
        'deductions' => $monthDeductions->where('amount', '>', 0),
        'bonuses' => $monthDeductions->where('amount', '<', 0),
        'total_deductions' => $actualDeductions,
        'total_bonuses' => $bonuses,
        'net_salary' => $employee->salary - $actualDeductions + $bonuses,
    ];
    
    // Leave statistics
    $leaveStats = [
        'total_leaves' => $employee->leaves()->count(),
        'pending_leaves' => $employee->leaves()->where('status', 'pending')->count(),
        'approved_leaves' => $employee->leaves()->where('status', 'approved')->count(),
        'rejected_leaves' => $employee->leaves()->where('status', 'rejected')->count(),
    ];
    
    // Recent leaves
    $recentLeaves = $employee->leaves()
        ->with('leaveType')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    
    // Recent deductions (excluding bonuses for this view)
    $recentDeductions = $employee->deductions()
        ->where('amount', '>', 0)
        ->orderBy('deduction_date', 'desc')
        ->limit(5)
        ->get();

    return view('hr::employees.show', compact(
        'employee', 
        'salaryBreakdown', 
        'leaveStats', 
        'recentLeaves', 
        'recentDeductions'
    ));
}

    /**
     * Show the form for editing the specified employee.
     */
    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        $departments = Department::all();
        
        return view('hr::employees.edit', compact('employee', 'departments'));
    }

    /**
     * Update the specified employee.
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'hire_date' => 'required|date',
            'salary' => 'required|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'position' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ], [
            'first_name.required' => __('hr::employee.first_name_required'),
            'last_name.required' => __('hr::employee.last_name_required'),
            'email.required' => __('hr::employee.email_required'),
            'email.email' => __('hr::employee.email_invalid'),
            'email.unique' => __('hr::employee.email_unique'),
            'hire_date.required' => __('hr::employee.hire_date_required'),
            'salary.required' => __('hr::employee.salary_required'),
            'salary.min' => __('hr::employee.salary_positive'),
            'department_id.required' => __('hr::employee.department_required'),
        ]);

        // Auto-calculate daily rate if not provided
        if (!$validated['daily_rate']) {
            $validated['daily_rate'] = round($validated['salary'] / 30, 2);
        }

        $employee->update($validated);

        return redirect()
            ->route('hr.employees.index')
            ->with('success', __('hr::employee.employee_updated'));
    }

    /**
     * Remove the specified employee.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()
            ->route('hr.employees.index')
            ->with('success', __('hr::employee.employee_deleted'));
    }

    /**
     * Calculate salary for a specific month
     */
    public function calculateSalary(Request $request, Employee $employee)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        
        $breakdown = [
            'employee' => $employee,
            'year' => $year,
            'month' => $month,
            'gross_salary' => $employee->salary,
            'deductions' => $employee->deductions()
                ->whereYear('deduction_date', $year)
                ->whereMonth('deduction_date', $month)
                ->get(),
            'total_deductions' => $employee->deductions()
                ->whereYear('deduction_date', $year)
                ->whereMonth('deduction_date', $month)
                ->sum('amount'),
            'net_salary' => $employee->calculateNetSalary($year, $month),
        ];
        
        return view('hr::employees.salary', compact('breakdown'));
    }
}