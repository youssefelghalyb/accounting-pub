# Requested Features — Design, Scenarios & Solutions

> Six features requested by the business. For each: **current state in the code**, **proposed design**
> (tables / flow / rules), **all scenarios & edge cases with a recommended answer**, and **open decisions**.
> This is a design document only — no code was changed.
> Companion: [`missing-accounting-features.md`](./missing-accounting-features.md) (gap analysis + roadmap).
> Conventions: table/column names are proposals; "doc" = business document; EGP = base currency.

## Index
1. [Sales return / credit note (مرتجع / فاتورة مردودات)](#1-sales-return--credit-note)
2. [Quotation (عرض سعر)](#2-quotation-عرض-سعر)
3. [Customer payments on account & allocation](#3-customer-payments-on-account--allocation)
4. [Book fairs (separate inventory, currency, return shipment)](#4-book-fairs)
5. [Own products vs. products bought for resale](#5-own-products-vs-products-bought-for-resale)
6. [Multi-part books (Part 1/2/3) and sets](#6-multi-part-books-and-sets)
7. [Cross-cutting rules](#7-cross-cutting-rules)
8. [Dependencies & build order](#8-dependencies--build-order)

---

## 1. Sales return / credit note

### 1.1 The business problem
Client bought 10, then 20, then 40 books (three invoices, possibly at three different prices).
He now returns 15 — 5 from each purchase. Sometimes the user **cannot (or does not want to) return line-by-line
against each invoice**; the user wants to enter a return **with a price** and deduct it from the client's balance.
**What if prices changed** since the sale?

### 1.2 Current state
No return concept exists. `SalesInvoiceService` can only `cancel` (whole invoice, only if `paid_amount == 0`),
`delete`, or `update` (recreate items). Partial returns are impossible, and a paid invoice can't be touched at all.
Party balance = Σ invoices − Σ receipts (…) so there's nothing to subtract a return from.

### 1.3 Key insight: price is a snapshot, so "price changed" is a policy, not a bug
`sales_invoice_items.unit_price` is stored per line, so each original invoice already remembers what the client paid
(e.g. 50 / 55 / 60 EGP). The return value must be decided **per returned line** with an explicit *pricing policy*.

### 1.4 Proposed design

**Tables**
```
sales_returns                       (header — a credit note)
  id, return_number (SR-YYYY-00001), party_id, return_date,
  mode ENUM('linked','open'),       -- linked = against invoices; open = customer-level
  status ENUM('draft','posted','cancelled'),
  warehouse_id (sub_warehouse where goods come back), restock_condition,
  subtotal, discount_amount, tax_amount, total_amount,
  settlement ENUM('apply_to_invoices','credit_balance','refund'),
  reason_id, notes, created_by, approved_by, posted_at, timestamps, softDeletes

sales_return_items
  id, sales_return_id,
  sales_invoice_item_id NULL,       -- NULL for open-mode lines
  product_id, quantity,
  original_unit_price NULL,         -- snapshot from the invoice line (for variance report)
  return_unit_price,                -- what we credit the client
  price_source ENUM('original','manual','current_list'),
  line_total, tax_amount,
  condition ENUM('good','damaged','expired'),   -- decides destination warehouse
  unit_cost_snapshot                -- for COGS reversal (needs costing, see missing-features I1)

credit_allocations  (shared with feature #3: credit note → invoice)
```
Add to `sales_invoice_items` a computed `returned_qty` (or compute via `SUM`) → **returnable = quantity − returned**.

**Two entry modes (this solves the "can't return on each invoice" problem)**

| Mode | UI | Use when |
|---|---|---|
| **Linked** | Pick customer → system lists his invoice lines with *sold / already returned / returnable*. User types qty per line (5 + 5 + 5). | Normal, auditable return |
| **Open (customer-level)** | Pick customer + product + **total qty 15** (+ price). System *auto-allocates* the qty to his invoices using a rule, user can edit. If the user insists on no link at all, the line is saved with `sales_invoice_item_id = NULL`. | Client can't say which invoice |

**Auto-allocation rules for "return 15 of book X"** (selectable, default FIFO):
- **FIFO** – oldest invoice first (10 → 5 from the 40 …)
- **LIFO** – newest first (matches what is most likely on the client's shelf)
- **Proportional** – spread by quantity (10:20:40 → 2/4/9 of 15, remainder to largest)
- **Equal / manual** – the "5 from each" case; user edits the allocation grid
Allocation only decides **which price and which royalty record** are reversed, so it matters for money — hence it is
always shown and editable before posting.

### 1.5 Pricing policy (the "what if prices changed" answer)

| Policy | Credit price | Pros | Cons / when to use |
|---|---|---|---|
| **P1 – Original invoice price** *(default for linked mode)* | `unit_price` of the matched invoice line (net of invoice-level discount, see 1.6) | Exactly reverses what he was charged; no gain/loss; tax/royalty reverse cleanly | If allocation across invoices differs, credit differs (50 vs 60) |
| **P2 – Manual agreed price** *(default for open mode)* | Typed by user, per line, with reason; requires permission/approval if it differs from original by more than X% | Matches reality of negotiated returns | Needs control: store `original_unit_price` + variance, report "return price variances" |
| **P3 – Weighted average of his purchases** | Σ(qty×price)/Σqty across invoices of that product for that client | Fair when invoices can't be matched | Not an actual invoice price — flag as "average" |
| **P4 – Current list price** | `products.base_price` today | Simple | Almost always wrong (client gets more/less than he paid). **Not recommended** except defective-exchange |

Recommended rule set:
1. Linked → P1 automatically (user may override with permission).
2. Open → default P3 (average), user may type P2. Never default to P4.
3. A credit price **above** the original price of the matched line needs approval (anti-fraud); below is allowed.
4. Always store both `original_unit_price` and `return_unit_price`; the difference goes to a *"returns price variance / discount allowed"* ledger account so the books stay explainable.

### 1.6 Calculation details (where errors usually hide)
- **Invoice-level discount**: `calculateTotals()` applies the discount on the whole subtotal, not per line. A returned line's *effective* price =
  `line_total × (1 − invoice.discount_amount / invoice.subtotal) / qty`. Without this, returns over-credit discounted invoices.
- **Tax**: credit the same tax rate as the original invoice (`tax_rate` is on the invoice); tax portion = proportional. If the original was non-taxable, no tax.
- **Rounding**: last returned unit of a line takes the rounding remainder so Σreturns never exceeds the invoice line total.
- **Cap**: Σ returned qty ≤ sold qty per line (linked) and ≤ client's net sold qty of that product (open mode — computed across all his non-cancelled invoices minus earlier returns).

### 1.7 What posting a return does (one DB transaction)
1. **Stock**: `stock_movements` row `reason='sales_return'`, `to_sub_warehouse_id` = chosen warehouse, quantity +; `condition=damaged` → goes to *damaged/quarantine* warehouse, not sellable stock.
2. **Cost**: reverse COGS at the *original* unit cost (not today's cost).
3. **Royalty**: for books with a contractor, insert a **negative `book_sales` row** (or a `book_sale_returns` row) copying the original snapshots (`percentage_snapshot`, `percentage_basis_snapshot`) → `contractor_profit` negative. Never recompute with today's contract terms.
   - If the royalty was **already paid out** (`contract_transactions` of type `royalty_payment`) the negative accrues as a carry-forward deducted from the next payment (type `adjustment`).
   - Gift lines (`is_gift`) → no royalty reversal.
4. **Customer account**: credit note total reduces his balance.
5. **Settlement** (user chooses at posting; can be changed later via allocation):
   - *Apply to invoices* → reduce `outstanding` of the chosen invoices (oldest-unpaid default). If he has already fully paid, the credit becomes **customer credit balance**.
   - *Credit balance* → stays as unapplied credit usable on future invoices/receipts (feature #3 engine).
   - *Refund* → creates a **payment voucher** (cash out) to the client.
6. **Ledger** (when GL exists): see posting map in missing-features §3.2.
7. Original invoices stay **immutable**; they show "Returned: 5 / net: 5" and net amount; PDF of the invoice can list linked credit notes.

### 1.8 Scenarios & answers

| # | Scenario | Answer |
|---|---|---|
| a | Client returns 15 = 5 from each of 3 invoices | Linked mode, qty 5/5/5. Credit = 5×p1 + 5×p2 + 5×p3 (each at its own price) |
| b | Client returns 15 but can't say which invoices | Open mode: auto-allocate (default FIFO) or P3 average price; allocation grid editable |
| c | Price rose from 50 → 60 since sale; client paid 50 | Linked P1 = 50. Open + manual: user may type 50; typing 60 needs approval and is flagged |
| d | Price **dropped** 60 → 50 (client paid 60) | P1 = 60 (credit what he paid). We don't re-price down |
| e | Invoice was partially paid / fully paid | Credit goes to invoice outstanding first, then credit balance, or refund. Never edit the paid invoice |
| f | Return of a book whose royalty was already paid to the author | Negative royalty carried to next author payment (1.7.3) |
| g | Return more than sold (typo or another seller's stock) | Blocked: exceeds returnable qty |
| h | Return after the invoice was **cancelled** | Not allowed — cancelled invoice is already reversed |
| i | Damaged returns | `condition=damaged` → quarantine warehouse; credit still issued (or reduced via price policy P2); write-off later |
| j | Client returns a book bought on a **fair** invoice | Return into the fair's warehouse if fair still open, else main warehouse (+ flag) |
| k | Return of a **set** but he keeps one volume | Credit the set price share of returned volumes (see feature 6 §6.6) |
| l | Wrong credit note | Cancel by *reversal* (cancel a posted return → opposite movements), not by editing |
| m | Invoice had discount 10% on whole invoice | Use effective net price (1.6) |
| n | Mixed currencies (fair invoice in MAD) | Credit in the **invoice currency** at the **original rate** (see feature 4) |
| o | Exchange (return + new sale) | Two documents: return + new invoice, then settle one against the other via allocation |
| p | Return after period closed | Posts in the current period (never alters closed periods) |
| q | Taxable invoice, ETA e-invoice already submitted | Must issue a **credit note** document to ETA — this design already produces one |

### 1.9 Also needed: purchase returns
Mirror image (`purchase_returns`): returning books to a vendor — stock −, AP −, vendor credit, no royalty.

### 1.10 Open decisions
1. Default auto-allocation: FIFO or LIFO? (Recommend FIFO.)
2. Who may type a manual return price, and at what variance does approval trigger? (Suggest > 5%.)
3. Is "open mode without any invoice link" allowed, or must the system force-allocate to invoices? (Recommend: force-allocate; keep NULL-link only for legacy/unknown history.)

---

## 2. Quotation (عرض سعر)

### 2.1 Requirement
Looks like an invoice but **not approved / no accounting or stock effect**; later converted into a sales invoice.

### 2.2 Current state
`sales_invoices.status` enum already has `draft` and `pending`, but nothing uses them and a draft invoice would still be
counted in `Party` balance (`SUM(total_amount)` ignores status) and deduct stock on create. So **do not reuse the invoice table** — use a separate document.

### 2.3 Design
```
quotations
  id, quotation_number (QT-YYYY-00001), revision (int), parent_quotation_id NULL,
  party_id NULL, prospect_name/phone NULL,        -- allows quoting people who aren't customers yet
  quotation_date, valid_until,
  currency, exchange_rate,
  subtotal, discount_*, is_taxable, tax_rate, tax_amount, total_amount,
  status ENUM('draft','sent','accepted','rejected','expired','converted','cancelled'),
  converted_invoice_id NULL, notes, terms_conditions, created_by...

quotation_items  (same columns as sales_invoice_items + sort_order, optional is_optional)
sales_invoices.quotation_id NULL   -- back-reference
sales_invoice_items.quotation_item_id NULL
```
**Rules**
- No stock movement, no balance impact, no royalty record, no numbering gap on invoices.
- Own number series `QT-`.
- `valid_until` – nightly job sets `expired`; expired quotes can be *re-opened* (new revision).
- Revisions: editing a *sent* quote creates revision n+1 and marks the old `superseded` (customer keeps a PDF history).
- Prints/PDF/Excel/WhatsApp-share with the existing invoice print template + validity and "not a tax invoice" watermark.
- Prospect: if no `party_id`, convert asks to create the party (use existing `parties.quick-store`).

**Convert to invoice** (button on accepted/sent quote):
1. Copy header + items into a new `SalesInvoice` through `SalesInvoiceService::createInvoice` (so stock check, tax, royalty, numbering all run normally).
2. Mark quote `converted`, store `converted_invoice_id`; link both ways.
3. **Price policy at conversion** (setting, default *keep quoted price*):
   - *Keep quoted prices* (honour the offer while valid)
   - *Refresh to current prices* (show diff before confirming)
4. **Stock re-check**: warn/block for items now out of stock; allow converting part of the lines.
5. **Partial conversion**: convert only selected lines/quantities; quote becomes `partially_converted` and keeps remaining qty (supports staged deliveries).
6. A quote can convert to **one or many** invoices until fully consumed; never converts twice for the same qty.

### 2.4 Scenarios

| # | Scenario | Answer |
|---|---|---|
| a | Customer accepts after validity expired | Reopen as new revision; choose keep/refresh prices |
| b | Price of a book changed after quote | Default honour quoted price if still valid; otherwise refresh |
| c | Customer wants only part | Partial conversion |
| d | Quote for a set (feature 6) | Quote line = set; on conversion it explodes to volume movements |
| e | Customer negotiates | New revision; compare revisions side-by-side |
| f | Quote in foreign currency (fair or export) | Currency + rate stored; on conversion the invoice currency = quote currency; rate re-asked |
| g | Quote needs internal approval (large discount) | `pending_approval` status + permission |
| h | Reserve stock for a quoted order | Optional soft reservation (`reserved_qty`), released on expiry/reject. Default **off** |
| i | Deposit requested on accept | Create receipt (advance) tied to quote → allocated to the invoice at conversion (feature 3) |
| j | Deleting a converted quote | Not allowed (cancel only); invoice keeps the link |
| k | Reporting | Conversion rate, expired value, pipeline by customer/sales rep |

---

## 3. Customer payments on account & allocation

### 3.1 The business problem
Client takes invoice A = 1,000 EGP (credit / دفع آجل), then invoice B = 3,000. A month later he pays 2,000
**without saying which invoice**. Total owed 4,000 → 2,000. Which invoices are paid?

### 3.2 Current state (important finding)
- `receipt_vouchers.sales_invoice_id` is **nullable**.
- `ReceiptVoucherService::createReceipt` only calls `$invoice->addPayment()` when `sales_invoice_id` is given.
- A receipt with no invoice **lowers the party balance** (`Party::customerBalance`) **but leaves both invoices `unpaid`**.
  Result: statement says he owes 2,000 while invoices A and B still show 1,000 and 3,000 unpaid → overdue reports, statuses and the balance disagree.
- A receipt can reference only **one** invoice, so one payment cannot be split across A and B.
- `getPartyInvoices` lists only `unpaid/partial` invoices, but doesn't suggest allocation.
- `updateReceipt` adjusts `paid_amount` by arithmetic (no row-level record of who paid what) — fragile.

### 3.3 Design: separate **payment** from **allocation**
```
receipt_vouchers   (keep, but amount = money received; sales_invoice_id becomes deprecated/legacy)
  + allocation_mode ENUM('auto_fifo','manual','on_account')
  + allocated_amount (cached), unallocated_amount (cached)

payment_allocations  (new — the settlement engine; used by receipts AND credit notes AND vendor payments)
  id, party_id,
  source_type ('receipt_voucher'|'sales_return'|'opening_credit'), source_id,
  target_type ('sales_invoice'|'opening_balance'),            target_id,
  amount, allocated_at, allocated_by, reversed_at NULL
```
Invariants: `Σ allocations(source) ≤ source.amount`, `Σ allocations(target) ≤ target.total`, `invoice.paid_amount = Σ allocations(target)`
(compute or keep cached and re-derive after each change; never mutate by `+=`).
Invoice status derives from allocations. **Party balance = Σ invoices − Σ receipts − Σ credit notes** unchanged; allocations only change *which* invoice is closed.

**Receipt flow**
1. Choose customer → show **open invoices** (oldest first) with outstanding, due date, overdue days, **and open credit notes**.
2. Enter amount received.
3. Choose mode:
   - **Auto FIFO** (default): fills oldest due first. Example above → A fully paid (1,000), B partially paid (1,000 of 3,000, outstanding 2,000).
   - **Manual**: user types amount per invoice (the system pre-fills FIFO).
   - **On account**: keep as *unapplied advance* (سلفة عميل) — balance reduced, no invoice touched.
4. Excess beyond all open invoices → automatically **on-account credit**, shown on his statement and the next invoice ("Use available credit 500?").
5. Unapplied money can be **allocated later** (button "Allocate" on the receipt or customer page), partially or fully, and **re-allocated** (reverse + re-apply) with audit.

**On new invoice with existing credit**: offer to apply available credit immediately (creates allocation, no cash movement).

### 3.4 Variants of the allocation rule (configurable per customer / global)
| Rule | Behaviour | Typical customer |
|---|---|---|
| FIFO by invoice date | Oldest first | Default |
| FIFO by due date | Most overdue first | Credit control |
| Specific invoice | He says "this one" | Receipt tied to invoice (today's behaviour) |
| Oldest-first but **never partially** pay a newer invoice before older fully closed | strict | Disputes |
| On account only | never auto-allocate | Distributors with running accounts (running balance model, no per-invoice tracking) |

### 3.5 Scenarios

| # | Scenario | Answer |
|---|---|---|
| a | Pays 2,000 for A=1,000 and B=3,000 | FIFO: A=paid, B=1,000/3,000 (status partial) |
| b | He later says "that was for B" | Re-allocate: reverse the 1,000 on A, apply 2,000 on B → A unpaid again (audit trail kept) |
| c | Pays more than total owed (5,000 vs 4,000) | 4,000 allocated, 1,000 on-account credit (or refund) |
| d | Pays before any invoice (deposit) | On-account; consumed when invoice is created/converted from quotation |
| e | Receipt deleted/edited | Allocations reversed automatically; invoice statuses recomputed from allocations |
| f | Invoice edited/cancelled after allocation | Cancel blocked if allocations exist unless they are reversed first (today: blocked if paid > 0 — same rule, now explicit) |
| g | Credit note exists (feature 1) | Credit note is an allocation *source* just like cash — same screen |
| h | Cheque received (post-dated) | Receipt created `pending`; allocation happens **when cleared**, not on receipt |
| i | Payment in a different currency (fair customer in MAD) | Allocation in invoice currency using rate at payment date; difference → FX gain/loss |
| j | Discount for early payment | "Settlement discount" line in the allocation → posts to discount-allowed account |
| k | Partial allocation to many invoices | Supported (many rows per receipt) — **fixes the one-invoice-per-receipt limitation** |
| l | Same customer is also a vendor (Party is both) | Optional **contra/offset** document: set off receivable vs payable. Today `customerBalance` already nets both; make that an explicit, visible document |
| m | Opening balance (old system) | Treated as a pseudo-invoice `opening_balance` so it can be allocated against |
| n | Overdue/aging report | Computed from open allocations → accurate buckets |

### 3.6 Migration of existing data
For each receipt with `sales_invoice_id`: create one allocation row = `amount`. For receipts without invoice: leave
unallocated and show a one-time "Allocate historical receipts" wizard (with FIFO suggestion). Backfill must be
idempotent (unique `(source,target)` key) and compare resulting `paid_amount` against current values; list mismatches.

### 3.7 Vendors
Same engine for `payment_vouchers` ↔ `purchase_invoices` (today the same nullable `purchase_invoice_id` limitation exists).

---

## 4. Book fairs

### 4.1 Requirement
Each book fair is **separate**: own inventory, own currency, and afterwards a **return shipment** (مرتجع المعرض).
Example — Morocco fair: took 1,000 books, sold 400, returned 600 to the home country.

### 4.2 Current state
- `sub_warehouses.type` has `book_fair` and `country` ✅ — a fair can already hold its own stock via `sub_warehouse_products`.
- Transfers (`stock_movements` `transfer`) are **instant**; no shipment document, no in-transit state, no packing list.
- Sales deduct from a single chosen sub-warehouse, **but only if the user selects one** (optional).
- No currency on invoices; `accounts.currency` is cosmetic; `organization_settings.currency` is global.
- No fair entity (dates, status, budget), no fair-level P&L, no variance reconciliation (sent vs sold vs returned).
- No product cost → can't compute fair profit.

### 4.3 Design

**New entity `book_fairs`** (event / معرض)
```
book_fairs
  id, name, country, city, venue, start_date, end_date,
  currency (e.g. MAD), default_exchange_rate, rate_policy ('fixed','daily','at_settlement'),
  sub_warehouse_id (auto-created, type=book_fair, dedicated),
  cash_account_id (auto-created cash account in fair currency),
  price_list_id, walkin_party_id (default customer "Walk-in – <fair>"),
  status ENUM('planned','shipped','open','closed','reconciled'),
  budget, notes
fair_shipments (documents)             -- outbound and inbound legs
  id, book_fair_id, direction ENUM('outbound','return'), number, shipped_at, received_at,
  from_sub_warehouse_id, to_sub_warehouse_id, status ENUM('draft','in_transit','received','discrepancy'),
  carrier, tracking_no, customs_ref (carnet/ATA/customs), shipping_cost, notes
fair_shipment_items
  shipment_id, product_id, qty_sent, qty_received, qty_damaged, qty_missing, unit_cost_snapshot
book_fair_expenses (or generic expense vouchers tagged with cost center = fair)
```
Use the existing `sub_warehouse` as the fair stock location (don't duplicate stock tables) and add an
**in-transit** concept: `qty_sent` leaves the main warehouse at dispatch and lands in the fair warehouse only on "received"; in between it sits in a virtual *In-transit* location (so physical and book stock never silently diverge, and a lost box is visible).

**Lifecycle**
1. **Plan** – create fair, set currency/rate/price list, budget.
2. **Dispatch (outbound shipment)** – packing list of 1,000 books. Stock: Main −1,000 → In-transit +1,000. Cost captured (customs value, shipping cost).
3. **Receive at fair** – confirm counts; In-transit → Fair warehouse. Differences recorded (damaged/missing).
4. **Sell at the fair** – quick POS-style cash invoices to the *Walk-in* party in **MAD** from the fair warehouse (stock required). Each invoice stores `currency=MAD`, `exchange_rate`, base-currency equivalent. Payments go to the fair cash account (MAD). Royalty (`BookSale`) is recorded on these sales exactly as at home.
5. **Fair expenses** – booth rent, travel, shipping, customs, local purchases; in any currency; tagged to the fair (cost center).
6. **Close & return** – return shipment: Fair warehouse −600 → In-transit → Main +600 on receipt. Unsold books may alternatively be: *donated*, *gifted*, *sold in bulk to a local distributor*, *left at the fair's consignment*, *written off* — each is a typed stock outflow, never just "missing".
7. **Cash settlement** – move fair cash (MAD) to home account: transfer with FX (EGP amount received ÷ MAD amount = realized rate; difference vs book rate → FX gain/loss).
8. **Reconcile** – report must satisfy:
   `Sent − Sold − Returned(received) − Donated − Gifted − Damaged − Missing = 0` (else variance with reasons).
   Example: sent 1,000; sold 400; returned received 590; damaged at fair 6; missing 4 → balanced. P&L of the fair = Sales(in EGP) − COGS − fair expenses − royalties − FX effects.

### 4.4 Currency design (minimum viable)
```
currencies (code, name, symbol, decimals)
exchange_rates (from, to, rate, effective_date, source ENUM('manual','api'))
-- documents (sales_invoices, receipts, payments, expenses, accounts-transfers):
   currency, exchange_rate, base_total (EGP), foreign_total
```
- Document is stored in **both**: foreign amount (what customer saw) and base amount (for ledger).
- Cash accounts have a currency; receipts into a MAD cash account must be MAD.
- **Rate choices** per fair (`rate_policy`):
  - *Fixed rate for the whole fair* (simplest; recommended for small fairs) — set at dispatch.
  - *Daily rate* – entered daily by the fair manager.
  - *At settlement* – use the actual rate at which MAD cash is converted; interim reports are provisional.
- **Realized FX** when cash is converted; **unrealized FX** revaluation at period end for foreign cash/receivables.
- Display: show EGP and MAD side by side in fair reports.

### 4.5 Scenarios

| # | Scenario | Answer |
|---|---|---|
| a | Morocco: 1,000 out, 400 sold, 600 back | Shipment 1,000 → sell 400 (MAD) → return shipment 600 → variance 0 |
| b | 600 expected back, only 590 arrive | Return shipment `discrepancy` status; 10 booked as *lost/damaged in transit* (loss account) until investigated; insurance claim note |
| c | Customer returns a book at the fair | Fair-level sales return (feature 1) into the fair warehouse in MAD |
| d | More stock sent mid-fair | Additional outbound shipment on the same fair |
| e | Sold books priced differently from home | Fair **price list** in MAD; invoice price in MAD; margin vs cost shown in EGP |
| f | Rate moves during the fair | Policy-based; difference caught as FX gain/loss at settlement |
| g | Fair sells to a bookstore on credit (not walk-in) | Normal party + invoice in MAD; receivable in foreign currency; revalued |
| h | Books sold out at fair, need top-up from a local printer/vendor | Purchase invoice received **directly into fair warehouse** (feature 5 flow) |
| i | Cash stolen/short at close | Cash count vs system → shortage expense with explanation |
| j | Leftover books donated to local library | Typed outflow `donation` (expense at cost) with customs doc |
| k | Two fairs running simultaneously | Each fair has its own warehouse, cash account, currency, cost center — no cross-contamination |
| l | Partial return then re-send to *another* fair | Transfer fair A → fair B directly (or via home) with shipment documents |
| m | Customs: temporary export, duty on unsold stock | `customs_ref`, duty as fair expense or landed cost; ATA carnet tracked per shipment |
| n | Staff-held cash | Petty-cash custody account per staff member linked to the fair |
| o | Offline connectivity at fair | PWA/offline POS queue syncing later (P3) |
| p | Reporting | Fair P&L, stock reconciliation, sales per day, per title, per currency, expense vs budget |
| q | Books in fair warehouse but user forgot to select warehouse on invoice | Make `sub_warehouse_id` **mandatory** when the invoice has stock products; default to fair warehouse in fair POS |

### 4.6 What to reuse vs add
Reuse: `sub_warehouses`, `sub_warehouse_products`, `stock_movements`, `accounts`.
Add: `book_fairs`, `fair_shipments(+items)`, `currencies`, `exchange_rates`, in-transit location, cost center, price lists, per-document currency, expense vouchers, cost (for profit).

---

## 5. Own products vs. products bought for resale

### 5.1 Requirement
When making a purchase, distinguish *our own* products from *products bought to resell*.
Example (as given): a sales request for 100 books; "500 of them from me … and 500 from another vendor" — the numbers don't add up to 100,
so this document uses **100 = 50 own + 50 bought** (scale as needed). The customer wants 100; the first part is
fulfilled from our stock, the rest must be bought from a vendor and sold on.

### 5.2 Current state
- `products` has no ownership/origin field; royalties are recorded for any book with a `contractor_book` (`BookSaleService`) — fine for own titles, wrong for resold third-party titles *if* one is ever linked to a contractor.
- `PurchaseInvoiceService` creates items but **does not move stock** and does not link to any sales demand; `manual_amount` allows non-product expense invoices.
- No sales order/backorder: `createInvoice` throws if stock is insufficient (only if a warehouse was chosen).

### 5.3 Two different distinctions — model both
1. **Product origin** (property of the item): `own_publication` (we publish; cost = print cost; royalties apply) vs
   `purchased_for_resale` (third-party/distributor title; cost = purchase price; **no royalty**) vs `service/expense`.
   Add `products.origin` (+ default vendor `preferred_vendor_id`).
2. **Purchase purpose** (property of the purchase line): `restock` (to general stock) vs `special_order` (bought for a specific customer order / sales line) vs `expense` (not stock).
   Add `purchase_invoice_items.purpose`, `.linked_sales_invoice_item_id/ .sales_order_item_id`, `.destination_sub_warehouse_id`.

A title can be both over time (we reprint our book **and** occasionally buy copies from another distributor). Therefore origin lives on the *product* for defaults, but **cost layers/lots** record where each copy came from (`stock_lots.source`: `printed`, `purchased`, `returned`, `fair_return`).

### 5.4 Sourcing flow for the 100-book request

```
Sales order (SO)  ── line: Book X × 100
   │ availability check: main warehouse has 50 → reserve 50
   ├─ from_stock = 50        (own copies, cost = print cost)
   └─ to_source  = 50        → create Purchase Order line (purpose=special_order, linked to SO line)
            vendor delivers → Goods receipt (stock +50 to warehouse, reserved for SO)
            vendor invoice  → Purchase invoice (AP +, cost lot = purchase price)
   Fulfil → Sales invoice 100 (from stock 50 own + 50 bought lots)
```
Three business variants — support each by configuration, not code forks:

| Variant | Description | Stock effect | When |
|---|---|---|---|
| **Stock-then-sell** | Receive vendor goods into our warehouse, then invoice customer | Goods receipt +50, sale −100 | We physically handle books (default) |
| **Drop-ship / back-to-back** | Vendor ships straight to customer | Receipt and issue both virtual; margin = sale − purchase | Large orders, saves handling |
| **Sell first, source later (backorder)** | Invoice/order accepted before vendor delivers | Backorder on SO line; invoice only delivered qty | Long lead time |

### 5.5 Data & rules
```
purchase_orders / purchase_order_items  (new; purpose, linked_sales_order_item_id)
goods_receipts / goods_receipt_items    (new; stock + into destination warehouse; partial receipts)
stock_lots (or cost layers)             (product_id, sub_warehouse_id, qty_remaining, unit_cost, source, vendor_id, received_at)
sales_invoice_items.sourcing            JSON/rows: [{lot_id|source:'own', qty:50, cost:…},{source:'purchased', qty:50, cost:…}]
```
Costing: weighted average per product (simplest) **or** FIFO lots (needed if own-cost and bought-cost differ a lot and margin per source must be visible). Recommendation: lots (FIFO) because the user wants to *differentiate* the two sources and the margin on each.

Rules:
- **Royalty only on `own_publication` quantities** (e.g. 50 of 100). `BookSaleService` takes the lot source into account; bought lots → no `book_sales`.
- Margin report per line: own 50 × (price − print cost − royalty) vs bought 50 × (price − purchase cost).
- Purchase invoice items **require a destination warehouse** when `purpose=restock|special_order` (fixes today's no-restock gap).
- A special-order purchase auto-reserves received qty for the linked SO so that it isn't sold to someone else.
- Vendor price per product and last purchase cost stored for quick quoting.
- Allow one invoice line to be **split** into two sourcing lines automatically (50 stock + 50 purchase) with different cost.

### 5.6 Scenarios

| # | Scenario | Answer |
|---|---|---|
| a | 100 requested, 50 own, 50 to buy | SO splits 50/50; PO for 50; invoice after receipt (or drop-ship) |
| b | Vendor delivers only 30 | Partial goods receipt; invoice 80 now, 20 remain backordered |
| c | Vendor price rose after quote | Quote/sale price fixed → margin shrinks; show warning at PO time when cost > price − min margin |
| d | The bought books are the **same title** we publish (e.g. we're out of stock, buy from distributor) | Same product, different lot source; royalty applies **only if** the contract says royalties are due on all copies sold — configurable per contract (`royalty_on_third_party_copies`); default: no |
| e | Customer returns books → which lot/cost/royalty | Return line remembers the source lot (feature 1); goes back to that lot cost; royalty reversed only if it was accrued |
| f | Bought books are damaged on arrival | Goods receipt `qty_damaged` → purchase return/debit note to vendor (purchase return doc) |
| g | Order cancelled after PO placed | PO stays; received/arriving stock becomes normal stock (release reservation) or PO cancelled if not shipped |
| h | Vendor paid before delivery (advance) | Vendor advance via the allocation engine (feature 3 mirrored for AP) |
| i | Vendor is also a customer | Party is both; optional offset document |
| j | Foreign vendor | Purchase in vendor currency + landed cost (shipping/customs) allocated to lots |
| k | Which products are "mine" in reports? | Filter by `products.origin`; stock valuation split by origin |
| l | Service/expenses on purchase invoice | `purpose=expense` lines (no stock) — replaces the `manual_amount` hack |
| m | Existing data | Backfill `origin='own_publication'` for books having a `contractor_book`; others `purchased_for_resale` after review |

---

## 6. Multi-part books and sets

### 6.1 Requirement
Books come in parts (Part 1, 2, 3). Track **each part's quantity** and **how many complete sets** are available.

### 6.2 Current state
`products.type` enum contains `bundle` but there is **no components table**, no volume number, no set logic.
Each part would today have to be an unrelated product, and a set an unrelated product with its own stock count — so selling a set
wouldn't reduce part stocks, and set stock would be maintained by hand.

### 6.3 Design: Parts are real products; the set is a parent that references them
```
products             (parent "set" has type='bundle' or new is_set=true; each part is type='book')
product_components
  id, set_product_id, component_product_id, volume_no, qty_per_set (default 1)
  UNIQUE(set_product_id, component_product_id)
set_assembly_orders / items   (only for physical pre-packed sets — see 6.5)
```
- Part 1/2/3 each have own SKU/ISBN (`books` row), own stock in `sub_warehouse_products`, own price and — importantly — own cost.
- The set has its own SKU/ISBN (publishers usually assign one), price (often cheaper than Σparts), and **no stock row of its own** in the default mode.
- Optional `books.volume_no`, `books.series_id` for filtering and display ("Series: X — Vol 2/3").

### 6.4 Availability math (the "how many sets do I have" answer)
```
sets_available(warehouse) = MIN over components of floor( stock(component) / qty_per_set )
```
Example: Part 1 = 120, Part 2 = 100, Part 3 = 80 → **80 complete sets**.
Leftover ("orphan") parts: Part 1 = 40, Part 2 = 20, Part 3 = 0 — shown in a **Sets & Orphans report**; also "limiting part" (the volume that caps the set count) so the user knows which volume to reprint.
Always evaluated **per warehouse** (a fair may hold 30 complete sets while main holds 80) and optionally **total across warehouses** (with a note that sets can't be completed across locations without a transfer).

### 6.5 Two operating modes (choose per set, default = virtual)

| Mode | How it works | Pros | Cons |
|---|---|---|---|
| **Virtual set (kit)** *(recommended default)* | Set has no stock. Selling the set creates **one invoice line (set)** and **N hidden/explicit stock movements** (one per part). Availability computed (6.4). | Always consistent; no assembling effort; can sell loose volumes freely | Can't count pre-wrapped boxes |
| **Physical set (assembled)** | An *assembly order* consumes parts and creates set stock (shrink-wrapped box with own barcode); *disassembly* reverses. | Matches warehouses that pack sets in advance, own barcode | More documents; double stock (loose + boxed) |

Hybrid is allowed: set stock row exists (boxed sets) **and** virtual availability from loose parts; selling first consumes boxed sets, then explodes loose parts (priority configurable).

### 6.6 Selling, returning, pricing

**Sale of a set** (virtual): invoice line `Set ×N, price P` →
1. Check each part's stock (`N × qty_per_set`); block/warn if any part is short and tell which one.
2. Stock movements `outbound` per part (reason `sales`, `parent_item_id` = set line).
3. Revenue/price allocation across parts for reporting, royalty and returns:
   `part_price = P × (part_base_price / Σ part_base_prices)` (stored as snapshot on a child record `sales_invoice_item_components`).
4. Royalty: `contractor_books` is per **book** (`book_id` unique) → compute per part from its allocated price using each part's own contract (`BookSaleService` runs per component). Set-level contract (single author for all volumes) is the common case; then all parts typically share contractor/percentage.
5. Cost (COGS) = Σ part costs.

**Return of a set / of loose parts** (ties to feature 1): the returned unit is the *part*. If the client returns a whole set → N part movements; if he returns only Part 2 of a set he bought, the credit =
that part's **allocated price snapshot**, not the "set price ÷ 3".

**Selling parts individually** is always allowed; prices differ (single-volume price vs set price).

**Purchasing/printing** is done per part (different print runs/prices per volume). Set cost = Σ part costs.

### 6.7 Scenarios

| # | Scenario | Answer |
|---|---|---|
| a | Stock Part1=120, Part2=100, Part3=80 | 80 sets, orphans 40/20/0, limiting part = Part 3 |
| b | Sell 10 sets | Parts become 110/90/70; sets = 70 |
| c | Sell Part 2 alone ×15 | Part 2 = 75 → sets drop to min(…, 75, …) |
| d | Customer wants set but Part 3 short by 5 | Block, or split: sell available sets + backorder/purchase (feature 5) |
| e | Part reprinted in a bigger run than others | Orphans of the bigger run; "reprint advice" = max(sets target) per part |
| f | Different editions/ISBNs for set vs parts | Each has own ISBN; lookup by any barcode resolves to part or set |
| g | Price of set changes | Only future sales; history keeps allocated snapshot |
| h | Set returned with one volume damaged | Return 2 good parts (good warehouse) + 1 damaged (quarantine); credit by allocated part prices |
| i | Fair shipment of sets | Ship parts (not set) so the fair warehouse can sell loose too; fair POS shows set availability for that warehouse |
| j | Stock count (جرد) | Count parts only (sets virtual); for boxed sets count set stock separately |
| k | Part used in two different sets (anthology, "Vol 1 in Set A and Set B") | Supported by components table (many-to-many) |
| l | Quotation containing a set | Quote line = set; explode only at invoicing |
| m | Search/product screen | Show badge "Set of 3 · available 80" and the three parts with stock each |
| n | Existing `bundle` products | Migration: convert to sets, add components manually (cannot be inferred) |
| o | Fractional / uneven quantities (e.g. 2 copies of Part 1 per set) | `qty_per_set` handles it |

---

## 7. Cross-cutting rules
- **Immutability & reversals**: posted documents are never edited in place; corrections are reversal + new document (today's `updateInvoice` deletes and re-creates items and stock movements — acceptable for drafts only).
- **Atomicity**: every operation (stock + royalty + allocation + ledger) in one DB transaction with `lockForUpdate` on stock rows (copy the pattern already used by `cancelInvoice`/`activateInvoice`).
- **Mandatory warehouse** on any document with stock products.
- **Snapshots**: price, tax, currency/rate, cost, royalty terms and set-component prices are copied to the line at the time of the event — all later documents (returns, allocations) reference snapshots.
- **Numbering**: one sequence table per doc type/year with row lock (gapless; replace "last id + 1").
- **Statuses**: cancelled/voided documents excluded from every balance query (fix `Party` aggregates).
- **Permissions & approvals**: manual return price, discounts above threshold, write-offs, backdating, cancelling posted docs.
- **Localization**: all new documents need AR/EN lang files and RTL print templates like existing modules (`resources/lang/{ar,en}`).
- **Tests**: unit tests for allocation math, set availability, return caps, FX conversion; feature tests per scenario table above.

## 8. Dependencies & build order

| Feature | Needs first | Notes |
|---|---|---|
| 3 Payment allocation | Decimal money, status fix | Foundation for 1, 2(deposit), 4(FX payments), vendor side |
| 1 Sales return | 3 (credit settlement), product cost (for COGS), warehouse mandatory | Royalty reversal on `book_sales` |
| 2 Quotation | none (independent); deposit uses 3 | Quick win, low risk — can ship early |
| 6 Sets | stock integrity (purchases restock, mandatory warehouse) | Touches invoices, returns, search, fairs |
| 5 Own vs bought | Goods receipt, cost lots, PO | Heaviest in inventory logic |
| 4 Book fairs | Multi-currency, in-transit, expenses, cost, return documents (1) | Largest scope; deliver in two steps: (a) fair + shipments + stock reconciliation in base currency, (b) foreign currency & FX |

**Suggested delivery order**: ① Foundations (money, status bug, warehouse mandatory, purchase restock, cost) →
② Feature 3 → ③ Feature 2 (quick) → ④ Feature 1 → ⑤ Feature 6 → ⑥ Feature 5 → ⑦ Feature 4 (a) → ⑧ Feature 4 (b) → ⑨ ledger/double-entry if not already done in parallel.

## 9. Questions to confirm with the business owner
1. Sales return default price policy and approval limit (§1.10).
2. Do customers ever pay by cheque (post-dated)? Needed for allocation timing (§3.5h).
3. Fair currencies and rate policy: fixed per fair vs daily (§4.4).
4. Costing method preferred: weighted average vs FIFO lots (§5.5).
5. Do you pre-pack physical sets or only sell loose volumes (§6.5)?
6. Is the company VAT-registered / on ETA e-invoicing (affects credit-note format, §1.8q)?
7. Do you work with bookstores on **consignment / sale-or-return** (strong overlap with returns and fairs)?
8. In example #5 please confirm the quantities (100 total = 50 + 50?).
