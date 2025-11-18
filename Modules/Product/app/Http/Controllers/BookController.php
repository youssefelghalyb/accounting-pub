<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Models\Book;
use Modules\Product\Models\Product;
use Modules\Product\Models\Author;
use Modules\Product\Models\BookCategory;
use Modules\Product\Http\Requests\StoreBookRequest;
use Modules\Product\Http\Requests\UpdateBookRequest;

class BookController extends Controller
{
    /**
     * Display a listing of books.
     */
    public function index(Request $request)
    {
        $query = Book::with('product', 'author', 'category', 'subCategory');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('isbn', 'like', "%{$search}%")
                  ->orWhereHas('product', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('author', function($aq) use ($search) {
                      $aq->where('full_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by author
        if ($request->filled('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $books = $query->orderBy('created_at', 'desc')->get();
        $authors = Author::all();
        $categories = BookCategory::whereNull('parent_id')->get();

        // Statistics
        $stats = [
            'total_books' => Book::count(),
            'total_pages' => Book::sum('num_of_pages'),
            'translated_books' => Book::where('is_translated', true)->count(),
        ];

        return view('product::books.index', compact('books', 'authors', 'categories', 'stats'));
    }

    /**
     * Show the form for creating a new book.
     */
    public function create()
    {
        $authors = Author::all();
        $categories = BookCategory::whereNull('parent_id')->get();
        $subCategories = BookCategory::whereNotNull('parent_id')->get();

        return view('product::books.create', compact('authors', 'categories', 'subCategories'));
    }

    /**
     * Store a newly created book.
     */
    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();

        // Create product first
        $product = Product::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'sku' => $validated['sku'] ?? null,
            'description' => $validated['description'] ?? null,
            'base_price' => $validated['base_price'],
            'status' => $validated['status'],
            'created_by' => auth()->id(),
        ]);

        // Create book with product_id
        Book::create([
            'product_id' => $product->id,
            'author_id' => $validated['author_id'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'sub_category_id' => $validated['sub_category_id'] ?? null,
            'isbn' => $validated['isbn'],
            'num_of_pages' => $validated['num_of_pages'] ?? null,
            'cover_type' => $validated['cover_type'],
            'published_at' => $validated['published_at'] ?? null,
            'language' => $validated['language'] ?? null,
            'is_translated' => $validated['is_translated'] ?? false,
            'translated_from' => $validated['translated_from'] ?? null,
            'translated_to' => $validated['translated_to'] ?? null,
            'translator_name' => $validated['translator_name'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('product.books.index')
            ->with('success', __('product::book.book_added'));
    }

    /**
     * Display the specified book.
     */
    public function show($id)
    {
        $book = Book::with('product', 'author', 'category', 'subCategory', 'contracts.transactions')
            ->findOrFail($id);

        // Get contract statistics
        $contractStats = [
            'total_contracts' => $book->contracts()->count(),
            'total_contract_value' => $book->contracts()->sum('contract_price'),
            'total_paid' => $book->contracts()->get()->sum('total_paid'),
        ];

        return view('product::books.show', compact('book', 'contractStats'));
    }

    /**
     * Show the form for editing the specified book.
     */
    public function edit($id)
    {
        $book = Book::with('product')->findOrFail($id);
        $authors = Author::all();
        $categories = BookCategory::whereNull('parent_id')->get();
        $subCategories = BookCategory::whereNotNull('parent_id')->get();

        return view('product::books.edit', compact('book', 'authors', 'categories', 'subCategories'));
    }

    /**
     * Update the specified book.
     */
    public function update(UpdateBookRequest $request, $id)
    {
        $book = Book::with('product')->findOrFail($id);
        $validated = $request->validated();

        // Update product
        $book->product->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'sku' => $validated['sku'] ?? null,
            'description' => $validated['description'] ?? null,
            'base_price' => $validated['base_price'],
            'status' => $validated['status'],
            'edited_by' => auth()->id(),
        ]);

        // Update book
        $book->update([
            'author_id' => $validated['author_id'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'sub_category_id' => $validated['sub_category_id'] ?? null,
            'isbn' => $validated['isbn'],
            'num_of_pages' => $validated['num_of_pages'] ?? null,
            'cover_type' => $validated['cover_type'],
            'published_at' => $validated['published_at'] ?? null,
            'language' => $validated['language'] ?? null,
            'is_translated' => $validated['is_translated'] ?? false,
            'translated_from' => $validated['translated_from'] ?? null,
            'translated_to' => $validated['translated_to'] ?? null,
            'translator_name' => $validated['translator_name'] ?? null,
            'edited_by' => auth()->id(),
        ]);

        return redirect()
            ->route('product.books.index')
            ->with('success', __('product::book.book_updated'));
    }

    /**
     * Remove the specified book.
     */
    public function destroy($id)
    {
        $book = Book::with('product')->findOrFail($id);

        // Check if book has contracts
        if ($book->contracts()->count() > 0) {
            return redirect()
                ->route('product.books.index')
                ->with('error', __('product::book.cannot_delete_has_contracts'));
        }

        // Delete the product (which will cascade delete the book)
        $book->product->delete();

        return redirect()
            ->route('product.books.index')
            ->with('success', __('product::book.book_deleted'));
    }
}
