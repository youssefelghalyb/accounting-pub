<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReceiptVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'party_id' => 'required|exists:parties,id',
            'account_id' => 'required|exists:accounts,id',
            'sales_invoice_id' => 'nullable|exists:sales_invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'voucher_date' => 'required|date',
            'payment_method' => 'required|in:cash,cheque,bank_transfer,credit_card,other',
            'reference_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'party_id.required' => __('finance::receipt.party_required'),
            'account_id.required' => __('finance::receipt.account_required'),
            'amount.required' => __('finance::receipt.amount_required'),
            'amount.min' => __('finance::receipt.amount_min'),
        ];
    }
}