<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuthorRequest extends FormRequest
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
            'full_name' => 'required|string|max:255',
            'nationality' => 'nullable|string|max:150',
            'country_of_residence' => 'nullable|string|max:150',
            'bio' => 'nullable|string',
            'occupation' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'id_image' => 'nullable|image|max:2048',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'full_name.required' => __('product::author.full_name_required'),
            'email.email' => __('product::author.email_invalid'),
            'id_image.image' => __('product::author.id_image_invalid'),
            'id_image.max' => __('product::author.id_image_max_size'),
        ];
    }
}
