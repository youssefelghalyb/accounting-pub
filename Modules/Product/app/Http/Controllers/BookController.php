<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Product\Exports\BooksExport;
use Modules\Product\Models\Author;
use Modules\Product\Models\Book;
use Modules\Product\Models\BookCategory;
use Modules\Product\Models\Product;
use Modules\Product\Http\Requests\StoreBookRequest;
use Modules\Product\Http\Requests\UpdateBookRequest;
use Modules\Product\Imports\BooksImport;

class BookController extends Controller
{
    public function index(Request $request)
    {
        // Books now load authors through their contract
        $query = Book::with('product', 'contract.authors', 'category', 'subCategory');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('isbn', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('contract.authors', function ($aq) use ($search) {
                        $aq->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by author — now via contract pivot
        if ($request->filled('author_id')) {
            $query->whereHas('contract.authors', function ($q) use ($request) {
                $q->where('authors.id', $request->author_id);
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $books      = $query->orderBy('created_at', 'desc')->paginate(15);
        $authors    = Author::orderBy('full_name')->get();
        $categories = BookCategory::whereNull('parent_id')->get();

        $stats = [
            'total_books'      => Book::count(),
            'total_pages'      => Book::sum('num_of_pages'),
            'translated_books' => Book::where('is_translated', true)->count(),
        ];

        return view('product::books.index', compact('books', 'authors', 'categories', 'stats'));
    }

    public function create()
    {
        $categories    = BookCategory::whereNull('parent_id')->get();
        $subCategories = BookCategory::whereNotNull('parent_id')->get();

        return view('product::books.create', compact('categories', 'subCategories'));
    }

    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();

        $product = Product::create([
            'name'        => $validated['name'],
            'type'        => $validated['type'],
            'sku'         => $validated['sku'] ?? null,
            'description' => $validated['description'] ?? null,
            'base_price'  => $validated['base_price'],
            'status'      => $validated['status'],
            'created_by'  => Auth::id(),
        ]);

        Book::create([
            'product_id'      => $product->id,
            'category_id'     => $validated['category_id'] ?? null,
            'sub_category_id' => $validated['sub_category_id'] ?? null,
            'isbn'            => $validated['isbn'],
            'num_of_pages'    => $validated['num_of_pages'] ?? null,
            'cover_type'      => $validated['cover_type'],
            'published_at'    => $validated['published_at'] ?? null,
            'language'        => $validated['language'] ?? null,
            'is_translated'   => $validated['is_translated'] ?? false,
            'translated_from' => $validated['translated_from'] ?? null,
            'translated_to'   => $validated['translated_to'] ?? null,
            'translator_name' => $validated['translator_name'] ?? null,
            'created_by'      => Auth::id(),
        ]);

        return redirect()
            ->route('product.books.index')
            ->with('success', __('product::book.book_added'));
    }

    public function show(Request $request, $id)
    {
        $book = Book::with('product', 'contract.authors', 'contract.transactions', 'category', 'subCategory')
            ->findOrFail($id);

        $contract      = $book->contract;
        $contractStats = $contract ? [
            'contract_price'      => $contract->contract_price,
            'total_paid'          => $contract->total_paid,
            'outstanding_balance' => $contract->outstanding_balance,
            'payment_status'      => $contract->payment_status,
        ] : null;

        // ── Sales rows ────────────────────────────────────────────────────────────
        // line_total = unit_price * qty - item_discount  (already net of item discount)
        // si.discount_amount = invoice-level discount (not split per item — shown separately)
        $salesItems = \Illuminate\Support\Facades\DB::table('sales_invoice_items as sii')
            ->join('sales_invoices as si', function ($join) {
                $join->on('si.id', '=', 'sii.sales_invoice_id')
                    ->whereNull('si.deleted_at');
            })
            ->join('parties as p', 'p.id', '=', 'si.party_id')
            ->where('sii.product_id', $book->product_id)
            ->select(
                'sii.id                  as item_id',
                'si.id                   as invoice_id',
                'si.invoice_number',
                'si.invoice_date',
                'si.status               as invoice_status',
                'si.discount_amount      as invoice_discount',   // header-level discount (informational)
                'p.id                    as customer_id',
                'p.name                  as customer_name',
                'p.phone                 as customer_phone',
                'sii.quantity',
                'sii.unit_price',
                'sii.line_total',                               // = qty * unit_price - item_discount
            )
            ->orderByDesc('si.invoice_date')
            ->orderByDesc('sii.id')
            ->paginate(15, ['*'], 'sales_page')
            ->withQueryString();

        // ── Aggregate totals ──────────────────────────────────────────────────────
        // total_revenue = SUM(line_total) — gross item totals, net of item-level discounts
        // Does NOT subtract invoice-level discounts (those belong to the whole invoice)
        $salesTotals = \Illuminate\Support\Facades\DB::table('sales_invoice_items as sii')
            ->join('sales_invoices as si', function ($join) {
                $join->on('si.id', '=', 'sii.sales_invoice_id')
                    ->whereNull('si.deleted_at');
            })
            ->where('sii.product_id', $book->product_id)
            ->selectRaw('
            COUNT(sii.id)        as total_orders,
            SUM(sii.quantity)    as total_qty,
            SUM(sii.line_total)  as total_revenue,
            AVG(sii.unit_price)  as avg_price,
            MIN(sii.unit_price)  as min_price,
            MAX(sii.unit_price)  as max_price
        ')
            ->first();

        return view('product::books.show', compact(
            'book',
            'contractStats',
            'salesItems',
            'salesTotals',
        ));
    }

    public function edit($id)
    {
        $book          = Book::with('product')->findOrFail($id);
        $categories    = BookCategory::whereNull('parent_id')->get();
        $subCategories = BookCategory::whereNotNull('parent_id')->get();

        return view('product::books.edit', compact('book', 'categories', 'subCategories'));
    }

    public function update(UpdateBookRequest $request, $id)
    {
        $book      = Book::with('product')->findOrFail($id);
        $validated = $request->validated();

        $book->product->update([
            'name'        => $validated['name'],
            'type'        => $validated['type'],
            'sku'         => $validated['sku'] ?? null,
            'description' => $validated['description'] ?? null,
            'base_price'  => $validated['base_price'],
            'status'      => $validated['status'],
            'edited_by'   => Auth::id(),
        ]);

        $book->update([
            'category_id'     => $validated['category_id'] ?? null,
            'sub_category_id' => $validated['sub_category_id'] ?? null,
            'isbn'            => $validated['isbn'],
            'num_of_pages'    => $validated['num_of_pages'] ?? null,
            'cover_type'      => $validated['cover_type'],
            'published_at'    => $validated['published_at'] ?? null,
            'language'        => $validated['language'] ?? null,
            'is_translated'   => $validated['is_translated'] ?? false,
            'translated_from' => $validated['translated_from'] ?? null,
            'translated_to'   => $validated['translated_to'] ?? null,
            'translator_name' => $validated['translator_name'] ?? null,
            'edited_by'       => Auth::id(),
        ]);

        return redirect()
            ->route('product.books.index')
            ->with('success', __('product::book.book_updated'));
    }

    public function destroy($id)
    {
        $book = Book::with('product')->findOrFail($id);

        if ($book->contract()->exists()) {
            return redirect()
                ->route('product.books.index')
                ->with('error', __('product::book.cannot_delete_has_contracts'));
        }

        $book->product->delete();

        return redirect()
            ->route('product.books.index')
            ->with('success', __('product::book.book_deleted'));
    }

    public function bulkPriceUpdate(Request $request)
    {
        $request->validate([
            'operation' => ['required', 'in:increment,decrement'],
            'amount'    => ['required', 'numeric', 'min:0.01', 'max:999999'],
            'type'      => ['required', 'in:fixed,percentage'],
        ]);

        $operation = $request->operation;
        $amount    = (float) $request->amount;
        $type      = $request->type;

        return response()->stream(function () use ($operation, $amount, $type) {
            while (ob_get_level() > 0) ob_end_clean();

            $books   = Book::with('product')->orderBy('id')->get();
            $total   = $books->count();
            $updated = 0;
            $errors  = [];

            foreach ($books as $index => $book) {
                try {
                    if (! $book->product) throw new \Exception('Product record missing');

                    $currentPrice = (float) $book->product->base_price;
                    $delta        = $type === 'percentage'
                        ? $currentPrice * ($amount / 100)
                        : $amount;
                    $newPrice = $operation === 'increment'
                        ? $currentPrice + $delta
                        : $currentPrice - $delta;

                    if ($newPrice < 0) throw new \Exception("New price would be negative ({$newPrice})");

                    $book->product->update(['base_price' => round($newPrice, 2), 'edited_by' => Auth::id()]);
                    $updated++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'book_id'   => $book->id,
                        'book_name' => $book->product?->name ?? "Book #{$book->id}",
                        'reason'    => $e->getMessage(),
                    ];
                }

                echo json_encode([
                    'type'      => 'progress',
                    'current'   => $index + 1,
                    'total'     => $total,
                    'percent'   => $total > 0 ? round((($index + 1) / $total) * 100) : 100,
                    'book_name' => $book->product?->name ?? "Book #{$book->id}",
                ]) . "\n";
                flush();
            }

            echo json_encode([
                'type'    => 'done',
                'total'   => $total,
                'updated' => $updated,
                'failed'  => count($errors),
                'errors'  => $errors,
            ]) . "\n";
            flush();
        }, 200, [
            'Content-Type'     => 'application/x-ndjson',
            'X-Accel-Buffering' => 'no',
            'Cache-Control'    => 'no-cache',
        ]);
    }


    public function exportBooks(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return (new BooksExport())->download();
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel' => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'],
        ]);

        $result = (new BooksImport())->import($request->file('excel'));

        $hasErrors = ! empty($result['errors']);

        return back()->with([
            'import_result' => $result,
            'status'        => $hasErrors ? 'warning' : 'success',
            'message'       => $hasErrors
                ? "Import completed with " . count($result['errors']) . " error(s)."
                : "Import completed successfully.",
        ]);
    }
}
