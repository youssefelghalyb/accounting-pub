<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Product\Services\BookGridException;
use Modules\Product\Services\BookGridService;

/**
 * History of everything saved from the Books grid, and "Restore" (revert a saved batch).
 * Restoring goes through the same strict plan → confirm → apply pipeline as a normal grid save.
 */
class BookGridHistoryController extends Controller
{
    private const TTL = 1800;

    public function __construct(private BookGridService $grid) {}

    public function index(Request $request): \Illuminate\Contracts\View\View|JsonResponse
    {
        $q = DB::table('book_grid_batches as b')->leftJoin('users as u', 'u.id', '=', 'b.user_id')
            ->select('b.*', 'u.name as user_name')->orderByDesc('b.id');

        if ($term = trim((string) $request->query('book'))) {
            $like = '%' . $term . '%';
            $bookIds = DB::table('books as bk')->join('products as p', 'p.id', '=', 'bk.product_id')
                ->where(fn ($w) => $w->where('p.name', 'like', $like)->orWhere('bk.isbn', 'like', $like)->orWhere('bk.id', $term))
                ->limit(500)->pluck('bk.id');
            $q->whereIn('b.batch_uuid', DB::table('book_grid_audits')->select('batch_uuid')
                ->where(fn ($w) => $w->whereIn('book_id', $bookIds)->orWhere('snapshot', 'like', $like)->orWhere('new_value', 'like', $like)->orWhere('old_value', 'like', $like)));
        }

        if ($request->boolean('json')) {
            return response()->json($q->paginate(20)->withQueryString());
        }

        return view('product::books.grid-history', [
            'config' => [
                'listUrl' => route('product.book-grid.history', ['json' => 1]),
                'showUrl' => url('product/book-grid/history/__ID__'),
                'previewUrl' => url('product/book-grid/history/__ID__/restore-preview'),
                'applyUrl' => route('product.book-grid.history.restore-apply'),
                'i18n' => [
                    'ui' => __('product::book_grid.ui'),
                    'h' => __('product::book_grid.history'),
                    'col' => __('product::book_grid.col'),
                ],
            ],
        ]);
    }

    /** Everything one batch did, grouped for display */
    public function show(string $batch): JsonResponse
    {
        $meta = DB::table('book_grid_batches as b')->leftJoin('users as u', 'u.id', '=', 'b.user_id')
            ->where('b.batch_uuid', $batch)->select('b.*', 'u.name as user_name')->first();
        abort_unless($meta, 404);

        $audits = DB::table('book_grid_audits')->where('batch_uuid', $batch)->orderBy('id')->get();
        $names = DB::table('books as bk')->join('products as p', 'p.id', '=', 'bk.product_id')
            ->whereIn('bk.id', $audits->pluck('book_id')->filter()->unique())->pluck('p.name', 'bk.id');

        $changed = $added = $deleted = $created = $contractors = [];
        foreach ($audits as $a) {
            $snap = $a->snapshot ? (json_decode($a->snapshot, true) ?: []) : [];
            switch ($a->action) {
                case 'change':
                    $changed[$a->book_id]['name'] = $names[$a->book_id] ?? ('#' . $a->book_id);
                    $changed[$a->book_id]['book_id'] = (int) $a->book_id;
                    $changed[$a->book_id]['diff'][] = ['field' => $a->field, 'old' => $a->old_value, 'new' => $a->new_value];
                    break;
                case 'add':
                    $added[] = ['book_id' => (int) $a->book_id, 'name' => $snap['name'] ?? ($names[$a->book_id] ?? ''), 'isbn' => $snap['isbn'] ?? null, 'quantity' => $snap['quantity'] ?? 0, 'exists' => isset($names[$a->book_id])];
                    break;
                case 'delete':
                    $deleted[] = ['book_id' => (int) $a->book_id, 'name' => $snap['name'] ?? '', 'isbn' => $snap['isbn'] ?? null, 'stock' => $snap['qty_total'] ?? ($snap['quantity'] ?? 0)];
                    break;
                case 'category_create':
                    $created[] = $a->new_value;
                    break;
                case 'contractor_create':
                    $contractors[] = $a->new_value;
                    break;
            }
        }

        return response()->json([
            'batch' => $meta, 'added' => $added, 'changed' => array_values($changed), 'deleted' => $deleted,
            'created_categories' => $created, 'created_contractors' => $contractors,
        ]);
    }

    public function restorePreview(Request $request, string $batch): JsonResponse
    {
        try {
            $built = $this->grid->restoreBuild($batch, (bool) $request->boolean('skip_conflicts'));
        } catch (\RuntimeException $e) {
            return response()->json(['ok' => false, 'fatal' => $e->getMessage()], 422);
        }

        $token = Str::random(40);
        $blocked = ! empty($built['conflicts']) && ! $request->boolean('skip_conflicts'); // conflicts must be resolved or explicitly skipped
        Cache::put($this->key($token), ['payload' => $built['payload'], 'restore_of' => $batch, 'blocked' => $blocked], self::TTL);

        return response()->json($this->grid->publicPlan($built['plan']) + [
            'token' => $token, 'conflicts' => $built['conflicts'], 'notes' => $built['notes'],
        ]);
    }

    public function restoreApply(Request $request): JsonResponse
    {
        $request->validate(['token' => ['required', 'string', 'size:40'], 'ack_stock' => ['nullable', 'boolean'], 'confirm_delete_count' => ['nullable', 'integer', 'min:0']]);
        $stored = Cache::get($this->key($request->input('token')));
        if (! $stored) {
            return response()->json(['ok' => false, 'expired' => true, 'message' => __('product::book_grid.ui.expired')], 410);
        }

        if (! empty($stored['blocked'])) {
            return response()->json(['ok' => false, 'fatal' => __('product::book_grid.history.blocked_by_conflicts')], 422);
        }

        try {
            $result = $this->grid->apply($stored['payload'], Auth::id(), (bool) $request->boolean('ack_stock'),
                $request->filled('confirm_delete_count') ? (int) $request->input('confirm_delete_count') : null, $stored['restore_of']);
        } catch (BookGridException $e) {
            return response()->json($e->plan + ['ok' => false, 'fatal' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['ok' => false, 'fatal' => $e->getMessage()], 500);
        }
        Cache::forget($this->key($request->input('token')));

        return response()->json($result + ['ok' => true]);
    }

    private function key(string $token): string
    {
        return 'book-grid-restore:' . Auth::id() . ':' . $token;
    }
}
