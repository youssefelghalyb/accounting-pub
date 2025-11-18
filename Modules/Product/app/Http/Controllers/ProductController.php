<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Product\Models\Product;
use Modules\Product\Http\Requests\StoreProductRequest;
use Modules\Product\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->orderBy('created_at', 'desc')->get();

        // Calculate statistics
        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('status', 'active')->count(),
            'total_value' => Product::sum('base_price'),
        ];

        return view('product::products.index', compact('products', 'stats'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('product::products.create');
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();

        Product::create($validated);

        return redirect()
            ->route('product.products.index')
            ->with('success', __('product::product.product_added'));
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        $product = Product::with('book.author', 'book.category')->findOrFail($id);

        return view('product::products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);

        return view('product::products.edit', compact('product'));
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $validated = $request->validated();
        $validated['edited_by'] = Auth::id();

        $product->update($validated);

        return redirect()
            ->route('product.products.index')
            ->with('success', __('product::product.product_updated'));
    }

    /**
     * Remove the specified product.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()
            ->route('product.products.index')
            ->with('success', __('product::product.product_deleted'));
    }
}