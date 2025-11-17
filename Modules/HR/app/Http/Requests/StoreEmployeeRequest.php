<?php

namespace Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'hire_date' => 'required|date',
            'salary' => 'required|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'position' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => __('hr::employee.first_name_required'),
            'last_name.required' => __('hr::employee.last_name_required'),
            'email.required' => __('hr::employee.email_required'),
            'email.email' => __('hr::employee.email_invalid'),
            'email.unique' => __('hr::employee.email_unique'),
            'hire_date.required' => __('hr::employee.hire_date_required'),
            'salary.required' => __('hr::employee.salary_required'),
            'salary.min' => __('hr::employee.salary_positive'),
            'department_id.required' => __('hr::employee.department_required'),
        ];
    }
}
