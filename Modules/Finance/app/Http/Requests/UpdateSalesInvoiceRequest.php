<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'party_id' => 'required|exists:parties,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'payment_terms' => 'nullable|string|max:255',
            'is_taxable' => 'boolean',
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',

            'sub_warehouse_id' => 'required|exists:sub_warehouses,id',
            
            // Items
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ];
    }

    protected function prepareForValidation()
    {
        // Ensure is_taxable is boolean
        if (!$this->has('is_taxable')) {
            $this->merge(['is_taxable' => false]);
        }

        // Set default tax rate from organization settings if taxable and not provided
        if ($this->is_taxable && !$this->has('tax_rate')) {
            $orgSettings = \Modules\Settings\Models\OrganizationSetting::first();
            $this->merge([
                'tax_rate' => $orgSettings->tax_rate ?? 0
            ]);
        }
    }
}