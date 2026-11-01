-- =====================================================================================
-- 01_preflight_on_old_copy.sql   (MySQL)
-- Run on the BACKUP COPY (accounting_old) BEFORE touching anything.
-- Tells you which books are used by sales invoices etc. (so you know what must survive).
-- Replace accounting_old with your backup database name (search & replace).
-- =====================================================================================
USE accounting_old;

-- 1. Overall size
SELECT (SELECT COUNT(*) FROM products)                AS products,
       (SELECT COUNT(*) FROM books)                   AS books,
       (SELECT COUNT(*) FROM sales_invoices)          AS sales_invoices,
       (SELECT COUNT(*) FROM sales_invoice_items)     AS sales_invoice_items,
       (SELECT COUNT(*) FROM book_sales)              AS book_sales,
       (SELECT COUNT(*) FROM contractor_books)        AS contractor_books;

-- 2. Book ID <-> Product ID pairing problems that exist today
--    (a) books pointing at a missing product, (b) products with more than one book
SELECT 'book without product' AS problem, b.id AS book_id, b.product_id
FROM books b LEFT JOIN products p ON p.id = b.product_id WHERE p.id IS NULL
UNION ALL
SELECT 'product with >1 book', MIN(id), product_id FROM books GROUP BY product_id HAVING COUNT(*) > 1;

-- 3. Products referenced by sales invoices (MUST still exist, same id, after the update)
SELECT p.id AS product_id, p.sku, p.name,
       COUNT(i.id) AS invoice_lines, COUNT(DISTINCT i.sales_invoice_id) AS invoices,
       SUM(i.quantity) AS qty_sold, SUM(i.line_total) AS value
FROM sales_invoice_items i JOIN products p ON p.id = i.product_id
GROUP BY p.id, p.sku, p.name ORDER BY invoices DESC;

-- 4. Books with royalty history (their contract/contractor must not be re-attributed)
SELECT cb.id AS contractor_book_id, cb.book_id, c.name AS contractor, cb.profit_percentage,
       COUNT(bs.id) AS accruals, SUM(bs.contractor_profit) AS royalty_accrued,
       (SELECT COUNT(*) FROM contract_transactions t WHERE t.contractor_book_id = cb.id) AS transactions
FROM contractor_books cb JOIN contractors c ON c.id = cb.contractor_id
LEFT JOIN book_sales bs ON bs.contractor_book_id = cb.id
GROUP BY cb.id, cb.book_id, c.name, cb.profit_percentage HAVING accruals > 0 OR transactions > 0;

-- 5. Stock on hand per product (stock is never changed by the sync)
SELECT product_id, SUM(quantity) AS qty FROM sub_warehouse_products GROUP BY product_id HAVING qty <> 0;

-- 6. Existing duplicates that would confuse matching
SELECT 'duplicate ISBN' AS problem, isbn AS value, COUNT(*) AS n FROM books GROUP BY isbn HAVING COUNT(*) > 1
UNION ALL
SELECT 'duplicate SKU', sku, COUNT(*) FROM products WHERE sku IS NOT NULL GROUP BY sku HAVING COUNT(*) > 1
UNION ALL
SELECT 'duplicate contractor name', name, COUNT(*) FROM contractors WHERE deleted_at IS NULL GROUP BY name HAVING COUNT(*) > 1
UNION ALL
SELECT 'duplicate category', CONCAT(IFNULL(parent_id,0), ':', name), COUNT(*) FROM book_categories GROUP BY name, parent_id HAVING COUNT(*) > 1;
