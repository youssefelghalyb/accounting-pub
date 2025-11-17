<?php

namespace Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdvanceRequest extends FormRequest
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
            'amount' => 'required|numeric|min:0.01',
            'issue_date' => 'required|date',
            'expected_settlement_date' => 'nullable|date|after:issue_date',
            'type' => 'required|in:cash,salary_advance,petty_cash,travel,purchase',
            'purpose' => 'required|string',
            'notes' => 'nullable|string',
        ];
    }
}
