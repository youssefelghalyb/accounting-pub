<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePurchaseInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // If paid_amount is 0 or "0" consider it as not provided
        if ($this->has('paid_amount') && (float) $this->input('paid_amount') == 0) {
            $this->merge([
                'paid_amount' => null,
                'account_id'  => null
            ]);
        }

        // Set default tax rate from organization settings if taxable and not provided
        if ($this->is_taxable && !$this->has('tax_rate')) {
            $orgSettings = \Modules\Settings\Models\OrganizationSetting::first();
            $this->merge([
                'tax_rate' => $orgSettings->tax_rate ?? 0
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */

    public function rules(): array
    {
        return [
            'party_id' => ['required', 'exists:parties,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'is_taxable' => ['boolean'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'], // Keep nullable
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'manual_amount' => ['nullable', 'numeric', 'min:0'],

            // Items - required only if no manual amount
            'items' => ['nullable', 'array', 'required_without:manual_amount'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],

            // Optional payment
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'account_id' => ['nullable', 'required_with:paid_amount', 'exists:accounts,id'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'party_id' => __('finance::invoice.party'),
            'invoice_date' => __('finance::invoice.invoice_date'),
            'due_date' => __('finance::invoice.due_date'),
            'reference_number' => __('finance::invoice.reference_number'),
            'tax_rate' => __('finance::invoice.tax_rate'),
            'discount_amount' => __('finance::invoice.discount_amount'),
            'notes' => __('finance::invoice.notes'),
            'items' => __('finance::invoice.items'),
            'items.*.product_id' => __('finance::invoice.product'),
            'items.*.quantity' => __('finance::invoice.quantity'),
            'items.*.unit_price' => __('finance::invoice.unit_price'),
            'paid_amount' => __('finance::invoice.paid_amount'),
            'account_id' => __('finance::invoice.account'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // dd($validator->errors()->toArray()); // 🔥 Dumps validation errors

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => __('Validation failed'),
            'errors'  => $validator->errors(),
        ], 422));
    }
}
