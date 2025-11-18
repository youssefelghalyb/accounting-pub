<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'type' => 'required|in:book,ebook,journal,course,bundle',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('product::product.name_required'),
            'type.required' => __('product::product.type_required'),
            'type.in' => __('product::product.type_invalid'),
            'sku.unique' => __('product::product.sku_unique'),
            'base_price.required' => __('product::product.base_price_required'),
            'base_price.min' => __('product::product.base_price_positive'),
            'status.required' => __('product::product.status_required'),
        ];
    }
}
