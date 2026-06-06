<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Product\Exports\AuthorsFinancialExport;
use Modules\Product\Http\Requests\StoreAuthorRequest;
use Modules\Product\Http\Requests\UpdateAuthorRequest;
use Modules\Product\Models\Author;
use Modules\Product\Models\Contract;
use Modules\Product\Services\AuthorService;

class AuthorController extends Controller
{
    public function __construct(private AuthorService $authorService) {}

    public function index(Request $request)
    {
        $query = Author::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nationality', 'like', "%{$search}%")
                    ->orWhere('occupation', 'like', "%{$search}%");
            });
        }

        $authors = $query->orderBy('full_name')
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        $stats = [
            'total_authors'        => Author::count(),
            'total_contracts'      => Contract::count(),
            'total_contract_value' => Contract::sum('contract_price'),
        ];

        return view('product::authors.index', compact('authors', 'stats'));
    }

    public function create()
    {
        return view('product::authors.create');
    }

    public function store(StoreAuthorRequest $request)
    {
        $validated               = $request->validated();
        $validated['created_by'] = Auth::id();

        $this->authorService->createAuthor($validated, $request->file('id_image'));

        return redirect()
            ->route('product.authors.index')
            ->with('success', __('product::author.author_added'));
    }

    public function show($id)
    {
        $author = Author::findOrFail($id);

        $stats        = $this->authorService->getAuthorStats($author);
        $transactions = $this->authorService->getAuthorTransactions($author);

        // Eager-load contracts with their book/product for the view
        $author->load('contracts.book.product');

        return view('product::authors.show', compact('author', 'stats', 'transactions'));
    }

    public function edit($id)
    {
        $author = Author::findOrFail($id);
        return view('product::authors.edit', compact('author'));
    }

    public function update(UpdateAuthorRequest $request, $id)
    {
        $author                  = Author::findOrFail($id);
        $validated               = $request->validated();
        $validated['edited_by']  = Auth::id();

        $this->authorService->updateAuthor($author, $validated, $request->file('id_image'));

        return redirect()
            ->route('product.authors.index')
            ->with('success', __('product::author.author_updated'));
    }

    public function destroy($id)
    {
        $author = Author::findOrFail($id);

        try {
            $this->authorService->deleteAuthor($author);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('product.authors.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('product.authors.index')
            ->with('success', __('product::author.author_deleted'));
    }

    public function search(Request $request)
    {
        $query = Author::query();

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nationality', 'like', "%{$search}%");
            });
        }

        $authors = $query->orderBy('full_name')->paginate(10);

        return response()->json([
            'results' => $authors->map(fn($a) => [
                'id'   => $a->id,
                'text' => $a->full_name . ($a->nationality ? ' - ' . $a->nationality : ''),
            ]),
            'pagination' => ['more' => $authors->hasMorePages()],
        ]);
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'full_name'   => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
        ]);

        $validated['created_by'] = Auth::id();

        $author = $this->authorService->createAuthor($validated);

        return response()->json([
            'success' => true,
            'message' => __('product::author.author_added'),
            'author'  => [
                'id'   => $author->id,
                'text' => $author->full_name . ($author->nationality ? ' - ' . $author->nationality : ''),
            ],
        ]);
    }

    public function registerAsClient(Author $author): \Illuminate\Http\JsonResponse
    {
        if ($author->party_id) {
            return response()->json([
                'success'     => true,
                'party_id'    => $author->party_id,
                'invoice_url' => route('finance.sales-invoices.create', ['party_id' => $author->party_id]),
                'already_client' => true,
            ]);
        }

        DB::transaction(function () use ($author) {
            $party = \Modules\Finance\Models\Party::create([
                'name'       => $author->full_name,
                'type'       => 'individual',
                'email'      => $author->email,
                'phone'      => $author->phone_number,
                'status'     => 'active',
                'created_by' => Auth::id(),
            ]);

            $author->update(['party_id' => $party->id]);
        });

        $author->refresh();

        return response()->json([
            'success'        => true,
            'party_id'       => $author->party_id,
            'invoice_url'    => route('finance.sales-invoices.create', ['party_id' => $author->party_id]),
            'already_client' => false,
        ]);
    }


    public function exportFinancial(): \Symfony\Component\HttpFoundation\StreamedResponse
{
    return (new AuthorsFinancialExport())->download();
}
}
