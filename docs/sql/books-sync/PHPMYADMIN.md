# Running the checks in phpMyAdmin

Names used in the files: live database **accountingsys**, backup copy **accounting_old** (the copy made *before* the sync).
If yours differ, use Ctrl+H (find/replace) inside the SQL box before pressing **Go**.
You need a phpMyAdmin user that can read both databases (root can).

## 1. Old vs new — must all PASS
1. phpMyAdmin → click any database (e.g. `accountingsys`) → **SQL** tab.
2. Paste the whole content of `02a_tests_phpmyadmin.sql` → **Go**.
3. One table appears: columns `t`, `failures`, `result`. **Every row must say PASS.**
   Any FAIL → stop, do not let users in, restore from backup, send me that row.

## 2. What changed (informational) — run each query on its own
Open `02b_changes_phpmyadmin.sql`. Paste **one query at a time** (they are separated by `-- B…` comments), press Go, read, then the next:

| Block | What to look for |
|---|---|
| B1 | row counts old vs new (books/products should match the sheet) |
| B2 / B3 | how many values changed per column (same numbers as the dry run) |
| B4 | products removed (should be exactly the 5 deleted books) |
| **B4b** | removed products that had stock: must be the same 5 products and quantities (97/100/100/100/100) as `storage/app/book-sync/*_zeroed_stock.csv` |
| B5 | added products (0 expected for this sheet) |
| **B6** | new categories (5), new contractors (213), new contracts. **Scan the contractor names: any that look like a duplicate of a person you already had?** |
| B7b | contracts moved from 0% to 10% |
| B7 | contracts whose contractor changed (565) |

## 3. Does the live DB equal the sheet?
1. On the server create the clean CSV (full-digit ISBNs, no Excel formatting problems):
   ```bash
   php artisan product:sync-books new_books.xlsx --export-csv=storage/app/book-sync/sheet_for_check.csv
   ```
   Download `storage/app/book-sync/sheet_for_check.csv` to your PC.
2. phpMyAdmin → click **accountingsys** → **SQL** → paste `03a_create_sheet_table_phpmyadmin.sql` → Go (creates empty table `sheet_books`).
3. Click table `sheet_books` → **Import** tab:
   * File: `sheet_for_check.csv`, Character set **utf-8**, Format **CSV**
   * **"Skip this number of queries from the start": 1** (the header line)
   * Columns separated with `,`, enclosed with `"`, escaped with `"`, lines terminated `auto`
   * Go → "Import has been successfully finished, 1744 queries executed" (or similar).
4. Back on the **SQL** tab (accountingsys selected): paste `03b_checks_phpmyadmin.sql` **one query at a time**.
   First the `UPDATE` (normalising), then **3a … 3f must each return "MySQL returned an empty result set (0 rows)"**.
   * Expected exceptions: Book **11788** may show in 3d (its ISBN is empty in the sheet and the old ISBN was kept).
   * 3g shows counts; 3h should be empty (no contract left at 0%).
5. Optional cleanup: `DROP TABLE sheet_books;`
