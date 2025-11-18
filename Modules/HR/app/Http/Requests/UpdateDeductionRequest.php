<?php

namespace Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeductionRequest extends FormRequest
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
            'type' => 'required|in:days,amount,unpaid_leave',
            'days' => 'nullable|integer|min:1',
            'amount' => 'nullable|numeric|min:0',
            'deduction_date' => 'required|date',
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
            'employee_id.required' => __('hr::deductions.validation.employee_required'),
            'type.required' => __('hr::deductions.validation.type_required'),
            'days.min' => __('hr::deductions.validation.days_invalid'),
            'amount.min' => __('hr::deductions.validation.amount_invalid'),
            'deduction_date.required' => __('hr::deductions.validation.date_required'),
            'reason.required' => __('hr::deductions.validation.reason_required'),
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $type = $this->input('type');
            $days = $this->input('days');
            $amount = $this->input('amount');

            // Validate based on type
            if (in_array($type, ['days', 'unpaid_leave']) && empty($days)) {
                $validator->errors()->add('days', __('hr::deductions.validation.days_required_for_type'));
            }

            if ($type === 'amount' && empty($amount)) {
                $validator->errors()->add('amount', __('hr::deductions.validation.amount_required_for_type'));
            }
        });
    }
}
