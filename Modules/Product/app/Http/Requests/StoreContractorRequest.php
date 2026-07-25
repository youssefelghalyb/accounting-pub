<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'secondary_phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'secondary_email' => 'nullable|email|max:255',
            'nationality' => 'nullable|string|max:150',
            'address' => 'nullable|string',
            'national_id_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('product::contractor.name_required'),
            'email.email' => __('product::contractor.email_invalid'),
        ];
    }
}
