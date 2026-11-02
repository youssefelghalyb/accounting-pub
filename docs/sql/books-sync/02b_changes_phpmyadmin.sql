-- phpMyAdmin version. Live database = accountingsys (the one the sync ran on), backup copy = accounting_old.
-- If your names differ: Ctrl+H (find/replace) in the SQL box before pressing Go.
-- Open phpMyAdmin > any database > SQL tab > paste this WHOLE file > Go.
-- RUN ONE QUERY AT A TIME (phpMyAdmin shows only the last result of a multi-query paste).
-- Each query is separated by a '-- B' comment; paste one, press Go, then the next.
-- ============================ PART B — WHAT CHANGED (informational) ===================
-- B1 row counts
SELECT 'products' AS tbl, (SELECT COUNT(*) FROM accounting_old.products) AS old_rows, (SELECT COUNT(*) FROM accountingsys.products) AS new_rows
UNION ALL SELECT 'books', (SELECT COUNT(*) FROM accounting_old.books), (SELECT COUNT(*) FROM accountingsys.books)
UNION ALL SELECT 'contractors', (SELECT COUNT(*) FROM accounting_old.contractors), (SELECT COUNT(*) FROM accountingsys.contractors)
UNION ALL SELECT 'contractor_books', (SELECT COUNT(*) FROM accounting_old.contractor_books), (SELECT COUNT(*) FROM accountingsys.contractor_books)
UNION ALL SELECT 'book_categories', (SELECT COUNT(*) FROM accounting_old.book_categories), (SELECT COUNT(*) FROM accountingsys.book_categories);

-- B2 how many surviving rows changed, per column (products)
SELECT SUM(NOT (o.name <=> n.name)) AS name, SUM(NOT (o.base_price <=> n.base_price)) AS price,
       SUM(NOT (o.status <=> n.status)) AS status, SUM(NOT (o.description <=> n.description)) AS description,
       COUNT(*) AS rows_compared
FROM accounting_old.products o JOIN accountingsys.products n ON n.id = o.id;

-- B3 same for books
SELECT SUM(NOT (o.isbn <=> n.isbn)) AS isbn, SUM(NOT (o.num_of_pages <=> n.num_of_pages)) AS pages,
       SUM(NOT (o.cover_type <=> n.cover_type)) AS cover, SUM(NOT (o.published_at <=> n.published_at)) AS published,
       SUM(NOT (o.language <=> n.language)) AS language, SUM(NOT (o.is_translated <=> n.is_translated)) AS is_translated,
       SUM(NOT (o.translator_name <=> n.translator_name)) AS translator, SUM(NOT (o.supervisor <=> n.supervisor)) AS supervisor,
       SUM(NOT (o.introduction_by <=> n.introduction_by)) AS intro, SUM(NOT (o.category_id <=> n.category_id)) AS category,
       SUM(NOT (o.sub_category_id <=> n.sub_category_id)) AS sub_category, SUM(NOT (o.authors <=> n.authors)) AS authors,
       SUM(NOT (o.product_id <=> n.product_id)) AS product_id_repointed, COUNT(*) AS rows_compared
FROM accounting_old.books o JOIN accountingsys.books n ON n.id = o.id;

-- B4 removed products (should be exactly the SAFE list from product:prune-books)
SELECT o.id, o.sku, o.name FROM accounting_old.products o
 LEFT JOIN accountingsys.products n ON n.id = o.id WHERE n.id IS NULL ORDER BY o.id;

-- B5 added products
SELECT n.id, n.sku, n.name FROM accountingsys.products n
 LEFT JOIN accounting_old.products o ON o.id = n.id WHERE o.id IS NULL ORDER BY n.id;

-- B4b removed products that still had stock in the OLD copy (only expected for books deleted with --zero-stock;
--     compare with storage/app/book-sync/*_zeroed_stock.csv — the list and quantities must be the same)
SELECT o.product_id, p.name, SUM(o.quantity) AS stock_removed
FROM accounting_old.sub_warehouse_products o JOIN accounting_old.products p ON p.id = o.product_id
WHERE o.quantity <> 0 AND NOT EXISTS (SELECT 1 FROM accountingsys.products n WHERE n.id = o.product_id)
GROUP BY o.product_id, p.name;

-- B6 new categories / new contractors / new contracts (at the 10% default)
SELECT 'new category' AS what, n.id, n.name FROM accountingsys.book_categories n LEFT JOIN accounting_old.book_categories o ON o.id = n.id WHERE o.id IS NULL
UNION ALL SELECT 'new contractor', n.id, n.name FROM accountingsys.contractors n LEFT JOIN accounting_old.contractors o ON o.id = n.id WHERE o.id IS NULL
UNION ALL SELECT 'new contract (default %)', n.id, CONCAT('book ', n.book_id) FROM accountingsys.contractor_books n LEFT JOIN accounting_old.contractor_books o ON o.id = n.id WHERE o.id IS NULL AND n.profit_percentage > 0;

-- B7b existing contracts moved from 0% to the default
SELECT n.id AS contractor_book_id, n.book_id, o.profit_percentage AS old_pct, n.profit_percentage AS new_pct
FROM accounting_old.contractor_books o JOIN accountingsys.contractor_books n ON n.id = o.id WHERE o.profit_percentage = 0 AND n.profit_percentage <> 0;

-- B7 contractors whose contract changed owner (only possible for contracts WITHOUT history — see T10)
SELECT n.book_id, o.contractor_id AS old_contractor, n.contractor_id AS new_contractor
FROM accounting_old.contractor_books o JOIN accountingsys.contractor_books n ON n.id = o.id WHERE NOT (o.contractor_id <=> n.contractor_id);


