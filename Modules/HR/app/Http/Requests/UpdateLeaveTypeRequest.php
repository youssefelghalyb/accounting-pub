<?php

namespace Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveTypeRequest extends FormRequest
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
        $leaveTypeId = $this->route('leaveType')
            ? $this->route('leaveType')->id
            : $this->route('id');

        return [
            'name' => 'required|string|max:255|unique:leave_types,name,' . $leaveTypeId,
            'description' => 'nullable|string',
            'max_days_per_year' => 'nullable|integer|min:1',
            'is_paid' => 'required|boolean',
            'color' => 'required|string',
        ];
    }
}
