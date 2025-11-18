# Product Module Documentation

## Overview

The Product module manages products, books, authors, categories, contracts, and transactions within the accounting system. It provides a comprehensive solution for publishing houses to track their products, author contracts, and payment transactions.

## Table of Contents

- [Database Schema](#database-schema)
- [Models](#models)
- [Relationships](#relationships)
- [Scopes and Accessors](#scopes-and-accessors)
- [Business Logic](#business-logic)

---

## Database Schema

### Products Table (`products`)

The main table for storing product information.

| Column | Type | Description | Constraints |
|--------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | Primary key | AUTO_INCREMENT |
| `name` | VARCHAR(255) | Product name | NOT NULL |
| `type` | ENUM | Product type | 'book', 'ebook', 'journal', 'course', 'bundle' |
| `sku` | VARCHAR(100) | Stock keeping unit | NULLABLE |
| `description` | TEXT | Product description | NULLABLE |
| `base_price` | DECIMAL(10,2) | Base price | DEFAULT 0 |
| `status` | ENUM | Product status | 'active', 'inactive' |
| `created_by` | BIGINT UNSIGNED | User who created | FK to users, NULLABLE |
| `edited_by` | BIGINT UNSIGNED | User who last edited | FK to users, NULLABLE |
| `created_at` | TIMESTAMP | Creation timestamp | |
| `updated_at` | TIMESTAMP | Last update timestamp | |

**Indexes:**
- Primary key on `id`
- Foreign key on `created_by` references `users(id)`
- Foreign key on `edited_by` references `users(id)`

---

### Book Categories Table (`book_categories`)

Hierarchical category system for organizing books.

| Column | Type | Description | Constraints |
|--------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | Primary key | AUTO_INCREMENT |
| `name` | VARCHAR(255) | Category name | NOT NULL |
| `parent_id` | BIGINT UNSIGNED | Parent category ID | FK to book_categories, NULLABLE |
| `created_by` | BIGINT UNSIGNED | User who created | FK to users, NULLABLE |
| `edited_by` | BIGINT UNSIGNED | User who last edited | FK to users, NULLABLE |
| `created_at` | TIMESTAMP | Creation timestamp | |
| `updated_at` | TIMESTAMP | Last update timestamp | |

**Indexes:**
- Primary key on `id`
- Foreign key on `parent_id` references `book_categories(id)`
- Foreign key on `created_by` references `users(id)`
- Foreign key on `edited_by` references `users(id)`

**Notes:**
- Supports hierarchical structure (parent-child categories)
- A category can have multiple subcategories

---

### Authors Table (`authors`)

Stores author information and contact details.

| Column | Type | Description | Constraints |
|--------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | Primary key | AUTO_INCREMENT |
| `full_name` | VARCHAR(255) | Author's full name | NOT NULL |
| `nationality` | VARCHAR(150) | Author's nationality | NULLABLE |
| `country_of_residence` | VARCHAR(150) | Country of residence | NULLABLE |
| `bio` | TEXT | Author biography | NULLABLE |
| `occupation` | VARCHAR(255) | Author's occupation | NULLABLE |
| `phone_number` | VARCHAR(50) | Phone number | NULLABLE |
| `whatsapp_number` | VARCHAR(50) | WhatsApp number | NULLABLE |
| `email` | VARCHAR(255) | Email address | NULLABLE |
| `id_image` | VARCHAR(255) | ID document image path | NULLABLE |
| `created_by` | BIGINT UNSIGNED | User who created | FK to users, NULLABLE |
| `edited_by` | BIGINT UNSIGNED | User who last edited | FK to users, NULLABLE |
| `created_at` | TIMESTAMP | Creation timestamp | |
| `updated_at` | TIMESTAMP | Last update timestamp | |

**Indexes:**
- Primary key on `id`
- Foreign key on `created_by` references `users(id)`
- Foreign key on `edited_by` references `users(id)`

---

### Books Table (`books`)

Detailed book information linked to products.

| Column | Type | Description | Constraints |
|--------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | Primary key | AUTO_INCREMENT |
| `product_id` | BIGINT UNSIGNED | Associated product | FK to products, NOT NULL |
| `author_id` | BIGINT UNSIGNED | Book author | FK to authors, NULLABLE |
| `category_id` | BIGINT UNSIGNED | Main category | FK to book_categories, NULLABLE |
| `sub_category_id` | BIGINT UNSIGNED | Subcategory | FK to book_categories, NULLABLE |
| `isbn` | VARCHAR(50) | ISBN number | NOT NULL |
| `num_of_pages` | INTEGER | Number of pages | NULLABLE |
| `cover_type` | ENUM | Cover type | 'hard', 'soft' |
| `published_at` | DATE | Publication date | NULLABLE |
| `language` | VARCHAR(100) | Book language | NULLABLE |
| `is_translated` | BOOLEAN | Translation flag | DEFAULT false |
| `translated_from` | VARCHAR(100) | Source language | NULLABLE |
| `translated_to` | VARCHAR(100) | Target language | NULLABLE |
| `translator_name` | VARCHAR(255) | Translator's name | NULLABLE |
| `created_by` | BIGINT UNSIGNED | User who created | FK to users, NULLABLE |
| `edited_by` | BIGINT UNSIGNED | User who last edited | FK to users, NULLABLE |
| `created_at` | TIMESTAMP | Creation timestamp | |
| `updated_at` | TIMESTAMP | Last update timestamp | |

**Indexes:**
- Primary key on `id`
- Foreign key on `product_id` references `products(id)` CASCADE
- Foreign key on `author_id` references `authors(id)`
- Foreign key on `category_id` references `book_categories(id)`
- Foreign key on `sub_category_id` references `book_categories(id)`
- Foreign key on `created_by` references `users(id)`
- Foreign key on `edited_by` references `users(id)`

---

### Author Book Contracts Table (`author_book_contracts`)

Manages contracts between authors and the publishing house.

| Column | Type | Description | Constraints |
|--------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | Primary key | AUTO_INCREMENT |
| `author_id` | BIGINT UNSIGNED | Contract author | FK to authors, NOT NULL |
| `book_id` | BIGINT UNSIGNED | Associated book | FK to books, NULLABLE |
| `book_name` | VARCHAR(255) | Book name fallback | NULLABLE |
| `contract_date` | DATE | Contract signing date | NOT NULL |
| `contract_price` | DECIMAL(12,2) | Total contract amount | DEFAULT 0 |
| `percentage_from_book_profit` | DECIMAL(5,2) | Profit sharing percentage | DEFAULT 0 |
| `contract_file` | VARCHAR(255) | Contract document path | NULLABLE |
| `created_by` | BIGINT UNSIGNED | User who created | FK to users, NULLABLE |
| `edited_by` | BIGINT UNSIGNED | User who last edited | FK to users, NULLABLE |
| `created_at` | TIMESTAMP | Creation timestamp | |
| `updated_at` | TIMESTAMP | Last update timestamp | |

**Indexes:**
- Primary key on `id`
- Foreign key on `author_id` references `authors(id)` CASCADE
- Foreign key on `book_id` references `books(id)` CASCADE
- Foreign key on `created_by` references `users(id)`
- Foreign key on `edited_by` references `users(id)`

**Notes:**
- `book_name` is used when `book_id` is null (contract for a book not yet in the system)
- Contract price is the total amount agreed upon
- Percentage represents ongoing profit sharing

---

### Author Contract Transactions Table (`author_contract_transactions`)

Tracks payments made to authors for their contracts.

| Column | Type | Description | Constraints |
|--------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | Primary key | AUTO_INCREMENT |
| `contract_id` | BIGINT UNSIGNED | Associated contract | FK to author_book_contracts, NOT NULL |
| `amount` | DECIMAL(12,2) | Payment amount | NOT NULL |
| `payment_date` | DATE | Date of payment | NOT NULL |
| `notes` | TEXT | Payment notes | NULLABLE |
| `receipt_file` | VARCHAR(255) | Receipt document path | NULLABLE |
| `created_by` | BIGINT UNSIGNED | User who created | FK to users, NULLABLE |
| `edited_by` | BIGINT UNSIGNED | User who last edited | FK to users, NULLABLE |
| `created_at` | TIMESTAMP | Creation timestamp | |
| `updated_at` | TIMESTAMP | Last update timestamp | |

**Indexes:**
- Primary key on `id`
- Foreign key on `contract_id` references `author_book_contracts(id)` CASCADE
- Foreign key on `created_by` references `users(id)`
- Foreign key on `edited_by` references `users(id)`

---

## Models

### Product Model

**Namespace:** `Modules\Product\Models\Product`

**Fillable Attributes:**
- `name`, `type`, `sku`, `description`, `base_price`, `status`, `created_by`, `edited_by`

**Casts:**
- `base_price` → `decimal:2`

**Relationships:**
- `book()` - HasOne relationship with Book
- `creator()` - BelongsTo User (created_by)
- `editor()` - BelongsTo User (edited_by)

**Scopes:**
- `active()` - Filter active products
- `byType(string $type)` - Filter by product type

**Accessors:**
- `status_color` - Returns color based on status
- `type_color` - Returns color based on type

---

### BookCategory Model

**Namespace:** `Modules\Product\Models\BookCategory`

**Fillable Attributes:**
- `name`, `parent_id`, `created_by`, `edited_by`

**Relationships:**
- `parent()` - BelongsTo BookCategory (self-referencing)
- `children()` - HasMany BookCategory (self-referencing)
- `books()` - HasMany Book (as category)
- `subCategoryBooks()` - HasMany Book (as sub_category)
- `creator()` - BelongsTo User (created_by)
- `editor()` - BelongsTo User (edited_by)

**Scopes:**
- `parents()` - Filter parent categories (no parent_id)
- `children()` - Filter child categories (has parent_id)

**Accessors:**
- `full_name` - Returns hierarchical name (e.g., "Fiction > Mystery")
- `is_parent` - Boolean indicating if category is a parent

---

### Author Model

**Namespace:** `Modules\Product\Models\Author`

**Fillable Attributes:**
- `full_name`, `nationality`, `country_of_residence`, `bio`, `occupation`, `phone_number`, `whatsapp_number`, `email`, `id_image`, `created_by`, `edited_by`

**Casts:**
- `created_at` → `datetime`
- `updated_at` → `datetime`

**Appends:**
- `total_contract_value`, `total_paid`, `outstanding_balance`

**Relationships:**
- `books()` - HasMany Book
- `contracts()` - HasMany Contract
- `creator()` - BelongsTo User (created_by)
- `editor()` - BelongsTo User (edited_by)

**Accessors:**
- `total_contract_value` - Sum of all contract prices
- `total_paid` - Sum of all transaction amounts
- `outstanding_balance` - Difference between contract value and paid

**Methods:**
- `getAllTransactions()` - Retrieve all transactions across all contracts

---

### Book Model

**Namespace:** `Modules\Product\Models\Book`

**Fillable Attributes:**
- `product_id`, `author_id`, `category_id`, `sub_category_id`, `isbn`, `num_of_pages`, `cover_type`, `published_at`, `language`, `is_translated`, `translated_from`, `translated_to`, `translator_name`, `created_by`, `edited_by`

**Casts:**
- `published_at` → `date`
- `is_translated` → `boolean`

**Relationships:**
- `product()` - BelongsTo Product
- `author()` - BelongsTo Author
- `category()` - BelongsTo BookCategory
- `subCategory()` - BelongsTo BookCategory
- `contracts()` - HasMany Contract
- `creator()` - BelongsTo User (created_by)
- `editor()` - BelongsTo User (edited_by)

**Accessors:**
- `cover_type_color` - Returns color based on cover type
- `full_title` - Returns product name
- `translation_info` - Formatted translation information string

---

### Contract Model

**Namespace:** `Modules\Product\Models\Contract`

**Table Name:** `author_book_contracts`

**Fillable Attributes:**
- `author_id`, `book_id`, `contract_date`, `contract_price`, `percentage_from_book_profit`, `contract_file`, `created_by`, `edited_by`, `book_name`

**Casts:**
- `contract_date` → `date`
- `contract_price` → `decimal:2`
- `percentage_from_book_profit` → `decimal:2`

**Relationships:**
- `author()` - BelongsTo Author
- `book()` - BelongsTo Book
- `transactions()` - HasMany ContractTransaction
- `creator()` - BelongsTo User (created_by)
- `editor()` - BelongsTo User (edited_by)

**Scopes:**
- `fullyPaid()` - Filter fully paid contracts
- `pending()` - Filter contracts without transactions
- `forAuthor(int $authorId)` - Filter by author
- `forBook(int $bookId)` - Filter by book

**Accessors:**
- `total_paid` - Sum of transaction amounts
- `outstanding_balance` - Remaining payment amount
- `payment_percentage` - Percentage of contract paid
- `payment_status` - Status: 'paid', 'partial', or 'pending'
- `status_color` - Color based on payment status

**Methods:**
- `isFullyPaid()` - Check if contract is fully paid

---

### ContractTransaction Model

**Namespace:** `Modules\Product\Models\ContractTransaction`

**Table Name:** `author_contract_transactions`

**Fillable Attributes:**
- `contract_id`, `amount`, `payment_date`, `notes`, `receipt_file`, `created_by`, `edited_by`

**Casts:**
- `payment_date` → `date`
- `amount` → `decimal:2`

**Relationships:**
- `contract()` - BelongsTo Contract
- `creator()` - BelongsTo User (created_by)
- `editor()` - BelongsTo User (edited_by)

**Scopes:**
- `forContract(int $contractId)` - Filter by contract
- `thisMonth()` - Filter current month transactions
- `thisYear()` - Filter current year transactions

---

## Relationships

### Entity Relationship Diagram

```
┌─────────┐
│  User   │
└────┬────┘
     │
     │ created_by/edited_by
     │
     ├─────────────┬──────────────┬─────────────┬────────────┬─────────────┐
     │             │              │             │            │             │
     ▼             ▼              ▼             ▼            ▼             ▼
┌─────────┐  ┌──────────┐  ┌─────────┐  ┌──────────┐  ┌──────────┐  ┌──────────────────┐
│ Product │  │BookCateg-│  │ Author  │  │  Book    │  │ Contract │  │ContractTransactio│
└────┬────┘  │   ory    │  └────┬────┘  └────┬─────┘  └────┬─────┘  └────────┬─────────┘
     │       └────┬─────┘       │            │             │                 │
     │            │              │            │             │                 │
     │ 1:1        │ self-ref     │ 1:N        │ N:1         │ 1:N             │ N:1
     │            │ parent       │            │             │                 │
     ▼            │ /children    ▼            │             ▼                 │
┌─────────┐       │         ┌─────────┐      │        ┌──────────┐           │
│  Book   │◄──────┴─────────┤  Book   │      │        │ Contract │◄──────────┘
└─────────┘                 └────┬────┘      │        └────┬─────┘
     │                           │            │             │
     │ N:1                       │ N:1        │ 1:N         │ N:1
     └───────────────────────────┴────────────┴─────────────┘
                                 Author
```

### Relationship Details

1. **Product → Book**
   - Type: One-to-One
   - A product can have one book detail
   - Cascade: Product deletion removes book

2. **BookCategory → BookCategory**
   - Type: Self-referencing (parent/children)
   - A category can have a parent category
   - A category can have multiple child categories

3. **BookCategory → Book**
   - Type: One-to-Many (as category and sub_category)
   - A category can have multiple books
   - A book has one main category and one subcategory

4. **Author → Book**
   - Type: One-to-Many
   - An author can write multiple books
   - A book has one primary author

5. **Author → Contract**
   - Type: One-to-Many
   - An author can have multiple contracts
   - A contract belongs to one author

6. **Book → Contract**
   - Type: One-to-Many
   - A book can have multiple contracts
   - A contract belongs to one book (or has book_name if book not in system)

7. **Contract → ContractTransaction**
   - Type: One-to-Many
   - A contract can have multiple payment transactions
   - A transaction belongs to one contract

8. **User → All Models**
   - Type: One-to-Many (created_by/edited_by)
   - All models track creator and last editor

---

## Scopes and Accessors

### Global Scopes

None defined.

### Local Scopes

**Product:**
- `active()` - Only active products
- `byType($type)` - Filter by product type

**BookCategory:**
- `parents()` - Only parent categories
- `children()` - Only child categories

**Contract:**
- `fullyPaid()` - Contracts with total_paid >= contract_price
- `pending()` - Contracts without any transactions
- `forAuthor($authorId)` - Contracts for specific author
- `forBook($bookId)` - Contracts for specific book

**ContractTransaction:**
- `forContract($contractId)` - Transactions for specific contract
- `thisMonth()` - Current month transactions
- `thisYear()` - Current year transactions

### Accessors

**Product:**
- `status_color` - Visual indicator color
- `type_color` - Visual indicator color

**BookCategory:**
- `full_name` - Hierarchical category path
- `is_parent` - Boolean parent check

**Author:**
- `total_contract_value` - Sum of all contract prices
- `total_paid` - Sum of all payments received
- `outstanding_balance` - Remaining amount to be paid

**Book:**
- `cover_type_color` - Visual indicator color
- `full_title` - Product name
- `translation_info` - Formatted translation details

**Contract:**
- `total_paid` - Sum of transaction amounts
- `outstanding_balance` - Remaining balance
- `payment_percentage` - Completion percentage
- `payment_status` - 'paid', 'partial', 'pending'
- `status_color` - Visual indicator color

---

## Business Logic

### Contract Payment Tracking

The module implements comprehensive contract payment tracking:

1. **Contract Creation**
   - Contract specifies total amount (`contract_price`)
   - Can be linked to existing book or use `book_name` for future books
   - Supports profit-sharing percentage

2. **Payment Transactions**
   - Multiple transactions can be created for a contract
   - Each transaction has amount, date, and optional receipt
   - System validates that total transactions don't exceed contract price

3. **Payment Status Calculation**
   - **Pending**: No transactions recorded
   - **Partial**: Some amount paid, but less than contract price
   - **Paid**: Total transactions >= contract price

4. **Author Financial Summary**
   - `total_contract_value`: Sum of all contract prices
   - `total_paid`: Sum of all transaction amounts
   - `outstanding_balance`: Total contracts - total paid

### Validation Rules

**Contract Validation:**
- Total transactions cannot exceed contract price
- Validation occurs in `StoreTransactionRequest` and `UpdateContractRequest`

**Book Validation:**
- Translation fields required when `is_translated` is true
- Category and subcategory must exist in `book_categories`

### File Uploads

The module handles file uploads for:
- **Author**: `id_image` (ID document)
- **Contract**: `contract_file` (signed contract)
- **Transaction**: `receipt_file` (payment receipt)

---

## Usage Examples

### Creating a Product with Book

```php
$product = Product::create([
    'name' => 'Introduction to Laravel',
    'type' => 'book',
    'sku' => 'BK-001',
    'base_price' => 29.99,
    'status' => 'active',
    'created_by' => auth()->id(),
]);

$book = Book::create([
    'product_id' => $product->id,
    'author_id' => $author->id,
    'category_id' => $category->id,
    'isbn' => '978-1234567890',
    'num_of_pages' => 350,
    'cover_type' => 'soft',
    'published_at' => '2025-11-18',
    'created_by' => auth()->id(),
]);
```

### Creating a Contract with Transactions

```php
$contract = Contract::create([
    'author_id' => $author->id,
    'book_id' => $book->id,
    'contract_date' => '2025-11-01',
    'contract_price' => 5000.00,
    'percentage_from_book_profit' => 15.00,
    'created_by' => auth()->id(),
]);

$transaction = ContractTransaction::create([
    'contract_id' => $contract->id,
    'amount' => 2000.00,
    'payment_date' => '2025-11-15',
    'notes' => 'First installment payment',
    'created_by' => auth()->id(),
]);

// Check payment status
echo $contract->payment_status; // 'partial'
echo $contract->outstanding_balance; // 3000.00
```

### Querying Author Finances

```php
$author = Author::with('contracts.transactions')->find(1);

echo $author->total_contract_value; // 15000.00
echo $author->total_paid; // 8500.00
echo $author->outstanding_balance; // 6500.00

// Get all transactions
$transactions = $author->getAllTransactions();
```

### Working with Categories

```php
// Create parent category
$fiction = BookCategory::create([
    'name' => 'Fiction',
    'created_by' => auth()->id(),
]);

// Create subcategory
$mystery = BookCategory::create([
    'name' => 'Mystery',
    'parent_id' => $fiction->id,
    'created_by' => auth()->id(),
]);

// Get full name
echo $mystery->full_name; // "Fiction > Mystery"

// Get all parent categories
$parents = BookCategory::parents()->get();
```

---

## Module Structure

```
Modules/Product/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ProductController.php
│   │   │   ├── BookController.php
│   │   │   ├── AuthorController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── ContractController.php
│   │   │   └── TransactionController.php
│   │   └── Requests/
│   │       ├── StoreProductRequest.php
│   │       ├── UpdateProductRequest.php
│   │       ├── StoreBookRequest.php
│   │       ├── UpdateBookRequest.php
│   │       ├── StoreAuthorRequest.php
│   │       ├── UpdateAuthorRequest.php
│   │       ├── StoreCategoryRequest.php
│   │       ├── UpdateCategoryRequest.php
│   │       ├── StoreContractRequest.php
│   │       ├── UpdateContractRequest.php
│   │       ├── StoreTransactionRequest.php
│   │       └── UpdateTransactionRequest.php
│   ├── Models/
│   │   ├── Product.php
│   │   ├── Book.php
│   │   ├── Author.php
│   │   ├── BookCategory.php
│   │   ├── Contract.php
│   │   └── ContractTransaction.php
│   └── Providers/
│       ├── ProductServiceProvider.php
│       ├── RouteServiceProvider.php
│       └── EventServiceProvider.php
├── database/
│   ├── migrations/
│   │   ├── 2025_11_18_000001_create_products_table.php
│   │   ├── 2025_11_18_000002_create_book_categories_table.php
│   │   ├── 2025_11_18_000003_create_authors_table.php
│   │   ├── 2025_11_18_000004_create_books_table.php
│   │   ├── 2025_11_18_000005_create_author_book_contracts_table.php
│   │   └── 2025_11_18_000006_create_author_contract_transactions_table.php
│   └── seeders/
│       └── ProductDatabaseSeeder.php
├── resources/
│   └── views/
│       ├── products/
│       ├── books/
│       ├── authors/
│       ├── categories/
│       └── contracts/
├── routes/
│   ├── web.php
│   └── api.php
├── tests/
│   ├── Feature/
│   └── Unit/
└── docs/
    └── README.md
```

---

## API Endpoints

### Web Routes

Defined in `routes/web.php`:

- Products: `/products` (CRUD operations)
- Books: `/books` (CRUD operations)
- Authors: `/authors` (CRUD operations)
- Categories: `/categories` (CRUD operations)
- Contracts: `/contracts` (CRUD operations)
- Transactions: `/transactions` (CRUD operations)

---

## Configuration

Module configuration is defined in:
- `module.json` - Module metadata
- `composer.json` - PHP dependencies
- `package.json` - JavaScript dependencies

---

## Testing

See the test files in `tests/` directory for comprehensive unit and feature tests covering:
- Model relationships
- Business logic
- Validation rules
- API endpoints

---

## License

This module is part of the accounting system and follows the same license.
