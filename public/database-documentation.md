# Database Documentation

## Overview
This document provides comprehensive database documentation for the Customer, Product, and Warehouse modules. It includes table schemas, relationships, and data processing flows.

---

## Customer Module

### Tables

#### `customers`
Stores customer information for the accounting system.

**Table Structure:**
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Unique customer identifier |
| name | VARCHAR(255) | NOT NULL | Customer name or company name |
| type | ENUM | NOT NULL, DEFAULT 'individual' | Customer type: 'individual', 'company', 'online' |
| phone | VARCHAR(50) | NULLABLE | Customer phone number |
| email | VARCHAR(255) | NULLABLE | Customer email address |
| address | TEXT | NULLABLE | Customer physical address |
| tax_number | VARCHAR(100) | NULLABLE | Tax/VAT registration number |
| is_active | BOOLEAN | NOT NULL, DEFAULT true | Customer account status |
| created_at | TIMESTAMP | NOT NULL | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL | Record last update timestamp |

**Indexes:**
- PRIMARY KEY on `id`

**Key Features:**
- Supports three customer types: individual, company, and online
- Tracks active/inactive status for customer management
- Optional tax number for VAT/tax compliance
- Searchable fields: name, email, phone, tax_number

**Data Processing:**
1. **Create:** New customers are validated through `StoreCustomerRequest` and inserted with all required fields
2. **Read:** Customers can be filtered by type, status, and searched by multiple fields
3. **Update:** Customer data is updated through `UpdateCustomerRequest` validation
4. **Delete:** Soft constraints - cannot delete customers with existing orders
5. **Status Toggle:** Customers can be activated/deactivated without deletion

---

## Product Module

### Tables

#### `products`
Core product table storing base product information.

**Table Structure:**
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Unique product identifier |
| name | VARCHAR(255) | NOT NULL | Product name |
| type | ENUM | NOT NULL, DEFAULT 'book' | Product type: 'book', 'ebook', 'journal', 'course', 'bundle' |
| sku | VARCHAR(100) | NULLABLE | Stock Keeping Unit identifier |
| description | TEXT | NULLABLE | Product description |
| base_price | DECIMAL(10,2) | NOT NULL, DEFAULT 0 | Base price of the product |
| status | ENUM | NOT NULL, DEFAULT 'active' | Product status: 'active', 'inactive' |
| created_by | BIGINT | FOREIGN KEY, NULLABLE | User who created the record |
| edited_by | BIGINT | FOREIGN KEY, NULLABLE | User who last edited the record |
| created_at | TIMESTAMP | NOT NULL | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL | Record last update timestamp |

**Foreign Keys:**
- `created_by` → `users.id` (ON DELETE SET NULL)
- `edited_by` → `users.id` (ON DELETE SET NULL)

**Relationships:**
- One-to-One with `books` table
- One-to-Many referenced by `sub_warehouse_products`
- One-to-Many referenced by `stock_movements`

---

#### `book_categories`
Hierarchical category structure for organizing books.

**Table Structure:**
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Unique category identifier |
| name | VARCHAR(255) | NOT NULL | Category name |
| parent_id | BIGINT | FOREIGN KEY, NULLABLE | Parent category for hierarchy |
| created_by | BIGINT | FOREIGN KEY, NULLABLE | User who created the record |
| edited_by | BIGINT | FOREIGN KEY, NULLABLE | User who last edited the record |
| created_at | TIMESTAMP | NOT NULL | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL | Record last update timestamp |

**Foreign Keys:**
- `parent_id` → `book_categories.id` (ON DELETE SET NULL)
- `created_by` → `users.id` (ON DELETE SET NULL)
- `edited_by` → `users.id` (ON DELETE SET NULL)

**Key Features:**
- Self-referencing hierarchy (parent-child relationships)
- Categories can have sub-categories
- Used for both main category and sub-category in books

---

#### `authors`
Stores author information and contact details.

**Table Structure:**
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Unique author identifier |
| full_name | VARCHAR(255) | NOT NULL | Author's full name |
| nationality | VARCHAR(150) | NULLABLE | Author's nationality |
| country_of_residence | VARCHAR(150) | NULLABLE | Current country of residence |
| bio | TEXT | NULLABLE | Author biography |
| occupation | VARCHAR(255) | NULLABLE | Author's occupation |
| phone_number | VARCHAR(50) | NULLABLE | Phone number |
| whatsapp_number | VARCHAR(50) | NULLABLE | WhatsApp number |
| email | VARCHAR(255) | NULLABLE | Email address |
| id_image | VARCHAR(255) | NULLABLE | Path to ID document image |
| created_by | BIGINT | FOREIGN KEY, NULLABLE | User who created the record |
| edited_by | BIGINT | FOREIGN KEY, NULLABLE | User who last edited the record |
| created_at | TIMESTAMP | NOT NULL | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL | Record last update timestamp |

**Foreign Keys:**
- `created_by` → `users.id` (ON DELETE SET NULL)
- `edited_by` → `users.id` (ON DELETE SET NULL)

**Relationships:**
- One-to-Many with `books`
- One-to-Many with `author_book_contracts`

**Computed Attributes:**
- `total_contract_value`: Sum of all contract prices for the author
- `total_paid`: Sum of all payments made to the author
- `outstanding_balance`: Remaining amount owed to the author

---

#### `books`
Extended product information specific to books.

**Table Structure:**
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Unique book identifier |
| product_id | BIGINT | FOREIGN KEY, NOT NULL | Reference to base product |
| author_id | BIGINT | FOREIGN KEY, NULLABLE | Book author |
| category_id | BIGINT | FOREIGN KEY, NULLABLE | Main category |
| sub_category_id | BIGINT | FOREIGN KEY, NULLABLE | Sub-category |
| isbn | VARCHAR(50) | NOT NULL | International Standard Book Number |
| num_of_pages | INTEGER | NULLABLE | Number of pages |
| cover_type | ENUM | NOT NULL, DEFAULT 'soft' | Cover type: 'hard', 'soft' |
| published_at | DATE | NULLABLE | Publication date |
| language | VARCHAR(100) | NULLABLE | Book language |
| is_translated | BOOLEAN | NOT NULL, DEFAULT false | Whether book is translated |
| translated_from | VARCHAR(100) | NULLABLE | Original language |
| translated_to | VARCHAR(100) | NULLABLE | Target language |
| translator_name | VARCHAR(255) | NULLABLE | Translator's name |
| created_by | BIGINT | FOREIGN KEY, NULLABLE | User who created the record |
| edited_by | BIGINT | FOREIGN KEY, NULLABLE | User who last edited the record |
| created_at | TIMESTAMP | NOT NULL | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL | Record last update timestamp |

**Foreign Keys:**
- `product_id` → `products.id` (ON DELETE CASCADE)
- `author_id` → `authors.id` (ON DELETE SET NULL)
- `category_id` → `book_categories.id` (ON DELETE SET NULL)
- `sub_category_id` → `book_categories.id` (ON DELETE SET NULL)
- `created_by` → `users.id` (ON DELETE SET NULL)
- `edited_by` → `users.id` (ON DELETE SET NULL)

**Relationships:**
- Belongs-To `products`
- Belongs-To `authors`
- Belongs-To `book_categories` (category)
- Belongs-To `book_categories` (sub_category)
- One-to-Many with `author_book_contracts`

---

#### `author_book_contracts`
Manages contracts between authors and the publishing house.

**Table Structure:**
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Unique contract identifier |
| author_id | BIGINT | FOREIGN KEY, NOT NULL | Contract author |
| book_id | BIGINT | FOREIGN KEY, NULLABLE | Associated book |
| book_name | VARCHAR(255) | NULLABLE | Book name if book_id not set |
| contract_date | DATE | NOT NULL | Contract signing date |
| contract_price | DECIMAL(12,2) | NOT NULL, DEFAULT 0 | Total contract value |
| percentage_from_book_profit | DECIMAL(5,2) | NOT NULL, DEFAULT 0 | Profit sharing percentage |
| contract_file | VARCHAR(255) | NULLABLE | Path to contract document |
| created_by | BIGINT | FOREIGN KEY, NULLABLE | User who created the record |
| edited_by | BIGINT | FOREIGN KEY, NULLABLE | User who last edited the record |
| created_at | TIMESTAMP | NOT NULL | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL | Record last update timestamp |

**Foreign Keys:**
- `author_id` → `authors.id` (ON DELETE CASCADE)
- `book_id` → `books.id` (ON DELETE CASCADE)
- `created_by` → `users.id` (ON DELETE SET NULL)
- `edited_by` → `users.id` (ON DELETE SET NULL)

**Relationships:**
- Belongs-To `authors`
- Belongs-To `books`
- One-to-Many with `author_contract_transactions`

**Computed Attributes:**
- `total_paid`: Sum of all transaction amounts
- `outstanding_balance`: contract_price - total_paid
- `payment_percentage`: (total_paid / contract_price) * 100
- `payment_status`: 'paid', 'partial', or 'pending'

---

#### `author_contract_transactions`
Records payments made to authors under their contracts.

**Table Structure:**
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Unique transaction identifier |
| contract_id | BIGINT | FOREIGN KEY, NOT NULL | Associated contract |
| amount | DECIMAL(12,2) | NOT NULL | Payment amount |
| payment_date | DATE | NOT NULL | Date of payment |
| notes | TEXT | NULLABLE | Additional notes |
| receipt_file | VARCHAR(255) | NULLABLE | Path to receipt document |
| created_by | BIGINT | FOREIGN KEY, NULLABLE | User who created the record |
| edited_by | BIGINT | FOREIGN KEY, NULLABLE | User who last edited the record |
| created_at | TIMESTAMP | NOT NULL | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL | Record last update timestamp |

**Foreign Keys:**
- `contract_id` → `author_book_contracts.id` (ON DELETE CASCADE)
- `created_by` → `users.id` (ON DELETE SET NULL)
- `edited_by` → `users.id` (ON DELETE SET NULL)

**Relationships:**
- Belongs-To `author_book_contracts`

---

## Warehouse Module

### Tables

#### `warehouses`
Main warehouse entities.

**Table Structure:**
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Unique warehouse identifier |
| name | VARCHAR(255) | NOT NULL | Warehouse name |
| description | TEXT | NULLABLE | Warehouse description |
| created_by | BIGINT | FOREIGN KEY, NULLABLE | User who created the record |
| edited_by | BIGINT | FOREIGN KEY, NULLABLE | User who last edited the record |
| created_at | TIMESTAMP | NOT NULL | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL | Record last update timestamp |

**Foreign Keys:**
- `created_by` → `users.id` (ON DELETE SET NULL)
- `edited_by` → `users.id` (ON DELETE SET NULL)

**Relationships:**
- One-to-Many with `sub_warehouses`

**Computed Attributes:**
- `total_sub_warehouses`: Count of sub-warehouses
- `total_products`: Count of products across all sub-warehouses
- `total_stock`: Sum of all product quantities

---

#### `sub_warehouses`
Physical locations within warehouses where products are stored.

**Table Structure:**
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Unique sub-warehouse identifier |
| warehouse_id | BIGINT | FOREIGN KEY, NOT NULL | Parent warehouse |
| name | VARCHAR(255) | NOT NULL | Sub-warehouse name |
| type | ENUM | NOT NULL, DEFAULT 'main' | Type: 'main', 'branch', 'book_fair', 'temporary', 'other' |
| address | TEXT | NULLABLE | Physical address |
| country | VARCHAR(255) | NULLABLE | Country location |
| notes | TEXT | NULLABLE | Additional notes |
| created_by | BIGINT | FOREIGN KEY, NULLABLE | User who created the record |
| edited_by | BIGINT | FOREIGN KEY, NULLABLE | User who last edited the record |
| created_at | TIMESTAMP | NOT NULL | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL | Record last update timestamp |

**Foreign Keys:**
- `warehouse_id` → `warehouses.id` (ON DELETE CASCADE)
- `created_by` → `users.id` (ON DELETE SET NULL)
- `edited_by` → `users.id` (ON DELETE SET NULL)

**Relationships:**
- Belongs-To `warehouses`
- One-to-Many with `sub_warehouse_products`
- One-to-Many with `stock_movements` (as source)
- One-to-Many with `stock_movements` (as destination)

**Computed Attributes:**
- `total_quantity`: Sum of all product quantities
- `total_products`: Count of distinct products

---

#### `sub_warehouse_products`
Inventory tracking for products in sub-warehouses.

**Table Structure:**
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Unique record identifier |
| sub_warehouse_id | BIGINT | FOREIGN KEY, NOT NULL | Sub-warehouse location |
| product_id | BIGINT | FOREIGN KEY, NOT NULL | Product stored |
| quantity | INTEGER | NOT NULL, DEFAULT 0 | Current stock quantity |
| created_by | BIGINT | FOREIGN KEY, NULLABLE | User who created the record |
| edited_by | BIGINT | FOREIGN KEY, NULLABLE | User who last edited the record |
| created_at | TIMESTAMP | NOT NULL | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL | Record last update timestamp |

**Foreign Keys:**
- `sub_warehouse_id` → `sub_warehouses.id` (ON DELETE CASCADE)
- `product_id` → `products.id` (ON DELETE CASCADE)
- `created_by` → `users.id` (ON DELETE SET NULL)
- `edited_by` → `users.id` (ON DELETE SET NULL)

**Unique Constraints:**
- UNIQUE(`sub_warehouse_id`, `product_id`) - One record per product per sub-warehouse

**Relationships:**
- Belongs-To `sub_warehouses`
- Belongs-To `products`

**Business Rules:**
- Low stock: quantity < 10
- Out of stock: quantity <= 0
- Stock status colors: red (out), yellow (low), green (normal)

---

#### `stock_movements`
Tracks all inventory movements between locations.

**Table Structure:**
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Unique movement identifier |
| product_id | BIGINT | FOREIGN KEY, NOT NULL | Product being moved |
| from_sub_warehouse_id | BIGINT | FOREIGN KEY, NULLABLE | Source location |
| to_sub_warehouse_id | BIGINT | FOREIGN KEY, NULLABLE | Destination location |
| quantity | INTEGER | NOT NULL | Quantity moved |
| movement_type | ENUM | NOT NULL, DEFAULT 'transfer' | Type: 'transfer', 'inbound', 'outbound' |
| reason | VARCHAR(255) | NULLABLE | Movement reason |
| reference_id | BIGINT | NULLABLE | Reference to related transaction |
| notes | TEXT | NULLABLE | Additional notes |
| user_id | BIGINT | FOREIGN KEY, NULLABLE | User who performed movement |
| created_by | BIGINT | FOREIGN KEY, NULLABLE | User who created the record |
| edited_by | BIGINT | FOREIGN KEY, NULLABLE | User who last edited the record |
| created_at | TIMESTAMP | NOT NULL | Record creation timestamp |
| updated_at | TIMESTAMP | NOT NULL | Record last update timestamp |

**Foreign Keys:**
- `product_id` → `products.id` (ON DELETE CASCADE)
- `from_sub_warehouse_id` → `sub_warehouses.id` (ON DELETE SET NULL)
- `to_sub_warehouse_id` → `sub_warehouses.id` (ON DELETE SET NULL)
- `user_id` → `users.id` (ON DELETE SET NULL)
- `created_by` → `users.id` (ON DELETE SET NULL)
- `edited_by` → `users.id` (ON DELETE SET NULL)

**Relationships:**
- Belongs-To `products`
- Belongs-To `sub_warehouses` (from)
- Belongs-To `sub_warehouses` (to)
- Belongs-To `users`

**Movement Types:**
- `transfer`: Between two sub-warehouses
- `inbound`: Stock arriving (from_sub_warehouse_id is NULL)
- `outbound`: Stock leaving (to_sub_warehouse_id is NULL)

---

## Data Processing Flows

### Customer Management Flow
```
1. CREATE CUSTOMER
   ↓
   Validate through StoreCustomerRequest
   ↓
   Insert into customers table
   ↓
   Set is_active = true by default

2. SEARCH/FILTER CUSTOMERS
   ↓
   Query customers table
   ↓
   Apply filters: type, status, search term
   ↓
   Return ordered results with statistics

3. UPDATE CUSTOMER
   ↓
   Validate through UpdateCustomerRequest
   ↓
   Update customers table

4. DEACTIVATE CUSTOMER
   ↓
   Check for existing orders
   ↓
   Set is_active = false (soft delete)
```

### Product and Book Management Flow
```
1. CREATE PRODUCT
   ↓
   Insert base product into products table
   ↓
   If type = 'book', insert extended data into books table
   ↓
   Link to author and categories

2. BOOK WITH CATEGORIES
   ↓
   Categories organized hierarchically
   ↓
   Book assigned to category and sub_category
   ↓
   Enables multi-level organization

3. AUTHOR CONTRACT PROCESSING
   ↓
   Create contract in author_book_contracts
   ↓
   Record payments in author_contract_transactions
   ↓
   Auto-calculate: total_paid, outstanding_balance, payment_percentage
   ↓
   Update payment_status: pending → partial → paid
```

### Warehouse and Stock Management Flow
```
1. WAREHOUSE HIERARCHY
   ↓
   Create warehouse
   ↓
   Add sub_warehouses (main, branch, book_fair, etc.)
   ↓
   Track products in sub_warehouse_products

2. STOCK INBOUND (New Stock Arrival)
   ↓
   Create stock_movement (type = 'inbound')
   ↓
   from_sub_warehouse_id = NULL
   ↓
   to_sub_warehouse_id = target location
   ↓
   Update/Insert sub_warehouse_products quantity

3. STOCK TRANSFER (Between Locations)
   ↓
   Create stock_movement (type = 'transfer')
   ↓
   from_sub_warehouse_id = source
   ↓
   to_sub_warehouse_id = destination
   ↓
   Decrease quantity at source
   ↓
   Increase quantity at destination

4. STOCK OUTBOUND (Stock Leaving)
   ↓
   Create stock_movement (type = 'outbound')
   ↓
   from_sub_warehouse_id = source location
   ↓
   to_sub_warehouse_id = NULL
   ↓
   Decrease quantity at source

5. STOCK MONITORING
   ↓
   Query sub_warehouse_products
   ↓
   Check quantity levels
   ↓
   Flag: Out of Stock (≤ 0) or Low Stock (< 10)
   ↓
   Generate alerts/reports
```

### Inter-Module Processing

#### Product → Warehouse Integration
```
Products created in Product module
↓
Products stored in Warehouses
↓
sub_warehouse_products links product_id to sub_warehouse_id
↓
Tracks quantity at each location
↓
Stock movements track all inventory changes
```

#### Customer → Product Integration (Future)
```
Customers (Customer module)
↓
Create Orders (Order module - future)
↓
Order Items reference Products
↓
Trigger Stock Outbound movements
↓
Update sub_warehouse_products quantities
```

---

## Database Diagram (Relationships)

### Customer Module
```
customers (standalone table)
  ├─ Related to future orders/invoices
  └─ Self-contained customer management
```

### Product Module
```
products (base)
  └─── books (1:1)
         ├─── author_id → authors
         ├─── category_id → book_categories
         └─── sub_category_id → book_categories

authors
  ├─── books (1:N)
  └─── author_book_contracts (1:N)
         └─── author_contract_transactions (1:N)

book_categories (hierarchical)
  ├─── parent_id → book_categories (self-reference)
  ├─── books as category (1:N)
  └─── books as sub_category (1:N)
```

### Warehouse Module
```
warehouses
  └─── sub_warehouses (1:N)
         ├─── sub_warehouse_products (1:N)
         │      └─── product_id → products
         ├─── stock_movements as from (1:N)
         └─── stock_movements as to (1:N)

stock_movements
  ├─── product_id → products
  ├─── from_sub_warehouse_id → sub_warehouses
  ├─── to_sub_warehouse_id → sub_warehouses
  └─── user_id → users
```

---

## Audit Trail

All major tables implement audit tracking:
- `created_by`: User who created the record
- `edited_by`: User who last modified the record
- `created_at`: Timestamp of creation
- `updated_at`: Timestamp of last modification

**Audit-enabled tables:**
- customers
- products
- books
- authors
- book_categories
- author_book_contracts
- author_contract_transactions
- warehouses
- sub_warehouses
- sub_warehouse_products
- stock_movements

---

## Indexes and Performance Considerations

### Recommended Indexes

**Customer Module:**
- Index on `customers.type` for filtering
- Index on `customers.is_active` for status queries
- Full-text index on `customers.name`, `customers.email` for search

**Product Module:**
- Index on `products.type` and `products.status`
- Index on `books.author_id`, `books.category_id`, `books.sub_category_id`
- Index on `author_book_contracts.author_id` and `author_id, book_id`
- Index on `author_contract_transactions.contract_id`

**Warehouse Module:**
- Composite index on `sub_warehouse_products(sub_warehouse_id, product_id)` (already unique)
- Index on `stock_movements.product_id`
- Index on `stock_movements.from_sub_warehouse_id` and `to_sub_warehouse_id`
- Index on `stock_movements.movement_type` and `created_at` for reporting

---

## Data Integrity Rules

1. **Cascading Deletes:**
   - Deleting a warehouse cascades to sub_warehouses and sub_warehouse_products
   - Deleting a product cascades to books and sub_warehouse_products
   - Deleting an author cascades to contracts and transactions

2. **Set Null on Delete:**
   - User deletions set created_by/edited_by to NULL (preserve audit trail)
   - Category deletions set category_id/sub_category_id to NULL in books

3. **Unique Constraints:**
   - `sub_warehouse_products`: One record per (sub_warehouse_id, product_id) pair

4. **Business Logic Constraints:**
   - Stock quantity cannot go negative (enforced in application layer)
   - Contract payments cannot exceed contract_price (enforced in application layer)
   - Customer with orders cannot be deleted (enforced in application layer)

---

## Query Optimization Tips

1. **Use Model Scopes:**
   - `Customer::active()` instead of `where('is_active', true)`
   - `Product::byType('book')` instead of `where('type', 'book')`
   - Scopes improve readability and consistency

2. **Eager Loading:**
   - Load relationships in advance to avoid N+1 queries
   - Example: `Book::with(['author', 'category', 'product'])->get()`

3. **Computed Attributes:**
   - Use appended attributes for derived data
   - Cached through model accessors: `total_paid`, `outstanding_balance`

4. **Pagination:**
   - Always paginate large result sets
   - Use `orderBy()` for consistent ordering

---

## Version History

- **v1.0** (2025-12-21): Initial database documentation
  - Customer module tables
  - Product module tables (products, books, authors, categories, contracts)
  - Warehouse module tables (warehouses, sub-warehouses, products, movements)
  - Data processing flows
  - Relationship diagrams
