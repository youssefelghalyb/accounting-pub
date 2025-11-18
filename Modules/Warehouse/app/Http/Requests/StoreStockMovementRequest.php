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
            'reference_number' => 'required|string|max:255|unique:stock_movements,reference_number',
            'type' => 'required|in:in,out,transfer,adjustment',
            'movement_date' => 'required|date',
            'source_warehouse' => 'nullable|string|max:255',
            'destination_warehouse' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,completed,cancelled',

            // Items validation
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'reference_number.required' => __('warehouse::movements.reference_number_required'),
            'reference_number.unique' => __('warehouse::movements.reference_number_unique'),
            'type.required' => __('warehouse::movements.type_required'),
            'type.in' => __('warehouse::movements.type_invalid'),
            'movement_date.required' => __('warehouse::movements.movement_date_required'),
            'movement_date.date' => __('warehouse::movements.movement_date_invalid'),
            'status.required' => __('warehouse::movements.status_required'),
            'status.in' => __('warehouse::movements.status_invalid'),

            'items.required' => __('warehouse::movements.items_required'),
            'items.min' => __('warehouse::movements.items_min'),
            'items.*.product_id.required' => __('warehouse::movements.item_product_required'),
            'items.*.product_id.exists' => __('warehouse::movements.item_product_not_found'),
            'items.*.quantity.required' => __('warehouse::movements.item_quantity_required'),
            'items.*.quantity.integer' => __('warehouse::movements.item_quantity_integer'),
            'items.*.quantity.min' => __('warehouse::movements.item_quantity_min'),
        ];
    }
}
