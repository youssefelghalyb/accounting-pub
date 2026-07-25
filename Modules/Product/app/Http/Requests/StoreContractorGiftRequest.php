<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractorGiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sub_warehouse_id' => 'required|exists:sub_warehouses,id',
            'lines' => 'required|array|min:1',
            'lines.*.contractor_book_id' => 'required|exists:contractor_books,id',
            'lines.*.quantity' => 'required|integer|min:1',
        ];
    }
}
