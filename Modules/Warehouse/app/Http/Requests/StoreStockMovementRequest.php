<?php

namespace Modules\Warehouse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockMovementRequest extends FormRequest
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
            'movements' => 'required|array|min:1',
            'movements.*.product_id' => 'required|exists:products,id',
            'movements.*.from_sub_warehouse_id' => 'nullable|exists:sub_warehouses,id',
            'movements.*.to_sub_warehouse_id' => 'nullable|exists:sub_warehouses,id',
            'movements.*.quantity' => 'required|integer|min:1',
            'movements.*.movement_type' => 'required|in:transfer,inbound,outbound',
            'movements.*.reason' => 'nullable|string|max:255',
            'movements.*.reference_id' => 'nullable|integer',
            'movements.*.notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'movements.required' => __('warehouse::stock_movement.movements_required'),
            'movements.array' => __('warehouse::stock_movement.movements_invalid'),
            'movements.min' => __('warehouse::stock_movement.movements_min'),
            'movements.*.product_id.required' => __('warehouse::stock_movement.product_required'),
            'movements.*.product_id.exists' => __('warehouse::stock_movement.product_not_found'),
            'movements.*.from_sub_warehouse_id.exists' => __('warehouse::stock_movement.from_warehouse_not_found'),
            'movements.*.to_sub_warehouse_id.exists' => __('warehouse::stock_movement.to_warehouse_not_found'),
            'movements.*.quantity.required' => __('warehouse::stock_movement.quantity_required'),
            'movements.*.quantity.min' => __('warehouse::stock_movement.quantity_min'),
            'movements.*.movement_type.required' => __('warehouse::stock_movement.type_required'),
            'movements.*.movement_type.in' => __('warehouse::stock_movement.type_invalid'),
        ];
    }
}
