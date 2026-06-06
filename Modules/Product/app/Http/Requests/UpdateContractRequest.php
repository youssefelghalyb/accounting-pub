<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'author_ids'                   => 'required|array|min:1',
            'author_ids.*'                 => 'required|integer|exists:authors,id',
            'representative_id'            => 'required|integer|in_array:author_ids.*',
            'book_name'                    => 'required|string|max:255',
            'book_id'                      => 'nullable|exists:books,id',
            'contract_date'                => 'required|date',
            'contract_price'               => 'required|numeric|min:0',
            'percentage_from_book_profit'  => 'required|numeric|min:0|max:100',
            'contract_file'                => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'author_ids.required'               => __('product::contract.author_required'),
            'author_ids.min'                     => __('product::contract.author_required'),
            'author_ids.*.exists'                => __('product::contract.author_invalid'),
            'representative_id.required'         => __('product::contract.representative_required'),
            'representative_id.in_array'         => __('product::contract.representative_must_be_author'),
            'book_id.exists'                     => __('product::contract.book_invalid'),
            'contract_date.required'             => __('product::contract.contract_date_required'),
            'contract_price.required'            => __('product::contract.contract_price_required'),
            'contract_price.min'                 => __('product::contract.contract_price_positive'),
            'percentage_from_book_profit.required' => __('product::contract.percentage_required'),
            'percentage_from_book_profit.min'    => __('product::contract.percentage_min'),
            'percentage_from_book_profit.max'    => __('product::contract.percentage_max'),
            'contract_file.mimes'                => __('product::contract.contract_file_invalid'),
            'contract_file.max'                  => __('product::contract.contract_file_max_size'),
        ];
    }
}