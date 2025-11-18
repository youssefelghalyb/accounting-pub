<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Models\BookCategory;
use Modules\Product\Http\Requests\StoreCategoryRequest;
use Modules\Product\Http\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        $query = BookCategory::with('parent', 'children');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by parent/child
        if ($request->filled('filter')) {
            if ($request->filter === 'parent') {
                $query->whereNull('parent_id');
            } elseif ($request->filter === 'child') {
                $query->whereNotNull('parent_id');
            }
        }

        $categories = $query->orderBy('parent_id')->orderBy('name')->get();

        // Statistics
        $stats = [
            'total_categories' => BookCategory::count(),
            'parent_categories' => BookCategory::whereNull('parent_id')->count(),
            'sub_categories' => BookCategory::whereNotNull('parent_id')->count(),
        ];

        return view('product::categories.index', compact('categories', 'stats'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $parentCategories = BookCategory::whereNull('parent_id')->get();

        return view('product::categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();

        BookCategory::create($validated);

        return redirect()
            ->route('product.categories.index')
            ->with('success', __('product::category.category_added'));
    }

    /**
     * Display the specified category.
     */
    public function show($id)
    {
        $category = BookCategory::with('parent', 'children', 'books')->findOrFail($id);

        return view('product::categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit($id)
    {
        $category = BookCategory::findOrFail($id);
        $parentCategories = BookCategory::whereNull('parent_id')
            ->where('id', '!=', $id)
            ->get();

        return view('product::categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, $id)
    {
        $category = BookCategory::findOrFail($id);
        $validated = $request->validated();
        $validated['edited_by'] = auth()->id();

        $category->update($validated);

        return redirect()
            ->route('product.categories.index')
            ->with('success', __('product::category.category_updated'));
    }

    /**
     * Remove the specified category.
     */
    public function destroy($id)
    {
        $category = BookCategory::findOrFail($id);

        // Check if category has children or books
        if ($category->children()->count() > 0) {
            return redirect()
                ->route('product.categories.index')
                ->with('error', __('product::category.cannot_delete_has_children'));
        }

        if ($category->books()->count() > 0) {
            return redirect()
                ->route('product.categories.index')
                ->with('error', __('product::category.cannot_delete_has_books'));
        }

        $category->delete();

        return redirect()
            ->route('product.categories.index')
            ->with('success', __('product::category.category_deleted'));
    }
}
