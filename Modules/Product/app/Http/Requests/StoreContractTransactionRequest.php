<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contractor_book_id' => 'required|exists:contractor_books,id',
            'type' => 'required|in:publishing_fee,royalty_payment,advance_payment,refund,adjustment',
            'direction' => 'required|in:receipt,payment',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'payment_method' => 'nullable|in:cash,cheque,bank_transfer,credit_card,other',
            'notes' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ];
    }
}
