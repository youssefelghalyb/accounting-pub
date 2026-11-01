-- =====================================================================================
-- 03_verify_against_sheet.sql   (MySQL)
-- Proves the LIVE database now equals the Excel sheet, field by field.
-- Run on accounting_new (replace the name) AFTER product:sync-books --apply (and prune).
--
-- Step 1: In Excel, "Save As" -> CSV UTF-8 (Comma delimited) -> e.g. /tmp/books.csv
--         (keep the 30 columns in the same order as the sheet header).
-- Step 2: load it into a scratch table (all text, so nothing is "fixed" silently).
-- Every query in step 3 must return ZERO rows (except 3h/3i which are informational).
-- =====================================================================================
USE accounting_new;

DROP TABLE IF EXISTS sheet_books;
CREATE TABLE sheet_books (
  book_id VARCHAR(20), product_id VARCHAR(20), book_name TEXT, sku VARCHAR(100), price VARCHAR(30), status VARCHAR(20),
  description TEXT, isbn VARCHAR(50), pages VARCHAR(20), cover_type VARCHAR(20), published_date VARCHAR(30),
  language VARCHAR(20), is_translated VARCHAR(10), translated_from VARCHAR(100), translated_to VARCHAR(100),
  translator VARCHAR(255), supervisor VARCHAR(255), introduction VARCHAR(255), category VARCHAR(255),
  sub_category VARCHAR(255), used_in_sales VARCHAR(10), author1 TEXT, contractor VARCHAR(255), nationality VARCHAR(150),
  notes TEXT, status2 VARCHAR(100), gift_copies VARCHAR(20), deposit_number VARCHAR(50), deposit_year VARCHAR(10),
  deposit_status VARCHAR(50)
) DEFAULT CHARSET = utf8mb4;

LOAD DATA LOCAL INFILE '/tmp/books.csv' INTO TABLE sheet_books
  CHARACTER SET utf8mb4 FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' LINES TERMINATED BY '\n' IGNORE 1 LINES;
-- (if LOCAL is disabled: use the MySQL Workbench "Table Data Import Wizard" into sheet_books instead)

-- normalise like the importer does (trim, drop thousands separators)
UPDATE sheet_books SET price = REPLACE(TRIM(price), ',', ''), book_name = TRIM(book_name), contractor = NULLIF(TRIM(contractor), ''),
  category = NULLIF(TRIM(category), ''), sub_category = NULLIF(TRIM(sub_category), '');

-- 3a. sheet rows missing from the DB, or Book ID/Product ID not the stored pair
SELECT s.book_id, s.product_id, s.book_name, '3a missing or wrong pair' AS problem
FROM sheet_books s LEFT JOIN books b ON b.id = s.book_id AND b.product_id = s.product_id
WHERE b.id IS NULL;

-- 3b. DB books/products NOT in the sheet (after prune: only the BLOCKED/used ones may remain — they should be inactive)
SELECT p.id AS product_id, b.id AS book_id, p.name, p.status AS status, '3b not in sheet' AS problem
FROM products p LEFT JOIN books b ON b.product_id = p.id
WHERE (p.type = 'book' OR b.id IS NOT NULL) AND p.id NOT IN (SELECT product_id FROM sheet_books);

-- 3c. product fields: name, price, status, description (=Notes). SKU is intentionally NOT compared.
SELECT s.product_id, p.name AS db_name, s.book_name, p.base_price AS db_price, s.price AS sheet_price,
       p.status AS db_status, s.status AS sheet_status, '3c product fields' AS problem
FROM sheet_books s JOIN products p ON p.id = s.product_id
WHERE p.name <> s.book_name OR ROUND(p.base_price, 2) <> ROUND(s.price, 2)
   OR p.status <> LOWER(s.status)
   OR (NULLIF(TRIM(s.notes), '') IS NOT NULL AND IFNULL(p.description, '') <> TRIM(s.notes));

-- 3d. book fields
SELECT s.book_id, '3d book fields' AS problem, b.isbn AS db_isbn, s.isbn AS sheet_isbn, b.num_of_pages, s.pages
FROM sheet_books s JOIN books b ON b.id = s.book_id
WHERE b.isbn <> s.isbn
   OR NOT (b.num_of_pages <=> NULLIF(s.pages, ''))
   OR b.cover_type <> LOWER(s.cover_type)
   OR NOT (b.language <=> NULLIF(s.language, ''))
   OR (b.is_translated = 1) <> (LOWER(s.is_translated) = 'yes')
   OR NOT (b.translator_name <=> NULLIF(s.translator, ''))
   OR NOT (b.supervisor <=> NULLIF(s.supervisor, ''))
   OR NOT (b.introduction_by <=> NULLIF(s.introduction, ''))
   OR NOT (b.authors <=> NULLIF(TRIM(s.author1), ''))
   OR NOT (b.deposit_number <=> NULLIF(s.deposit_number, ''))
   OR NOT (b.deposit_year <=> NULLIF(s.deposit_year, ''))
   OR NOT (b.deposit_status <=> NULLIF(s.deposit_status, ''))
   OR b.contract_status <> IF(s.status2 LIKE '%ملغي%', 'ended', 'active')
   OR NOT (YEAR(b.published_at) <=> YEAR(NULLIF(s.published_date, '')));

-- 3e. categories (name + sub-category under it)
SELECT s.book_id, s.category, c.name AS db_category, s.sub_category, sc.name AS db_sub_category, '3e category' AS problem
FROM sheet_books s JOIN books b ON b.id = s.book_id
LEFT JOIN book_categories c  ON c.id  = b.category_id
LEFT JOIN book_categories sc ON sc.id = b.sub_category_id
WHERE NOT (c.name <=> s.category) OR NOT (sc.name <=> s.sub_category)
   OR (sc.id IS NOT NULL AND NOT (sc.parent_id <=> c.id));

-- 3f. contractor, nationality, gift copies, contract status
--     (a "blocked" contractor change from the sync report legitimately shows up here)
SELECT s.book_id, s.contractor AS sheet_contractor, c.name AS db_contractor, s.nationality AS sheet_nat, c.nationality AS db_nat,
       s.gift_copies, cb.gift_copies_given, cb.status, '3f contract' AS problem
FROM sheet_books s JOIN books b ON b.id = s.book_id
LEFT JOIN contractor_books cb ON cb.book_id = b.id AND cb.deleted_at IS NULL
LEFT JOIN contractors c ON c.id = cb.contractor_id
WHERE (s.contractor IS NOT NULL AND (c.id IS NULL OR c.name <> s.contractor))
   OR (s.contractor IS NOT NULL AND NULLIF(s.nationality, '') IS NOT NULL AND NOT (c.nationality <=> s.nationality))
   OR (cb.id IS NOT NULL AND cb.gift_copies_given <> IFNULL(NULLIF(s.gift_copies, ''), 0))
   OR (cb.id IS NOT NULL AND cb.status <> IF(s.status2 LIKE '%ملغي%', 'ended', 'active'));

-- 3g. counts: sheet vs DB (equal unless BLOCKED leftovers remain)
SELECT (SELECT COUNT(*) FROM sheet_books) AS sheet_rows, (SELECT COUNT(*) FROM books) AS db_books, (SELECT COUNT(*) FROM products WHERE type = 'book') AS db_book_products;

-- 3h. informational: contracts created by the sync with 0% (percentage/basis are not in the sheet — set them in the UI)
SELECT cb.id, cb.book_id, c.name AS contractor FROM contractor_books cb JOIN contractors c ON c.id = cb.contractor_id
WHERE cb.profit_percentage = 0 AND cb.deleted_at IS NULL ORDER BY cb.book_id;

-- 3i. cleanup
-- DROP TABLE sheet_books;
