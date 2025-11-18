# Product Module

This module manages products, books, authors, categories, contracts, and payments for the accounting system.

## Database Schema

The module implements the following tables:

### 1. products
- Base product table for books and future product types
- Fields: id, name, type (enum), sku, description, base_price, status, created_by, edited_by, timestamps

### 2. book_categories
- Supports parent → subcategory structure
- Fields: id, name, parent_id, created_by, edited_by, timestamps

### 3. authors
- Full author profile management
- Fields: id, full_name, nationality, country_of_residence, bio, occupation, phone_number, whatsapp_number, email, id_image, created_by, edited_by, timestamps

### 4. books
- Book-specific metadata linked to products
- Fields: id, product_id, author_id, category_id, sub_category_id, isbn, num_of_pages, cover_type, published_at, language, is_translated, translated_from, translated_to, translator_name, created_by, edited_by, timestamps

### 5. author_book_contracts
- An author can have MANY books with different contracts
- Fields: id, author_id, book_id, contract_date, contract_price, percentage_from_book_profit, contract_file, created_by, edited_by, timestamps

### 6. author_contract_transactions
- Tracks installment payments toward contract price
- Fields: id, contract_id, amount, payment_date, notes, receipt_file, created_by, edited_by, timestamps

## Components Created

### ✅ Migrations
All database migrations have been created with proper foreign keys and created_by/edited_by fields:
- `2025_11_18_000001_create_products_table.php`
- `2025_11_18_000002_create_book_categories_table.php`
- `2025_11_18_000003_create_authors_table.php`
- `2025_11_18_000004_create_books_table.php`
- `2025_11_18_000005_create_author_book_contracts_table.php`
- `2025_11_18_000006_create_author_contract_transactions_table.php`

### ✅ Models
All Eloquent models created with relationships, accessors, and scopes:
- `Product.php` - Base product model
- `BookCategory.php` - Category with parent/child relationships
- `Author.php` - Author profile with contract calculations
- `Book.php` - Book details with product relationship
- `Contract.php` - Contract management with payment tracking
- `ContractTransaction.php` - Payment transaction records

### ✅ Form Requests
Validation requests for all entities:
- Store/Update requests for: Product, Category, Author, Book, Contract, Transaction
- Custom validation messages
- File upload validation

### ✅ Controllers
Full CRUD controllers following HR module patterns:
- `ProductController.php`
- `CategoryController.php`
- `AuthorController.php`
- `BookController.php`
- `ContractController.php`
- `TransactionController.php`

### ✅ Routes
All routes configured in `routes/web.php`:
- `/product/products/*` - Product management
- `/product/categories/*` - Category management
- `/product/authors/*` - Author management
- `/product/books/*` - Book management
- `/product/contracts/*` - Contract management
- `/product/transactions/*` - Payment transaction management

### ✅ Translations
Translation files created for English and Arabic:
- English: product.php, category.php, author.php, book.php, contract.php, transaction.php
- Arabic: Basic translations for all entities

## User Workflows Implemented

The module supports the following workflows:

1. **Adding a new author**
   - Route: `/product/authors/create`
   - Full profile including contact info, ID image upload

2. **Registering a new book**
   - Route: `/product/books/create`
   - Creates product and book records
   - Links to author and categories
   - Supports translation information

3. **Assigning categories & subcategories**
   - Route: `/product/categories/*`
   - Parent/child category structure
   - Prevents circular references

4. **Creating a contract between author and book**
   - Route: `/product/contracts/create`
   - Contract price and profit percentage
   - File upload for contract documents

5. **Recording partial payments for a contract**
   - Route: `/product/transactions/create`
   - Payment tracking with receipts
   - Automatic contract status updates

6. **Tracking outstanding contract balances**
   - Available in contract show view
   - Real-time calculation of paid vs. outstanding amounts

7. **Viewing author's payment history and contract summary**
   - Route: `/product/authors/{id}`
   - Shows all contracts, books, and payment history

## Next Steps - Views

The views still need to be created. They should follow the HR module pattern using:
- `x-dashboard` component for layout
- `x-dashboard.packages.form-builder` for forms
- Breadcrumb navigation
- Statistics cards
- Search and filter functionality

Example view structure needed:
```
resources/views/
├── authors/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
├── books/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
├── categories/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
├── contracts/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
├── products/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
└── transactions/
    ├── index.blade.php
    ├── create.blade.php
    ├── edit.blade.php
    └── show.blade.php
```

## Running Migrations

To run the migrations:

```bash
php artisan migrate
```

Or if using module-specific migration:

```bash
php artisan module:migrate Product
```

## Testing

Basic test structure should be created following the HR module pattern.

## Features

- ✅ Complete database schema with relationships
- ✅ CRUD operations for all entities
- ✅ File uploads (author ID, contract files, receipts)
- ✅ Contract payment tracking
- ✅ Outstanding balance calculations
- ✅ Multi-language support (EN/AR)
- ✅ Search and filter functionality
- ✅ Author-Book-Contract relationships
- ✅ Category hierarchy (parent/child)
- ✅ Translation tracking for books
- ⏳ Views (to be implemented)
- ⏳ Tests (to be implemented)

## Code Style

This module follows the same code style and patterns as the HR module:
- Proper namespacing
- Relationship definitions in models
- Validation in Form Requests
- Business logic in models
- Clean controllers
- Translation keys for all messages
