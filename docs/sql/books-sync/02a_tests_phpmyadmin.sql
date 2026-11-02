-- phpMyAdmin version. Live database = accountingsys (the one the sync ran on), backup copy = old_accounting.
-- If your names differ: Ctrl+H (find/replace) in the SQL box before pressing Go.
-- Open phpMyAdmin > any database > SQL tab > paste this WHOLE file > Go.
-- ============================ PART A — MUST ALL BE 0 =================================
SELECT t, failures, IF(failures = 0, 'PASS', 'FAIL') AS result FROM (

-- T01 sales invoices identical (header rows, every money column)
SELECT 'T01 sales_invoices identical' AS t, COUNT(*) AS failures FROM (
  SELECT o.id FROM old_accounting.sales_invoices o LEFT JOIN accountingsys.sales_invoices n ON n.id = o.id
   WHERE n.id IS NULL OR NOT (o.invoice_number <=> n.invoice_number AND o.party_id <=> n.party_id
     AND o.invoice_date <=> n.invoice_date AND o.subtotal <=> n.subtotal AND o.discount_amount <=> n.discount_amount
     AND o.tax_amount <=> n.tax_amount AND o.total_amount <=> n.total_amount AND o.paid_amount <=> n.paid_amount
     AND o.status <=> n.status AND o.deleted_at <=> n.deleted_at)
  UNION ALL
  SELECT n.id FROM accountingsys.sales_invoices n LEFT JOIN old_accounting.sales_invoices o ON o.id = n.id WHERE o.id IS NULL
) x

UNION ALL
-- T02 sales invoice lines identical (product_id, name/sku snapshot, qty, price, total)
SELECT 'T02 sales_invoice_items identical', COUNT(*) FROM (
  SELECT o.id FROM old_accounting.sales_invoice_items o LEFT JOIN accountingsys.sales_invoice_items n ON n.id = o.id
   WHERE n.id IS NULL OR NOT (o.sales_invoice_id <=> n.sales_invoice_id AND o.product_id <=> n.product_id
     AND o.product_name <=> n.product_name AND o.product_sku <=> n.product_sku AND o.quantity <=> n.quantity
     AND o.unit_price <=> n.unit_price AND o.discount_amount <=> n.discount_amount AND o.line_total <=> n.line_total)
  UNION ALL
  SELECT n.id FROM accountingsys.sales_invoice_items n LEFT JOIN old_accounting.sales_invoice_items o ON o.id = n.id WHERE o.id IS NULL
) x

UNION ALL
-- T03 royalty accruals identical (snapshots must never be recalculated)
SELECT 'T03 book_sales identical', COUNT(*) FROM (
  SELECT o.id FROM old_accounting.book_sales o LEFT JOIN accountingsys.book_sales n ON n.id = o.id
   WHERE n.id IS NULL OR NOT (o.book_id <=> n.book_id AND o.contractor_book_id <=> n.contractor_book_id
     AND o.invoice_id <=> n.invoice_id AND o.invoice_item_id <=> n.invoice_item_id AND o.quantity <=> n.quantity
     AND o.sale_price_snapshot <=> n.sale_price_snapshot AND o.base_price_snapshot <=> n.base_price_snapshot
     AND o.percentage_snapshot <=> n.percentage_snapshot AND o.contractor_profit <=> n.contractor_profit
     AND o.is_gift <=> n.is_gift)
  UNION ALL
  SELECT n.id FROM accountingsys.book_sales n LEFT JOIN old_accounting.book_sales o ON o.id = n.id WHERE o.id IS NULL
) x

UNION ALL
-- T04 receipts identical
SELECT 'T04 receipt_vouchers identical', COUNT(*) FROM (
  SELECT o.id FROM old_accounting.receipt_vouchers o LEFT JOIN accountingsys.receipt_vouchers n ON n.id = o.id
   WHERE n.id IS NULL OR NOT (o.party_id <=> n.party_id AND o.account_id <=> n.account_id
     AND o.sales_invoice_id <=> n.sales_invoice_id AND o.amount <=> n.amount AND o.voucher_date <=> n.voucher_date
     AND o.deleted_at <=> n.deleted_at)
  UNION ALL
  SELECT n.id FROM accountingsys.receipt_vouchers n LEFT JOIN old_accounting.receipt_vouchers o ON o.id = n.id WHERE o.id IS NULL
) x

UNION ALL
-- T05 payment vouchers identical
SELECT 'T05 payment_vouchers identical', COUNT(*) FROM (
  SELECT o.id FROM old_accounting.payment_vouchers o LEFT JOIN accountingsys.payment_vouchers n ON n.id = o.id
   WHERE n.id IS NULL OR NOT (o.party_id <=> n.party_id AND o.account_id <=> n.account_id
     AND o.purchase_invoice_id <=> n.purchase_invoice_id AND o.amount <=> n.amount AND o.deleted_at <=> n.deleted_at)
  UNION ALL
  SELECT n.id FROM accountingsys.payment_vouchers n LEFT JOIN old_accounting.payment_vouchers o ON o.id = n.id WHERE o.id IS NULL
) x

UNION ALL
-- T06 purchase invoices + lines identical
SELECT 'T06 purchase_invoices + items identical', COUNT(*) FROM (
  SELECT o.id FROM old_accounting.purchase_invoices o LEFT JOIN accountingsys.purchase_invoices n ON n.id = o.id
   WHERE n.id IS NULL OR NOT (o.party_id <=> n.party_id AND o.total_amount <=> n.total_amount
     AND o.paid_amount <=> n.paid_amount AND o.status <=> n.status AND o.deleted_at <=> n.deleted_at)
  UNION ALL
  SELECT o.id FROM old_accounting.purchase_invoice_items o LEFT JOIN accountingsys.purchase_invoice_items n ON n.id = o.id
   WHERE n.id IS NULL OR NOT (o.purchase_invoice_id <=> n.purchase_invoice_id AND o.product_id <=> n.product_id
     AND o.quantity <=> n.quantity AND o.unit_price <=> n.unit_price AND o.line_total <=> n.line_total)
) x

UNION ALL
-- T07 stock of every product that still exists is unchanged (sync never touches stock)
SELECT 'T07 stock on hand unchanged (surviving products)', COUNT(*) FROM (
  SELECT o.id FROM old_accounting.sub_warehouse_products o
   JOIN accountingsys.products p ON p.id = o.product_id
   LEFT JOIN accountingsys.sub_warehouse_products n ON n.id = o.id
   WHERE n.id IS NULL OR NOT (o.sub_warehouse_id <=> n.sub_warehouse_id AND o.product_id <=> n.product_id AND o.quantity <=> n.quantity)
) x

UNION ALL
-- T09 contract money history identical
SELECT 'T09 contract_transactions identical', COUNT(*) FROM (
  SELECT o.id FROM old_accounting.contract_transactions o LEFT JOIN accountingsys.contract_transactions n ON n.id = o.id
   WHERE n.id IS NULL OR NOT (o.contractor_book_id <=> n.contractor_book_id AND o.type <=> n.type
     AND o.amount <=> n.amount AND o.transaction_date <=> n.transaction_date)
  UNION ALL
  SELECT n.id FROM accountingsys.contract_transactions n LEFT JOIN old_accounting.contract_transactions o ON o.id = n.id WHERE o.id IS NULL
) x

UNION ALL
-- T10 contracts that carry MONEY (accrued royalty <> 0 or any payment/transaction) kept the same contractor / basis
--     and percentage. (A contract at 0% may become the 10% default, a contract whose past sales earned 0 may move to the
--     real contractor from the sheet — nothing is re-attributed because nothing was earned.)
SELECT 'T10 contracts carrying money not re-attributed', COUNT(*) FROM old_accounting.contractor_books o
 LEFT JOIN accountingsys.contractor_books n ON n.id = o.id
 WHERE (IFNULL((SELECT SUM(s.contractor_profit) FROM old_accounting.book_sales s WHERE s.contractor_book_id = o.id), 0) <> 0
     OR EXISTS (SELECT 1 FROM old_accounting.contract_transactions t WHERE t.contractor_book_id = o.id))
   AND (n.id IS NULL OR NOT (o.contractor_id <=> n.contractor_id AND (o.profit_percentage <=> n.profit_percentage OR o.profit_percentage = 0)
        AND o.percentage_basis <=> n.percentage_basis AND o.book_id <=> n.book_id))

UNION ALL
-- T11 every product used by an invoice (sales or purchase) still exists with the SAME id and SKU
SELECT 'T11 invoiced products still exist, same id+SKU', COUNT(*) FROM old_accounting.products o
 LEFT JOIN accountingsys.products n ON n.id = o.id
 WHERE (EXISTS (SELECT 1 FROM old_accounting.sales_invoice_items i WHERE i.product_id = o.id)
     OR EXISTS (SELECT 1 FROM old_accounting.purchase_invoice_items i WHERE i.product_id = o.id))
   AND (n.id IS NULL OR NOT (o.sku <=> n.sku))

UNION ALL
-- T12 SKU of every surviving product unchanged ("keep SKU as it was")
SELECT 'T12 SKU unchanged for all surviving products', COUNT(*) FROM old_accounting.products o
 JOIN accountingsys.products n ON n.id = o.id WHERE NOT (o.sku <=> n.sku)

UNION ALL
-- T13 no product was deleted that any invoice / royalty / contract used
SELECT 'T13 nothing used was deleted', COUNT(*) FROM old_accounting.products o
 WHERE NOT EXISTS (SELECT 1 FROM accountingsys.products n WHERE n.id = o.id)
   AND (EXISTS (SELECT 1 FROM old_accounting.sales_invoice_items i WHERE i.product_id = o.id)
     OR EXISTS (SELECT 1 FROM old_accounting.purchase_invoice_items i WHERE i.product_id = o.id)
     OR EXISTS (SELECT 1 FROM old_accounting.book_sales s JOIN old_accounting.books b ON b.id = s.book_id WHERE b.product_id = o.id))

UNION ALL
-- T14 customer / vendor balances identical (sales - receipts - purchases + payments), per party
SELECT 'T14 party balances identical', COUNT(*) FROM (
  SELECT p.id,
    (SELECT IFNULL(SUM(total_amount),0) FROM old_accounting.sales_invoices WHERE party_id = p.id)
  - (SELECT IFNULL(SUM(amount),0)       FROM old_accounting.receipt_vouchers WHERE party_id = p.id)
  - (SELECT IFNULL(SUM(total_amount),0) FROM old_accounting.purchase_invoices WHERE party_id = p.id)
  + (SELECT IFNULL(SUM(amount),0)       FROM old_accounting.payment_vouchers WHERE party_id = p.id) AS bal_old,
    (SELECT IFNULL(SUM(total_amount),0) FROM accountingsys.sales_invoices WHERE party_id = p.id)
  - (SELECT IFNULL(SUM(amount),0)       FROM accountingsys.receipt_vouchers WHERE party_id = p.id)
  - (SELECT IFNULL(SUM(total_amount),0) FROM accountingsys.purchase_invoices WHERE party_id = p.id)
  + (SELECT IFNULL(SUM(amount),0)       FROM accountingsys.payment_vouchers WHERE party_id = p.id) AS bal_new
  FROM old_accounting.parties p
) b WHERE ROUND(bal_old,2) <> ROUND(bal_new,2)

UNION ALL
-- T15 royalty owed per contractor identical (contractors whose accrued total was 0 are ignored)
SELECT 'T15 royalty per contractor identical', COUNT(*) FROM (
  SELECT cb.contractor_id, SUM(s.contractor_profit) AS v FROM old_accounting.book_sales s JOIN old_accounting.contractor_books cb ON cb.id = s.contractor_book_id GROUP BY cb.contractor_id
) o LEFT JOIN (
  SELECT cb.contractor_id, SUM(s.contractor_profit) AS v FROM accountingsys.book_sales s JOIN accountingsys.contractor_books cb ON cb.id = s.contractor_book_id GROUP BY cb.contractor_id
) n ON n.contractor_id = o.contractor_id WHERE ROUND(IFNULL(o.v,0),2) <> 0 AND (n.contractor_id IS NULL OR ROUND(o.v,2) <> ROUND(n.v,2))

UNION ALL
-- T16 Book ID <-> Product ID integrity in NEW: every book has a product, one book per product
SELECT 'T16 books<->products pairing valid', (
  SELECT COUNT(*) FROM accountingsys.books b LEFT JOIN accountingsys.products p ON p.id = b.product_id WHERE p.id IS NULL
) + (
  SELECT COUNT(*) FROM (SELECT product_id FROM accountingsys.books GROUP BY product_id HAVING COUNT(*) > 1) d
)

UNION ALL
-- T17 orphans in NEW (royalties / contracts / invoice lines pointing at nothing)
SELECT 'T17 no orphan references', (
  SELECT COUNT(*) FROM accountingsys.book_sales s LEFT JOIN accountingsys.books b ON b.id = s.book_id WHERE b.id IS NULL
) + (
  SELECT COUNT(*) FROM accountingsys.contractor_books c LEFT JOIN accountingsys.books b ON b.id = c.book_id WHERE b.id IS NULL
) + (
  SELECT COUNT(*) FROM accountingsys.sales_invoice_items i LEFT JOIN accountingsys.products p ON p.id = i.product_id WHERE p.id IS NULL
)

UNION ALL
-- T18 no duplicates created in NEW (contractors, categories)
SELECT 'T18 no duplicate contractors/categories', (
  SELECT COUNT(*) FROM (SELECT name FROM accountingsys.contractors WHERE deleted_at IS NULL GROUP BY name HAVING COUNT(*) > 1) d
) + (
  SELECT COUNT(*) FROM (SELECT name, parent_id FROM accountingsys.book_categories GROUP BY name, parent_id HAVING COUNT(*) > 1) d
)

) tests ORDER BY t;


