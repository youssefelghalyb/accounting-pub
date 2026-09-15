# SaaS Readiness & Architecture Audit

**Project:** accounting-pub (Laravel 12 modular accounting/ERP system)
**Scope:** Architecture & design patterns, security, accounting correctness, database design, and a path to multi-tenant SaaS.
**Method:** Static review of the codebase on branch `claude/peaceful-bell-vtwui7` (no runtime testing). File paths/lines are cited so every claim can be re-verified.

---

## 1. Executive Summary

The project is a **Laravel 12 modular monolith** (via `nwidart/laravel-modules`) implementing an accounting/inventory/HR system for what looks like a publishing house (books, authors/contractors, warehouses, sales & purchase invoices, payroll). The module structure and Eloquent usage are clean and idiomatic Laravel. However, three areas need attention before this can be sold as a serious SaaS product:

| Area | Verdict |
|---|---|
| Code architecture | Reasonable modular monolith, but coupled modules and no domain/authorization layer |
| **Security** | **Not production-safe as-is** — CSRF/cookie encryption is disabled app-wide, several modules have zero authentication, and there is no authorization (RBAC) at all |
| **Accounting correctness** | Single-entry, not double-entry; a couple of confirmed correctness bugs in balance aggregation |
| Database | Sound normalization and use of transactions/row locking in places, but missing indexes, no ledger tables, and no tenant column anywhere |
| SaaS/multi-tenancy | Zero multi-tenant infrastructure today — this is a from-scratch project, not a refactor |

Everything below is backed by direct code citations.

---

## 2. Current Coding Architecture & Design Patterns

### 2.1 Stack

- **Framework:** Laravel 12, PHP ^8.2
- **Modularity:** `nwidart/laravel-modules` ^12 — modules in `Modules/{Customer,Finance,HR,Product,SearchDrawer,Settings,Warehouse}`
- **Frontend:** Blade + Alpine.js + Tailwind, bundled with Vite (no SPA framework, no Livewire/Inertia)
- **Desktop packaging:** `nativephp/electron` is required — the app can also ship as a desktop app, not just web
- **Spreadsheet export:** `phpoffice/phpspreadsheet`
- **No** `spatie/permission`, no Sanctum/Fortify/Jetstream/Breeze beyond the stock Breeze scaffolding, no queue-worker packages beyond Laravel's own, no CI config (`.github/` doesn't exist), no Docker files.

### 2.2 Architectural pattern: Modular Monolith

```
Modules/{ModuleName}/
├── app/Http/Controllers/, Requests/
├── app/Models/
├── app/Services/          ← business logic, mostly present in Finance
├── app/Providers/          ← Module/Route/Event service providers
├── database/migrations/, seeders/, factories/
├── resources/views/, lang/{en,ar}/, assets/
├── routes/web.php, api.php
└── module.json
```

This is a standard, defensible starting point for a modular Laravel app — each module owns its migrations, routes, views, and translations. Nine module folders are registered (`modules_statuses.json`); seven currently exist on disk.

### 2.3 Layered request flow (as actually implemented)

```
Route (web.php, per-module)
  → group middleware (only ['web'] in HR/Warehouse/Product/Settings/SearchDrawer;
     ['web','auth'] in Finance; ['auth','web'] in Customer)
  → Controller
  → Form Request (validation only — authorize() is a stub, see §4)
  → Service class (Finance module only: SalesInvoiceService, PurchaseInvoiceService,
     PartyService, AccountService, ReceiptVoucherService, PaymentVoucherService)
  → Eloquent Model (fillable, casts, scopes, accessors)
  → Blade view
```

### 2.4 Design patterns actually in use

| Pattern | Where | Notes |
|---|---|---|
| **Active Record (Eloquent)** | All models | Models double as the "repository" — no separate repository layer |
| **Service Layer** | `Modules/Finance/app/Services/*` | Good separation of business logic from controllers, but **only in Finance** — HR/Product/Warehouse controllers appear to embed logic directly |
| **Form Request validation** | 47 `*Request.php` files | Consistently used for input validation |
| **Query Scopes** | `scopeActive`, `scopeOfType`, `scopeSearch`, etc. | Idiomatic, reused across modules |
| **Accessor/Mutator (computed attributes)** | e.g. `Account::getCurrentBalanceAttribute()` (`Modules/Finance/app/Models/Account.php:135`) | Balances, totals, labels computed on read, not stored |
| **DB transactions + row locking** | `SalesInvoiceService::cancelInvoice/activateInvoice` (`Modules/Finance/app/Services/SalesInvoiceService.php:333-463`) | Correctly uses `DB::transaction()` + `lockForUpdate()` — but inconsistently (see §5.6) |
| **Soft deletes** | `SalesInvoice`, `PurchaseInvoice` | `Party`, `StockMovement` do **not** use it |
| **Multi-language** | `resources/lang/{en,ar}` per module | Consistent `module::file.key` convention |

### 2.5 Cross-module coupling

Modules import each other's models directly rather than communicating through events, contracts, or an internal API:

```php
// Modules/Finance/app/Services/SalesInvoiceService.php
use Modules\Product\Models\Product;
use Modules\Product\Services\BookSaleService;
use Modules\Warehouse\Models\StockMovement;
use Modules\Warehouse\Models\SubWarehouseProduct;
```

This works fine at the current size but means the modules are not independently deployable/testable units — they're really just a namespacing convention inside one monolith. That's an acceptable trade-off for a monolith, but worth naming honestly: **this is not a "modular architecture" in the decoupled-boundaries sense, it's a monolith organized into folders.**

There is also no `app/Actions`, `app/DTO`, interfaces/contracts, or dependency-injected service bindings — services are `new`'d up directly inside other services (e.g. `new BookSaleService()`, `new ReceiptVoucherService()` inside `SalesInvoiceService`), which makes unit testing with mocks harder than it needs to be.

---

## 3. Is This the Preferred Architecture?

**Short answer: it's a reasonable starting point, but it is not yet the architecture you want under a paying-customer SaaS product.** Three structural gaps matter most:

1. **No authorization/domain layer.** There isn't a single `Policy` or `Gate` in the codebase, and `FormRequest::authorize()` returns `true` in 45 of 47 requests. For a single-tenant internal tool that's a shortcut; for a SaaS product sold to multiple companies it's disqualifying (see §4).
2. **Services are instantiated ad-hoc, not injected.** Fine at this scale, painful once you need to mock external integrations (payments, e-invoicing, tenant-aware queues) in tests.
3. **No event-driven boundary between modules.** Direct cross-module model imports (Finance → Product/Warehouse) mean a change in Warehouse's stock schema can silently break Finance. A SaaS product that will keep growing modules (e.g. adding e-invoicing, bank feeds, payroll tax filing) benefits from domain events (`InvoicePaid`, `StockReserved`) so modules react without hard imports.

### Recommended target architecture (incremental, not a rewrite)

- Keep the **modular monolith** — it's the right call for a small team; don't jump to microservices.
- Add a thin **Action/Service contract layer**: bind `SalesInvoiceServiceInterface` in each module's `ServiceProvider`, inject via constructor instead of `new`.
- Introduce **Laravel Policies** for every model that's user-facing (`SalesInvoicePolicy`, `PartyPolicy`, `EmployeePolicy`, …), and make every `FormRequest::authorize()` actually check them.
- Introduce **domain events** (`SalesInvoiceCreated`, `StockMovementRecorded`) with listeners in the *consuming* module, replacing some of the direct cross-module calls (not all — some, like stock deduction on sale, are legitimately synchronous and should stay so within a DB transaction).
- Move long-running/export work (Excel export, `BookSaleService::recordSale`, PDF printing) onto **queued jobs** — `QUEUE_CONNECTION=database` is already configured but nothing seems to be queued today.
- Add an **API resource layer** (`JsonResource` classes) if/when a real public API is needed — currently `routes/api.php` files exist per module with `auth:sanctum` but there's no evidence of `Resource` classes or API versioning strategy.

---

## 4. Security Issues & Fixes

Ordered by severity. These are concrete, verified findings, not generic advice.

### 4.1 🔴 CRITICAL — CSRF protection and cookie encryption are disabled application-wide

`bootstrap/app.php:14-23` replaces Laravel's default `web` middleware group instead of extending it:

```php
$middleware->group('web', [
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
    \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
    \App\Http\Middleware\SetLocale::class,
]);
```

Laravel's `Middleware::group()` **replaces** the named group entirely (confirmed against framework source) — it does not merge with the built-in defaults. That means the following are **completely missing** from every single request in the app:

- `Illuminate\Cookie\Middleware\EncryptCookies` — cookies (including the session cookie) are sent unencrypted.
- `Illuminate\Foundation\Http\Middleware\ValidateCsrfToken` — **every `@csrf` token in every Blade form is decorative and unverified.** The app is open to CSRF on every POST/PUT/DELETE route, including invoice creation, payment vouchers, payroll, and account changes.

**Fix:** Stop overriding the group wholesale. Use the dedicated `web()` configurator instead:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        \App\Http\Middleware\SetLocale::class,
    ]);
})
```

This keeps Laravel's default `EncryptCookies`, `ValidateCsrfToken`, `StartSession`, etc., and just appends `SetLocale`. Verify afterward with `php artisan route:list -v` / a manual CSRF test that a POST without a valid token is rejected with 419.

### 4.2 🔴 CRITICAL — Several modules require no authentication at all

Only some modules gate their web routes with `auth`:

| Module | Middleware on web routes | Auth required? |
|---|---|---|
| Finance | `['web', 'auth']` (`Modules/Finance/routes/web.php:9`) | ✅ Yes |
| Customer | `['auth', 'web']` (`Modules/Customer/routes/web.php:7`) | ✅ Yes |
| **HR** | `['web']` (`Modules/HR/routes/web.php:11`) | ❌ **No** |
| **Warehouse** | `['web']` (`Modules/Warehouse/routes/web.php:8`) | ❌ **No** |
| **Product** | `['web']` (`Modules/Product/routes/web.php:10`) | ❌ **No** |
| **Settings** | no middleware group at all (`Modules/Settings/routes/web.php`) | ❌ **No** |
| **SearchDrawer** | no middleware group | ❌ **No** |

Because §4.1 also means there's no global `auth` fallback, **anyone who can reach the app over the network can view/create/edit employees, salaries, leave, advances (HR), warehouses and stock (Warehouse), products/authors/contracts (Product), and change organization-wide settings (Settings) without logging in.** This is the single most serious issue in the codebase.

**Fix:**
1. Add `'auth'` to every module's route group immediately (one-line change per file, same pattern already used in Finance/Customer).
2. Better: apply `auth` as a global default in `bootstrap/app.php` for anything under an authenticated prefix, and explicitly whitelist public routes (login, health check), rather than trusting each module to remember to add it.

### 4.3 🟠 HIGH — No authorization (RBAC) layer at all

- No `spatie/permission` or equivalent in `composer.json`.
- Zero `*Policy.php` files in the entire codebase.
- `App\Models\User` (`app/Models/User.php`) is a single flat model — no `role`, `team_id`, or `permissions` concept.
- 45 of 47 Form Requests' `authorize()` method is literally `return true;` (e.g. `Modules/Finance/app/Http/Requests/StoreSalesInvoiceRequest.php`).

**Consequence:** once a user is logged in (only enforced for Finance/Customer today), they have full read/write access to every record — every invoice, every party, every account — regardless of role. There's no separation between, say, a sales clerk and an accountant/admin, and no per-record ownership check (party balances, invoice edits, voucher creation are all globally accessible to any authenticated user).

**Fix:**
1. Install `spatie/laravel-permission`, define roles (`admin`, `accountant`, `sales`, `warehouse`, `hr`) and permissions per module.
2. Write Policies for the sensitive models (`SalesInvoice`, `PurchaseInvoice`, `Account`, `Employee`, `OrganizationSetting`) and wire `FormRequest::authorize()` to `$this->user()->can(...)`.
3. Gate destructive/financial actions (cancel invoice, delete party, edit organization tax rate) behind an explicit permission, not just "logged in."

### 4.4 🟡 MEDIUM — Unescaped Blade output (`{!! !!}`)

11 occurrences across the app, e.g.:
```blade
<p>Module: {!! config('customer.name') !!}</p>   {# Modules/Customer/resources/views/index.blade.php:4 #}
{!! $column['format']($settlement[$column['field']], $settlement) !!}  {# HR advances/show.blade.php:249 #}
```
The `config()` ones are effectively harmless (server-controlled string), but the HR `advances/show.blade.php` ones render output from a callback (`$column['format']`) whose upstream data source should be audited to confirm it never contains user-supplied text (e.g. employee-entered notes). Any place where a party/employee-controlled free-text field could flow into `{!! !!}` is a stored-XSS risk.

**Fix:** Default to `{{ }}` everywhere; only use `{!! !!}` for values you've explicitly sanitized (e.g. via `Str::sanitizeHtml` or a whitelist) and document why at the call site.

### 4.5 🟡 MEDIUM — Cookie/session hardening left at framework defaults

`config/session.php`: `'secure' => env('SESSION_SECURE_COOKIE')` (unset ⇒ `null`/false), `'same_site' => 'lax'`. Combined with §4.1 (no `EncryptCookies`), session cookies can be sent over plain HTTP and are not encrypted. `.env.example` also ships `APP_DEBUG=true` and `SESSION_ENCRYPT=false` as defaults — fine for local dev, but there's nothing in the repo (no `.env.production.example`, no deployment checklist) reminding an operator to flip these for production.

**Fix:** Add a `.env.production.example` (or deployment doc) that sets `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, `SESSION_SAME_SITE=strict` (or `lax` if cross-site redirects are needed), and force HTTPS via `URL::forceScheme('https')` in a production-only service-provider boot check.

### 4.6 🟢 LOW — Repo hygiene / potential data leakage

`final.xlsx` (258KB) and `products.zip` (132KB) are committed at the repo root (added in commits `19bf817`, `060f31a`). These look like working data exports, not application code. If they contain real customer/product data they shouldn't be in git history (which is forever, even if deleted later) — and either way they don't belong in a source repo.

**Fix:** Remove from the working tree (`git rm`), add `*.xlsx`, `*.zip` to `.gitignore` for anything not an intentional template, and if they contain sensitive data, scrub them from history (`git filter-repo`) before the repo is shared more broadly.

### 4.7 🟢 LOW — No CI, no dependency/security scanning

No `.github/workflows`, no Dependabot config, no `composer audit`/`npm audit` step anywhere. For a product that will hold customers' financial data, some minimum CI (tests + `composer audit` + static analysis) is expected before calling it production-ready.

---

## 5. Accounting / Business-Logic Issues & Fixes

### 5.1 🔴 CRITICAL — Not double-entry bookkeeping

There is no chart of accounts, no journal/ledger table, no debit/credit model anywhere in the schema. The "Finance" module is really a **single-entry cash-basis system** built from independent tables:

- `parties` (customers/vendors)
- `accounts` (cash/bank tills)
- `sales_invoices` / `sales_invoice_items`
- `purchase_invoices` / `purchase_invoice_items`
- `receipt_vouchers` (money in) / `payment_vouchers` (money out)

Balances are derived on the fly, e.g. `Account::getCurrentBalanceAttribute()` (`Modules/Finance/app/Models/Account.php:135-139`): `opening_balance + SUM(receipts) - SUM(payments)`.

**Why this matters:** a real accounting system needs to produce a **trial balance, balance sheet, and profit & loss statement**, support **general-ledger adjusting entries**, and reconcile to the penny for audit/compliance. None of that is possible without a proper `journal_entries` / `journal_entry_lines` (debit/credit) table backing every transaction. This isn't a bug to patch — it's a missing subsystem.

**Fix (incremental, not a rewrite):**
1. Add `chart_of_accounts` (account code, type: asset/liability/equity/revenue/expense, parent for hierarchy).
2. Add `journal_entries` (header: date, memo, source type/id) and `journal_entry_lines` (account_id, debit, credit) with a DB-level or application-level constraint that `SUM(debit) = SUM(credit)` per entry.
3. Make every existing financial action (sale, purchase, receipt, payment) **post a journal entry** in the same DB transaction, instead of (or in addition to) updating the flat tables. Keep the flat tables as a fast "operational" view if useful, but the ledger becomes the source of truth.
4. Build trial balance / P&L / balance sheet reports off the ledger.

### 5.2 🔴 CRITICAL — Cancelled and soft-deleted invoices are still counted in receivables

`PartyService::getPartyFinancials()` (`Modules/Finance/app/Services/PartyService.php:133-159`) computes global receivables with a raw query:

```php
COALESCE(SUM(si.total), 0) - COALESCE(SUM(rv.total), 0) - (...) AS total_receivables
```
where `si` is a raw subquery `SELECT party_id, SUM(total_amount) ... FROM sales_invoices GROUP BY party_id`.

Two problems with this:
1. **It doesn't filter `status != 'cancelled'`.** `SalesInvoiceService::cancelInvoice()` (`Modules/Finance/app/Services/SalesInvoiceService.php:333-395`) sets `status = 'cancelled'` but never zeroes `total_amount`, so a cancelled invoice's full amount still inflates receivables.
2. **It bypasses Eloquent's SoftDeletes global scope.** `DB::table('sales_invoices')` is a raw query builder call — it does not know about the `deleted_at` scope that `SalesInvoice`'s `SoftDeletes` trait applies for normal Eloquent queries. A soft-deleted invoice is still summed.

**Fix:** filter both conditions explicitly in the raw subqueries: `WHERE status != 'cancelled' AND deleted_at IS NULL`. Better yet, replace these hand-written subqueries with Eloquent aggregate queries (`SalesInvoice::whereNotCancelled()->sum(...)`) so the scopes are applied automatically and the next person who edits invoice statuses doesn't have to remember to update three raw SQL strings by hand.

### 5.3 🟠 HIGH — Balances are recomputed live via `SUM()`, with no period closing

Every balance (`Account::current_balance`, party `customer_balance`/`vendor_balance`) is a live aggregate over the full transaction history, recalculated on every page load. This has two consequences:

- **Performance** degrades linearly as transaction volume grows (already mitigated somewhat in `PartyService::getStatistics()` via pre-aggregated subqueries, but per-record accessors like `Account::getCurrentBalanceAttribute()` still run 2 extra queries per account whenever accessed without `withSum`).
- **No historical point-in-time balance.** There's no way to ask "what was this account's balance at the end of last quarter?" and no concept of closing a period (month-end/year-end) so that prior-period entries become immutable. Anyone can still edit or backdate a six-month-old invoice today (`SalesInvoiceService::updateInvoice()` has no date-based guard).

**Fix:** Once §5.1's ledger exists, add period-end closing (a `period_closes` table or a `locked_before` date on the org), and reject edits to transactions dated before the lock. For balances, consider a materialized running-balance column updated transactionally on each posting (with the live `SUM()` kept only as a reconciliation check), rather than recomputing from scratch every time.

### 5.4 🟠 HIGH — No multi-currency support despite a `currency` column

`accounts` has a `currency` field (`Modules/Finance/app/Models/Account.php:21`), but nothing in `SalesInvoiceService`, `PurchaseInvoiceService`, or the migrations handles FX rates or currency mismatches between an invoice, a party, and the account a payment is received into. If two accounts have different currencies, summing them together (as `Account::getTotalReceiptsAttribute()` and the global statistics do) silently mixes currencies as if they were equal.

**Fix:** Either drop the unused `currency` field until it's implemented, or implement it properly: an `exchange_rates` table, currency-aware totals, and explicit currency selection at invoice/voucher creation with conversion recorded at the rate on that date (never recalculated retroactively).

### 5.5 🟠 HIGH — Arithmetic in PHP floats, not integers/cents or a decimal library

`SalesInvoiceItem::calculateLineTotal()` (`Modules/Finance/app/Models/SalesInvoiceItem.php:48-52`) and `SalesInvoice::calculateTotals()` (`Modules/Finance/app/Models/SalesInvoice.php:181-201`) do plain PHP float arithmetic (`$quantity * $unit_price`, percentage discounts, percentage tax) before Eloquent's `decimal:2` cast rounds it for storage. This is standard practice in most Laravel apps and is *usually* fine at 2-decimal-place currency precision, but it's worth being deliberate: floating-point binary representation cannot represent every 2-decimal value exactly (e.g. `0.1 + 0.2 != 0.3`), and repeated additions across many line items/discounts can accumulate a one-cent drift that won't match a bank statement to the penny.

**Fix:** Not urgent to rewrite today, but when building the ledger (§5.1), store money as integer minor units (cents) or use `brick/money` / `bcmath` consistently for all monetary math, and always round explicitly at the point of persistence with a single, centralized rounding rule (not scattered `round()` calls with different precisions).

### 5.6 🟡 MEDIUM — Inconsistent concurrency safety between invoice actions

`cancelInvoice()`/`activateInvoice()` correctly use `lockForUpdate()` on the invoice and stock rows to prevent race conditions (`Modules/Finance/app/Services/SalesInvoiceService.php:338, 351, 363, 405, 414, 426`). But `createInvoice()` and `updateInvoice()` — the far more frequently-hit paths — check stock with a plain `SELECT` and then `save()` without any row lock (`Modules/Finance/app/Services/SalesInvoiceService.php:109-122, 251-263`). Under concurrent checkout (two sales clerks selling the last copy of a book at the same moment), both requests can pass the "enough stock?" check before either decrements it, resulting in **oversold inventory** (negative or incorrect stock).

Invoice numbering has the same shape of bug: `generateInvoiceNumber()` (`Modules/Finance/app/Services/SalesInvoiceService.php:19-34`) reads the last invoice, increments in PHP, and only relies on the DB `unique()` constraint on `invoice_number` (`Modules/Finance/database/migrations/..._create_sales_invoices_table.php`) to catch a collision — which will surface as an unhandled DB exception (ugly 500) rather than a retried/serialized allocation.

**Fix:** Wrap `createInvoice`/`updateInvoice` stock checks in the same `lockForUpdate()` pattern already proven in `cancelInvoice`. For invoice numbering, either use a dedicated per-year sequence table with `lockForUpdate()`, or catch the unique-constraint violation and retry with a freshly generated number.

### 5.7 🟢 LOW — No gapless/immutable invoice numbering for tax compliance

Many jurisdictions' e-invoicing/VAT rules require sequential, gapless invoice numbers with no reuse, even for cancelled invoices. Today, cancelling an invoice doesn't affect its number, but nothing prevents deleting a never-paid invoice (`deleteInvoice()`, `Modules/Finance/app/Services/SalesInvoiceService.php:290-328`) and leaving a gap — acceptable for many businesses, but worth flagging as a compliance question before selling into markets with strict e-invoicing mandates (increasingly common — e.g. Egypt, Saudi Arabia, EU).

---

## 6. Database Issues & Fixes

### 6.1 Overall design quality

Normalization is generally solid: proper foreign keys, sensible `ON DELETE` policies (`cascade` for owned children, `set null` for audit fields, `restrict` for financial references like `party_id`/`product_id` on invoices — correctly preventing deletion of a party/product that has invoice history). `decimal(15,2)` is used consistently for money, which is the right column type (not `float`/`double`).

### 6.2 🟠 HIGH — Missing indexes for actual query patterns

Foreign key columns get an index implicitly via `foreignId()->constrained()`, but several **non-FK columns that are filtered/sorted on in every list view** have no index:

- `sales_invoices.status`, `sales_invoices.invoice_date` — used by `SalesInvoice::overdue()`/`unpaid()`/`partial()` scopes and by `getStatistics()` (`Modules/Finance/app/Services/SalesInvoiceService.php:469-482`), which run full-table scans as data grows.
- `sub_warehouse_products.quantity` — used for low-stock/out-of-stock dashboards on every page load.
- `customers.type`, `customers.is_active` — noted as "recommended" in the project's own `docs/database-documentation.md:611-614` but not actually present in the migrations.
- No full-text index anywhere despite `scopeSearch()` methods doing `LIKE '%term%'` on `name`/`email`/`phone` across Customer, Party, Product — this can't use a B-tree index efficiently at all and will get slow.

**Fix:** add composite indexes matching real query shapes, e.g. `sales_invoices(status, invoice_date)`, `sub_warehouse_products(sub_warehouse_id, quantity)`, and consider MySQL/Postgres full-text indexes (or a search service) for the `LIKE '%…%'` searches once record counts grow past a few thousand.

### 6.3 🟠 HIGH — Raw queries bypass Eloquent scopes (see §5.2)

Already covered under accounting correctness, but it's fundamentally a database-layer issue: mixing `DB::table()`/`DB::raw()` subqueries with Eloquent models that have global scopes (`SoftDeletes`) is a recurring source of silent data-correctness bugs. Any future raw query against `sales_invoices`, `purchase_invoices`, or any other soft-deleting table needs an explicit `deleted_at IS NULL`.

### 6.4 🟡 MEDIUM — No persisted running balances → full-table aggregation on read

Covered in §5.3. From a pure database perspective: `SUM()` over the full `receipt_vouchers`/`payment_vouchers`/`sales_invoices` tables on every account/party page view will not scale past tens of thousands of transactions without becoming visibly slow, especially once this is multi-tenant and every tenant's dashboard is running these aggregates concurrently.

### 6.5 🟡 MEDIUM — Inconsistent soft-delete usage

`SalesInvoice`/`PurchaseInvoice` use `SoftDeletes`; `Party`, `Account`, `StockMovement`, `Product`, `Employee`, and most other models do not. For financial and inventory-movement history, hard deletes lose the audit trail (e.g. `StockMovement::delete()` calls in `SalesInvoiceService::updateInvoice()/deleteInvoice()` at lines 209, 320 permanently remove movement rows rather than reversing them with a new record, unlike the equivalent logic in `cancelInvoice()` which correctly appends a reversing movement instead of deleting).

**Fix:** Decide a policy — "financial/inventory history rows are never hard-deleted, only reversed or soft-deleted" — and apply it consistently. Change `updateInvoice()`/`deleteInvoice()` to follow the same reversing-movement pattern already used correctly in `cancelInvoice()`.

### 6.6 🟢 LOW — `sqlite` as the framework default, `mysql` in `.env.example`

`config/database.php:19` defaults to `sqlite` if `DB_CONNECTION` is unset, while `.env.example` specifies `mysql`. Not a bug, but worth pinning explicitly in a production `.env` template so a misconfigured deploy doesn't silently fall back to a local SQLite file.

---

## 7. Converting to a Multi-Tenant SaaS

**Current state: zero multi-tenancy infrastructure.** There is no `tenant_id`/`organization_id`/`team_id` column anywhere in the 49 migrations, no tenant-scoping middleware, `OrganizationSetting` (`Modules/Settings`) is a **single global row** (`OrganizationSetting::first()` is called directly in `SalesInvoiceService::createInvoice()`, `Modules/Finance/app/Services/SalesInvoiceService.php:66`), and `User` has no relationship to an organization at all. This is a from-scratch build-out, not a refactor of existing tenant logic. Below is a realistic phased plan.

### 7.1 Choose a tenancy model

| Model | Isolation | Cost | Fit here |
|---|---|---|---|
| **Shared DB, `tenant_id` column on every table (row-level multi-tenancy)** | Logical only | Cheapest, simplest ops | **Recommended starting point** — matches the current single-DB architecture, works with existing MySQL setup, easiest to build with Eloquent global scopes |
| Database-per-tenant | Strong (separate schema/DB per customer) | More ops overhead (migrations × N tenants, connection pooling) | Consider later if a large enterprise customer requires strict data isolation, or once tenant count/size makes noisy-neighbor query performance a problem |
| Schema-per-tenant (Postgres) | Medium | Requires Postgres | Not applicable — project is on MySQL |

Given the current stack (MySQL, single Laravel app, no existing tenant logic to migrate away from), **shared-database row-level multi-tenancy** is the pragmatic choice — likely using `stancl/tenancy` (single-DB mode) or `spatie/laravel-multitenancy`, or a hand-rolled version of the same pattern (both packages essentially wrap what's described below).

### 7.2 Concrete steps

1. **Add the tenant model.** New `organizations` (or `tenants`) table: `id`, `name`, `slug`/`domain`, `plan`, `status`, timestamps. This becomes the paying-customer boundary — one publishing house per organization, in the domain example.

2. **Add `organization_id` to every tenant-owned table.** That's essentially all of them: `parties`, `accounts`, `sales_invoices`, `sales_invoice_items`, `purchase_invoices`, `purchase_invoice_items`, `receipt_vouchers`, `payment_vouchers`, `products`, `books`, `authors`, `author_book_contracts`, `author_contract_transactions`, `warehouses`, `sub_warehouses`, `sub_warehouse_products`, `stock_movements`, `book_categories`, `customers`, `employees`, `departments`, and `organization_settings` itself (which currently assumes exactly one row — this needs to become one row *per organization*, not global). `users` needs either a `organization_id` column (simplest, one org per user) or a `organization_user` pivot table (if you want one person to belong to multiple organizations, e.g. an accountant serving several client companies — worth deciding explicitly, as it changes the shape of everything downstream).

3. **Enforce tenant scoping automatically, not by convention.** Add a global Eloquent scope (e.g. a `BelongsToOrganization` trait + `TenantScope`) applied via each model's `booted()` method, so every query is automatically filtered to `organization_id = current_tenant_id` without every controller/service having to remember to add `->where('organization_id', ...)`. This is the single most important step — the codebase today already shows a pattern (raw `DB::table()` queries, §5.2/§6.3) of bypassing Eloquent scopes; that exact failure mode would leak data **across tenants** if not addressed deliberately before adding the tenant scope, and would need re-auditing of every raw query in the codebase.

4. **Resolve the current tenant per request.** Middleware that determines the tenant from the authenticated user's `organization_id` (or from subdomain/custom-domain routing if you want `customer-a.yourapp.com`), and binds it into the container/session for the request lifecycle. Apply this middleware in `bootstrap/app.php`'s web group (once §4.1's CSRF fix is also in place) so it's impossible to add a new route without tenant scoping unless deliberately excluded.

5. **Fix authentication & authorization together with tenancy** (this is also §4.2/§4.3): a login should resolve which organization(s) a user belongs to, and RBAC (§4.3) should be **per-organization** (a user's "admin" role in Org A must not grant admin in Org B). Building roles/permissions and tenancy at the same time avoids doing the RBAC work twice.

6. **Uniqueness constraints need to move from global to per-tenant.** E.g. `sales_invoices.invoice_number` is currently globally unique (`Modules/Finance/database/migrations/..._create_sales_invoices_table.php`); under multi-tenancy it should be unique **per organization** (`unique(['organization_id', 'invoice_number'])`), otherwise Org A and Org B would compete for the same invoice-number sequence. Same for anything else with a bare `unique()` — audit every migration for this.

7. **Background jobs, queues, cache, and file storage all need tenant awareness.** `QUEUE_CONNECTION=database` and `CACHE_STORE=database` are fine for a single tenant but need tenant-scoped keys/queues (or `stancl/tenancy`'s queue/cache tenancy bootstrappers) so one tenant's cached "organization settings" or queued export job doesn't leak into another's. File uploads (ID images, contract files, receipts — currently just `local`/`public` disk paths on the model) need a tenant-prefixed storage path (`storage/app/organizations/{id}/...`) or per-tenant S3 prefixes, plus access control on file URLs (today, anything in the `public` disk is served unauthenticated by URL — that needs to change to signed/authenticated downloads once multiple tenants' files share infrastructure).

8. **Billing & plan enforcement.** Add a subscription/plan layer (Laravel Cashier + Stripe is the standard pairing) gating feature access and usage limits (seats, storage, invoice volume) per organization — currently there's no concept of a "plan" at all.

9. **Data migration for existing data.** If there's existing production data for the current single organization, it all needs a migration that backfills `organization_id = 1` (the first/default org) across every table before the `NOT NULL` constraint and foreign key are added.

10. **Testing.** Add feature tests that explicitly assert cross-tenant isolation (Org A's user cannot read/write Org B's invoices via any route, including IDOR-style `sales-invoices/{id}` direct access) — this is the test suite most likely to catch a regression that would otherwise be a serious data breach.

### 7.3 Suggested sequencing

Given the current gaps, the safe order is:

1. Fix §4.1 (CSRF/cookies) and §4.2 (missing auth on HR/Warehouse/Product/Settings) — these are pre-existing bugs, not SaaS-specific, and fixing them first avoids building tenancy on top of an already-broken auth boundary.
2. Build RBAC (§4.3) and tenancy (§7.2) together, since roles need to be tenant-scoped from day one.
3. Add the ledger (§5.1) — ideally tenant-aware from the start, since retrofitting accounting correctness *and* tenancy at once is much more work than doing the ledger once, correctly, per-tenant.
4. Only then invest in billing/plan enforcement (§7.2.8) — you need real tenant boundaries before you can meter or gate anything.

---

## 8. Summary Checklist

- [ ] Fix `bootstrap/app.php` to stop replacing the default `web` middleware group (§4.1)
- [ ] Add `auth` middleware to HR, Warehouse, Product, Settings, SearchDrawer routes (§4.2)
- [ ] Introduce roles/permissions + Policies; stop `authorize() { return true; }` (§4.3)
- [ ] Audit and fix `{!! !!}` usage in HR views (§4.4)
- [ ] Harden session cookies for production (§4.5)
- [ ] Remove `final.xlsx`/`products.zip` from the repo (§4.6)
- [ ] Add CI (tests, `composer audit`) (§4.7)
- [ ] Design and implement a proper chart-of-accounts/journal ledger (§5.1)
- [ ] Fix cancelled/soft-deleted invoices leaking into receivables totals (§5.2)
- [ ] Add row locking to `createInvoice`/`updateInvoice` stock checks (§5.6)
- [ ] Add the missing indexes in §6.2
- [ ] Standardize soft-delete policy across financial/inventory models (§6.5)
- [ ] Plan and execute the multi-tenancy build-out in §7, in the sequence suggested in §7.3

---

*This document reflects a point-in-time code review and cites specific file paths and line numbers so each finding can be independently re-verified against the current `Modules/` and `app/` trees.*
