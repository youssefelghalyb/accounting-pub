<?php

namespace Modules\HR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HR\Models\Department;
use Modules\HR\Models\Employee;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index()
    {
        $departments = Department::withCount('employees')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate statistics
        $stats = [
            'total_departments' => Department::count(),
            'total_employees' => Employee::count(),
            'total_salary_cost' => Employee::sum('salary'),
        ];

        return view('hr::departments.index', compact('departments', 'stats'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {
        return view('hr::departments.create');
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
        ], [
            'name.required' => __('hr::department.name_required'),
            'name.unique' => __('hr::department.name_unique'),
            'color.required' => __('hr::department.color_required'),
        ]);

        Department::create($validated);

        return redirect()
            ->route('hr.departments.index')
            ->with('success', __('hr::department.department_added'));
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department)
    {
        $department->load('employees');
        
        $stats = [
            'total_employees' => $department->employees->count(),
            'total_salary_cost' => $department->employees->sum('salary'),
            'average_salary' => $department->employees->avg('salary'),
        ];

        return view('hr::departments.show', compact('department', 'stats'));
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department)
    {
        dd($department);
        return view('hr::departments.edit', compact('department'));
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
        ], [
            'name.required' => __('hr::department.name_required'),
            'name.unique' => __('hr::department.name_unique'),
            'color.required' => __('hr::department.color_required'),
        ]);

        $department->update($validated);

        return redirect()
            ->route('hr.departments.index')
            ->with('success', __('hr::department.department_updated'));
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department)
    {
        // Check if department has employees
        if ($department->employees()->count() > 0) {
            return redirect()
                ->route('hr.departments.index')
                ->with('error', __('hr::department.cannot_delete'));
        }

        $department->delete();

        return redirect()
            ->route('hr.departments.index')
            ->with('success', __('hr::department.department_deleted'));
    }
}