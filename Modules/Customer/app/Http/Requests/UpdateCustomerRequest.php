<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
        $customerId = $this->route('customer');

        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:individual,company,online'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customerId)
            ],
            'address' => ['nullable', 'string'],
            'tax_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('customers', 'tax_number')->ignore($customerId)
            ],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => __('customer::customer.name'),
            'type' => __('customer::customer.type'),
            'phone' => __('customer::customer.phone'),
            'email' => __('customer::customer.email'),
            'address' => __('customer::customer.address'),
            'tax_number' => __('customer::customer.tax_number'),
            'is_active' => __('customer::customer.status'),
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('customer::customer.validation.name_required'),
            'type.required' => __('customer::customer.validation.type_required'),
            'type.in' => __('customer::customer.validation.type_invalid'),
            'email.email' => __('customer::customer.validation.email_invalid'),
            'email.unique' => __('customer::customer.validation.email_unique'),
            'tax_number.unique' => __('customer::customer.validation.tax_number_unique'),
        ];
    }
}