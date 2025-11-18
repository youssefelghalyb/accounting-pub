<?php

namespace Modules\Warehouse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockRequest extends FormRequest
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
            'warehouse_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'reserved_quantity' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'minimum_quantity' => 'required|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => __('warehouse::stocks.product_required'),
            'product_id.exists' => __('warehouse::stocks.product_not_found'),
            'warehouse_name.required' => __('warehouse::stocks.warehouse_name_required'),
            'warehouse_name.max' => __('warehouse::stocks.warehouse_name_max'),
            'quantity.required' => __('warehouse::stocks.quantity_required'),
            'quantity.integer' => __('warehouse::stocks.quantity_integer'),
            'quantity.min' => __('warehouse::stocks.quantity_min'),
            'status.required' => __('warehouse::stocks.status_required'),
            'status.in' => __('warehouse::stocks.status_invalid'),
            'minimum_quantity.required' => __('warehouse::stocks.minimum_quantity_required'),
            'minimum_quantity.integer' => __('warehouse::stocks.minimum_quantity_integer'),
            'minimum_quantity.min' => __('warehouse::stocks.minimum_quantity_min'),
        ];
    }
}
