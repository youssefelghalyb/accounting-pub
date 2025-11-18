<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
            'contract_id' => 'required|exists:author_book_contracts,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'contract_id.required' => __('product::transaction.contract_required'),
            'contract_id.exists' => __('product::transaction.contract_invalid'),
            'amount.required' => __('product::transaction.amount_required'),
            'amount.min' => __('product::transaction.amount_positive'),
            'payment_date.required' => __('product::transaction.payment_date_required'),
            'receipt_file.mimes' => __('product::transaction.receipt_file_invalid'),
            'receipt_file.max' => __('product::transaction.receipt_file_max_size'),
        ];
    }
}
