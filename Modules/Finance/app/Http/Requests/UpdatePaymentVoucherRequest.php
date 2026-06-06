<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentVoucherRequest extends FormRequest
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
            'account_id' => ['required', 'exists:accounts,id'],
            'purchase_invoice_id' => ['nullable', 'exists:purchase_invoices,id'],
            'voucher_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,cheque,bank_transfer,credit_card,other'],
            'cheque_number' => ['nullable', 'required_if:payment_method,cheque', 'string', 'max:255'],
            'cheque_date' => ['nullable', 'required_if:payment_method,cheque', 'date'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'party_id' => __('finance::payment.party'),
            'account_id' => __('finance::payment.account'),
            'purchase_invoice_id' => __('finance::payment.invoice'),
            'voucher_date' => __('finance::payment.voucher_date'),
            'amount' => __('finance::payment.amount'),
            'payment_method' => __('finance::payment.payment_method'),
            'cheque_number' => __('finance::payment.cheque_number'),
            'cheque_date' => __('finance::payment.cheque_date'),
            'transaction_reference' => __('finance::payment.transaction_reference'),
            'description' => __('finance::payment.description'),
        ];
    }
}