<?php

namespace Modules\Warehouse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockMovementRequest extends FormRequest
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
            'product_id' => 'required|exists:products,id',
            'from_sub_warehouse_id' => 'nullable|exists:sub_warehouses,id',
            'to_sub_warehouse_id' => 'nullable|exists:sub_warehouses,id',
            'quantity' => 'required|integer|min:1',
            'movement_type' => 'required|in:transfer,inbound,outbound',
            'reason' => 'nullable|string|max:255',
            'reference_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => __('warehouse::stock_movement.product_required'),
            'product_id.exists' => __('warehouse::stock_movement.product_not_found'),
            'from_sub_warehouse_id.exists' => __('warehouse::stock_movement.from_warehouse_not_found'),
            'to_sub_warehouse_id.exists' => __('warehouse::stock_movement.to_warehouse_not_found'),
            'quantity.required' => __('warehouse::stock_movement.quantity_required'),
            'quantity.min' => __('warehouse::stock_movement.quantity_min'),
            'movement_type.required' => __('warehouse::stock_movement.type_required'),
            'movement_type.in' => __('warehouse::stock_movement.type_invalid'),
        ];
    }
}
