<?php

namespace Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
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
        $departmentId = $this->route('department')
            ? $this->route('department')->id
            : $this->route('id');

        return [
            'name' => 'required|string|max:255|unique:departments,name,' . $departmentId,
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('hr::department.name_required'),
            'name.unique' => __('hr::department.name_unique'),
            'color.required' => __('hr::department.color_required'),
        ];
    }
}
