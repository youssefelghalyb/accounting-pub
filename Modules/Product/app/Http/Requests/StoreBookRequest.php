<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
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
            // Product fields
            'name' => 'required|string|max:255',
            'type' => 'required|in:book,ebook,journal,course,bundle',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',

            // Book fields
            'author_id' => 'nullable|exists:authors,id',
            'category_id' => 'nullable|exists:book_categories,id',
            'sub_category_id' => 'nullable|exists:book_categories,id',
            'isbn' => 'required|string|max:50|unique:books,isbn',
            'num_of_pages' => 'nullable|integer|min:1',
            'cover_type' => 'required|in:hard,soft',
            'published_at' => 'nullable|date',
            'language' => 'nullable|string|max:100',
            'is_translated' => 'boolean',
            'translated_from' => 'nullable|string|max:100',
            'translated_to' => 'nullable|string|max:100',
            'translator_name' => 'nullable|string|max:255',
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
            'author_id.exists' => __('product::book.author_invalid'),
            'category_id.exists' => __('product::book.category_invalid'),
            'sub_category_id.exists' => __('product::book.sub_category_invalid'),
            'cover_type.required' => __('product::book.cover_type_required'),
            'base_price.required' => __('product::book.base_price_required'),
            'base_price.min' => __('product::book.base_price_positive'),
        ];
    }
}
