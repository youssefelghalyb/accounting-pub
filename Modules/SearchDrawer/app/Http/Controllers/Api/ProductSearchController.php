<?php

namespace Modules\SearchDrawer\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Product;

class ProductSearchController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['book.author', 'book.category', 'book.subCategory'])
            ->where('status', 'active');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhereHas('book', function($bq) use ($search) {
                      $bq->where('isbn', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('book', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Filter by sub-category
        if ($request->filled('sub_category_id')) {
            $query->whereHas('book', function($q) use ($request) {
                $q->where('sub_category_id', $request->sub_category_id);
            });
        }

        // Filter by author
        if ($request->filled('author_id')) {
            $query->whereHas('book', function($q) use ($request) {
                $q->where('author_id', $request->author_id);
            });
        }

        // Paginate
        $perPage = $request->get('per_page', 20);
        $products = $query->paginate($perPage);

        // Transform data
        $products->getCollection()->transform(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'isbn' => $p->book->isbn ?? null,
                'sku' => $p->sku,
                'price' => $p->base_price,
                'stock_quantity' => $p->stock_quantity,
                'category_id' => $p->book->category_id ?? null,
                'category_name' => $p->book->category->name ?? null,
                'sub_category_id' => $p->book->sub_category_id ?? null,
                'sub_category_name' => $p->book->subCategory->name ?? null,
                'author_id' => $p->book->author_id ?? null,
                'author_name' => $p->book->author->name ?? null,
                'highlight_value' => number_format($p->base_price, 2),
                'highlight_class' => 'text-blue-600',
            ];
        });

        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::with(['book.author', 'book.category', 'book.subCategory'])
            ->findOrFail($id);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'isbn' => $product->book->isbn ?? null,
            'sku' => $product->sku,
            'price' => $product->base_price,
            'stock_quantity' => $product->stock_quantity,
            'category_id' => $product->book->category_id ?? null,
            'category_name' => $product->book->category->name ?? null,
            'author_id' => $product->book->author_id ?? null,
            'author_name' => $product->book->author->name ?? null,
        ]);
    }
}