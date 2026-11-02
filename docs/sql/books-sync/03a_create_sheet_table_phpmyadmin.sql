-- Run with the LIVE database (accountingsys) selected. Creates the empty staging table for the sheet.
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
