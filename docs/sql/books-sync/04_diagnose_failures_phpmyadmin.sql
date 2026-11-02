-- Diagnose failing tests T02 / T03 / T13 (phpMyAdmin: run ONE query at a time).
-- live = accountingsys, backup copy = accounting_old.

-- D1: invoice lines that differ between old and live
SELECT 'in old only/different' AS side, o.id, o.sales_invoice_id, o.product_id, o.product_name, o.quantity, o.unit_price, o.line_total
FROM accounting_old.sales_invoice_items o LEFT JOIN accountingsys.sales_invoice_items n ON n.id = o.id
WHERE n.id IS NULL OR NOT (o.sales_invoice_id <=> n.sales_invoice_id AND o.product_id <=> n.product_id AND o.product_name <=> n.product_name
  AND o.product_sku <=> n.product_sku AND o.quantity <=> n.quantity AND o.unit_price <=> n.unit_price AND o.discount_amount <=> n.discount_amount AND o.line_total <=> n.line_total)
UNION ALL
SELECT 'in live only', n.id, n.sales_invoice_id, n.product_id, n.product_name, n.quantity, n.unit_price, n.line_total
FROM accountingsys.sales_invoice_items n LEFT JOIN accounting_old.sales_invoice_items o ON o.id = n.id WHERE o.id IS NULL
ORDER BY sales_invoice_id, id;

-- D2: when were the affected invoices last touched (updated_at after the backup => edited in the app)
SELECT s.id, s.invoice_number, s.invoice_date, s.created_at, s.updated_at, s.edited_by
FROM accountingsys.sales_invoices s
WHERE s.id IN (SELECT o.sales_invoice_id FROM accounting_old.sales_invoice_items o LEFT JOIN accountingsys.sales_invoice_items n ON n.id = o.id
  WHERE n.id IS NULL OR NOT (o.product_id <=> n.product_id AND o.unit_price <=> n.unit_price AND o.line_total <=> n.line_total AND o.quantity <=> n.quantity));

-- D3: deleted books that were used in the OLD copy
SELECT o.id, o.sku, o.name,
  (SELECT COUNT(*) FROM accounting_old.sales_invoice_items i WHERE i.product_id = o.id) AS old_invoice_lines,
  (SELECT COUNT(*) FROM accountingsys.sales_invoice_items i WHERE i.product_id = o.id) AS live_invoice_lines
FROM accounting_old.products o
WHERE NOT EXISTS (SELECT 1 FROM accountingsys.products n WHERE n.id = o.id)
  AND (EXISTS (SELECT 1 FROM accounting_old.sales_invoice_items i WHERE i.product_id = o.id)
    OR EXISTS (SELECT 1 FROM accounting_old.book_sales s JOIN accounting_old.books b ON b.id = s.book_id WHERE b.product_id = o.id));

-- D4: royalty rows that differ
SELECT o.id, o.book_id, o.invoice_id, o.invoice_item_id, o.quantity, o.contractor_profit, n.id AS live_id, n.invoice_item_id AS live_item
FROM accounting_old.book_sales o LEFT JOIN accountingsys.book_sales n ON n.id = o.id
WHERE n.id IS NULL OR NOT (o.book_id <=> n.book_id AND o.invoice_item_id <=> n.invoice_item_id AND o.quantity <=> n.quantity AND o.contractor_profit <=> n.contractor_profit);
