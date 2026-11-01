# Missing Accounting Features — Gap Analysis for a Full Accounting System

> Scope: what this project (Laravel 11 + nwidart modules: `Finance`, `Product`, `Warehouse`, `Customer`,
> `HR`, `Settings`, `SearchDrawer`) **has today** versus what a complete accounting / ERP system for a
> **publishing + book-trading business** needs.
> Based on reading the actual code (migrations, models, services) — not the older READMEs.
> Companion document: [`feature-requests-design.md`](./feature-requests-design.md) (detailed design of the 6 requested features).
> Related existing docs: [`saas-readiness-audit.md`](./saas-readiness-audit.md), [`finance-product-warehouse-architecture.md`](./finance-product-warehouse-architecture.md).
> Legend: ✅ exists · ⚠️ partial / flawed · ❌ missing.

---

## 1. What exists today (verified in code)

| Area | Status | Evidence |
|---|---|---|
| Parties (customer + vendor in one table) | ✅ | `parties` table, `Party` model; role is *derived* (`is_customer` = has sales invoices) |
| Party balance / statement (كشف حساب) | ⚠️ | `Party::getCustomerBalanceAttribute` = Σ sales − Σ receipts − (Σ purchases − Σ payments); statement in `PartyController::accountStatement` |
| Sales invoice (items, discount, tax, due date, status) | ✅ | `SalesInvoiceService`, `sales_invoices`, `sales_invoice_items` |
| Receipt voucher (سند قبض) | ⚠️ | `receipt_vouchers.sales_invoice_id` is **nullable**; only a linked receipt updates `invoice.paid_amount` |
| Purchase invoice + payment voucher (سند صرف) | ⚠️ | `PurchaseInvoiceService` — **never touches stock** |
| Cash / bank accounts | ⚠️ | `accounts` (opening balance + Σ receipts − Σ payments). Has `currency` column but nothing uses it |
| Warehouses / sub-warehouses / stock movements | ⚠️ | `sub_warehouse_products.quantity`, `stock_movements`; sub-warehouse `type` includes `book_fair` + `country` |
| Stock deduction on sale | ⚠️ | Only if the user picks a `sub_warehouse_id` on the invoice (optional!) |
| Author/contractor royalties | ✅ | `contractors`, `contractor_books`, `book_sales`, `contract_transactions` (+ links to vouchers) |
| Products / books | ⚠️ | `products` (+`books`); `type` enum includes `bundle` but there is **no component table**; no cost price |
| HR (employees, leaves, deductions, advances) | ✅ (isolated) | Nothing posts to finance (no payroll expense, no advance as receivable) |
| Org settings (currency, tax rate, language) | ⚠️ | Single `currency` string; single tax rate; no exchange rates |

### Defects that block "full accounting" (must be fixed first)
1. **No double-entry.** Balances are `SUM()`s over documents; there is no journal, no chart of accounts, so no P&L / balance sheet is possible (audit §5.1).
2. **Cancelled/soft-deleted invoices still counted** in `Party` balances (audit §5.2).
3. **Purchases don't increase stock**; sales only decrease it when a warehouse is chosen → inventory can never reconcile.
4. **No product cost** → no COGS, no gross margin, no stock valuation.
5. **Payments not allocated** unless the voucher is tied to one invoice → invoice statuses drift from party balance (see feature #3).
6. `stock_quantity` is read in `SalesInvoiceController` / `SearchDrawer` but **not defined on `Product`** (dangling accessor) — stock shown to the user may be wrong/null.
7. Floats in PHP for money, no period closing, no gapless numbering (audit §5.3–5.7).
8. No authorization layer; CSRF disabled (audit §4) — not accounting, but blocks any production use of financial data.

---

## 2. Missing features by domain

Priority: **P0** = required for the system to be trustworthy · **P1** = required to call it a "full accounting system" · **P2** = strongly recommended · **P3** = nice to have.

### 2.1 Core accounting engine
| # | Feature | Status | Pri | Notes |
|---|---|---|---|---|
| A1 | Chart of accounts (دليل الحسابات) — assets, liabilities, equity, revenue, expense, tree with codes | ❌ | P0 | Today `accounts` = only cash/bank |
| A2 | Journal entries + lines (double-entry), posted by every document | ❌ | P0 | See §3 "Posting map" |
| A3 | General ledger, trial balance, account ledger | ❌ | P0 | Derived from A2 |
| A4 | Manual journal voucher (قيد يومية) + reversal entries | ❌ | P1 | Adjustments, corrections |
| A5 | Opening balances (customers, vendors, stock, cash, bank, equity) | ⚠️ | P0 | Only `accounts.opening_balance`; none for parties/stock |
| A6 | Fiscal years / accounting periods + period lock | ❌ | P1 | Block edits to closed periods |
| A7 | Financial statements: Income statement, Balance sheet, Cash-flow | ❌ | P1 | |
| A8 | Cost centers / projects (per book, per book fair, per department) | ❌ | P2 | Needed for fair P&L and title P&L |
| A9 | Immutable posted documents, void via reversal, audit trail | ❌ | P1 | Today invoices are edited/deleted freely (`updateInvoice` deletes & recreates items) |
| A10 | Decimal money type (BCMath/`brick/money`) and rounding policy | ❌ | P1 | |

### 2.2 Sales cycle
| # | Feature | Status | Pri | Notes |
|---|---|---|---|---|
| S1 | **Sales return / credit note (مرتجع مبيعات / إشعار دائن)** | ❌ | P0 | Requested — feature #1 |
| S2 | **Quotation (عرض سعر)** → convert to invoice | ❌ | P1 | Requested — feature #2 (`draft`/`pending` statuses exist in the enum but are unused) |
| S3 | **Payment allocation** (receipts applied to invoices, advance/credit balance) | ❌ | P0 | Requested — feature #3 |
| S4 | Sales order (أمر بيع) + reservation + backorder | ❌ | P2 | Needed for feature #5 sourcing |
| S5 | Delivery note (إذن تسليم) separate from invoice | ❌ | P3 | Optional |
| S6 | Customer credit limit, hold on overdue | ❌ | P2 | |
| S7 | Price lists (retail / wholesale / per-fair currency) and customer-specific prices | ❌ | P1 | Needed for fairs & wholesale |
| S8 | Discount rules (per customer, per quantity, per set) | ⚠️ | P3 | Only manual discount per line/invoice |
| S9 | POS / quick cash sale with walk-in customer | ❌ | P1 | Needed at book fairs |
| S10 | Recurring invoices | ❌ | P3 | |
| S11 | Consignment / sale-or-return to bookstores & distributors | ❌ | P1 | Very common in publishing (stock at the shop, owned by us until sold) |
| S12 | Customer aging report (0-30/31-60/61-90/90+) | ❌ | P1 | |

### 2.3 Purchase cycle
| # | Feature | Status | Pri | Notes |
|---|---|---|---|---|
| P1 | **Goods receipt → increases stock** from a purchase invoice | ❌ | P0 | Today purchases don't restock |
| P2 | Purchase order (أمر شراء) → goods receipt → invoice (3-way match) | ❌ | P2 | |
| P3 | **Own vs. bought-for-resale distinction** + special-order sourcing | ❌ | P0 | Requested — feature #5 |
| P4 | Purchase return / debit note (مرتجع مشتريات) | ❌ | P1 | Mirror of S1 |
| P5 | Payment allocation to vendor invoices | ❌ | P0 | Same engine as S3 |
| P6 | Landed cost (shipping, customs, handling added to item cost) | ❌ | P2 | Critical for imported books & fairs |
| P7 | Vendor aging report | ❌ | P1 | |
| P8 | Printing orders / job costing (print run → cost per copy) | ❌ | P1 | Publisher's *own* product cost comes from here |

### 2.4 Inventory
| # | Feature | Status | Pri | Notes |
|---|---|---|---|---|
| I1 | Cost per item + costing method (weighted average recommended) | ❌ | P0 | |
| I2 | COGS posted at sale; stock valuation report | ❌ | P0 | |
| I3 | Stock count / adjustments (جرد) with variance posting | ❌ | P1 | |
| I4 | In-transit stock between warehouses (shipment → receipt) | ❌ | P1 | Needed for fairs/overseas; today transfer is instant |
| I5 | Reorder levels, low-stock alerts | ❌ | P3 | |
| I6 | **Multi-volume sets (أجزاء) and set availability** | ❌ | P0 | Requested — feature #6 |
| I7 | Damaged / quarantine warehouse and write-off | ❌ | P1 | Returns inspection |
| I8 | Negative stock policy (block / warn), concurrency-safe decrement | ⚠️ | P1 | Check exists but not atomic in create/update (cancel/activate use `lockForUpdate`) |
| I9 | Stock card (كارت صنف) per product per warehouse | ⚠️ | P1 | `stock_movements` exists but has no running balance/cost, and purchases never write to it |
| I10 | Reservation of stock (quotation / order) | ❌ | P3 | |

### 2.5 Cash, bank & treasury
| # | Feature | Status | Pri | Notes |
|---|---|---|---|---|
| T1 | **Multi-currency** (currency per document, rate table, FX gain/loss) | ❌ | P0 | Requested — feature #4 (Morocco fair in MAD) |
| T2 | Transfers between cash/bank accounts (also with FX) | ❌ | P1 | |
| T3 | Cheque management (post-dated, deposited, bounced) | ⚠️ | P1 | Payment method `cheque` exists, no lifecycle |
| T4 | Bank reconciliation | ❌ | P2 | |
| T5 | Expense vouchers (rent, salaries, shipping, booth fees…) with categories | ❌ | P0 | Today only a hack: purchase invoice with `manual_amount` |
| T6 | Petty-cash custody (عهدة) per employee/fair | ❌ | P2 | Fair staff carry cash |
| T7 | Unapplied/advance payments (customer & vendor) | ❌ | P0 | Same as S3 |

### 2.6 Tax & compliance (Egypt context — EGP)
| # | Feature | Status | Pri | Notes |
|---|---|---|---|---|
| X1 | VAT input/output accounts and VAT return report | ❌ | P1 | Tax amount exists on invoices but isn't accounted |
| X2 | Egyptian Tax Authority **e-invoice / e-receipt** integration (ETA) | ❌ | P2 (P0 if registered) | Credit notes must also be reported |
| X3 | Withholding tax (خصم وإضافة) on vendors/customers | ❌ | P3 | |
| X4 | Gapless immutable numbering per document type & year | ⚠️ | P1 | Numbers derived from `last id`; race-prone |
| X5 | Print/PDF templates in Arabic/English for all docs | ⚠️ | P2 | Exists for invoice & vouchers only |

### 2.7 Publishing-specific
| # | Feature | Status | Pri | Notes |
|---|---|---|---|---|
| B1 | Royalty accrual per sale | ✅ | — | `BookSaleService::recordSale` |
| B2 | **Royalty reversal on return** | ❌ | P0 | Needed with S1 |
| B3 | Royalty statement per contractor & period, payable posted to AP | ⚠️ | P1 | Transactions link to vouchers but no GL liability |
| B4 | Title P&L (revenue − print cost − royalty − marketing per book) | ❌ | P2 | |
| B5 | Rights/ISBN registry, editions & reprints | ⚠️ | P3 | `books.isbn` only |
| B6 | Gifts / review copies / author copies as controlled stock outflow | ⚠️ | P2 | `is_gift` flag exists; no gift expense posting |

### 2.8 HR ↔ Finance
| # | Feature | Status | Pri |
|---|---|---|---|
| H1 | Payroll run → expense + payable + payment voucher | ❌ | P2 |
| H2 | Employee advance as receivable, settlement through payroll (data exists in `HR`) | ❌ | P2 |
| H3 | Deductions posted to liabilities (insurance, tax) | ❌ | P3 |

### 2.9 Reporting
Missing: customer/vendor aging, sales by product/customer/period, purchases report, gross margin, stock valuation,
stock card, cash-flow, P&L, balance sheet, trial balance, VAT report, fair reconciliation report,
returns report, quotation conversion report, dashboard KPIs. Export to Excel/PDF for each.

### 2.10 Platform
RBAC (roles/permissions per document & approval), approval workflows (returns, discounts, write-offs), document attachments,
activity/audit log, backups, notifications (overdue, expiring quotations), API, multi-branch, multi-tenant (see audit §7).

---

## 3. Target accounting architecture (what makes the above coherent)

### 3.1 Principle
Keep today's "document" tables (invoices, vouchers…) as the **business layer**; add a **ledger layer**
(`ledger_accounts`, `journal_entries`, `journal_lines`) that every posted document writes to inside the same DB transaction.
Balances then come from the ledger; party balances become the sub-ledger of Receivable / Payable control accounts.

### 3.2 Posting map (minimum)
| Event | Debit | Credit |
|---|---|---|
| Sales invoice | Accounts receivable (party) | Sales revenue; VAT payable |
| …COGS | COGS | Inventory |
| Receipt voucher | Cash/Bank | Accounts receivable (party) *or* Customer advances (if unallocated) |
| Sales return (credit note) | Sales returns (contra-revenue); VAT payable | Accounts receivable |
| …stock back | Inventory (good) / Damaged-stock-loss (bad) | COGS |
| Purchase invoice (goods) | Inventory (+landed cost); VAT input | Accounts payable (party) |
| Payment voucher | Accounts payable | Cash/Bank |
| Purchase return | Accounts payable | Inventory; VAT input |
| Expense voucher | Expense (category) | Cash/Bank |
| Royalty accrual | Royalty expense | Royalty payable (contractor) |
| FX revaluation / settlement | FX loss | FX gain (and cash/receivable) |
| Fair shipment (transfer) | Inventory – Fair X (in transit) | Inventory – Main |

### 3.3 Suggested roadmap (dependency-ordered)
1. **Phase 0 – Foundations (P0):** decimal money; fix cancelled/deleted-invoice balance bug; mandatory warehouse on stock-affecting docs; purchase goods-receipt restock; fix `stock_quantity`; product cost (weighted avg); RBAC + CSRF.
2. **Phase 1 – Settlement engine:** `payment_allocations` (feature #3) + customer/vendor credit. This is also the foundation for returns (credit notes settle invoices the same way).
3. **Phase 2 – Returns & quotations:** sales return/credit note (#1), purchase return, quotation (#2).
4. **Phase 3 – Ledger:** chart of accounts, journal engine, posting from all documents, period lock, opening balances, trial balance/P&L/BS.
5. **Phase 4 – Multi-currency + book fairs:** currency/rate tables, fair entity, shipments in-transit, fair reconciliation (#4), expense vouchers, cost centers.
6. **Phase 5 – Sourcing & sets:** product origin + special-order purchases (#5), set components & assembly (#6).
7. **Phase 6 – Compliance & extras:** VAT report, ETA e-invoice, cheques, bank reconciliation, payroll posting, consignment, title P&L.

> Note on ordering: features #1, #3 and #4 each need *something* from the foundation (cost, allocation, currency).
> Building them before Phase 0 means rework — see the dependency table in the companion document §8.
