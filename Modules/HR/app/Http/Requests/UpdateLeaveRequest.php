<?php

namespace Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveRequest extends FormRequest
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
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => __('hr::leaves.validation.employee_required'),
            'leave_type_id.required' => __('hr::leaves.validation.type_required'),
            'start_date.required' => __('hr::leaves.validation.start_date_required'),
            'end_date.required' => __('hr::leaves.validation.end_date_required'),
            'end_date.after_or_equal' => __('hr::leaves.validation.end_date_after_start'),
            'reason.required' => __('hr::leaves.validation.reason_required'),
        ];
    }
}
