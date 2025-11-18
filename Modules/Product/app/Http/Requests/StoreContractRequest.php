<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
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
            'author_id' => 'required|exists:authors,id',
            'book_id' => 'required|exists:books,id',
            'contract_date' => 'required|date',
            'contract_price' => 'required|numeric|min:0',
            'percentage_from_book_profit' => 'required|numeric|min:0|max:100',
            'contract_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'author_id.required' => __('product::contract.author_required'),
            'author_id.exists' => __('product::contract.author_invalid'),
            'book_id.required' => __('product::contract.book_required'),
            'book_id.exists' => __('product::contract.book_invalid'),
            'contract_date.required' => __('product::contract.contract_date_required'),
            'contract_price.required' => __('product::contract.contract_price_required'),
            'contract_price.min' => __('product::contract.contract_price_positive'),
            'percentage_from_book_profit.required' => __('product::contract.percentage_required'),
            'percentage_from_book_profit.min' => __('product::contract.percentage_min'),
            'percentage_from_book_profit.max' => __('product::contract.percentage_max'),
            'contract_file.mimes' => __('product::contract.contract_file_invalid'),
            'contract_file.max' => __('product::contract.contract_file_max_size'),
        ];
    }
}
