<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseInvoiceRequest extends FormRequest
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
            'party_id' => ['required', 'exists:parties,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            
            // Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
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
        ];
    }
}