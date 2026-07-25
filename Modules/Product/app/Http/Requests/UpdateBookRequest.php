<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
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
        $bookId = $this->route('id');

        // Get the book to find product_id
        $book = \Modules\Product\Models\Book::find($bookId);
        $productId = $book ? $book->product_id : null;

        return [
            // Product fields
            'name' => 'required|string|max:255',
            'type' => 'required|in:book,ebook,journal,course,bundle',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $productId,
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',

            // Book fields
            'authors' => 'nullable|string|max:500',
            'supervisor' => 'nullable|string|max:255',
            'introduction_by' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:book_categories,id',
            'sub_category_id' => 'nullable|exists:book_categories,id',
            'isbn' => 'required|string|max:50|unique:books,isbn,' . $bookId,
            'num_of_pages' => 'nullable|integer|min:1',
            'cover_type' => 'required|in:hard,soft',
            'published_at' => 'nullable|date',
            'language' => 'nullable|string|max:100',
            'is_translated' => 'boolean',
            'translated_from' => 'nullable|string|max:100',
            'translated_to' => 'nullable|string|max:100',
            'translator_name' => 'nullable|string|max:255',

            // Contractor / royalty (optional — a book need not have a contractor on file)
            'contractor_id' => 'nullable|exists:contractors,id',
            'profit_percentage' => 'required_with:contractor_id|nullable|numeric|min:0|max:100',
            'percentage_basis' => 'nullable|in:base_price,sale_price',
            'contract_date' => 'nullable|date',
            'end_contract_date' => 'nullable|date|after_or_equal:contract_date',
            'contract_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:8192',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('product::book.name_required'),
            'isbn.required' => __('product::book.isbn_required'),
            'isbn.unique' => __('product::book.isbn_unique'),
            'category_id.exists' => __('product::book.category_invalid'),
            'sub_category_id.exists' => __('product::book.sub_category_invalid'),
            'cover_type.required' => __('product::book.cover_type_required'),
            'base_price.required' => __('product::book.base_price_required'),
            'base_price.min' => __('product::book.base_price_positive'),
        ];
    }
}
