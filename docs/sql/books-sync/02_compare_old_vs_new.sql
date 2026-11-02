-- =====================================================================================
-- 02_compare_old_vs_new.sql   (MySQL 5.7+/8, both databases on the SAME server)
--
--   accounting_old = untouched copy taken BEFORE the sync
--   accounting_new = the database the sync/prune commands were run on
-- Search & replace those two names with yours.
--
-- PART A: pass/fail tests.  Every row must show failures = 0.  Any other number = problem;
--         the "details" query for that test is in PART C.
-- PART B: informational diff summary (what changed).
-- Nothing here writes data.
-- =====================================================================================

-- ============================ PART A — MUST ALL BE 0 =================================
SELECT t, failures, IF(failures = 0, 'PASS', 'FAIL') AS result FROM (

-- T01 sales invoices identical (header rows, every money column)
SELECT 'T01 sales_invoices identical' AS t, COUNT(*) AS failures FROM (
  SELECT o.id FROM accounting_old.sales_invoices o LEFT JOIN accounting_new.sales_invoices n ON n.id = o.id
   WHERE n.id IS NULL OR NOT (o.invoice_number <=> n.invoice_number AND o.party_id <=> n.party_id
     AND o.invoice_date <=> n.invoice_date AND o.subtotal <=> n.subtotal AND o.discount_amount <=> n.discount_amount
     AND o.tax_amount <=> n.tax_amount AND o.total_amount <=> n.total_amount AND o.paid_amount <=> n.paid_amount
     AND o.status <=> n.status AND o.deleted_at <=> n.deleted_at)
  UNION ALL
  SELECT n.id FROM accounting_new.sales_invoices n LEFT JOIN accounting_old.sales_invoices o ON o.id = n.id WHERE o.id IS NULL
) x

UNION ALL
-- T02 sales invoice lines identical (product_id, name/sku snapshot, qty, price, total)
SELECT 'T02 sales_invoice_items identical', COUNT(*) FROM (
  SELECT o.id FROM accounting_old.sales_invoice_items o LEFT JOIN accounting_new.sales_invoice_items n ON n.id = o.id
   WHERE n.id IS NULL OR NOT (o.sales_invoice_id <=> n.sales_invoice_id AND o.product_id <=> n.product_id
     AND o.product_name <=> n.product_name AND o.product_sku <=> n.product_sku AND o.quantity <=> n.quantity
     AND o.unit_price <=> n.unit_price AND o.discount_amount <=> n.discount_amount AND o.line_total <=> n.line_total)
  UNION ALL
  SELECT n.id FROM accounting_new.sales_invoice_items n LEFT JOIN accounting_old.sales_invoice_items o ON o.id = n.id WHERE o.id IS NULL
) x

UNION ALL
-- T03 royalty accruals identical (snapshots must never be recalculated)
SELECT 'T03 book_sales identical', COUNT(*) FROM (
  SELECT o.id FROM accounting_old.book_sales o LEFT JOIN accounting_new.book_sales n ON n.id = o.id
   WHERE n.id IS NULL OR NOT (o.book_id <=> n.book_id AND o.contractor_book_id <=> n.contractor_book_id
     AND o.invoice_id <=> n.invoice_id AND o.invoice_item_id <=> n.invoice_item_id AND o.quantity <=> n.quantity
     AND o.sale_price_snapshot <=> n.sale_price_snapshot AND o.base_price_snapshot <=> n.base_price_snapshot
     AND o.percentage_snapshot <=> n.percentage_snapshot AND o.contractor_profit <=> n.contractor_profit
     AND o.is_gift <=> n.is_gift)
  UNION ALL
  SELECT n.id FROM accounting_new.book_sales n LEFT JOIN accounting_old.book_sales o ON o.id = n.id WHERE o.id IS NULL
) x

UNION ALL
-- T04 receipts identical
SELECT 'T04 receipt_vouchers identical', COUNT(*) FROM (
  SELECT o.id FROM accounting_old.receipt_vouchers o LEFT JOIN accounting_new.receipt_vouchers n ON n.id = o.id
   WHERE n.id IS NULL OR NOT (o.party_id <=> n.party_id AND o.account_id <=> n.account_id
     AND o.sales_invoice_id <=> n.sales_invoice_id AND o.amount <=> n.amount AND o.voucher_date <=> n.voucher_date
     AND o.deleted_at <=> n.deleted_at)
  UNION ALL
  SELECT n.id FROM accounting_new.receipt_vouchers n LEFT JOIN accounting_old.receipt_vouchers o ON o.id = n.id WHERE o.id IS NULL
) x

UNION ALL
-- T05 payment vouchers identical
SELECT 'T05 payment_vouchers identical', COUNT(*) FROM (
  SELECT o.id FROM accounting_old.payment_vouchers o LEFT JOIN accounting_new.payment_vouchers n ON n.id = o.id
   WHERE n.id IS NULL OR NOT (o.party_id <=> n.party_id AND o.account_id <=> n.account_id
     AND o.purchase_invoice_id <=> n.purchase_invoice_id AND o.amount <=> n.amount AND o.deleted_at <=> n.deleted_at)
  UNION ALL
  SELECT n.id FROM accounting_new.payment_vouchers n LEFT JOIN accounting_old.payment_vouchers o ON o.id = n.id WHERE o.id IS NULL
) x

UNION ALL
-- T06 purchase invoices + lines identical
SELECT 'T06 purchase_invoices + items identical', COUNT(*) FROM (
  SELECT o.id FROM accounting_old.purchase_invoices o LEFT JOIN accounting_new.purchase_invoices n ON n.id = o.id
   WHERE n.id IS NULL OR NOT (o.party_id <=> n.party_id AND o.total_amount <=> n.total_amount
     AND o.paid_amount <=> n.paid_amount AND o.status <=> n.status AND o.deleted_at <=> n.deleted_at)
  UNION ALL
  SELECT o.id FROM accounting_old.purchase_invoice_items o LEFT JOIN accounting_new.purchase_invoice_items n ON n.id = o.id
   WHERE n.id IS NULL OR NOT (o.purchase_invoice_id <=> n.purchase_invoice_id AND o.product_id <=> n.product_id
     AND o.quantity <=> n.quantity AND o.unit_price <=> n.unit_price AND o.line_total <=> n.line_total)
) x

UNION ALL
-- T07 stock of every product that still exists is unchanged (sync never touches stock)
SELECT 'T07 stock on hand unchanged (surviving products)', COUNT(*) FROM (
  SELECT o.id FROM accounting_old.sub_warehouse_products o
   JOIN accounting_new.products p ON p.id = o.product_id
   LEFT JOIN accounting_new.sub_warehouse_products n ON n.id = o.id
   WHERE n.id IS NULL OR NOT (o.sub_warehouse_id <=> n.sub_warehouse_id AND o.product_id <=> n.product_id AND o.quantity <=> n.quantity)
) x

UNION ALL
-- T09 contract money history identical
SELECT 'T09 contract_transactions identical', COUNT(*) FROM (
  SELECT o.id FROM accounting_old.contract_transactions o LEFT JOIN accounting_new.contract_transactions n ON n.id = o.id
   WHERE n.id IS NULL OR NOT (o.contractor_book_id <=> n.contractor_book_id AND o.type <=> n.type
     AND o.amount <=> n.amount AND o.transaction_date <=> n.transaction_date)
  UNION ALL
  SELECT n.id FROM accounting_new.contract_transactions n LEFT JOIN accounting_old.contract_transactions o ON o.id = n.id WHERE o.id IS NULL
) x

UNION ALL
-- T10 contracts that carry MONEY (accrued royalty <> 0 or any payment/transaction) kept the same contractor / basis
--     and percentage. (A contract at 0% may become the 10% default, a contract whose past sales earned 0 may move to the
--     real contractor from the sheet — nothing is re-attributed because nothing was earned.)
SELECT 'T10 contracts carrying money not re-attributed', COUNT(*) FROM accounting_old.contractor_books o
 LEFT JOIN accounting_new.contractor_books n ON n.id = o.id
 WHERE (IFNULL((SELECT SUM(s.contractor_profit) FROM accounting_old.book_sales s WHERE s.contractor_book_id = o.id), 0) <> 0
     OR EXISTS (SELECT 1 FROM accounting_old.contract_transactions t WHERE t.contractor_book_id = o.id))
   AND (n.id IS NULL OR NOT (o.contractor_id <=> n.contractor_id AND (o.profit_percentage <=> n.profit_percentage OR o.profit_percentage = 0)
        AND o.percentage_basis <=> n.percentage_basis AND o.book_id <=> n.book_id))

UNION ALL
-- T11 every product used by an invoice (sales or purchase) still exists with the SAME id and SKU
SELECT 'T11 invoiced products still exist, same id+SKU', COUNT(*) FROM accounting_old.products o
 LEFT JOIN accounting_new.products n ON n.id = o.id
 WHERE (EXISTS (SELECT 1 FROM accounting_old.sales_invoice_items i WHERE i.product_id = o.id)
     OR EXISTS (SELECT 1 FROM accounting_old.purchase_invoice_items i WHERE i.product_id = o.id))
   AND (n.id IS NULL OR NOT (o.sku <=> n.sku))

UNION ALL
-- T12 SKU of every surviving product unchanged ("keep SKU as it was")
SELECT 'T12 SKU unchanged for all surviving products', COUNT(*) FROM accounting_old.products o
 JOIN accounting_new.products n ON n.id = o.id WHERE NOT (o.sku <=> n.sku)

UNION ALL
-- T13 no product was deleted that any invoice / royalty / contract used
SELECT 'T13 nothing used was deleted', COUNT(*) FROM accounting_old.products o
 WHERE NOT EXISTS (SELECT 1 FROM accounting_new.products n WHERE n.id = o.id)
   AND (EXISTS (SELECT 1 FROM accounting_old.sales_invoice_items i WHERE i.product_id = o.id)
     OR EXISTS (SELECT 1 FROM accounting_old.purchase_invoice_items i WHERE i.product_id = o.id)
     OR EXISTS (SELECT 1 FROM accounting_old.book_sales s JOIN accounting_old.books b ON b.id = s.book_id WHERE b.product_id = o.id))

UNION ALL
-- T14 customer / vendor balances identical (sales - receipts - purchases + payments), per party
SELECT 'T14 party balances identical', COUNT(*) FROM (
  SELECT p.id,
    (SELECT IFNULL(SUM(total_amount),0) FROM accounting_old.sales_invoices WHERE party_id = p.id)
  - (SELECT IFNULL(SUM(amount),0)       FROM accounting_old.receipt_vouchers WHERE party_id = p.id)
  - (SELECT IFNULL(SUM(total_amount),0) FROM accounting_old.purchase_invoices WHERE party_id = p.id)
  + (SELECT IFNULL(SUM(amount),0)       FROM accounting_old.payment_vouchers WHERE party_id = p.id) AS bal_old,
    (SELECT IFNULL(SUM(total_amount),0) FROM accounting_new.sales_invoices WHERE party_id = p.id)
  - (SELECT IFNULL(SUM(amount),0)       FROM accounting_new.receipt_vouchers WHERE party_id = p.id)
  - (SELECT IFNULL(SUM(total_amount),0) FROM accounting_new.purchase_invoices WHERE party_id = p.id)
  + (SELECT IFNULL(SUM(amount),0)       FROM accounting_new.payment_vouchers WHERE party_id = p.id) AS bal_new
  FROM accounting_old.parties p
) b WHERE ROUND(bal_old,2) <> ROUND(bal_new,2)

UNION ALL
-- T15 royalty owed per contractor identical (contractors whose accrued total was 0 are ignored)
SELECT 'T15 royalty per contractor identical', COUNT(*) FROM (
  SELECT cb.contractor_id, SUM(s.contractor_profit) AS v FROM accounting_old.book_sales s JOIN accounting_old.contractor_books cb ON cb.id = s.contractor_book_id GROUP BY cb.contractor_id
) o LEFT JOIN (
  SELECT cb.contractor_id, SUM(s.contractor_profit) AS v FROM accounting_new.book_sales s JOIN accounting_new.contractor_books cb ON cb.id = s.contractor_book_id GROUP BY cb.contractor_id
) n ON n.contractor_id = o.contractor_id WHERE ROUND(IFNULL(o.v,0),2) <> 0 AND (n.contractor_id IS NULL OR ROUND(o.v,2) <> ROUND(n.v,2))

UNION ALL
-- T16 Book ID <-> Product ID integrity in NEW: every book has a product, one book per product
SELECT 'T16 books<->products pairing valid', (
  SELECT COUNT(*) FROM accounting_new.books b LEFT JOIN accounting_new.products p ON p.id = b.product_id WHERE p.id IS NULL
) + (
  SELECT COUNT(*) FROM (SELECT product_id FROM accounting_new.books GROUP BY product_id HAVING COUNT(*) > 1) d
)

UNION ALL
-- T17 orphans in NEW (royalties / contracts / invoice lines pointing at nothing)
SELECT 'T17 no orphan references', (
  SELECT COUNT(*) FROM accounting_new.book_sales s LEFT JOIN accounting_new.books b ON b.id = s.book_id WHERE b.id IS NULL
) + (
  SELECT COUNT(*) FROM accounting_new.contractor_books c LEFT JOIN accounting_new.books b ON b.id = c.book_id WHERE b.id IS NULL
) + (
  SELECT COUNT(*) FROM accounting_new.sales_invoice_items i LEFT JOIN accounting_new.products p ON p.id = i.product_id WHERE p.id IS NULL
)

UNION ALL
-- T18 no duplicates created in NEW (contractors, categories)
SELECT 'T18 no duplicate contractors/categories', (
  SELECT COUNT(*) FROM (SELECT name FROM accounting_new.contractors WHERE deleted_at IS NULL GROUP BY name HAVING COUNT(*) > 1) d
) + (
  SELECT COUNT(*) FROM (SELECT name, parent_id FROM accounting_new.book_categories GROUP BY name, parent_id HAVING COUNT(*) > 1) d
)

) tests ORDER BY t;


-- ============================ PART B — WHAT CHANGED (informational) ===================
-- B1 row counts
SELECT 'products' AS tbl, (SELECT COUNT(*) FROM accounting_old.products) AS old_rows, (SELECT COUNT(*) FROM accounting_new.products) AS new_rows
UNION ALL SELECT 'books', (SELECT COUNT(*) FROM accounting_old.books), (SELECT COUNT(*) FROM accounting_new.books)
UNION ALL SELECT 'contractors', (SELECT COUNT(*) FROM accounting_old.contractors), (SELECT COUNT(*) FROM accounting_new.contractors)
UNION ALL SELECT 'contractor_books', (SELECT COUNT(*) FROM accounting_old.contractor_books), (SELECT COUNT(*) FROM accounting_new.contractor_books)
UNION ALL SELECT 'book_categories', (SELECT COUNT(*) FROM accounting_old.book_categories), (SELECT COUNT(*) FROM accounting_new.book_categories);

-- B2 how many surviving rows changed, per column (products)
SELECT SUM(NOT (o.name <=> n.name)) AS name, SUM(NOT (o.base_price <=> n.base_price)) AS price,
       SUM(NOT (o.status <=> n.status)) AS status, SUM(NOT (o.description <=> n.description)) AS description,
       COUNT(*) AS rows_compared
FROM accounting_old.products o JOIN accounting_new.products n ON n.id = o.id;

-- B3 same for books
SELECT SUM(NOT (o.isbn <=> n.isbn)) AS isbn, SUM(NOT (o.num_of_pages <=> n.num_of_pages)) AS pages,
       SUM(NOT (o.cover_type <=> n.cover_type)) AS cover, SUM(NOT (o.published_at <=> n.published_at)) AS published,
       SUM(NOT (o.language <=> n.language)) AS language, SUM(NOT (o.is_translated <=> n.is_translated)) AS is_translated,
       SUM(NOT (o.translator_name <=> n.translator_name)) AS translator, SUM(NOT (o.supervisor <=> n.supervisor)) AS supervisor,
       SUM(NOT (o.introduction_by <=> n.introduction_by)) AS intro, SUM(NOT (o.category_id <=> n.category_id)) AS category,
       SUM(NOT (o.sub_category_id <=> n.sub_category_id)) AS sub_category, SUM(NOT (o.authors <=> n.authors)) AS authors,
       SUM(NOT (o.product_id <=> n.product_id)) AS product_id_repointed, COUNT(*) AS rows_compared
FROM accounting_old.books o JOIN accounting_new.books n ON n.id = o.id;

-- B4 removed products (should be exactly the SAFE list from product:prune-books)
SELECT o.id, o.sku, o.name FROM accounting_old.products o
 LEFT JOIN accounting_new.products n ON n.id = o.id WHERE n.id IS NULL ORDER BY o.id;

-- B5 added products
SELECT n.id, n.sku, n.name FROM accounting_new.products n
 LEFT JOIN accounting_old.products o ON o.id = n.id WHERE o.id IS NULL ORDER BY n.id;

-- B4b removed products that still had stock in the OLD copy (only expected for books deleted with --zero-stock;
--     compare with storage/app/book-sync/*_zeroed_stock.csv — the list and quantities must be the same)
SELECT o.product_id, p.name, SUM(o.quantity) AS stock_removed
FROM accounting_old.sub_warehouse_products o JOIN accounting_old.products p ON p.id = o.product_id
WHERE o.quantity <> 0 AND NOT EXISTS (SELECT 1 FROM accounting_new.products n WHERE n.id = o.product_id)
GROUP BY o.product_id, p.name;

-- B6 new categories / new contractors / new contracts (at the 10% default)
SELECT 'new category' AS what, n.id, n.name FROM accounting_new.book_categories n LEFT JOIN accounting_old.book_categories o ON o.id = n.id WHERE o.id IS NULL
UNION ALL SELECT 'new contractor', n.id, n.name FROM accounting_new.contractors n LEFT JOIN accounting_old.contractors o ON o.id = n.id WHERE o.id IS NULL
UNION ALL SELECT 'new contract (default %)', n.id, CONCAT('book ', n.book_id) FROM accounting_new.contractor_books n LEFT JOIN accounting_old.contractor_books o ON o.id = n.id WHERE o.id IS NULL AND n.profit_percentage > 0;

-- B7b existing contracts moved from 0% to the default
SELECT n.id AS contractor_book_id, n.book_id, o.profit_percentage AS old_pct, n.profit_percentage AS new_pct
FROM accounting_old.contractor_books o JOIN accounting_new.contractor_books n ON n.id = o.id WHERE o.profit_percentage = 0 AND n.profit_percentage <> 0;

-- B7 contractors whose contract changed owner (only possible for contracts WITHOUT history — see T10)
SELECT n.book_id, o.contractor_id AS old_contractor, n.contractor_id AS new_contractor
FROM accounting_old.contractor_books o JOIN accounting_new.contractor_books n ON n.id = o.id WHERE NOT (o.contractor_id <=> n.contractor_id);


-- ============================ PART C — DETAILS when a test fails =====================
-- Replace the SELECT COUNT(*) of the failing test by SELECT * (same FROM/WHERE). Quick helpers:
-- C-T02: invoice lines that differ
--   SELECT o.id, o.product_id, o.unit_price, n.unit_price, o.line_total, n.line_total
--   FROM accounting_old.sales_invoice_items o LEFT JOIN accounting_new.sales_invoice_items n ON n.id = o.id
--   WHERE n.id IS NULL OR NOT (o.product_id <=> n.product_id AND o.unit_price <=> n.unit_price AND o.line_total <=> n.line_total);
-- C-T14: parties whose balance moved — copy the T14 sub-select and drop the outer COUNT(*).
-- If ANY of T01–T15 fails: do not continue; restore the live DB from the backup (runbook §8).
