<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
        $categoryId = $this->route('id');

        return [
            'name' => 'required|string|max:255',
            'parent_id' => [
                'nullable',
                'exists:book_categories,id',
                function ($attribute, $value, $fail) use ($categoryId) {
                    // Prevent setting itself as parent
                    if ($value == $categoryId) {
                        $fail(__('product::category.cannot_be_own_parent'));
                    }
                },
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('product::category.name_required'),
            'parent_id.exists' => __('product::category.parent_invalid'),
        ];
    }
}
