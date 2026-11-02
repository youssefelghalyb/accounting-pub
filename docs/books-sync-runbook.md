# Books master-sheet sync — runbook

Goal: make the system's products/books/categories/contractors **equal to the new Excel sheet**, remove books that are not in the sheet, and prove that **no sales invoice, payment, stock row or royalty record changed**.

Tools added (all under `Modules/Product`):

| What | Where |
|---|---|
| Migration: deposit fields + contract status/gift copies | `database/migrations/2026_11_01_000100_add_deposit_and_contract_status_fields.php` |
| `php artisan product:sync-books` | `app/Console/SyncBooksCommand.php` + `Services/BooksSyncService.php`, `BooksSheetReader.php` |
| `php artisan product:prune-books` | `app/Console/PruneBooksCommand.php` + `Services/BooksPruneService.php` |
| SQL tests | `docs/sql/books-sync/01_preflight_on_old_copy.sql`, `02_compare_old_vs_new.sql`, `03_verify_against_sheet.sql` |

The commands were exercised against a throw-away SQLite database with invoices, royalties, stock and mismatching IDs (dry run changed nothing; apply left invoices/royalties/SKUs byte-identical; used books were blocked from deletion). They have **not** been run against your MySQL data — follow the steps below on the copy first.

---

## 0. Quick checklist (do these in order)

1. Stop users creating invoices during the update.
2. Backup + create `accounting_old` copy (§3 step 1).
3. `php artisan migrate`.
4. Save the new sheet on the server, e.g. `/var/www/new_books.xlsx` (keep the header names exactly as in your sheet).
5. `php artisan product:sync-books /var/www/new_books.xlsx` → read the tables and `storage/app/book-sync/*_conflicts.csv`. Fix the sheet or accept the conflicts.
6. `php artisan product:sync-books /var/www/new_books.xlsx --apply` (add `--relink` only if the dry run listed `id_pairing_mismatch` rows you want repaired).
7. `php artisan product:prune-books /var/www/new_books.xlsx` → review `SAFE` vs `BLOCKED`.
8. `php artisan product:prune-books /var/www/new_books.xlsx --delete --deactivate-blocked` (type `yes`).
9. Run `02_compare_old_vs_new.sql` → every PART A row must say PASS.
10. Export the sheet to CSV and run `03_verify_against_sheet.sql` → every query returns 0 rows.
11. Open a few books and an old invoice in the UI; then let users back in.
12. Anything fails in step 9 → restore from the backup (§8) and send me the output.

## 1. Column → field mapping

| Sheet column | Saved to | Rule |
|---|---|---|
| Book ID / Product ID | `books.id` / `products.id` | must be the stored pair (`books.product_id = Product ID`); see §6 for mismatches |
| Book Name | `products.name` | updated |
| SKU | `products.sku` | **never changed on existing rows** (only used when a row is inserted) |
| Price | `products.base_price` | updated (old invoices keep their own `unit_price`) |
| Status | `products.status` | `Active` → `active`, anything else → `inactive` |
| **Notes** | `products.description` | description := Notes (the sheet's own *Description* column is empty; empty Notes never wipes an existing description) |
| ISBN, Pages, Cover Type, Published Date, Language | `books.isbn`, `num_of_pages`, `cover_type`, `published_at`, `language` | updated |
| Is Translated / From / To / Translator | `books.is_translated`, `translated_from`, `translated_to`, `translator_name` | from/to only kept when Is Translated = Yes |
| اشراف / تقديم | `books.supervisor` / `books.introduction_by` | updated |
| Category / Sub Category | `book_categories` | found by exact name (sub-category under its category), **created if missing** |
| Used in Sales | — | ignored |
| Author 1 | `books.authors` | text |
| المتعاقد | `contractors` + `contractor_books` | find by name or create; one contract per book |
| Nationality | `contractors.nationality` | updated when the sheet has a value |
| status2 (`ملغي`) | `books.contract_status` **and** `contractor_books.status` = `ended` (else `active`) | display only — royalty accrual logic is **not** changed by it |
| نسخ الهدايا | `contractor_books.gift_copies_given` | number of gift copies already given to the author |
| رقم الإيداع / سنة الإيداع / حالة الإيداع | `books.deposit_number` / `deposit_year` / `deposit_status` | new columns |

Royalty %: the sheet has none, so every contract the sync **creates** gets **10% of the sale price** (`--default-percentage=10`, change if needed). Existing contracts still at 0% (e.g. made by the old importer) are also set to 10%; contracts that already have a non-zero percentage are never overwritten. Past royalty accruals keep their own snapshot, so only future sales use the new %.

## 2. Why invoices cannot be affected

* Invoice lines store their own snapshot (`product_name`, `product_sku`, `unit_price`, `line_total`); royalty rows (`book_sales`) store price/percentage snapshots. Changing a book's name/price/ISBN changes none of them.
* IDs and SKUs of existing products are never edited; products are never re-numbered.
* A book's **contractor is not switched** if its contract already has royalty accruals or transactions (that would silently re-assign money already earned) — it is written to the conflicts report instead.
* Stock tables are not touched by the sync.
* Everything runs in **one DB transaction**; before commit the command compares a fingerprint (row counts + sums of money/qty/ID columns) of invoices, invoice lines, royalties, receipts, payments, purchases, stock and contract transactions with the one taken before. Any difference → rollback + error.
* **Dry run is the default** (executes for real, then rolls back), so the report is exactly what `--apply` will do.

## 3. Step by step

```bash
# 0) maintenance window: stop users from creating invoices while this runs

# 1) backup + copy of the live DB (the copy is what the SQL tests compare against)
mysqldump --single-transaction -u root -p accountingsys > accountingsys_before_sync.sql
mysql -u root -p -e "CREATE DATABASE accounting_old CHARACTER SET utf8mb4"
mysql -u root -p accounting_old < accountingsys_before_sync.sql
```

```bash
# 2) deploy the code and run the additive migration (adds nullable/defaulted columns only)
php artisan migrate
```
> The copy `accounting_old` was taken before the migration, so the new columns are not in it. The SQL tests never compare those columns.

```sql
-- 3) SQL: mysql -u root -p < docs/sql/books-sync/01_preflight_on_old_copy.sql
--    (read it: which books are invoiced, which contracts have royalty history, existing duplicates)
```

```bash
# 4) DRY RUN — nothing is saved. Read the tables + CSVs in storage/app/book-sync/
php artisan product:sync-books /path/new_books.xlsx

# 5) apply
php artisan product:sync-books /path/new_books.xlsx --apply
```

```bash
# 6) which books are NOT in the sheet, and are any used? (read-only check)
php artisan product:prune-books /path/new_books.xlsx

# 7) delete the SAFE ones (asks for confirmation).  Used ones are never deleted.
php artisan product:prune-books /path/new_books.xlsx --delete
#    options:  --include-history      also delete ones whose only reference is old stock movements
#              --deactivate-blocked   set used (BLOCKED) books to inactive so they leave the selling lists
#              --yes                  no confirmation prompt
```

```bash
# 8) SQL proof (replace accounting_old / accounting_new with your names)
mysql -u root -p < docs/sql/books-sync/02_compare_old_vs_new.sql   # PART A: every row must say PASS
mysql --local-infile=1 -u root -p < docs/sql/books-sync/03_verify_against_sheet.sql   # after exporting the sheet to /tmp/books.csv
```

## 4. Reading the prune check

| Verdict | Meaning | `--delete` |
|---|---|---|
| `SAFE` | no sales/purchase line, royalty, contract transaction or stock | deleted |
| `SAFE_HISTORY` | only old `stock_movements` rows (they go with it) | deleted only with `--include-history` |
| `BLOCKED` | used by a sales invoice line (shows how many invoices and how many are live), purchase line, royalty accrual, contract transaction, or has stock on hand | **never deleted**; `--deactivate-blocked` sets it inactive |

Each candidate is re-checked inside the delete transaction, so something that became used in the meantime is skipped.

## 5. Conflict types (in `*_conflicts.csv`)

| type | What it means | What to do |
|---|---|---|
| `id_pairing_mismatch` | the sheet's Book ID/Product ID pair differs from what `books` stores | re-run with `--relink` if no history (see §6), else fix by hand |
| `id_pairing_blocked_by_history` | `--relink` refused: the wrong book/product has invoices/royalties/stock | decide manually which record is the real one |
| `isbn_exists_under_other_ids` | sheet row would be inserted but its ISBN already belongs to another book | probably the same book with different IDs — resolve manually |
| `contractor_change_blocked` | sheet contractor ≠ DB contractor and the contract already has royalty history | keep the old contract for past sales; end it and create a new one in the UI |
| `gift_copies_without_contractor` | gift copies given but no contractor in the sheet | add the contractor |

## 6. "Book ID and Product ID must match"

In the sheet the pair is e.g. Book 9945 ↔ Product 9946 (they intentionally differ by one). The sync treats the **pair** as the truth:

* both exist and are linked → update;
* neither exists → insert both with exactly those IDs;
* product exists but has no book → create the book row with the sheet's Book ID;
* anything else = mismatch → reported, and repaired only with `--relink` **and only if** the wrong book/product has no invoice, purchase, stock or royalty history (the wrongly-linked history-free book is removed and re-created under the right ID; its contract is re-created from the sheet).

## 7. If a SQL test fails

* PART A tests T01–T15 protect money/stock/royalties. Any failure → **stop**, do not let users in, restore (§8), send the output of the failing test.
* T16–T18 are structure checks (pairing, orphans, duplicates) — fix with the details queries in PART C.

## 8. Rollback

```bash
mysql -u root -p -e "DROP DATABASE accountingsys; CREATE DATABASE accountingsys CHARACTER SET utf8mb4"
mysql -u root -p accountingsys < accountingsys_before_sync.sql
```
(`php artisan migrate:rollback --step=1` only removes the new columns; it does not undo data changes.)

## 9. Not covered / follow-ups

* Forms/views for the new fields (deposit info, gift copies, contract status) are not built — data is stored and shown only by SQL for now.
* `status2 = ملغي` does not stop royalty accrual; tell me if an ended contract should stop accruing on new sales.
* Stock (`الكمية`) is not in this sheet, so no stock reconciliation happens here (the older `product:import-books` still does that).
* Category matching is by exact name; spelling variants (أ/ا, ة/ه) create a new category — SQL test 3b/B6 lists them.
