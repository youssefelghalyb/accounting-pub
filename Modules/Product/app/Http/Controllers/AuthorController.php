<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Product\Models\Author;
use Modules\Product\Http\Requests\StoreAuthorRequest;
use Modules\Product\Http\Requests\UpdateAuthorRequest;

class AuthorController extends Controller
{
    /**
     * Display a listing of authors.
     */
    public function index(Request $request)
    {
        $query = Author::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nationality', 'like', "%{$search}%")
                  ->orWhere('occupation', 'like', "%{$search}%");
            });
        }

        $authors = $query->orderBy('full_name')->get();

        // Statistics
        $stats = [
            'total_authors' => Author::count(),
            'total_contracts' => \Modules\Product\Models\Contract::count(),
            'total_contract_value' => \Modules\Product\Models\Contract::sum('contract_price'),
        ];

        return view('product::authors.index', compact('authors', 'stats'));
    }

    /**
     * Show the form for creating a new author.
     */
    public function create()
    {
        return view('product::authors.create');
    }

    /**
     * Store a newly created author.
     */
    public function store(StoreAuthorRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();

        // Handle file upload
        if ($request->hasFile('id_image')) {
            $validated['id_image'] = $request->file('id_image')->store('authors/ids', 'public');
        }

        Author::create($validated);

        return redirect()
            ->route('product.authors.index')
            ->with('success', __('product::author.author_added'));
    }

    /**
     * Display the specified author.
     */
    public function show($id)
    {
        $author = Author::with('books.product', 'contracts.book')->findOrFail($id);

        // Get payment history
        $paymentHistory = $author->getAllTransactions();

        // Calculate stats
        $stats = [
            'total_books' => $author->books()->count(),
            'total_contracts' => $author->contracts()->count(),
            'total_contract_value' => $author->total_contract_value,
            'total_paid' => $author->total_paid,
            'outstanding_balance' => $author->outstanding_balance,
        ];

        return view('product::authors.show', compact('author', 'paymentHistory', 'stats'));
    }

    /**
     * Show the form for editing the specified author.
     */
    public function edit($id)
    {
        $author = Author::findOrFail($id);

        return view('product::authors.edit', compact('author'));
    }

    /**
     * Update the specified author.
     */
    public function update(UpdateAuthorRequest $request, $id)
    {
        $author = Author::findOrFail($id);
        $validated = $request->validated();
        $validated['edited_by'] = Auth::id();

        // Handle file upload
        if ($request->hasFile('id_image')) {
            // Delete old file
            if ($author->id_image) {
                Storage::disk('public')->delete($author->id_image);
            }
            $validated['id_image'] = $request->file('id_image')->store('authors/ids', 'public');
        }

        $author->update($validated);

        return redirect()
            ->route('product.authors.index')
            ->with('success', __('product::author.author_updated'));
    }

    /**
     * Remove the specified author.
     */
    public function destroy($id)
    {
        $author = Author::findOrFail($id);

        // Check if author has books
        if ($author->books()->count() > 0) {
            return redirect()
                ->route('product.authors.index')
                ->with('error', __('product::author.cannot_delete_has_books'));
        }

        // Delete ID image if exists
        if ($author->id_image) {
            Storage::disk('public')->delete($author->id_image);
        }

        $author->delete();

        return redirect()
            ->route('product.authors.index')
            ->with('success', __('product::author.author_deleted'));
    }
}
