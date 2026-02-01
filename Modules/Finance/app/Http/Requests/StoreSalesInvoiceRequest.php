<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesInvoiceRequest extends FormRequest
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
            // 'tax_rate' => 'nullable|numeric|min:0|max:100',
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

            // Payment
            'paid_amount' => 'nullable|numeric|min:0',
            'account_id' => 'nullable|exists:accounts,id',
        ];
    }

    protected function prepareForValidation()
    {
        // If paid_amount is 0 or "0" consider it as not provided
        if ($this->has('paid_amount') && (float) $this->input('paid_amount') == 0) {
            $this->merge([
                'paid_amount' => null,
                'account_id'  => null
            ]);
        }

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
