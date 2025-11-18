<?php

namespace Modules\Warehouse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubWarehouseRequest extends FormRequest
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
            'warehouse_id' => 'required|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:main,branch,book_fair,temporary,other',
            'address' => 'nullable|string',
            'country' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'warehouse_id.required' => __('warehouse::sub_warehouse.warehouse_required'),
            'warehouse_id.exists' => __('warehouse::sub_warehouse.warehouse_not_found'),
            'name.required' => __('warehouse::sub_warehouse.name_required'),
            'name.max' => __('warehouse::sub_warehouse.name_max'),
            'type.required' => __('warehouse::sub_warehouse.type_required'),
            'type.in' => __('warehouse::sub_warehouse.type_invalid'),
            'country.max' => __('warehouse::sub_warehouse.country_max'),
        ];
    }
}
