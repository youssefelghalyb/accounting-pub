<?php

namespace Modules\Product\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Backend of the Books grid editor (spreadsheet-style editing of books, their product data,
 * categories, contractors/contracts and stock).
 *
 * The grid is the "source of truth" screen, so the rules are strict:
 *   - nothing is written by the browser's word: preview() re-validates and re-computes the diff
 *     from the DB, apply() runs the SAME computation again inside a transaction with row locks;
 *   - every row carries a version hash; if somebody else changed the row meanwhile → conflict, nothing saved;
 *   - deleting is refused for books used by sales/purchase invoices, royalty accruals or contract
 *     payments (set them inactive instead); a book that only has stock needs an explicit stock-destroy ack;
 *   - SKU of a product that appears on an invoice and the contractor of a contract that carries money are locked;
 *   - invoices/payments/royalty fingerprint is checked before COMMIT (same guard as product:sync-books);
 *   - every applied change is written to book_grid_audits (batch uuid, user, old → new, snapshot of deleted rows).
 */
class BookGridService
{
    public const DEFAULT_PERCENTAGE = 10.0;

    /** editable field => [table group, type] */
    public const FIELDS = [
        'name' => 'text', 'isbn' => 'text', 'base_price' => 'number', 'status' => 'select', 'quantity' => 'int',
        'authors' => 'text', 'contractor' => 'combo', 'nationality' => 'text', 'profit_percentage' => 'number',
        'percentage_basis' => 'select', 'contract_status' => 'select', 'gift_copies_given' => 'int',
        'category' => 'combo', 'sub_category' => 'combo', 'cover_type' => 'select', 'num_of_pages' => 'int',
        'published_at' => 'date', 'language' => 'text', 'is_translated' => 'bool', 'translated_from' => 'text',
        'translated_to' => 'text', 'translator_name' => 'text', 'supervisor' => 'text', 'introduction_by' => 'text',
        'deposit_number' => 'text', 'deposit_year' => 'int', 'deposit_status' => 'text', 'sku' => 'text',
        'description' => 'longtext',
    ];

    private const PRODUCT_COLS = ['name', 'base_price', 'status', 'description', 'sku'];
    private const BOOK_COLS = ['isbn', 'num_of_pages', 'cover_type', 'published_at', 'language', 'is_translated',
        'translated_from', 'translated_to', 'translator_name', 'supervisor', 'introduction_by', 'authors',
        'deposit_number', 'deposit_year', 'deposit_status', 'contract_status'];
    private const CONTRACT_COLS = ['profit_percentage', 'percentage_basis', 'gift_copies_given'];

    private BooksSyncService $names;
    private BooksPruneService $prune;

    public function __construct(?BooksSyncService $names = null, ?BooksPruneService $prune = null)
    {
        $this->names = $names ?? new BooksSyncService();
        $this->prune = $prune ?? new BooksPruneService();
    }

    // ─── Snapshot (what the grid loads) ────────────────────────────────────────────────────────

    public function columns(): array
    {
        $c = fn (string $key, string $type, array $extra = []) => array_merge(['key' => $key, 'type' => $type, 'label' => __('product::book_grid.col.' . $key)], $extra);

        return [
            $c('book_id', 'readonly', ['width' => 80, 'readonly' => true]),
            $c('name', 'text', ['width' => 340, 'required' => true, 'sticky' => true]),
            $c('isbn', 'text', ['width' => 150, 'required' => true]),
            $c('base_price', 'number', ['width' => 100, 'required' => true]),
            $c('status', 'select', ['width' => 100, 'options' => ['active', 'inactive']]),
            $c('quantity', 'int', ['width' => 150, 'group' => 'stock']),
            $c('qty_total', 'readonly', ['width' => 100, 'readonly' => true, 'group' => 'stock']),
            $c('authors', 'text', ['width' => 220]),
            $c('contractor', 'combo', ['width' => 220, 'group' => 'contract', 'list' => 'contractors']),
            $c('nationality', 'text', ['width' => 120, 'group' => 'contract']),
            $c('profit_percentage', 'number', ['width' => 90, 'group' => 'contract']),
            $c('percentage_basis', 'select', ['width' => 120, 'group' => 'contract', 'options' => ['sale_price', 'base_price']]),
            $c('contract_status', 'select', ['width' => 110, 'group' => 'contract', 'options' => ['active', 'ended']]),
            $c('gift_copies_given', 'int', ['width' => 100, 'group' => 'contract']),
            $c('category', 'combo', ['width' => 150, 'list' => 'categories']),
            $c('sub_category', 'combo', ['width' => 150, 'list' => 'sub_categories']),
            $c('cover_type', 'select', ['width' => 90, 'options' => ['soft', 'hard'], 'group' => 'details']),
            $c('num_of_pages', 'int', ['width' => 80, 'group' => 'details']),
            $c('published_at', 'date', ['width' => 110, 'group' => 'details']),
            $c('language', 'text', ['width' => 80, 'group' => 'details']),
            $c('is_translated', 'bool', ['width' => 90, 'group' => 'translation']),
            $c('translated_from', 'text', ['width' => 110, 'group' => 'translation']),
            $c('translated_to', 'text', ['width' => 110, 'group' => 'translation']),
            $c('translator_name', 'text', ['width' => 160, 'group' => 'translation']),
            $c('supervisor', 'text', ['width' => 160, 'group' => 'details']),
            $c('introduction_by', 'text', ['width' => 160, 'group' => 'details']),
            $c('deposit_number', 'text', ['width' => 110, 'group' => 'deposit']),
            $c('deposit_year', 'int', ['width' => 90, 'group' => 'deposit']),
            $c('deposit_status', 'text', ['width' => 100, 'group' => 'deposit']),
            $c('sku', 'text', ['width' => 190, 'group' => 'system']),
            $c('description', 'longtext', ['width' => 260, 'group' => 'details']),
            $c('sales_lines', 'readonly', ['width' => 90, 'readonly' => true, 'group' => 'system']),
            $c('product_id', 'readonly', ['width' => 90, 'readonly' => true, 'group' => 'system']),
        ];
    }

    public function snapshot(int $warehouseId): array
    {
        $rows = $this->loadBooks(null, $warehouseId);

        return [
            'columns' => $this->columns(),
            'rows' => array_values($rows),
            'warehouse_id' => $warehouseId,
            'warehouses' => DB::table('sub_warehouses')->orderBy('id')->get(['id', 'name', 'type'])->all(),
            'lists' => [
                'categories' => DB::table('book_categories')->whereNull('parent_id')->orderBy('name')->pluck('name')->all(),
                'sub_categories' => DB::table('book_categories as s')->join('book_categories as p', 'p.id', '=', 's.parent_id')
                    ->orderBy('s.name')->get(['s.name', 'p.name as parent'])->all(),
                'contractors' => DB::table('contractors')->whereNull('deleted_at')->orderBy('name')->get(['name', 'nationality'])->all(),
            ],
        ];
    }

    /** @return array<int,array> keyed by book_id */
    private function loadBooks(?array $ids, int $warehouseId, bool $lock = false): array
    {
        $q = DB::table('books as b')
            ->join('products as p', 'p.id', '=', 'b.product_id')
            ->leftJoin('book_categories as c', 'c.id', '=', 'b.category_id')
            ->leftJoin('book_categories as sc', 'sc.id', '=', 'b.sub_category_id')
            ->leftJoin('contractor_books as cb', function ($j) {
                $j->on('cb.book_id', '=', 'b.id')->whereNull('cb.deleted_at');
            })
            ->leftJoin('contractors as k', 'k.id', '=', 'cb.contractor_id')
            ->select([
                'b.id as book_id', 'p.id as product_id', 'p.name', 'p.sku', 'p.base_price', 'p.status', 'p.description',
                'b.isbn', 'b.num_of_pages', 'b.cover_type', 'b.published_at', 'b.language', 'b.is_translated',
                'b.translated_from', 'b.translated_to', 'b.translator_name', 'b.supervisor', 'b.introduction_by',
                'b.authors', 'b.deposit_number', 'b.deposit_year', 'b.deposit_status', 'b.contract_status',
                'c.name as category', 'sc.name as sub_category',
                'cb.id as cb_id', 'k.id as contractor_id', 'k.name as contractor', 'k.nationality',
                'cb.profit_percentage', 'cb.percentage_basis', 'cb.gift_copies_given',
                'b.updated_at as b_upd', 'p.updated_at as p_upd', 'cb.updated_at as cb_upd',
            ])
            ->orderBy('b.id');
        if ($ids !== null) {
            $q->whereIn('b.id', $ids ?: [0]);
        }
        if ($lock) {
            $q->lockForUpdate();
        }
        $books = $q->get();

        $pids = $books->pluck('product_id')->all();
        $bids = $books->pluck('book_id')->all();
        $cbIds = $books->pluck('cb_id')->filter()->all();

        $stockWh = DB::table('sub_warehouse_products')->where('sub_warehouse_id', $warehouseId)
            ->whereIn('product_id', $pids ?: [0])->pluck('quantity', 'product_id');
        $stockAll = DB::table('sub_warehouse_products')->whereIn('product_id', $pids ?: [0])
            ->groupBy('product_id')->selectRaw('product_id, SUM(quantity) as q')->pluck('q', 'product_id');
        $sales = DB::table('sales_invoice_items')->whereIn('product_id', $pids ?: [0])
            ->groupBy('product_id')->selectRaw('product_id, COUNT(*) as n')->pluck('n', 'product_id');
        $purch = DB::table('purchase_invoice_items')->whereIn('product_id', $pids ?: [0])
            ->groupBy('product_id')->selectRaw('product_id, COUNT(*) as n')->pluck('n', 'product_id');
        $roy = DB::table('book_sales')->whereIn('book_id', $bids ?: [0])
            ->groupBy('book_id')->selectRaw('book_id, COUNT(*) as n, SUM(contractor_profit) as profit')->get()->keyBy('book_id');
        $tx = DB::table('contract_transactions')->whereIn('contractor_book_id', $cbIds ?: [0])
            ->groupBy('contractor_book_id')->selectRaw('contractor_book_id, COUNT(*) as n')->pluck('n', 'contractor_book_id');

        $out = [];
        foreach ($books as $b) {
            $royRow = $roy[$b->book_id] ?? null;
            $hasMoney = ($b->cb_id && ($tx[$b->cb_id] ?? 0) > 0) || ($royRow && (float) $royRow->profit != 0.0);
            $hasInvoices = ($sales[$b->product_id] ?? 0) > 0 || ($purch[$b->product_id] ?? 0) > 0;
            $row = [
                'key' => 'b' . $b->book_id,
                'book_id' => (int) $b->book_id,
                'product_id' => (int) $b->product_id,
                'v' => md5($b->b_upd . '|' . $b->p_upd . '|' . $b->cb_id . '|' . $b->cb_upd . '|' . $b->contractor_id),
                'name' => $b->name, 'sku' => $b->sku, 'base_price' => (float) $b->base_price, 'status' => $b->status,
                'description' => $b->description, 'isbn' => $b->isbn, 'num_of_pages' => $b->num_of_pages,
                'cover_type' => $b->cover_type, 'published_at' => $b->published_at ? substr((string) $b->published_at, 0, 10) : null,
                'language' => $b->language, 'is_translated' => (bool) $b->is_translated,
                'translated_from' => $b->translated_from, 'translated_to' => $b->translated_to,
                'translator_name' => $b->translator_name, 'supervisor' => $b->supervisor,
                'introduction_by' => $b->introduction_by, 'authors' => $b->authors,
                'deposit_number' => $b->deposit_number, 'deposit_year' => $b->deposit_year,
                'deposit_status' => $b->deposit_status, 'contract_status' => $b->contract_status ?: 'active',
                'category' => $b->category, 'sub_category' => $b->sub_category,
                'contractor' => $b->contractor, 'nationality' => $b->nationality,
                'profit_percentage' => $b->cb_id ? (float) $b->profit_percentage : null,
                'percentage_basis' => $b->cb_id ? $b->percentage_basis : null,
                'gift_copies_given' => $b->cb_id ? (int) $b->gift_copies_given : null,
                'quantity' => (int) ($stockWh[$b->product_id] ?? 0),
                'qty_total' => (int) ($stockAll[$b->product_id] ?? 0),
                'sales_lines' => (int) ($sales[$b->product_id] ?? 0),
                'locks' => [
                    'sku' => $hasInvoices,
                    'contractor' => (bool) ($b->cb_id && $hasMoney),
                    'contractor_clear' => (bool) ($b->cb_id && ($hasMoney || ($royRow && $royRow->n > 0))),
                    'delete' => $hasInvoices || ($royRow && $royRow->n > 0) || $hasMoney ? 'used' : null,
                ],
                '_cb_id' => $b->cb_id ? (int) $b->cb_id : null,
                '_contractor_id' => $b->contractor_id ? (int) $b->contractor_id : null,
            ];
            $out[(int) $b->book_id] = $row;
        }

        return $out;
    }

    // ─── Normalisation & validation ────────────────────────────────────────────────────────────

    private function norm(string $field, mixed $v): mixed
    {
        $type = self::FIELDS[$field] ?? 'text';
        if ($type === 'bool') {
            return filter_var($v, FILTER_VALIDATE_BOOLEAN);
        }
        if ($v === null) {
            return null;
        }
        if (is_string($v)) {
            $v = $type === 'longtext' ? trim($v) : trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $v));
            if ($v === '') {
                return null;
            }
        }
        return match (true) {
            $field === 'isbn' => strtoupper(preg_replace('/[\s\-]/', '', (string) $v)),
            $type === 'int' => is_numeric($v) && floor((float) $v) == (float) $v ? (int) $v : $v,
            $type === 'number' => is_numeric($v) ? round((float) $v, 2) : $v,
            $field === 'published_at' && is_string($v) && preg_match('/^\d{4}$/', $v) => $v . '-01-01',
            default => $v,
        };
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255', 'isbn' => 'required|string|max:50', 'base_price' => 'required|numeric|min:0|max:9999999',
            'status' => 'required|in:active,inactive', 'quantity' => 'nullable|integer|min:0|max:100000000',
            'authors' => 'nullable|string|max:500', 'contractor' => 'nullable|string|max:255', 'nationality' => 'nullable|string|max:150',
            'profit_percentage' => 'nullable|numeric|min:0|max:100', 'percentage_basis' => 'nullable|in:base_price,sale_price',
            'contract_status' => 'required|in:active,ended', 'gift_copies_given' => 'nullable|integer|min:0|max:1000000',
            'category' => 'nullable|string|max:255', 'sub_category' => 'nullable|string|max:255',
            'cover_type' => 'required|in:hard,soft', 'num_of_pages' => 'nullable|integer|min:1|max:100000',
            'published_at' => 'nullable|date_format:Y-m-d', 'language' => 'nullable|string|max:100',
            'is_translated' => 'boolean', 'translated_from' => 'nullable|string|max:100', 'translated_to' => 'nullable|string|max:100',
            'translator_name' => 'nullable|string|max:255', 'supervisor' => 'nullable|string|max:255',
            'introduction_by' => 'nullable|string|max:255', 'deposit_number' => 'nullable|string|max:50',
            'deposit_year' => 'nullable|integer|between:1900,2100', 'deposit_status' => 'nullable|string|max:50',
            'sku' => 'nullable|string|max:100', 'description' => 'nullable|string|max:65000',
        ];
    }

    // ─── Plan (preview) ────────────────────────────────────────────────────────────────────────

    /**
     * @param  array{warehouse_id?:int,rows?:array,deleted?:array}  $payload
     * @return array  summary + errors; internal '_ops' is what apply() executes
     */
    public function plan(array $payload, bool $lock = false): array
    {
        $warehouseId = (int) ($payload['warehouse_id'] ?? 0);
        $rows = array_values($payload['rows'] ?? []);
        $deletedIds = array_values(array_unique(array_map('intval', $payload['deleted'] ?? [])));

        $errors = [];
        $err = function (?string $key, ?string $field, string $code, array $p = []) use (&$errors) {
            $errors[] = ['row' => $key, 'field' => $field, 'code' => $code, 'message' => __('product::book_grid.err.' . $code, $p)];
        };

        $editIds = collect($rows)->pluck('book_id')->filter()->map(fn ($i) => (int) $i)->all();
        $existing = $this->loadBooks(array_values(array_unique(array_merge($editIds, $deletedIds))), $warehouseId, $lock);

        // lookup tables
        $cats = DB::table('book_categories')->whereNull('parent_id')->pluck('id', 'name')->all();
        $subs = [];
        foreach (DB::table('book_categories')->whereNotNull('parent_id')->get(['id', 'name', 'parent_id']) as $s) {
            $subs[$s->parent_id . '|' . mb_strtolower($s->name)] = (int) $s->id;
        }
        $catsLower = [];
        foreach ($cats as $n => $id) {
            $catsLower[mb_strtolower($n)] = (int) $id;
        }
        $contractors = [];
        foreach (DB::table('contractors')->whereNull('deleted_at')->orderBy('id')->get(['id', 'name', 'nationality']) as $c) {
            $contractors[$this->names->normName($c->name)] ??= ['id' => (int) $c->id, 'name' => $c->name, 'nationality' => $c->nationality];
        }

        // freed identifiers (rows being deleted) + current owners of isbn/sku
        $isbnOwner = DB::table('books')->pluck('id', 'isbn')->all();
        $skuOwner = DB::table('products')->whereNotNull('sku')->pluck('id', 'sku')->all();
        $productToBook = DB::table('books')->pluck('id', 'product_id')->all();
        $freedBooks = array_flip($deletedIds);

        $seenIsbn = $seenSku = $seenRowBook = [];
        $nationalityByContractor = [];
        $added = $changed = $ops = [];
        $newCategories = $newSubCategories = $newContractors = $contractorUpdates = [];
        $stock = [];

        foreach ($rows as $i => $r) {
            $bookId = isset($r['book_id']) && $r['book_id'] !== null ? (int) $r['book_id'] : null;
            $key = (string) ($r['key'] ?? ($bookId ? 'b' . $bookId : 'n' . $i));
            if ($bookId && isset($freedBooks[$bookId])) {
                continue; // edited AND deleted → deletion wins
            }
            if ($bookId) {
                if (isset($seenRowBook[$bookId])) {
                    $err($key, null, 'duplicate_row');
                    continue;
                }
                $seenRowBook[$bookId] = true;
            }
            $ex = $bookId ? ($existing[$bookId] ?? null) : null;
            if ($bookId && ! $ex) {
                $err($key, null, 'row_missing');
                continue;
            }
            if ($ex && ($r['v'] ?? null) !== $ex['v']) {
                $err($key, null, 'stale');
                continue;
            }

            // values: submitted (normalised) or, for existing rows, untouched DB values
            $in = $r['fields'] ?? [];
            $f = [];
            foreach (array_keys(self::FIELDS) as $field) {
                $given = array_key_exists($field, $in);
                $base = $ex ? ($ex[$field] ?? null) : null;
                $f[$field] = $this->norm($field, $given ? $in[$field] : $base);
            }
            if (! $ex) {
                $f['sku'] ??= $f['isbn'] ? 'BOOK-' . $f['isbn'] : null;
                $f['status'] ??= 'active';
                $f['cover_type'] ??= 'soft';
                $f['contract_status'] ??= 'active';
            }
            $f['quantity'] ??= 0;
            if ($f['contractor'] && (! $ex || ! $ex['_cb_id'])) { // a contract is being created → sensible defaults (10% of sale price)
                $f['profit_percentage'] ??= self::DEFAULT_PERCENTAGE;
            }
            if ($f['contractor']) {
                $f['percentage_basis'] ??= 'sale_price';
                $f['gift_copies_given'] ??= 0;
            }

            if (! $f['contractor']) {
                $f['nationality'] = null; // nationality belongs to the contractor; no contractor → nothing to store
            }

            $v = Validator::make($f, $this->rules());
            foreach ($v->errors()->messages() as $field => $msgs) {
                $errors[] = ['row' => $key, 'field' => $field, 'code' => 'invalid', 'message' => $msgs[0]];
            }
            if ($f['contractor'] && $f['profit_percentage'] === null) {
                $err($key, 'profit_percentage', 'percentage_required');
            }
            if ($f['sub_category'] && ! $f['category']) {
                $err($key, 'sub_category', 'category_required');
            }
            if ($f['quantity'] !== null && is_int($f['quantity']) && $f['quantity'] < 0) {
                $err($key, 'quantity', 'negative_stock');
            }

            // uniqueness: isbn / sku
            if ($f['isbn']) {
                $owner = $isbnOwner[$f['isbn']] ?? null;
                if (($owner && $owner !== $bookId && ! isset($freedBooks[$owner])) || isset($seenIsbn[$f['isbn']])) {
                    $err($key, 'isbn', 'isbn_duplicate', ['isbn' => $f['isbn']]);
                }
                $seenIsbn[$f['isbn']] = $key;
            }
            if ($f['sku']) {
                $owner = $skuOwner[$f['sku']] ?? null;
                $ownerBook = $owner ? ($productToBook[$owner] ?? null) : null;
                if (($owner && (! $ex || $owner !== $ex['product_id']) && ! isset($freedBooks[(int) $ownerBook])) || isset($seenSku[$f['sku']])) {
                    $err($key, 'sku', 'sku_duplicate', ['sku' => $f['sku']]);
                }
                $seenSku[$f['sku']] = $key;
            }

            // category / sub-category resolution
            $catId = $subId = null;
            if ($f['category']) {
                $catId = $catsLower[mb_strtolower($f['category'])] ?? null;
                if (! $catId) {
                    $newCategories[mb_strtolower($f['category'])] = $f['category'];
                }
                if ($f['sub_category']) {
                    $subId = $catId ? ($subs[$catId . '|' . mb_strtolower($f['sub_category'])] ?? null) : null;
                    if (! $subId) {
                        $newSubCategories[mb_strtolower($f['category']) . '|' . mb_strtolower($f['sub_category'])] = [$f['category'], $f['sub_category']];
                    }
                }
            }

            // contractor resolution + conflicts
            $cKey = $f['contractor'] ? $this->names->normName($f['contractor']) : null;
            $cur = $cKey ? ($contractors[$cKey] ?? null) : null;
            if ($cur) {
                $f['contractor'] = $cur['name']; // same person typed differently → show/store the existing name
            }
            if ($f['contractor'] && ! $cur) {
                $newContractors[$cKey] = ['name' => $f['contractor'], 'nationality' => $f['nationality']];
            }
            if ($cKey && $f['nationality']) {
                $nationalityByContractor[$cKey][$f['nationality']][] = $key;
                if ($cur && $cur['nationality'] !== $f['nationality']) {
                    $contractorUpdates[$cKey] = ['id' => $cur['id'], 'name' => $cur['name'], 'old' => $cur['nationality'], 'new' => $f['nationality']];
                }
            }

            // locks
            if ($ex) {
                if ($ex['locks']['sku'] && $f['sku'] !== $ex['sku']) {
                    $err($key, 'sku', 'sku_locked');
                }
                $oldCKey = $ex['contractor'] ? $this->names->normName($ex['contractor']) : null;
                if ($oldCKey !== $cKey) {
                    if ($cKey === null && $ex['locks']['contractor_clear']) {
                        $err($key, 'contractor', 'contractor_clear_locked');
                    } elseif ($cKey !== null && $oldCKey !== null && $ex['locks']['contractor']) {
                        $err($key, 'contractor', 'contractor_locked');
                    }
                }
                if ($ex['quantity'] !== $f['quantity']) {
                    $q0 = $r['q0'] ?? null;
                    if ($q0 !== null && (int) $q0 !== $ex['quantity']) {
                        $err($key, 'quantity', 'stock_stale', ['current' => $ex['quantity']]);
                    }
                }
            }

            $restore = (! $ex && isset($r['restore']['book_id'], $r['restore']['product_id'])) ? ['book_id' => (int) $r['restore']['book_id'], 'product_id' => (int) $r['restore']['product_id']] : null;
            if ($restore && (DB::table('books')->where('id', $restore['book_id'])->exists() || DB::table('products')->where('id', $restore['product_id'])->exists())) {
                $err($key, null, 'restore_id_taken', ['name' => $f['name']]);
            }

            // diff / add
            if ($ex) {
                $diff = [];
                foreach (array_keys(self::FIELDS) as $field) {
                    $old = $this->norm($field, $ex[$field] ?? null);
                    if ($field === 'quantity') {
                        $old = $ex['quantity'];
                    }
                    if (! $this->same($old, $f[$field])) {
                        $diff[] = ['field' => $field, 'old' => $old, 'new' => $f[$field]];
                    }
                }
                if ($diff) {
                    $changed[] = ['key' => $key, 'book_id' => $bookId, 'name' => $ex['name'], 'isbn' => $ex['isbn'], 'diff' => $diff];
                    $ops[] = ['type' => 'change', 'key' => $key, 'ex' => $ex, 'f' => $f, 'diff' => array_column($diff, 'field'), 'cat' => [$catId, $subId]];
                }
            } else {
                $added[] = ['key' => $key, 'name' => $f['name'], 'isbn' => $f['isbn'], 'quantity' => $f['quantity'], 'contractor' => $f['contractor'], 'category' => $f['category']];
                $ops[] = ['type' => 'add', 'key' => $key, 'f' => $f, 'cat' => [$catId, $subId], 'restore' => $restore];
            }
        }

        foreach ($nationalityByContractor as $cKey => $byNat) {
            if (count($byNat) > 1) {
                foreach ($byNat as $nat => $keys) {
                    foreach ($keys as $k) {
                        $err($k, 'nationality', 'nationality_conflict', ['name' => $contractors[$cKey]['name'] ?? $cKey]);
                    }
                }
            }
        }

        // deletions
        $deleted = [];
        $stockUnits = 0;
        foreach ($deletedIds as $id) {
            $ex = $existing[$id] ?? null;
            if (! $ex) {
                $err('b' . $id, null, 'row_missing');
                continue;
            }
            $ref = $this->prune->references((object) ['product_id' => $ex['product_id'], 'book_id' => $id, 'name' => $ex['name'],
                'sku' => $ex['sku'], 'isbn' => $ex['isbn'], 'status' => $ex['status']]);
            if ($ref['sales_lines'] || $ref['purchase_lines'] || $ref['book_sales'] || $ref['contract_tx']) {
                $err('b' . $id, null, 'delete_blocked', ['name' => $ex['name'], 'reason' => $ref['reason']]);
                continue;
            }
            $stockUnits += max(0, $ref['stock_qty']);
            $deleted[] = ['key' => 'b' . $id, 'book_id' => $id, 'product_id' => $ex['product_id'], 'name' => $ex['name'], 'isbn' => $ex['isbn'],
                'stock' => $ref['stock_qty'], 'has_contract' => (bool) $ex['_cb_id']];
            $ops[] = ['type' => 'delete', 'ex' => $ex];
        }

        // stock adjustments (changes + new rows)
        foreach ($ops as $op) {
            if ($op['type'] === 'change' && $op['ex']['quantity'] !== $op['f']['quantity']) {
                $stock[] = ['key' => $op['key'], 'name' => $op['ex']['name'], 'old' => $op['ex']['quantity'], 'new' => $op['f']['quantity']];
            } elseif ($op['type'] === 'add' && (int) $op['f']['quantity'] > 0) {
                $stock[] = ['key' => $op['key'], 'name' => $op['f']['name'], 'old' => 0, 'new' => $op['f']['quantity']];
            }
        }

        if ($stock && ! DB::table('sub_warehouses')->where('id', $warehouseId)->exists()) {
            $err(null, 'quantity', 'warehouse_required');
        }

        return [
            'ok' => ! $errors,
            'errors' => $errors,
            'added' => $added,
            'changed' => $changed,
            'deleted' => $deleted,
            'created' => [
                'categories' => array_values($newCategories),
                'sub_categories' => array_values(array_map(fn ($p) => $p[0] . ' › ' . $p[1], $newSubCategories)),
                'contractors' => array_values(array_map(fn ($c) => $c['name'], $newContractors)),
            ],
            'contractor_updates' => array_values($contractorUpdates),
            'stock' => $stock,
            'stock_units_destroyed' => $stockUnits,
            'totals' => ['added' => count($added), 'changed' => count($changed), 'deleted' => count($deleted)],
            '_ops' => $ops,
            '_new' => ['categories' => $newCategories, 'sub_categories' => $newSubCategories, 'contractors' => $newContractors, 'contractor_updates' => $contractorUpdates],
        ];
    }

    private function same(mixed $a, mixed $b): bool
    {
        if ($a === null || $b === null) {
            return $a === $b || ($a === '' && $b === null) || ($a === null && $b === '');
        }
        if (is_bool($a) || is_bool($b)) {
            return (bool) $a === (bool) $b;
        }
        if (is_numeric($a) && is_numeric($b)) {
            return abs((float) $a - (float) $b) < 0.0001;
        }

        return (string) $a === (string) $b;
    }

    // ─── Apply ────────────────────────────────────────────────────────────────────────────────

    /**
     * @throws \RuntimeException  with ->plan available via BookGridException when validation fails
     */
    public function apply(array $payload, ?int $userId, bool $ackStock = false, ?int $confirmDeleteCount = null, ?string $restoreOf = null): array
    {
        $uuid = (string) Str::uuid();
        $before = $this->names->fingerprint();

        $result = DB::transaction(function () use ($payload, $userId, $ackStock, $confirmDeleteCount, $uuid, $before, $restoreOf) {
            $plan = $this->plan($payload, true);
            if ($plan['errors']) {
                throw new BookGridException($this->publicPlan($plan));
            }
            if (count($plan['deleted']) > 0 && $confirmDeleteCount !== count($plan['deleted'])) {
                throw new BookGridException($this->publicPlan($plan) + ['fatal' => __('product::book_grid.err.delete_not_confirmed')]);
            }
            if ($plan['stock_units_destroyed'] > 0 && ! $ackStock) {
                throw new BookGridException($this->publicPlan($plan) + ['fatal' => __('product::book_grid.err.stock_not_acknowledged')]);
            }

            $now = now();
            $audit = [];
            $log = function (string $action, ?int $bookId, ?int $productId, ?string $field = null, mixed $old = null, mixed $new = null, mixed $snapshot = null) use (&$audit, $uuid, $userId, $now) {
                $audit[] = ['batch_uuid' => $uuid, 'user_id' => $userId, 'action' => $action, 'book_id' => $bookId, 'product_id' => $productId,
                    'field' => $field, 'old_value' => $this->flat($old), 'new_value' => $this->flat($new),
                    'snapshot' => $snapshot !== null ? json_encode($snapshot, JSON_UNESCAPED_UNICODE) : null, 'created_at' => $now];
            };
            $warehouseId = (int) ($payload['warehouse_id'] ?? 0);

            // 1. categories
            $catId = [];
            foreach (DB::table('book_categories')->whereNull('parent_id')->get(['id', 'name']) as $c) {
                $catId[mb_strtolower($c->name)] = (int) $c->id;
            }
            foreach ($plan['_new']['categories'] as $lower => $name) {
                $catId[$lower] = DB::table('book_categories')->insertGetId(['name' => $name, 'parent_id' => null, 'created_by' => $userId, 'created_at' => $now, 'updated_at' => $now]);
                $log('category_create', null, null, 'category', null, $name);
            }
            $subId = [];
            foreach (DB::table('book_categories')->whereNotNull('parent_id')->get(['id', 'name', 'parent_id']) as $s) {
                $subId[$s->parent_id . '|' . mb_strtolower($s->name)] = (int) $s->id;
            }
            foreach ($plan['_new']['sub_categories'] as [$cat, $sub]) {
                $pid = $catId[mb_strtolower($cat)];
                $subId[$pid . '|' . mb_strtolower($sub)] = DB::table('book_categories')->insertGetId(['name' => $sub, 'parent_id' => $pid, 'created_by' => $userId, 'created_at' => $now, 'updated_at' => $now]);
                $log('category_create', null, null, 'sub_category', null, $cat . ' › ' . $sub);
            }

            // 2. contractors
            $contractorId = [];
            foreach (DB::table('contractors')->whereNull('deleted_at')->orderBy('id')->get(['id', 'name']) as $c) {
                $contractorId[$this->names->normName($c->name)] ??= (int) $c->id;
            }
            foreach ($plan['_new']['contractors'] as $cKey => $c) {
                $contractorId[$cKey] = DB::table('contractors')->insertGetId(['name' => $c['name'], 'nationality' => $c['nationality'], 'created_at' => $now, 'updated_at' => $now]);
                $log('contractor_create', null, null, 'contractor', null, $c['name']);
            }
            foreach ($plan['_new']['contractor_updates'] as $u) {
                DB::table('contractors')->where('id', $u['id'])->update(['nationality' => $u['new'], 'updated_at' => $now]);
                $log('contractor_update', null, null, 'nationality', $u['old'], $u['new'], ['contractor' => $u['name']]);
            }

            // 3. deletes first (frees ISBN/SKU), then changes, then adds
            foreach ($plan['_ops'] as $op) {
                if ($op['type'] !== 'delete') {
                    continue;
                }
                $ex = $op['ex'];
                $snap = array_diff_key($ex, ['_cb_id' => 1, '_contractor_id' => 1]);
                $log('delete', $ex['book_id'], $ex['product_id'], null, null, null, $snap);
                $this->prune->deleteOne($ex['product_id'], $ex['book_id']);
            }

            foreach ($plan['_ops'] as $op) {
                if ($op['type'] === 'change') {
                    $ex = $op['ex'];
                    $f = $op['f'];
                    [$cid, $sid] = $this->resolveCats($f, $catId, $subId);
                    $this->writeProduct($ex['product_id'], $f, $op['diff'], $userId, $now);
                    $this->writeBook($ex['book_id'], $f, $op['diff'], $cid, $sid, $userId, $now);
                    $this->writeContract($ex, $f, $contractorId, $now, $log);
                    foreach ($op['diff'] as $field) {
                        $log('change', $ex['book_id'], $ex['product_id'], $field, $ex[$field] ?? null, $f[$field]);
                    }
                    if ($ex['quantity'] !== $f['quantity']) {
                        $this->setStock($ex['product_id'], $warehouseId, (int) $f['quantity'], $uuid, $userId, $now);
                    }
                } elseif ($op['type'] === 'add') {
                    $f = $op['f'];
                    [$cid, $sid] = $this->resolveCats($f, $catId, $subId);
                    $productRow = [
                        'name' => $f['name'], 'type' => 'book', 'sku' => $f['sku'], 'description' => $f['description'],
                        'base_price' => $f['base_price'], 'status' => $f['status'], 'created_by' => $userId, 'created_at' => $now, 'updated_at' => $now,
                    ];
                    if ($op['restore']) { // restoring a deleted book keeps its original ids
                        $productRow['id'] = $op['restore']['product_id'];
                    }
                    $productId = DB::table('products')->insertGetId($productRow);
                    $bookRow = ['product_id' => $productId, 'category_id' => $cid, 'sub_category_id' => $sid, 'created_by' => $userId, 'created_at' => $now, 'updated_at' => $now];
                    foreach (self::BOOK_COLS as $col) {
                        $bookRow[$col] = $f[$col];
                    }
                    $bookRow['is_translated'] = $f['is_translated'] ? 1 : 0;
                    if ($op['restore']) {
                        $bookRow['id'] = $op['restore']['book_id'];
                    }
                    $bookId = DB::table('books')->insertGetId($bookRow);
                    $this->writeContract(['book_id' => $bookId, '_cb_id' => null, '_contractor_id' => null, 'contractor' => null] + array_fill_keys(array_keys(self::FIELDS), null), $f, $contractorId, $now, $log, true);
                    if ((int) $f['quantity'] > 0) {
                        $this->setStock($productId, $warehouseId, (int) $f['quantity'], $uuid, $userId, $now);
                    }
                    $log('add', $bookId, $productId, null, null, null, $f);
                }
            }

            // guard: invoices / payments / royalties / contract payments untouched
            $after = $this->names->fingerprint();
            foreach (['sales_invoices', 'sales_invoice_items', 'receipt_vouchers', 'payment_vouchers', 'purchase_invoices', 'purchase_invoice_items', 'contract_transactions'] as $t) {
                if ($before[$t] !== $after[$t]) {
                    throw new \RuntimeException("Safety check failed — {$t} changed; nothing was saved.");
                }
            }

            foreach (array_chunk($audit, 200) as $chunk) {
                DB::table('book_grid_audits')->insert($chunk);
            }

            DB::table('book_grid_batches')->insert([
                'batch_uuid' => $uuid, 'user_id' => $userId, 'warehouse_id' => $warehouseId ?: null,
                'added' => count($plan['added']), 'changed' => count($plan['changed']), 'deleted' => count($plan['deleted']),
                'stock_changes' => count($plan['stock']), 'restore_of' => $restoreOf, 'created_at' => $now,
            ]);
            if ($restoreOf) {
                DB::table('book_grid_batches')->where('batch_uuid', $restoreOf)->whereNull('reverted_by')->update(['reverted_by' => $uuid]);
            }

            return $this->publicPlan($plan) + ['batch' => $uuid];
        });

        return $result;
    }

    // ─── Restore (revert a saved batch) ────────────────────────────────────────────────────────

    /**
     * Builds the payload that undoes a saved batch, to be run through the normal plan()/apply() pipeline:
     *   - changed cells go back to their old value — only if the cell STILL holds what the batch wrote
     *     (otherwise somebody changed it later → reported as a conflict, never overwritten silently);
     *   - books the batch added are deleted again (the usual delete rules apply: used books are refused);
     *   - books the batch deleted are re-created with their ORIGINAL ids from the stored snapshot;
     *   - stock edits are reverted in the warehouse the batch used (if still equal to what it set).
     * Created categories / contractors are not removed (harmless, and may be in use now).
     *
     * @param  bool  $skipConflicts  true → leave conflicting items alone and restore the rest; false → all-or-nothing
     * @return array{payload:array, conflicts:array, notes:array, plan:array, batch:object}
     */
    public function restoreBuild(string $uuid, bool $skipConflicts): array
    {
        $batch = DB::table('book_grid_batches')->where('batch_uuid', $uuid)->first();
        if (! $batch) {
            throw new \RuntimeException(__('product::book_grid.history.not_found'));
        }
        if ($batch->reverted_by) {
            throw new \RuntimeException(__('product::book_grid.history.already_restored'));
        }
        $audits = DB::table('book_grid_audits')->where('batch_uuid', $uuid)->orderBy('id')->get();
        $warehouseId = (int) ($batch->warehouse_id ?? 0);

        $changes = $addedIds = $deletedSnaps = $notes = [];
        foreach ($audits as $a) {
            if ($a->action === 'change' && isset(self::FIELDS[$a->field])) {
                $changes[(int) $a->book_id][$a->field] = [$a->old_value, $a->new_value];
            } elseif ($a->action === 'change') {
                $notes[] = __('product::book_grid.history.note_not_restorable', ['what' => $a->field]);
            } elseif ($a->action === 'add') {
                $addedIds[] = (int) $a->book_id;
            } elseif ($a->action === 'delete') {
                $deletedSnaps[(int) $a->book_id] = ['product_id' => (int) $a->product_id, 'snap' => json_decode((string) $a->snapshot, true) ?: []];
            } elseif (in_array($a->action, ['category_create', 'contractor_create', 'contractor_update'], true)) {
                $notes[$a->action . $a->new_value] = __('product::book_grid.history.note_kept', ['what' => $a->new_value ?? $a->field]);
            }
        }
        $notes = array_values(array_unique($notes));

        $ids = array_values(array_unique(array_merge(array_keys($changes), $addedIds)));
        $current = $ids ? $this->loadBooks($ids, $warehouseId) : [];

        $rows = $conflicts = [];
        $conflict = function (int $bookId, ?string $name, ?string $field, string $code, array $p = []) use (&$conflicts) {
            $conflicts[] = ['book_id' => $bookId, 'name' => $name, 'field' => $field, 'message' => __('product::book_grid.history.conflict.' . $code, $p)];
        };

        foreach ($changes as $bookId => $fields) {
            $cur = $current[$bookId] ?? null;
            if (! $cur) {
                $conflict($bookId, null, null, 'book_gone');
                continue;
            }
            $revert = [];
            foreach ($fields as $field => [$old, $new]) {
                if ($field === 'quantity' && ! $warehouseId) {
                    $conflict($bookId, $cur['name'], $field, 'no_warehouse');
                    continue;
                }
                $now = $cur[$field] ?? null;
                if (! $this->same($this->norm($field, $now), $this->norm($field, $new))) {
                    $conflict($bookId, $cur['name'], $field, 'changed_later', ['now' => $this->flat($now) ?? '—', 'expected' => $new ?? '—']);
                    continue;
                }
                $revert[$field] = $old;
            }
            if ($revert) {
                $rows['b' . $bookId] = ['key' => 'b' . $bookId, 'book_id' => $bookId, 'v' => $cur['v'], 'q0' => $cur['quantity'], 'fields' => $revert];
            }
        }
        $deleted = [];
        foreach ($addedIds as $bookId) {
            if (isset($current[$bookId])) {
                $deleted['b' . $bookId] = $bookId;
            } else {
                $notes[] = __('product::book_grid.history.note_already_gone', ['id' => $bookId]);
            }
        }
        foreach ($deletedSnaps as $bookId => $d) {
            $fields = array_intersect_key($d['snap'], self::FIELDS);
            $rows['r' . $bookId] = ['key' => 'r' . $bookId, 'book_id' => null, 'fields' => $fields,
                'restore' => ['book_id' => $bookId, 'product_id' => $d['product_id']]];
        }

        // plan; with $skipConflicts drop rows the rules refuse (locks, used books, taken ISBN…) and re-plan
        $plan = null;
        for ($pass = 0; $pass < 4; $pass++) {
            $payload = ['warehouse_id' => $warehouseId, 'rows' => array_values($rows), 'deleted' => array_values($deleted)];
            $plan = $this->plan($payload);
            if (! $plan['errors'] || ! $skipConflicts) {
                break;
            }
            $dropped = false;
            foreach ($plan['errors'] as $e) {
                $k = $e['row'];
                if ($k !== null && isset($rows[$k])) {
                    $conflict((int) ltrim($k, 'br'), $rows[$k]['fields']['name'] ?? null, $e['field'], 'refused', ['why' => $e['message']]);
                    unset($rows[$k]);
                    $dropped = true;
                } elseif ($k !== null && isset($deleted[$k])) {
                    $conflict($deleted[$k], null, null, 'refused', ['why' => $e['message']]);
                    unset($deleted[$k]);
                    $dropped = true;
                }
            }
            if (! $dropped) {
                break;
            }
        }

        return ['payload' => $payload, 'conflicts' => $conflicts, 'notes' => $notes, 'plan' => $plan, 'batch' => $batch];
    }

    public function publicPlan(array $plan): array
    {
        return array_diff_key($plan, ['_ops' => 1, '_new' => 1]);
    }

    private function resolveCats(array $f, array $catId, array $subId): array
    {
        $cid = $f['category'] ? ($catId[mb_strtolower($f['category'])] ?? null) : null;
        $sid = ($cid && $f['sub_category']) ? ($subId[$cid . '|' . mb_strtolower($f['sub_category'])] ?? null) : null;

        return [$cid, $sid];
    }

    private function writeProduct(int $productId, array $f, array $diff, ?int $userId, $now): void
    {
        $set = [];
        foreach (self::PRODUCT_COLS as $col) {
            if (in_array($col, $diff, true)) {
                $set[$col] = $f[$col];
            }
        }
        if ($set) {
            DB::table('products')->where('id', $productId)->update($set + ['edited_by' => $userId, 'updated_at' => $now]);
        }
    }

    private function writeBook(int $bookId, array $f, array $diff, ?int $cid, ?int $sid, ?int $userId, $now): void
    {
        $set = [];
        foreach (self::BOOK_COLS as $col) {
            if (in_array($col, $diff, true)) {
                $set[$col] = $col === 'is_translated' ? ($f[$col] ? 1 : 0) : $f[$col];
            }
        }
        if (array_intersect(['category', 'sub_category'], $diff)) {
            $set['category_id'] = $cid;
            $set['sub_category_id'] = $sid;
        }
        if ($set) {
            DB::table('books')->where('id', $bookId)->update($set + ['edited_by' => $userId, 'updated_at' => $now]);
        }
    }

    /** contractor_books create / update / soft-remove according to the row's final state */
    private function writeContract(array $ex, array $f, array $contractorId, $now, callable $log, bool $isNew = false): void
    {
        $bookId = $ex['book_id'];
        $cKey = $f['contractor'] ? $this->names->normName($f['contractor']) : null;
        $row = DB::table('contractor_books')->where('book_id', $bookId)->first(); // incl. soft-deleted (unique book_id)
        $active = $row && $row->deleted_at === null;

        if ($cKey === null) {
            if ($active) {
                DB::table('contractor_books')->where('id', $row->id)->update(['deleted_at' => $now, 'updated_at' => $now]);
                $log('change', $bookId, null, 'contract_removed', $ex['contractor'], null);
            }
            return;
        }
        $cid = $contractorId[$cKey];
        $data = ['contractor_id' => $cid, 'profit_percentage' => $f['profit_percentage'], 'percentage_basis' => $f['percentage_basis'] ?? 'sale_price',
            'gift_copies_given' => (int) ($f['gift_copies_given'] ?? 0), 'status' => $f['contract_status'], 'updated_at' => $now];
        if (! $row) {
            DB::table('contractor_books')->insert($data + ['book_id' => $bookId, 'created_at' => $now]);
        } elseif (! $active) {
            DB::table('contractor_books')->where('id', $row->id)->update($data + ['deleted_at' => null]);
        } else {
            $same = (int) $row->contractor_id === $cid && $this->same($row->profit_percentage, $data['profit_percentage'])
                && $row->percentage_basis === $data['percentage_basis'] && (int) $row->gift_copies_given === $data['gift_copies_given']
                && $row->status === $data['status'];
            if (! $same) {
                DB::table('contractor_books')->where('id', $row->id)->update($data);
            }
        }
    }

    private function setStock(int $productId, int $warehouseId, int $target, string $uuid, ?int $userId, $now): void
    {
        $row = DB::table('sub_warehouse_products')->where('sub_warehouse_id', $warehouseId)->where('product_id', $productId)->lockForUpdate()->first();
        $current = $row ? (int) $row->quantity : 0;
        $delta = $target - $current;
        if ($delta === 0) {
            return;
        }
        if ($row) {
            DB::table('sub_warehouse_products')->where('id', $row->id)->update(['quantity' => $target, 'edited_by' => $userId, 'updated_at' => $now]);
        } else {
            DB::table('sub_warehouse_products')->insert(['sub_warehouse_id' => $warehouseId, 'product_id' => $productId, 'quantity' => $target,
                'created_by' => $userId, 'created_at' => $now, 'updated_at' => $now]);
        }
        DB::table('stock_movements')->insert([
            'product_id' => $productId, 'from_sub_warehouse_id' => $delta < 0 ? $warehouseId : null, 'to_sub_warehouse_id' => $delta > 0 ? $warehouseId : null,
            'quantity' => abs($delta), 'movement_type' => $delta > 0 ? 'inbound' : 'outbound', 'reason' => 'grid_adjustment',
            'notes' => 'Books grid edit ' . $uuid, 'user_id' => $userId, 'created_by' => $userId, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    private function flat(mixed $v): ?string
    {
        if ($v === null) {
            return null;
        }
        if (is_bool($v)) {
            return $v ? '1' : '0';
        }

        return is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE);
    }
}
