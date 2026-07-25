# Author → Contractor Migration: Impact Analysis & Implementation Plan

> Status: **All phases (0–8) implemented** on branch `claude/product-contractor-refactor` (PR #19),
> including the destructive Phase 8 (Author/Contract deletion + table drop + the
> ContractorTransaction → ContractTransaction rename). Verified by actually running the full
> migration chain (additive schema → data migration → destructive drop) against a seeded SQLite
> database end-to-end, not just reviewed statically. Not done: full rewrite of
> `Modules/Product/README.md`/`docs/README.md`/`docs/database-documentation.md`/
> `docs/finance-product-warehouse-architecture.md` prose (deprioritized in favor of code
> correctness given the size of this change — flagged so it isn't mistaken for finished).
>
> **Revision 2** — updates §1/§2/§4/§5/§7/§9 to reflect four business-requirement changes made
> after the original plan (§ "Revision 2 summary" immediately below). Sections not called out
> there are unchanged from Revision 1. Findings are backed by a full-repository scan (every
> module, `app/`, `resources/`, `routes/`, `tests/`, `database/`, `docs/`).

## Revision 2 summary

1. **Contract-level financial transactions are staying**, generalized from author-specific
   installments to a neutral `ContractTransaction` model (typed: `publishing_fee`,
   `royalty_payment`, `advance_payment`, `refund`, `adjustment`), linked to `contractor_books`.
   This resolves former open question §9.3 ("no payout tracking") — payout tracking is back, just
   redesigned.
2. **Every `ContractTransaction` must be backed by exactly one real accounting voucher** — a
   `ReceiptVoucher` (money **in**, from the contractor) or a `PaymentVoucher` (money **out**, to
   the contractor), never both, never neither. Enforced in the service layer and, where the DB
   driver supports it, with a `CHECK` constraint.
3. **Both money directions are first-class**, not just the "publisher pays the author" case the
   old system assumed — a contractor paying the publishing house (e.g. a `publishing_fee`) is
   equally supported.
4. **`book_sales` is one row per invoice line item** (confirmed, matches the Revision 1 default)
   **plus an explicit `quantity` column** — the profit formula now multiplies by quantity instead
   of assuming 1.
5. **Migration representative rule clarified**: use the contract's `is_representative` author if
   one exists, else the first linked author — purely temporary until the Excel import's `المتعاقد`
   column supplies the real contractor.

---

## 1. Business change recap

| | Today (`Author`) | Tomorrow (`Contractor`) |
|---|---|---|
| Nature | Real business entity — receives royalty payments, owns contracts | Plain metadata on a book, stored as free text |
| Storage | `authors` table, `Author` model | `books.authors` TEXT column (comma-separated names) |
| Legal party | Assumed to be the author | A separate, explicit `Contractor` (may or may not also be an author name) |
| Contract ownership | `author_book_contracts` ↔ N authors via `contract_authors` pivot | `contractor_books`: **exactly one** contractor per book |
| Royalty tracking | `author_contract_transactions` (manual installments against `contract_price`, no real accounting backing) | `book_sales` (automatic, per-sale accrual — "what's owed") **+** `contract_transactions` (manual, typed, voucher-backed — "what's actually moved"), scoped to `contractor_books` |
| Gifts | Inferred after the fact from `discount_amount >= unit_price * quantity` | Explicit `book_sales.is_gift` flag, set at creation time by a dedicated "Add Gift" flow |
| Reports/search/exports | All keyed on `Author`/`Contract` | All keyed on `Contractor`/`ContractorBook`/`BookSale`; books searchable by free-text `authors` |

---

## 2. New schema (as specified, with concrete types)

```
contractors
  id
  party_id            FK -> parties, nullable
  name                string
  phone               string, nullable
  secondary_phone     string, nullable
  email               string, nullable
  secondary_email     string, nullable
  nationality         string, nullable
  address             text, nullable
  national_id_file    string, nullable   -- storage path, like authors.id_image today
  timestamps
  softDeletes

contractor_books                                   -- exactly ONE contractor per book (unique on book_id)
  id
  book_id             FK -> books, unique
  contractor_id       FK -> contractors
  contract_file       string, nullable
  profit_percentage   decimal(5,2)
  percentage_basis    enum('base_price','sale_price')
  contract_date       date, nullable
  end_contract_date   date, nullable
  timestamps
  softDeletes

book_sales                                         -- immutable royalty accrual, one row per invoice line item
  id
  book_id                 FK -> books
  contractor_book_id      FK -> contractor_books
  invoice_id              FK -> sales_invoices
  invoice_item_id         FK -> sales_invoice_items
  quantity                integer                        -- [Rev.2] snapshot of the line's quantity
  sale_price_snapshot     decimal(10,2), nullable
  base_price_snapshot     decimal(10,2), nullable
  percentage_snapshot     decimal(5,2)
  percentage_basis_snapshot enum('base_price','sale_price')
  contractor_profit       decimal(12,2)                  -- already accounts for `quantity` (see §4.2)
  is_gift                 boolean, default false
  timestamps
  -- no softDeletes (per spec) — immutable, append-only history

contract_transactions                              -- [Rev.2] neutral, accounting-backed contract-level ledger
  id
  contractor_book_id      FK -> contractor_books
  type                    enum('publishing_fee','royalty_payment','advance_payment','refund','adjustment')
  amount                  decimal(12,2)
  transaction_date        date
  notes                   text, nullable
  receipt_file            string, nullable   -- supporting document (mirrors old author_contract_transactions.receipt_file)
  receipt_voucher_id      FK -> receipt_vouchers, nullable, unique   -- set when money came IN from the contractor
  payment_voucher_id      FK -> payment_vouchers, nullable, unique   -- set when money went OUT to the contractor
  created_by/edited_by    FK -> users, nullable
  timestamps
  -- CHECK (exactly one of receipt_voucher_id / payment_voucher_id is set), applied where the DB driver supports it

books  (ALTER, additive)
  + authors            text, nullable      -- "Stephen Hawking, Leonard Mlodinow"
  + supervisor         string, nullable    -- اشراف
  + introduction_by     string, nullable    -- تقديم
```

`percentage_basis` replaces the boolean the spec calls `is_on_base_price` — **confirmed by repo-wide
grep that this boolean does not exist anywhere in the current codebase**, so there is nothing to
rename; we're introducing `percentage_basis` fresh on `contractor_books` (and snapshotting it per
sale on `book_sales`).

### 2.1 `book_sales` granularity — resolved
One row per invoice line item (§9 Revision-1 open question #1), now with an explicit `quantity`
column added per Revision 2 so the profit figure is fully reconstructable without re-reading the
(possibly since-modified) `sales_invoice_items` row.

### 2.2 Naming collision: `ContractTransaction` already exists (temporary resolution)
The **old**, still-active `Modules\Product\Models\ContractTransaction` (table
`author_contract_transactions`) keeps running until Phase 8 deletes it, per the "old system stays
fully functional through Phase 7" guarantee. The **new**, unrelated model the business wants named
`ContractTransaction` (table `contract_transactions`) cannot share that exact class name while both
exist side by side. Resolution: the new model is implemented now as
**`Modules\Product\Models\ContractorTransaction`** (table name is already the correct
`contract_transactions` — only the *class* differs), and gets a pure rename to `ContractTransaction`
in Phase 8 once the legacy class of that name is deleted. This is a mechanical, logic-free rename
tracked as a Phase 8 checklist item — flagging it here so it's not a surprise later.

---

## 3. Complete file-level impact inventory

### 3.1 Delete completely

**Models**
- `Modules/Product/app/Models/Author.php`
- `Modules/Product/app/Models/Contract.php`
- `Modules/Product/app/Models/ContractTransaction.php`

**Controllers**
- `Modules/Product/app/Http/Controllers/AuthorController.php`
- `Modules/Product/app/Http/Controllers/ContractController.php`
- `Modules/Product/app/Http/Controllers/TransactionController.php`

**Services**
- `Modules/Product/app/Services/AuthorService.php`
- `Modules/Product/app/Services/ContractService.php`

**Requests**
- `StoreAuthorRequest.php`, `UpdateAuthorRequest.php`, `StoreContractRequest.php`,
  `UpdateContractRequest.php`, `StoreTransactionRequest.php`, `UpdateTransactionRequest.php`
  (all in `Modules/Product/app/Http/Requests/`)

**Views** (entire directories)
- `Modules/Product/resources/views/authors/*`
- `Modules/Product/resources/views/contracts/*`
- `Modules/Product/resources/views/transactions/*`

**Lang files**
- `resources/lang/{en,ar}/author.php`, `contract.php`, `transaction.php` (both locales, under
  `Modules/Product/resources/lang/`)

**Exports**
- `Modules/Product/app/Exports/AuthorsFinancialExport.php` — replaced by a Contractor equivalent
  (§3.2)

**Routes**
- The entire `product.authors.*`, `product.contracts.*`, `product.transactions.*` groups in
  `Modules/Product/routes/web.php`

**Console commands**
- `Modules/Product/app/Console/SplitMultiAuthorsCommand.php` — its job is superseded/finished by
  the new one-time Contractor data migration (§5); keep it available in git history, delete from
  the active codebase.
- `app/Console/Commands/ImportBooksFromExcel.php` — already broken against the current schema
  (writes `books.author_id`, which doesn't exist); hard-`use`s `Modules\Product\Models\Author`, so
  it would be a fatal class-not-found the moment `Author` is deleted. Delete.

**Seeders**
- `Modules/Product/database/seeders/CreateContractsFromBooksSeeder.php` — historical, already
  served its one-time purpose pre-pivot-migration. Delete.

**Tests**
- `Modules/Product/tests/Unit/AuthorTest.php`, `ContractTest.php`, `ContractTransactionTest.php`,
  `BookTest.php`, `Modules/Product/tests/Feature/ContractPaymentFeatureTest.php`,
  `ProductModuleFeatureTest.php` — **all six are already broken against the current (pivot) schema**
  (they construct `Book`/`Contract` with a nonexistent `author_id` column and assert a nonexistent
  `->author` relation) — confirmed by two independent scans. Delete and replace with new tests for
  Contractor/ContractorBook/BookSale (§7).

### 3.2 Create

- `Modules/Product/app/Models/Contractor.php`
- `Modules/Product/app/Models/ContractorBook.php`
- `Modules/Product/app/Models/BookSale.php`
- `Modules/Product/app/Models/ContractorTransaction.php` **[Rev.2]** — temporary name for the new
  `ContractTransaction`, see §2.2; table is already `contract_transactions`.
- `Modules/Product/app/Http/Controllers/ContractorController.php` (CRUD + `addGift()` action)
- `Modules/Product/app/Services/ContractorService.php` (CRUD, `ensureParty()` — shared by gift flow
  and transactions per §4.2b, stats)
- `Modules/Product/app/Services/BookSaleService.php` (royalty accrual calc, called from Finance)
- `Modules/Product/app/Services/ContractorGiftService.php` (the gift flow, §4.4)
- `Modules/Product/app/Services/ContractTransactionService.php` **[Rev.2]** (§4.2b — voucher-backed
  contract transactions, reuses `ReceiptVoucherService`/`PaymentVoucherService`)
- `Modules/Product/app/Http/Requests/{Store,Update}ContractorRequest.php`,
  `{Store,Update}ContractorBookRequest.php`, `StoreContractorGiftRequest.php`,
  `StoreContractTransactionRequest.php` **[Rev.2]** (validates `direction` + type-appropriate voucher
  fields)
- `Modules/Product/app/Exports/ContractorsFinancialExport.php` (replaces `AuthorsFinancialExport`)
- `Modules/Product/resources/views/contractors/{index,create,edit,show}.blade.php` + a
  `contractors/partials/gift-modal.blade.php`
- `Modules/Product/resources/lang/{en,ar}/contractor.php`
- Database migrations (§5) for `contractors`, `contractor_books`, `book_sales`, and the `books`
  column additions
- New Product tests: `ContractorTest.php`, `ContractorBookTest.php`, `BookSaleTest.php`,
  `ContractorGiftFeatureTest.php`, `SalesInvoiceBookSaleFeatureTest.php` (this last one lives
  conceptually at the Finance↔Product boundary — see §4.3)

### 3.3 Modify

| File | Change |
|---|---|
| `Modules/Product/app/Models/Book.php` | Remove `contract()`, `getAuthorsAttribute()`, `getAuthorsNamesAttribute()`. Add plain `authors`/`supervisor`/`introduction_by` to `$fillable`. Add `contractorBook(): hasOne(ContractorBook)` and a `contractor()` "through" accessor. |
| `Modules/Product/app/Http/Controllers/BookController.php` | Replace all `contract.authors` eager-loads/filters with plain `authors` text search (`LIKE`) and `contractorBook.contractor` eager-load/filter. Add `supervisor`/`introduction_by` to store/update payload. Replace the "cannot delete: has contract" guard with "cannot delete: has contractor_book/book_sales". |
| `Modules/Product/app/Http/Requests/{Store,Update}BookRequest.php` | Drop the vestigial `author_id` rule (already unused). Add `authors` (string), `supervisor`, `introduction_by` validation. |
| `Modules/Product/resources/views/books/{index,create,edit,show}.blade.php` | Replace the Author Select2 field with a plain text `authors` input, add `supervisor`/`introduction_by` fields, replace the "Contract Summary" card with a "Contractor & Royalty" card (contractor name/percentage/basis/dates/file), fix the dead `@if($book->author)` block in `show.blade.php` (~line 175) as part of this rewrite rather than patching it separately. |
| `Modules/Product/app/Exports/BooksExport.php` | Replace the `contract_authors ⋈ author_book_contracts ⋈ authors` join with a plain `books.authors` read; drop the dynamic "Author 1..N" columns in favor of the new fixed column set (§6); add `اشراف/Supervisor`, `تقديم/Introduction By`, `المتعاقد/Contractor`, `الجنسية/Nationality`, `الكمية/Quantity` columns. |
| `Modules/Product/app/Imports/BooksImport.php`, `app/Console/ImportBooksCommand.php` | Rewritten per §6 — resolve-or-create `Contractor` instead of `Author`, write `contractor_books` instead of `author_book_contracts`/`contract_authors`, write `books.authors/supervisor/introduction_by` as plain columns, update `sub_warehouse_products.quantity` from the new "الكمية" column instead of a hardcoded 100. |
| `Modules/Product/app/Providers/ProductServiceProvider.php` | Update `registerCommands()`: drop `SplitMultiAuthorsCommand`, keep `ImportBooksCommand` (rewritten). |
| `Modules/Product/routes/web.php` | Remove authors/contracts/transactions groups, add `product.contractors.*` (resource + `POST /contractors/{contractor}/gift`). |
| `Modules/Finance/app/Http/Controllers/SalesInvoiceController.php` | Remove `use Modules\Product\Models\Author;` and the `Author::whereHas('contracts.book')` filter dropdown → replace with a plain "search by author name" text filter over `books.authors` (no more enumerable dropdown, since authors are free text now). Remove the two dead `author_id`/`author_name` blocks (already always-null) and replace with real data: `book.authors` (text) + `book.contractorBook.contractor.name`. This directly fixes a pre-existing bug flagged in `docs/finance-product-warehouse-architecture.md` §5.4. Also wire in `BookSaleService` call after invoice creation (§4.3). |
| `Modules/Finance/app/Http/Controllers/PurchaseInvoiceController.php` | Same `Author` import/dropdown/dead-field cleanup as above (no BookSale wiring needed — purchases aren't sales). |
| `Modules/Finance/app/Services/SalesInvoiceService.php` | After item + stock movement creation, call `BookSaleService::recordSale()` per item (§4.3). |
| `Modules/Finance/resources/views/sales-invoices/{create,edit}.blade.php`, `purchase-invoices/create.blade.php` | Replace `authorFilter`/`product.author_id`/`product.author_name` JS wiring with a free-text author search + `product.contractor_name` display. While here, fix the pre-existing missing `finance::purchase.author`/`all_authors` lang keys (they're referenced but never defined) as part of the same edit. |
| `Modules/Finance/resources/lang/{en,ar}/invoice.php`, `purchase.php` | Remove/repurpose `author`/`all_authors` keys for the new free-text filter; add `contractor` key. |
| `Modules/Warehouse/app/Http/Controllers/StockMovementController.php` | Fix `searchProducts()`'s dead `'author' => $product->book->author ...` block → `$product->book->authors` (text) + `contractorBook.contractor.full_name`... i.e. `->name` (see §9 naming note). Update the `product.book.contract.authors` eager-load to `product.book.contractorBook.contractor`. |
| `Modules/Warehouse/app/Http/Controllers/SubWarehouseController.php` | Same eager-load swap (`book.contract.authors` → `book.contractorBook.contractor`) in `show()`/`editStock()`. |
| `Modules/Warehouse/resources/views/sub_warehouses/{edit_stock,show}.blade.php`, `stock_movements/show.blade.php` | Fix the dead `$...->product->book->author` blocks (already broken today per the scan) → `$...->product->book->authors` (plain text) + contractor name. |
| `Modules/Warehouse/resources/views/sub_warehouses/add_stock.blade.php`, `stock_movements/create.blade.php` | Fix the client-side JS reading `book.contract.authors?.full_name` (already logically wrong today — treats a collection as a scalar) → read the new plain `book.authors` string. |
| `Modules/Warehouse/resources/lang/{en,ar}/sub_warehouse.php` | Keep the `author` label key (now labels the free-text field), add `contractor`. |
| `Modules/SearchDrawer/app/Http/Controllers/Api/ProductSearchController.php` | Remove the `author_id` filter (`whereHas('book', fn($q) => $q->where('author_id', ...))`) — this is a **live SQL-error bug today**, not just dead code, since `books.author_id` no longer exists; replace with a free-text `authors` search merged into the existing title/sku/isbn search. Fix the dead `author_id`/`author_name` output fields → `book.authors` + `contractorBook.contractor.name`. Update the `book.contract.authors` eager-load. |
| `Modules/SearchDrawer/routes/web.php` | Remove the now-pointless `$authors = Author::all();` line (confirmed unused by the view). |
| `app/Http/Controllers/SearchSelectController.php` | Remove `author_id` from the `books` resource's `filters` map — this is a **live SQL-error bug today** if ever triggered (`books.author_id` doesn't exist). Replace with an `authors` text-search filter if that resource is still meant to support author filtering. |
| `resources/views/layouts/dashboard.blade.php` | Repoint the sidebar nav link (2 occurrences) from `route('product.authors.index')` to `route('product.contractors.index')`; update `sidebar.authors` lang key/label to `sidebar.contractors`. |
| `resources/lang/{en,ar}/sidebar.php` | Rename `authors` key → `contractors` (or add new key and drop old). |
| `Modules/Product/README.md`, `Modules/Product/docs/README.md` | Full rewrite for the new schema (both are already stale against the *current* pivot schema, let alone the new one — rewrite once, from scratch, post-implementation). |
| `docs/database-documentation.md` (+ its `public/` mirror), `docs/finance-product-warehouse-architecture.md` (+ `public/` mirror where applicable) | Update the Product-module sections to describe Contractor/ContractorBook/BookSale instead of Author/Contract; the architecture doc's §2, §5.4 (the whole "dangling `Book::author()`" section becomes moot/resolved) and its issue list need updating post-migration. |

### 3.4 Already-broken code this refactor sweeps up "for free"

These are pre-existing bugs, unrelated in origin to this refactor, but they sit exactly on files
this refactor must touch anyway (per the "no dead code, nothing references removed tables"
requirement), so fixing them here costs nothing extra and is the safer choice vs. leaving stale
`Author`/`Contract` references that will hard-crash once those classes are deleted:

1. `Modules/SearchDrawer/.../ProductSearchController.php` — `author_id` filter throws a real SQL
   error today; `TransactionController` (being deleted anyway) reads a non-existent
   `Contract::author()`; `app/Console/Commands/ImportBooksFromExcel.php` would fatal on
   `Author`-class-not-found the moment we delete the model — must delete or it breaks the app
   outright (it's already functionally dead — writes to a column that doesn't exist).
2. `app/Http/Controllers/SearchSelectController.php`'s `author_id` filter — same SQL-error risk.
3. Missing `finance::purchase.author`/`all_authors` lang keys — fixed incidentally while reworking
   that same filter UI.

---

## 4. New business logic

### 4.1 Contractor & ContractorBook
- A `Contractor` is a standalone entity, optionally linked to a `Party` (nullable `party_id`,
  mirroring today's `Author.party_id` bridge pattern) — but unlike today, the link is created
  automatically the first time it's needed (gift flow, §4.4) rather than via a manual
  "Register as Client" button, since spec says *"If Contractor has no Party → Create one
  automatically."* I'll keep a manual "convert to client" action too for parity with today's UX
  where an admin wants to invoice a contractor directly (not just gift them).
- `ContractorBook` enforces **exactly one contractor per book** via a unique index on `book_id` —
  no pivot table, a plain `belongsTo`/`hasOne` pair (`Book::contractorBook()`,
  `ContractorBook::book()`, `ContractorBook::contractor()`, `Contractor::contractorBooks()`).
- Deleting a `Contractor` who still has `contractor_books` is blocked (mirrors today's
  `AuthorService::deleteAuthor()` guard), same for deleting a `Book`/`ContractorBook` that has
  `book_sales` or `contract_transactions`.
- **[Rev.2]** The finance design treats both directions as equally normal — a contractor is not
  assumed to be strictly a payee or strictly a payer. `ContractTransactionService` (§4.2b) takes an
  explicit `direction` rather than inferring one from `type`, so the UI must let the user pick
  "received from contractor" vs. "paid to contractor" for every transaction regardless of type.

### 4.2 BookSale — royalty accrual snapshot
Created automatically (never manually) whenever a book is sold, one row per invoice line item.
Formula, updated in Revision 2 to account for `quantity` (single source of truth:
`ContractorBook::calculateProfit()`):
```
unit_amount = (percentage_basis == 'base_price') ? base_price_snapshot : sale_price_snapshot
contractor_profit = round(unit_amount * quantity * (percentage_snapshot / 100), 2)
```
`base_price_snapshot` = the book's `products.base_price` at sale time; `sale_price_snapshot` = the
actual `sales_invoice_items.unit_price` for that line; `quantity` = the line's quantity. All are
stored so the calculation is fully reconstructable and auditable without re-reading
`products`/`contractor_books`, which may have changed since. **Never recalculated after creation**
— if a contract's percentage changes later, it only affects *future* sales.

### 4.2b ContractTransaction — accounting-backed, contract-level ledger [Rev.2]
Distinct from `BookSale` (automatic, per-copy-sold *accrual* of what's owed) —
`ContractTransaction` (implemented for now as `ContractorTransaction`, see §2.2) is the *manual*,
typed record of money actually moving, scoped to one `contractor_books` row:

- **Types**: `publishing_fee`, `royalty_payment`, `advance_payment`, `refund`, `adjustment`. The
  type is a category label — it does **not** encode direction. Direction is derived purely from
  which voucher is attached, via a `direction` accessor (`'in'` when `receipt_voucher_id` is set,
  `'out'` when `payment_voucher_id` is set).
- **Invariant**: exactly one of `receipt_voucher_id` / `payment_voucher_id` must be set, enforced
  three ways (defense in depth): (1) a `saving` model event on `ContractorTransaction` that throws
  if the invariant doesn't hold — this is what actually protects every write path the app itself
  uses, (2) `ContractTransactionService` never constructs the model directly without going through
  its voucher-creation step first, (3) a DB `CHECK` constraint added via `ALTER TABLE ... ADD
  CONSTRAINT` on MySQL 8.0.16+/Postgres. **Confirmed by actually running the migration**: SQLite
  (this app's local/test driver) has no `ALTER TABLE ADD CONSTRAINT` support at all — a CHECK there
  can only be declared inside the original `CREATE TABLE`, which Laravel's schema builder has no
  fluent API for — so on SQLite the invariant is enforced by the model guard alone. Tests for this
  invariant must go through the Eloquent model, not raw inserts.
- **Service flow** (`ContractTransactionService::record(ContractorBook $contractorBook, array $data)`,
  `$data` includes `type`, `amount`, `transaction_date`, `notes`, `direction` (`'receipt'|'payment'`),
  plus the usual voucher fields — `account_id`, `payment_method`, etc.):
  1. Ensure the contractor has a `party_id`, auto-creating one exactly like the gift flow does
     (§4.4) — **this "ensure party" step is extracted into one shared helper**
     (`ContractorService::ensureParty()`) reused by both the gift flow and this service, rather
     than duplicated (the old codebase's `Author`/`AuthorService` had this exact kind of
     duplication with its gift-copy heuristic — not repeating that here).
  2. `direction === 'receipt'` → `ReceiptVoucherService::createReceipt([...party_id, account_id,
     amount, voucher_date, payment_method, description])` (money **in**, the common case: the
     contractor paying the publishing house, e.g. a `publishing_fee`).
     `direction === 'payment'` → `PaymentVoucherService::createPayment([...])` (money **out**, e.g.
     `royalty_payment`/`advance_payment`, or a `refund` back to the contractor).
     Both existing services are reused as-is — no new accounting workflow, same principle already
     applied to the gift flow.
  3. Create the `ContractorTransaction` row with the resulting `receipt_voucher_id` or
     `payment_voucher_id` set, wrapped in one `DB::transaction()`.
- **Deletion**: deletes the transaction's dedicated voucher too (via
  `ReceiptVoucherService::deleteReceipt()`/`PaymentVoucherService::deletePayment()`), since that
  voucher exists solely to back this transaction (enforced 1:1 by the unique indexes on
  `receipt_voucher_id`/`payment_voucher_id`) — never a bare orphaned voucher left behind.
- **Reporting**: a contractor's outstanding royalty balance becomes
  `SUM(book_sales.contractor_profit WHERE is_gift=false) - SUM(contract_transactions.amount WHERE type='royalty_payment' AND direction='out')`
  — "what's owed" (automatic accrual) vs. "what's been paid" (manual, voucher-backed), directly
  resolving Revision 1's open question §9.3.

### 4.3 Sales invoice integration
`SalesInvoiceService::createInvoice()` (and nowhere else) is the single place `BookSale` rows get
created, immediately after each `SalesInvoiceItem` is persisted:
```
foreach ($items as $itemData) {
    ... existing item + stock movement logic (unchanged) ...
    $bookSaleService->recordSale($item, $product, isGift: false);
}
```
`BookSaleService::recordSale()`:
1. Resolve `$book = $product->book` — if the product isn't a book (or has no `contractorBook`), do
   nothing (no royalty applies to non-book products — mirrors today's implicit assumption).
2. Look up `$book->contractorBook` — if none exists, **log and skip** (a book can be sold without a
   contractor on file, e.g. legacy stock); this must not block the sale.
3. Snapshot `sale_price_snapshot` (= item unit_price), `base_price_snapshot` (= product base_price),
   `percentage_snapshot`/`percentage_basis_snapshot` (= from `contractor_book`), compute
   `contractor_profit` per §4.2, create the `BookSale` row.
`updateInvoice()` needs the equivalent of what it already does for stock movements: delete the old
invoice's `BookSale` rows and recreate them for the new item set (mirrors the existing
delete-and-reinsert pattern for `StockMovement` in that same method) — **except** rows created via
the gift flow are excluded from recalculation triggers since gifts don't get edited through the
normal invoice-edit screen in the same way (see open question in §9 about editing gift invoices).

### 4.4 Contractor gift flow
New "Add Gift" button on the Contractor show page → modal listing only books belonging to that
contractor (`contractor->contractorBooks->book`), with a quantity input per selected book. On
submit, `ContractorGiftService::createGift(Contractor $contractor, array $lines)`:
1. `party = $contractor->party_id ? Party::find(...) : PartyService::createParty([...from contractor...])`,
   persist `contractor->party_id` if newly created (same pattern as today's
   `AuthorController::registerAsClient`).
2. Build a `SalesInvoiceService::createInvoice()` payload: `party_id`, today's date, one item per
   selected book (`product_id` = the book's product, `quantity`, `unit_price` = current
   `base_price`, `discount_amount` = `unit_price * quantity` → **100% discount**, so
   `line_total = 0` and `total_amount = 0`), plus a flag/marker so `BookSaleService::recordSale()`
   is called with `isGift: true` for these items instead of the default `false`.
3. **Reuses `SalesInvoiceService::createInvoice()` verbatim** for invoice creation, item creation,
   and stock deduction (per spec: "Do NOT create a separate accounting workflow") — the only
   addition is threading an `is_gift` flag through to the `BookSale` step. Concretely: extend
   `createInvoice(array $data)`'s item loop to accept an optional `is_gift` per item (default
   `false`), so both the normal invoice-create controller and `ContractorGiftService` call the
   same method.
4. When `is_gift = true`: `BookSale.contractor_profit = 0`, `BookSale.is_gift = true` regardless of
   the formula in §4.2 (gifts never generate royalty).
5. Gift books are excluded from royalty sums (`WHERE is_gift = false` in all royalty
   aggregations) but included in a separate "gift copies" stat on the Contractor show page (mirrors
   today's `gift_copies_count` on the Author show page, now backed by a real flag instead of a
   discount-amount heuristic).

### 4.5 Search
`Book`/`Product` search (Product module's own book list, Finance's invoice item pickers,
Warehouse's product search, SearchDrawer) all need the same three-part predicate:
`title LIKE / books.authors LIKE / books.isbn LIKE / products.sku LIKE / contractorBook.contractor.name LIKE`.
I'll centralize this as a single reusable scope (`Book::scopeSearch()`) rather than re-implementing
the same `orWhere`/`whereHas` chain in four different controllers (today's code already duplicates
near-identical search blocks across `BookController`, `SalesInvoiceController`,
`PurchaseInvoiceController`, and `ProductSearchController` — worth fixing once here rather than
copy-pasting a fifth variant).

### 4.6 Reports/dashboard
Every place currently computing `Author::count()`/`Contract::sum('contract_price')`/
`Contract::all()->sum('total_paid')` (found in `AuthorController::index`,
`ContractController::index/show`, `Author::$appends`) gets a like-for-like Contractor/ContractorBook/
BookSale/ContractTransaction replacement:
- `total_contractors`, `total_contracted_books` → straightforward counts.
- **`total_royalty_accrued`** → `SUM(book_sales.contractor_profit) WHERE is_gift = false`
  (automatic, "what's owed").
- **`total_royalty_paid`** → `SUM(contract_transactions.amount) WHERE type = 'royalty_payment' AND
  direction = 'out'` (manual, voucher-backed, "what's actually been paid" — see §4.2b).
- **`outstanding_royalty`** → the difference between the two above, replacing the old
  `Contract::outstanding_balance` concept, now computed per-contractor rather than per-contract.

---

## 5. Data migration plan (production-safe, no data loss)

All steps implemented as ordered Laravel migrations (query-builder only, no raw SQL), each
idempotent and defensive (skip + log rather than throw on a per-row basis, matching the
existing codebase convention in `BooksImport`/`SplitMultiAuthorsCommand`). Order matters — each
step's migration only runs after the previous one is confirmed:

1. **Schema-additive migrations** (safe, reversible): create `contractors`, `contractor_books`,
   `book_sales`; add `authors`/`supervisor`/`introduction_by` to `books`. No data touched yet.
2. **Create Contractors from Authors.** For every row in `authors`, create one `contractors` row,
   copying `full_name→name, phone_number→phone, email→email, nationality, address, id_image→national_id_file`
   (`secondary_phone`/`secondary_email` have no source data — left null). Keep an
   `authors.id → contractors.id` in-memory/temp map for step 3.
3. **Populate `books.authors`.** For every book, join through the *old* `contract_authors` /
   `author_book_contracts` pivot (still present at this point — not yet dropped) ordered by
   `is_representative DESC, contract_authors.id`, and set `books.authors` to the comma-joined
   `full_name` list. Books with no contract at all get `null`.
4. **Assign temporary Contractors** (rule clarified in Revision 2). For every
   `author_book_contracts` row with `book_id` set, pick the contractor-to-be as:
   **the author flagged `is_representative = true` in `contract_authors`, if one exists — else the
   first linked author** (lowest `contract_authors.id`). This is purely a temporary bridge; it is
   explicitly superseded per-book the moment the master Excel import (§6) supplies a real
   `المتعاقد` value for that book. Create one `contractor_books` row: `book_id`, `contractor_id`
   (mapped from step 2), `contract_file`, `profit_percentage = percentage_from_book_profit`,
   `percentage_basis = 'sale_price'` (the closest current equivalent — the old system had no basis
   concept; flagged in §9 for confirmation), `contract_date`, `end_contract_date = null`.
   Contracts with `book_id = null` (i.e. only a free-text `book_name`, no real book row) **cannot**
   produce a `contractor_books` row (there's no book to attach to) — log these explicitly as
   "orphaned contract, contractor created but not linked to a book" and continue; nothing is lost,
   they just don't get a `contractor_books` row.
5. **Generate historical BookSales.** For every existing `sales_invoice_items` row whose
   `product_id` maps to a book with a `contractor_books` row, create one `book_sales` row (with
   `quantity` = the item's quantity, per Revision 2) using §4.2's formula with **today's**
   `contractor_books.profit_percentage`/`percentage_basis` as the best available snapshot (the true
   historical percentage at time of sale isn't recoverable from existing data — flagged in §9).
   `is_gift` backfilled using the **existing** discount heuristic
   (`discount_amount >= unit_price * quantity`) — this is explicitly a one-time best-effort
   backfill, not a live rule going forward. Items whose book has **no** `contractor_books` row: log
   "no contractor on file, skipping BookSale" and continue — per spec, do not block migration.
   **Sales invoices, invoice items, payment vouchers, warehouse stock/movements are never modified
   in this step** — purely additive `INSERT`s into `book_sales`.
6. **[Rev.2] Migrate historical author payments into `contract_transactions`.** For every existing
   `author_contract_transactions` row (`amount`, `payment_date`, `notes`, `receipt_file`) whose
   contract has a `contractor_books` row from step 4:
   - Ensure the contractor has a `party_id` (auto-create a `Party`, same rule as §4.2b/§4.4) —
     needed because every `ContractTransaction` requires a real voucher, and every voucher requires
     a real `party_id`.
   - **Problem surfaced by this requirement**: `payment_vouchers`/`receipt_vouchers` also require a
     real, non-null `account_id` (confirmed against the actual migrations — `accounts.id` is
     `NOT NULL` on both voucher tables), but the old author-payment flow never recorded which
     cash/bank account the money moved through. **Decision**: create one clearly-labeled placeholder
     `Account` (`account_name = 'Legacy Contractor Payments (Migration)'`, `account_type = 'cash'`,
     `opening_balance = 0`) used **only** to attribute these backfilled historical payments, so real
     account balances are never silently corrupted with invented data. This is called out explicitly
     here (not buried in code) — the finance team should be aware historical author-payment figures
     live in this bucket, not a real bank/cash account, until/unless reconciled by hand.
   - For each old transaction: create a `PaymentVoucher` (`party_id`, `account_id` = the legacy
     account above, `amount`, `voucher_date = payment_date`, `payment_method = 'cash'`,
     `description` referencing the original record), then create one `contract_transactions` row
     (`contractor_book_id`, `type = 'royalty_payment'`, `amount`, `transaction_date = payment_date`,
     `notes`, `payment_voucher_id` = the voucher just created). Old transactions whose contract has
     **no** `contractor_books` row (i.e. the book itself was never linked, per step 4's log) are
     logged and skipped — not silently dropped, not blocking the batch.
   - `author_contract_transactions.receipt_file` (a stored proof-of-payment document) is copied
     as-is into the new `contract_transactions.receipt_file` column (added to the schema in §2 for
     exactly this — a real, downloadable attachment, not a path buried in `notes`).
7. **Drop old tables**, only as the final migration in the batch: `contract_authors`,
   `author_contract_transactions`, `author_book_contracts`, `authors`. This migration's `down()`
   cannot fully restore step 2-6's derived data (that's expected/acceptable for a forward-only
   business migration — the `down()` will restore the table *shapes* from the original creation
   migrations, but not attempt to reverse-derive Contractor data back into them).

Every step above is idempotent (re-running the migration/command a second time is a no-op or picks
up only newly-added rows) and log-driven — nothing throws and aborts the whole batch on a single
bad row, per spec ("If no contract exists, log the issue, continue migration").

---

## 6. Excel importer redesign

Single new importer (retiring `BooksImport`, `ImportBooksCommand`'s current logic in favor of one
shared implementation used by both the web upload and the CLI command — currently they're two
independent, hand-duplicated copies of the same logic; consolidating avoids a third divergence).

**New header set** (superset of today's, per the given column list):
`Book ID, Product ID, Book Name, SKU, Price, Status, Description, ISBN, Pages, Cover Type,
Published Date, Language, Is Translated, Translated From, Translated To, Translator, اشراف,
تقديم, Category, Sub Category, Used in Sales, Author 1, المتعاقد, الجنسية, الكمية`.

Per-row logic changes from today's `BooksImport`:
- `اشراف`/`تقديم` → written straight to `books.supervisor`/`books.introduction_by`.
- `Author 1` → written straight to `books.authors` as-is (already comma-joined text in the source
  sheet per the existing convention — no more per-name `authors` table rows to create).
- `المتعاقد` (contractor name) → **find-or-create** `Contractor` by exact name (same convention as
  today's `resolveAuthor()`); if found, reuse (never duplicate, per spec); update/create the
  `contractor_books` row for this book (percentage/basis/dates aren't in this sheet, so they're
  left as-is if the row already exists, or created with zeroed defaults if new — matching today's
  `BooksImport` contract-creation defaults).
- `الجنسية` → sets `contractors.nationality` when creating/updating that contractor.
- `الكمية` → replaces today's hardcoded `quantity = 100`: upserts `sub_warehouse_products`
  (default sub-warehouse, same as today) to this exact quantity and logs a `stock_movements`
  row — but as an **adjustment to the real quantity**, not always-additive like today's "always
  insert 100 for new products" — since this importer is explicitly the "master Excel" reconciliation
  pass run *after* the DB migration, it should reconcile stock to match the sheet, not blindly add
  on top of whatever the earlier one-time import already inserted.

---

## 7. Testing plan
Replace the six broken Product tests (§3.1) with: `ContractorTest`, `ContractorBookTest`,
`BookSaleTest` (unit — relations, accessors, profit-calc formula for both `percentage_basis`
values **and multiple quantities**), `ContractorGiftFeatureTest` (feature — full gift flow: no-party
contractor gets a party, invoice total is zero, stock deducts, `BookSale.is_gift=true`/
`contractor_profit=0`), `SalesInvoiceBookSaleFeatureTest` (feature, at the Finance/Product boundary
— normal paid sale creates a correct non-gift `BookSale` row with the right formula applied,
including a quantity > 1 case), and **[Rev.2]** `ContractTransactionTest`/
`ContractTransactionFeatureTest` (unit — the XOR-voucher invariant rejects both-set and
neither-set; feature — `direction='receipt'` creates a `ReceiptVoucher` and auto-creates a `Party`
for a contractor that had none, `direction='payment'` creates a `PaymentVoucher`, deleting a
transaction deletes its voucher). Also add a regression test asserting `Book::scopeSearch()`
matches by title/authors-text/contractor-name/isbn/sku.

---

## 8. Phased implementation roadmap

| Phase | Contents | Risk |
|---|---|---|
| 0 | Branch/PR setup (see open question below) | none |
| 1 | Additive schema migrations: `contractors`, `contractor_books`, `book_sales` (+ `quantity`), `contract_transactions` (+ voucher-XOR check), `books` columns | low — purely additive, nothing removed yet |
| 2 | New models (`Contractor`, `ContractorBook`, `BookSale`, `ContractorTransaction`) | low |
| 3 | Data-migration migrations (§5 steps 2–6) — old tables still present and untouched, so fully re-runnable/inspectable before anything is deleted | medium — must be verified against real data before Phase 8 |
| 4 | `BookSaleService` + `SalesInvoiceService` integration (auto `BookSale` on every future sale), `ContractorService`/`ContractTransactionService`/`ContractorGiftService`, with feature tests | medium — touches the live sales path |
| 5 | Product module UI: `Book` create/edit/show/list rework, new `Contractor` CRUD screens, `Book::scopeSearch()` | low-medium |
| 6 | Cross-module cleanup: Finance invoice controllers/views, Warehouse controllers/views, SearchDrawer, root `SearchSelectController`, dashboard sidebar, lang files (§3.3/§3.4) | medium — many small edits across modules |
| 7 | Excel importer/exporter rewrite (§6), consolidating the three current import paths into one | medium |
| 8 | **Destructive step**: delete Author/Contract models/controllers/services/requests/views/lang/tests/routes (§3.1) + the final migration dropping `authors`/`author_book_contracts`/`author_contract_transactions`/`contract_authors`, **then** the mechanical rename `ContractorTransaction` → `ContractTransaction` (§2.2) now that the name is free | high — irreversible; only run after Phases 1–7 are verified end-to-end |
| 9 | Final verification pass against the checklist in §10 | — |

Phases 1–7 leave the old Author/Contract system fully intact and running side-by-side with the new
Contractor system, so the application never enters a broken intermediate state — Phase 8 is the
only genuinely irreversible step, and it's sequenced last on purpose.

---

## 9. Open questions

**Resolved in Revision 2** (kept here for the record): #1 `book_sales` granularity → one row per
line item + `quantity` column. #3 royalty-payment tracking → `ContractTransaction`, voucher-backed.
Branch/delivery-shape questions → resolved operationally (new branch `claude/product-contractor-refactor`,
PR #19, incremental commits per phase).

**Still open:**

1. **Historical royalty percentage isn't recoverable.** Step 5 of the data migration (§5) can only
   apply *today's* `contractor_books.profit_percentage`/`percentage_basis` retroactively to old
   sales, not whatever the real historical contract terms were at the time (that data was never
   captured per-sale before). Acceptable, or should historical `BookSale` rows be flagged
   differently (e.g. `is_estimated = true`) so reports can distinguish "real" from "backfilled"
   royalty figures?
2. **Contractor "gift" vs. general discounted sale to a contractor.** The spec's gift flow always
   forces 100% discount. Should a contractor ever be able to buy their own book at a *partial*
   discount through the same flow (recorded as a normal, non-gift `BookSale` with real
   `contractor_profit`), or is "Add Gift" strictly all-or-nothing and anything else goes through
   the regular sales-invoice screen against their Party?
3. **[Rev.2] `contract_transactions.contractor_book_id` is required (not nullable).** Every
   transaction must be tied to one specific book contract — there's no way to record a
   contractor-level transaction that isn't about a particular book (e.g. a lump-sum advance
   covering several future books at once, before any of them has a `contractor_books` row). Is that
   an acceptable limitation, or should `contractor_book_id` be nullable with a separate
   `contractor_id` always present (transaction can optionally, not always, point at a specific book
   contract)?
4. **[Rev.2] Legacy migration placeholder account naming/handling.** §5 step 6 proposes creating one
   `Account` named "Legacy Contractor Payments (Migration)" to attribute historical author-payment
   vouchers to, since the old system never recorded which real account was used. Confirm this
   naming/approach, or specify a real account those historical payments should actually be
   attributed to. (Implemented with this default for now — cheap to rename/reconcile later since
   it's clearly labeled and isolated to migration-era rows.)
5. **[Rev.2] `BookSale` vs. cancelled invoices.** `SalesInvoiceService::cancelInvoice()` reverses
   stock but does not delete the invoice or its items (only `deleteInvoice()`, blocked once paid,
   does that — and deleting cascades `book_sales` away via the FK). A cancelled-but-not-deleted
   invoice therefore keeps its `book_sales` rows, so `SUM(book_sales.contractor_profit)` still
   counts royalty for a sale that was cancelled. Implemented as-is for now (not addressed by the
   Revision 2 requirements) — should cancelling an invoice also zero out / exclude its
   `book_sales` rows from royalty aggregates?
