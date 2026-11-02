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
 * Spreadsheet-style editor for books (see BookGridService for the safety rules).
 * Three steps: load snapshot → preview (server re-validates + computes the diff, nothing written)
 * → apply (server re-runs the same plan in a transaction using the payload it stored at preview time).
 */
class BookGridController extends Controller
{
    private const PREVIEW_TTL = 1800; // seconds

    public function __construct(private BookGridService $grid) {}

    public function index()
    {
        return view('product::books.grid', [
            'config' => [
                'dataUrl' => route('product.book-grid.data'),
                'previewUrl' => route('product.book-grid.preview'),
                'applyUrl' => route('product.book-grid.apply'),
                'userKey' => (string) Auth::id(),
                'dir' => \App\Helpers\LocaleHelper::getDirection(),
                'i18n' => [
                    'ui' => __('product::book_grid.ui'),
                    'groups' => __('product::book_grid.groups'),
                    'opt' => __('product::book_grid.opt'),
                ],
            ],
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $warehouseId = (int) $request->query('warehouse_id') ?: $this->defaultWarehouse();

        return response()->json($this->grid->snapshot($warehouseId));
    }

    public function preview(Request $request): JsonResponse
    {
        $payload = $this->validated($request);
        $plan = $this->grid->plan($payload);

        $token = Str::random(40);
        Cache::put($this->cacheKey($token), $payload, self::PREVIEW_TTL);

        return response()->json($this->grid->publicPlan($plan) + ['token' => $token]);
    }

    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'size:40'],
            'ack_stock' => ['nullable', 'boolean'],
            'confirm_delete_count' => ['nullable', 'integer', 'min:0'],
        ]);

        $payload = Cache::get($this->cacheKey($request->input('token')));
        if (! $payload) {
            return response()->json(['ok' => false, 'expired' => true, 'message' => __('product::book_grid.ui.expired')], 410);
        }

        try {
            $result = $this->grid->apply(
                $payload,
                Auth::id(),
                (bool) $request->boolean('ack_stock'),
                $request->filled('confirm_delete_count') ? (int) $request->input('confirm_delete_count') : null,
            );
        } catch (BookGridException $e) {
            return response()->json($e->plan + ['ok' => false, 'fatal' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['ok' => false, 'fatal' => $e->getMessage()], 500);
        }

        Cache::forget($this->cacheKey($request->input('token')));

        return response()->json($result + ['ok' => true]);
    }

    // ─────────────────────────────────────────────────────────────────────────────────────────

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'warehouse_id' => ['nullable', 'integer'],
            'rows' => ['nullable', 'array', 'max:3000'],
            'rows.*.key' => ['nullable', 'string', 'max:40'],
            'rows.*.book_id' => ['nullable', 'integer'],
            'rows.*.v' => ['nullable', 'string', 'max:64'],
            'rows.*.q0' => ['nullable', 'integer'],
            'rows.*.fields' => ['required', 'array'],
            'deleted' => ['nullable', 'array', 'max:3000'],
            'deleted.*' => ['integer'],
        ]);
        $data['rows'] = $data['rows'] ?? [];
        $data['deleted'] = $data['deleted'] ?? [];
        // fields are validated field-by-field by BookGridService (strict, per-cell messages)
        foreach ($data['rows'] as $i => $row) {
            $data['rows'][$i]['fields'] = $request->input("rows.$i.fields", []);
        }

        return $data;
    }

    private function cacheKey(string $token): string
    {
        return 'book-grid:' . Auth::id() . ':' . $token; // a token only works for the user who created it
    }

    private function defaultWarehouse(): int
    {
        return (int) (DB::table('sub_warehouses')->where('type', 'main')->orderBy('id')->value('id')
            ?? DB::table('sub_warehouses')->orderBy('id')->value('id') ?? 0);
    }
}
