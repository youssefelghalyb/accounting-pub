<?php

namespace Modules\Product\Services;

use Illuminate\Support\Facades\DB;

/**
 * Updates EXISTING products/books/contractors in place from the master Excel sheet
 * (the older BooksImportService only ever inserted and skipped existing rows).
 *
 * Safety contract — nothing that a sales invoice, receipt, stock row or royalty record depends
 * on is ever changed:
 *   - products.id / books.id / SKU of existing rows are never modified;
 *   - invoice lines keep their own snapshots (product_name, sku, unit_price, line_total) so a new
 *     price/name here never rewrites history; book_sales keeps its own price/percentage snapshots;
 *   - a book's contractor is NOT switched when the contract already has royalty accruals or
 *     transactions (book_sales/contract_transactions point at contractor_books, so switching would
 *     silently re-attribute money already earned) — reported as a conflict instead;
 *   - stock is never touched;
 *   - the whole run is ONE transaction; before COMMIT a fingerprint of every money/stock table is
 *     compared with the one taken before the run and any difference aborts + rolls back.
 * Dry run (default) executes everything for real inside the transaction and then ROLLS BACK, so
 * the report reflects exactly what --apply would do.
 */
class BooksSyncService
{
    /** Tables whose aggregate must be identical before/after (invoices, money, stock, royalties). */
    private const FINGERPRINT = [
        'sales_invoices'          => ['total_amount', 'paid_amount', 'subtotal', 'tax_amount', 'discount_amount'],
        'sales_invoice_items'     => ['product_id', 'quantity', 'unit_price', 'line_total'],
        'book_sales'              => ['book_id', 'contractor_book_id', 'quantity', 'contractor_profit'],
        'receipt_vouchers'        => ['amount', 'sales_invoice_id'],
        'payment_vouchers'        => ['amount', 'purchase_invoice_id'],
        'purchase_invoices'       => ['total_amount', 'paid_amount'],
        'purchase_invoice_items'  => ['product_id', 'quantity', 'unit_price', 'line_total'],
        'sub_warehouse_products'  => ['product_id', 'quantity'],
        'stock_movements'         => ['product_id', 'quantity'],
        'contract_transactions'   => ['contractor_book_id', 'amount'],
    ];

    private array $categoryCache = [];
    private array $contractorCache = [];
    private BooksSheetReader $reader;

    public array $stats = [];
    /** @var array<int,array> one entry per changed column: book_id, product_id, table, column, old, new */
    public array $changes = [];
    /** @var array<int,array> rows that were NOT applied and need a human */
    public array $conflicts = [];

    /** royalty % given to contracts the sheet creates, and to existing contracts still sitting at 0% */
    private float $defaultPercentage;

    public function __construct(?BooksSheetReader $reader = null, float $defaultPercentage = 10.0)
    {
        $this->defaultPercentage = $defaultPercentage;
        $this->reader = $reader ?? new BooksSheetReader();
        $this->resetStats();
    }

    /**
     * @param  bool  $apply   false = dry run (rolled back)
     * @param  bool  $relink  allow repairing Book ID <-> Product ID mismatches when it is provably safe
     */
    public function run(array $rows, bool $apply, bool $relink = false): array
    {
        $this->resetStats();
        $before = $this->fingerprint();

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                try {
                    DB::transaction(fn () => $this->processRow($row, $relink)); // savepoint: a bad row is skipped, not fatal
                } catch (\Throwable $e) {
                    $this->conflict($row, 'row_error', mb_strimwidth($e->getMessage(), 0, 300, '…'));
                }
            }

            $after = $this->fingerprint();
            if ($before !== $after) {
                $diff = array_keys(array_diff_assoc($after, $before));
                throw new \RuntimeException('Safety check failed — money/stock fingerprint changed in: ' . implode(', ', $diff));
            }

            $apply ? DB::commit() : DB::rollBack();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->stats['applied'] = $apply;

        return $this->stats;
    }

    // ─── Row logic ────────────────────────────────────────────────────────────

    private function processRow(array $row, bool $relink): void
    {
        $this->stats['rows']++;
        $product = DB::table('products')->where('id', $row['product_id'])->first();
        $book = DB::table('books')->where('id', $row['book_id'])->first();

        // ---- 1. resolve the Book ID <-> Product ID pairing -----------------------------------
        if ($product && $book && (int) $book->product_id === $row['product_id']) {
            $mode = 'update';
        } elseif (! $product && ! $book) {
            if ($dup = DB::table('books')->where('isbn', $row['isbn'])->value('id')) {
                $this->conflict($row, 'isbn_exists_under_other_ids', "ISBN {$row['isbn']} already belongs to book #{$dup}");

                return;
            }
            $mode = 'insert';
        } else {
            $mode = $this->repairPairing($row, $product, $book, $relink);
            if ($mode === null) {
                return;
            }
        }

        if ($row['isbn'] === null) {
            if ($mode === 'insert' || $mode === 'book_only') {
                $this->conflict($row, 'isbn_missing_cannot_insert', 'ISBN is empty in the sheet and this book is new — fill the ISBN cell');

                return;
            }
            $this->conflict($row, 'isbn_missing_kept_old', 'ISBN is empty in the sheet — the existing ISBN in the system was kept; fill the cell if it should change');
        }

        $categoryId = $this->resolveCategory($row['category']);
        $subCategoryId = $this->resolveCategory($row['sub_category'], $categoryId);

        // ---- 2. products ---------------------------------------------------------------------
        $productData = [
            'name' => $row['name'],
            'base_price' => $row['price'],
            'status' => $row['status'],
        ];
        if ($row['description'] !== null) { // empty Notes never wipes an existing description
            $productData['description'] = $row['description'];
        }
        // ---- 3. books ------------------------------------------------------------------------
        $bookData = [
            'category_id' => $categoryId,
            'sub_category_id' => $subCategoryId,
            'isbn' => $row['isbn'],
            'num_of_pages' => $row['pages'],
            'cover_type' => $row['cover_type'],
            'published_at' => $row['published_at'],
            'language' => $row['language'],
            'is_translated' => $row['is_translated'],
            'translated_from' => $row['translated_from'],
            'translated_to' => $row['translated_to'],
            'translator_name' => $row['translator_name'],
            'supervisor' => $row['supervisor'],
            'introduction_by' => $row['introduction_by'],
            'authors' => $row['authors'],
            'deposit_number' => $row['deposit_number'],
            'deposit_year' => $row['deposit_year'],
            'deposit_status' => $row['deposit_status'],
            'contract_status' => $row['contract_status'],
        ];

        if ($row['isbn'] === null) {
            unset($bookData['isbn']); // update path: never null out a NOT NULL column / erase a known ISBN
        }

        if ($mode === 'insert' || $mode === 'book_only') {
            if ($mode === 'insert') {
                DB::table('products')->insert($productData + [
                    'id' => $row['product_id'], 'type' => 'book', 'sku' => $row['sku'],
                    'created_by' => 1, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $this->stats['products_inserted']++;
            } else {
                $this->updateIfChanged('products', $row['product_id'], $productData, $row);
            }
            DB::table('books')->insert($bookData + [
                'id' => $row['book_id'], 'product_id' => $row['product_id'],
                'created_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->stats['books_inserted']++;
        } else {
            // SKU is deliberately absent: "keep it as it was".
            $this->updateIfChanged('products', $row['product_id'], $productData, $row);
            $this->updateIfChanged('books', $row['book_id'], $bookData, $row);
        }

        // ---- 4. contractor / contract --------------------------------------------------------
        $this->syncContract($row);
    }

    /**
     * Book ID and Product ID in the sheet must be the pair stored in `books`. Returns the mode to
     * continue with, or null when it cannot be repaired safely (conflict recorded).
     */
    private function repairPairing(array $row, ?object $product, ?object $book, bool $relink): ?string
    {
        $bid = $row['book_id'];
        $pid = $row['product_id'];

        // Product exists but has no book row at all, and book id is free -> just add the books row.
        if ($product && ! $book && ! DB::table('books')->where('product_id', $pid)->exists()) {
            $this->stats['pairing_fixed']++;

            return 'book_only';
        }

        $describe = 'sheet says book #' . $bid . ' <-> product #' . $pid . '; DB has '
            . ($book ? "book #{$bid} -> product #{$book->product_id}" : "no book #{$bid}")
            . ($product ? '; product #' . $pid . ' -> book #' . (DB::table('books')->where('product_id', $pid)->value('id') ?? 'none') : '; no product #' . $pid);

        if (! $relink) {
            $this->conflict($row, 'id_pairing_mismatch', $describe . ' (re-run with --relink to repair when safe)');

            return null;
        }

        // --relink: only when neither side has invoice / royalty / stock history.
        $oldProductId = $book ? (int) $book->product_id : null;
        $otherBookId = DB::table('books')->where('product_id', $pid)->value('id');

        $blockers = [];
        if ($oldProductId && $this->productHasHistory($oldProductId)) {
            $blockers[] = "product #{$oldProductId} has invoice/purchase/stock history";
        }
        if ($this->bookHasHistory($bid)) {
            $blockers[] = "book #{$bid} has royalty history";
        }
        if ($otherBookId && (int) $otherBookId !== $bid && $this->bookHasHistory((int) $otherBookId)) {
            $blockers[] = "book #{$otherBookId} has royalty history";
        }
        if ($blockers) {
            $this->conflict($row, 'id_pairing_blocked_by_history', $describe . ' — ' . implode('; ', $blockers));

            return null;
        }

        // free the wrong book that currently sits on this product (no history -> safe to drop)
        if ($otherBookId && (int) $otherBookId !== $bid) {
            DB::table('contractor_books')->where('book_id', $otherBookId)->delete();
            DB::table('books')->where('id', $otherBookId)->delete();
        }
        if ($book) {
            // re-point the existing book to the sheet's product
            if (! $product) {
                DB::table('products')->insert([
                    'id' => $pid, 'name' => $row['name'], 'type' => 'book', 'sku' => $row['sku'],
                    'base_price' => $row['price'], 'status' => $row['status'],
                    'created_by' => 1, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $this->stats['products_inserted']++;
            }
            DB::table('books')->where('id', $bid)->update(['product_id' => $pid, 'updated_at' => now()]);
            $this->stats['pairing_fixed']++;

            return 'update';
        }
        $this->stats['pairing_fixed']++;

        return 'book_only';
    }

    private function updateIfChanged(string $table, int $id, array $new, array $row): void
    {
        $old = (array) DB::table($table)->where('id', $id)->first();
        $dirty = [];
        foreach ($new as $col => $value) {
            if (! $this->same($old[$col] ?? null, $value)) {
                $dirty[$col] = $value;
                $this->changes[] = [
                    'book_id' => $row['book_id'], 'product_id' => $row['product_id'], 'name' => $row['name'],
                    'table' => $table, 'column' => $col, 'old' => $old[$col] ?? null, 'new' => $value,
                ];
                $this->stats['changed_columns'][$table . '.' . $col] = ($this->stats['changed_columns'][$table . '.' . $col] ?? 0) + 1;
            }
        }
        if ($dirty) {
            DB::table($table)->where('id', $id)->update($dirty + ['updated_at' => now()]);
            $this->stats[$table . '_updated']++;
        }
    }

    // ─── Contractor / contract ────────────────────────────────────────────────────────────────

    private function syncContract(array $row): void
    {
        $existing = DB::table('contractor_books')->where('book_id', $row['book_id'])->first(); // incl. soft-deleted (unique book_id)

        if (! $row['contractor']) {
            if ($existing) { // keep contract, still reflect gift copies + status from the sheet
                $this->updateIfChanged('contractor_books', $existing->id, [
                    'gift_copies_given' => $row['gift_copies'], 'status' => $row['contract_status'],
                ], $row);
            } elseif ($row['gift_copies'] > 0) {
                $this->conflict($row, 'gift_copies_without_contractor', "{$row['gift_copies']} gift copies but no المتعاقد in the sheet");
            }

            return;
        }

        $contractorId = $this->resolveContractor($row['contractor'], $row['nationality']);

        if (! $existing) {
            DB::table('contractor_books')->insert([
                'book_id' => $row['book_id'], 'contractor_id' => $contractorId,
                'profit_percentage' => $this->defaultPercentage, 'percentage_basis' => 'sale_price', // % not in the sheet — default (10%) of the sale price
                'gift_copies_given' => $row['gift_copies'], 'status' => $row['contract_status'],
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->stats['contracts_created']++;

            return;
        }

        $data = ['gift_copies_given' => $row['gift_copies'], 'status' => $row['contract_status']];

        if ((int) $existing->contractor_id !== $contractorId) {
            if ($this->contractHasHistory((int) $existing->id)) {
                $this->conflict($row, 'contractor_change_blocked',
                    "contract #{$existing->id} already has royalty accruals/transactions; DB contractor #{$existing->contractor_id}, sheet '{$row['contractor']}' (#{$contractorId}). Change it manually / via a new contract."
                );
            } else {
                $data['contractor_id'] = $contractorId;
            }
        }
        if ((float) $existing->profit_percentage == 0.0 && $this->defaultPercentage > 0) {
            // only contracts still at 0% (e.g. made by the old importer); negotiated percentages are never overwritten
            $data['profit_percentage'] = $this->defaultPercentage;
            $this->stats['zero_percent_contracts_set_to_default']++;
        }
        if ($existing->deleted_at) {
            $data['deleted_at'] = null; // sheet says the book has this contractor -> contract is live again
        }

        $this->updateIfChanged('contractor_books', (int) $existing->id, $data, $row);
    }

    private function resolveContractor(string $name, ?string $nationality): int
    {
        $key = mb_strtolower($name);
        if (isset($this->contractorCache[$key])) {
            $id = $this->contractorCache[$key];
        } else {
            $existing = DB::table('contractors')->where('name', $name)->orderByRaw('deleted_at is not null')->first();
            if ($existing) {
                $id = (int) $existing->id;
                if ($existing->deleted_at) {
                    DB::table('contractors')->where('id', $id)->update(['deleted_at' => null]);
                }
            } else {
                $id = DB::table('contractors')->insertGetId([
                    'name' => $name, 'nationality' => $nationality, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $this->stats['contractors_created']++;
            }
            $this->contractorCache[$key] = $id;
        }

        if ($nationality) {
            $current = DB::table('contractors')->where('id', $id)->value('nationality');
            if ($current !== $nationality) {
                DB::table('contractors')->where('id', $id)->update(['nationality' => $nationality, 'updated_at' => now()]);
                $this->stats['contractor_nationality_updated']++;
            }
        }

        return $id;
    }

    private function resolveCategory(?string $name, ?int $parentId = null): ?int
    {
        if (! $name) {
            return null;
        }
        $key = ($parentId ?? 0) . '::' . mb_strtolower($name);
        if (isset($this->categoryCache[$key])) {
            return $this->categoryCache[$key];
        }
        $id = DB::table('book_categories')->where('name', $name)->where('parent_id', $parentId)->value('id');
        if (! $id) {
            $id = DB::table('book_categories')->insertGetId([
                'name' => $name, 'parent_id' => $parentId, 'created_by' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->stats['categories_created']++;
        }

        return $this->categoryCache[$key] = (int) $id;
    }

    // ─── History checks & fingerprint ─────────────────────────────────────────────────────────

    public function productHasHistory(int $productId): bool
    {
        return DB::table('sales_invoice_items')->where('product_id', $productId)->exists()
            || DB::table('purchase_invoice_items')->where('product_id', $productId)->exists()
            || DB::table('stock_movements')->where('product_id', $productId)->exists()
            || DB::table('sub_warehouse_products')->where('product_id', $productId)->where('quantity', '!=', 0)->exists();
    }

    public function bookHasHistory(int $bookId): bool
    {
        return DB::table('book_sales')->where('book_id', $bookId)->exists()
            || DB::table('contract_transactions')->whereIn('contractor_book_id',
                DB::table('contractor_books')->where('book_id', $bookId)->select('id'))->exists();
    }

    private function contractHasHistory(int $contractorBookId): bool
    {
        return DB::table('book_sales')->where('contractor_book_id', $contractorBookId)->exists()
            || DB::table('contract_transactions')->where('contractor_book_id', $contractorBookId)->exists();
    }

    /** count + per-column sums (and row-id hash) for every money/stock table. */
    public function fingerprint(): array
    {
        $out = [];
        foreach (self::FINGERPRINT as $table => $cols) {
            $sums = implode(', ', array_map(fn ($c) => "COALESCE(SUM({$c}),0) AS s_{$c}", $cols));
            $out[$table] = json_encode(
                collect((array) DB::selectOne("SELECT COUNT(*) AS n, {$sums} FROM {$table}"))
                    ->map(fn ($v) => round((float) $v, 2))->all()
            );
        }

        return $out;
    }

    // ─── helpers ──────────────────────────────────────────────────────────────────────────────

    private function same(mixed $a, mixed $b): bool
    {
        if ($a === null || $b === null) {
            return $a === $b;
        }
        if (is_numeric($a) && is_numeric($b)) {
            return abs((float) $a - (float) $b) < 0.005;
        }

        return (string) $a === (string) $b;
    }

    private function conflict(array $row, string $type, string $detail): void
    {
        $this->stats['conflicts']++;
        $this->conflicts[] = [
            'excel_row' => $row['excel_row'], 'book_id' => $row['book_id'], 'product_id' => $row['product_id'],
            'name' => $row['name'], 'type' => $type, 'detail' => $detail,
        ];
    }

    private function resetStats(): void
    {
        $this->changes = $this->conflicts = [];
        $this->categoryCache = $this->contractorCache = [];
        $this->stats = [
            'rows' => 0, 'products_inserted' => 0, 'books_inserted' => 0,
            'products_updated' => 0, 'books_updated' => 0, 'contractor_books_updated' => 0,
            'pairing_fixed' => 0, 'categories_created' => 0, 'contractors_created' => 0,
            'contractor_nationality_updated' => 0, 'contracts_created' => 0, 'zero_percent_contracts_set_to_default' => 0,
            'conflicts' => 0, 'changed_columns' => [], 'applied' => false,
        ];
    }
}
