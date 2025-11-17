<?php

namespace Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\HR\Models\Advance;

class StoreSettlementRequest extends FormRequest
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
            'advance_id' => 'nullable|exists:employee_advances,id',
            'cash_returned' => 'required|numeric|min:0',
            'amount_spent' => 'required|numeric|min:0',
            'settlement_date' => 'required|date',
            'settlement_notes' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $cashReturned = $this->input('cash_returned', 0);
            $amountSpent = $this->input('amount_spent', 0);

            // Validate that at least one amount is > 0
            if ($cashReturned == 0 && $amountSpent == 0) {
                $validator->errors()->add('cash_returned', __('hr::advance.at_least_one_amount'));
            }

            // Validate against advance amount if linked to an advance
            if ($this->input('advance_id')) {
                $advance = Advance::find($this->input('advance_id'));

                if ($advance) {
                    // Check if cash_returned exceeds the advance amount
                    if ($cashReturned > $advance->amount) {
                        $validator->errors()->add('cash_returned', __('hr::advance.cash_returned_exceeds_advance'));
                    }

                    // Check if amount_spent exceeds the advance amount
                    if ($amountSpent > $advance->amount) {
                        $validator->errors()->add('amount_spent', __('hr::advance.amount_spent_exceeds_advance'));
                    }

                    // Check if total accounted exceeds the outstanding balance
                    $totalAccounted = $cashReturned + $amountSpent;
                    if ($totalAccounted > $advance->amount) {
                        $validator->errors()->add(
                            'cash_returned',
                            __('hr::advance.total_exceeds_advance', ['amount' => number_format($advance->amount, 2)])
                        );
                    }
                }
            }
        });
    }
}
