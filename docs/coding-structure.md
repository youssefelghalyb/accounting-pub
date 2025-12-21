# Coding Structure Documentation

## Overview
This document describes the coding structure, architectural patterns, and conventions used across the Customer, Product, and Warehouse modules in this Laravel-based accounting system.

---

## Architecture Pattern

### Modular Monolith Architecture
The application follows a **modular monolith** pattern using Laravel's module structure:

```
Modules/
├── Customer/
├── Product/
└── Warehouse/
```

Each module is self-contained with its own:
- Models (Eloquent ORM)
- Controllers (HTTP request handlers)
- Requests (Form validation)
- Views (Blade templates)
- Routes (Web/API endpoints)
- Migrations (Database schema)
- Translations (Multi-language support)
- Service Providers (Module bootstrap)

---

## Module Structure

### Standard Module Layout

```
Modules/{ModuleName}/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── {Resource}Controller.php
│   │   └── Requests/
│   │       ├── Store{Resource}Request.php
│   │       └── Update{Resource}Request.php
│   ├── Models/
│   │   └── {Model}.php
│   └── Providers/
│       ├── {Module}ServiceProvider.php
│       ├── RouteServiceProvider.php
│       └── EventServiceProvider.php
├── config/
│   └── config.php
├── database/
│   ├── migrations/
│   │   └── YYYY_MM_DD_HHMMSS_create_{table}_table.php
│   └── seeders/
│       └── {Module}DatabaseSeeder.php
├── resources/
│   ├── lang/
│   │   ├── en/
│   │   │   └── {resource}.php
│   │   └── ar/
│   │       └── {resource}.php
│   ├── views/
│   │   └── {resource}/
│   │       ├── index.blade.php
│   │       ├── create.blade.php
│   │       ├── edit.blade.php
│   │       └── show.blade.php
│   └── assets/
│       ├── js/
│       └── sass/
├── routes/
│   ├── web.php
│   └── api.php
├── tests/
│   ├── Unit/
│   └── Feature/
├── composer.json
├── module.json
├── package.json
└── vite.config.js
```

---

## Layer Architecture

### 1. Routes Layer
**Location:** `routes/web.php`, `routes/api.php`

**Purpose:** Define HTTP endpoints and route them to controllers

**Example:**
```php
// Modules/Customer/routes/web.php
Route::prefix('customer')->group(function() {
    Route::resource('customers', CustomerController::class);
    Route::post('customers/{customer}/toggle-status',
        [CustomerController::class, 'toggleStatus'])
        ->name('customers.toggle-status');
});
```

**Patterns:**
- RESTful resource routes using `Route::resource()`
- Route grouping with prefixes
- Named routes for easy reference
- Custom actions outside standard CRUD

---

### 2. Controllers Layer
**Location:** `app/Http/Controllers/`

**Purpose:** Handle HTTP requests, orchestrate business logic, return responses

**Structure:**
```php
class CustomerController extends Controller
{
    // Display listing
    public function index(Request $request) { }

    // Show create form
    public function create() { }

    // Store new resource
    public function store(StoreCustomerRequest $request) { }

    // Display single resource
    public function show($id) { }

    // Show edit form
    public function edit($id) { }

    // Update resource
    public function update(UpdateCustomerRequest $request, $id) { }

    // Delete resource
    public function destroy(Customer $customer) { }

    // Custom actions
    public function toggleStatus(Customer $customer) { }
}
```

**Conventions:**
- Extends `Illuminate\Routing\Controller`
- Uses dependency injection for requests
- Returns views or redirects with flash messages
- Type-hints form requests for automatic validation
- Uses route model binding where appropriate

**Responsibilities:**
- Request validation (via Form Requests)
- Query building with filters and scopes
- Business logic orchestration
- Response rendering
- Flash message handling

---

### 3. Form Requests Layer
**Location:** `app/Http/Requests/`

**Purpose:** Validate incoming data before processing

**Structure:**
```php
class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // or check permissions
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:individual,company,online',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'tax_number' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ];
    }
}
```

**Patterns:**
- Separate request classes for Store and Update
- Authorization logic in `authorize()` method
- Validation rules in `rules()` method
- Custom error messages in `messages()` method
- Data transformation in `validated()` override

---

### 4. Models Layer
**Location:** `app/Models/`

**Purpose:** Represent database tables and business entities

**Structure:**
```php
class Customer extends Model
{
    // Mass assignment protection
    protected $fillable = [...];

    // Attribute casting
    protected $casts = [...];

    // Appended accessors
    protected $appends = [...];

    // Relationships
    public function orders(): HasMany { }

    // Scopes
    public function scopeActive($query) { }
    public function scopeSearch($query, $search) { }

    // Accessors
    public function getTypeLabelAttribute(): string { }

    // Mutators
    public function setEmailAttribute($value) { }

    // Business methods
    public function activate(): bool { }
    public function deactivate(): bool { }
}
```

**Key Components:**

#### a. Mass Assignment
```php
protected $fillable = [
    'name', 'type', 'phone', 'email', 'address'
];
```
- Whitelists attributes that can be mass-assigned
- Protects against mass assignment vulnerabilities

#### b. Attribute Casting
```php
protected $casts = [
    'is_active' => 'boolean',
    'published_at' => 'date',
    'base_price' => 'decimal:2',
];
```
- Automatically converts database values to PHP types
- Supports: boolean, integer, float, decimal, date, datetime, array, json

#### c. Relationships
```php
// One-to-Many
public function subWarehouses(): HasMany
{
    return $this->hasMany(SubWarehouse::class);
}

// Belongs-To
public function warehouse(): BelongsTo
{
    return $this->belongsTo(Warehouse::class);
}

// One-to-One
public function book(): HasOne
{
    return $this->hasOne(Book::class);
}
```

#### d. Query Scopes
```php
// Local scope (reusable query constraints)
public function scopeActive($query)
{
    return $query->where('is_active', true);
}

// Usage: Customer::active()->get()
```

#### e. Accessors (Computed Attributes)
```php
public function getFullNameAttribute(): string
{
    return "{$this->first_name} {$this->last_name}";
}

// Usage: $customer->full_name
```

#### f. Appended Attributes
```php
protected $appends = ['type_label', 'status_label'];

// Always includes computed attributes in JSON
```

---

### 5. Views Layer
**Location:** `resources/views/`

**Purpose:** Render HTML responses using Blade templates

**Structure:**
```
resources/views/{resource}/
├── index.blade.php     # List all records
├── create.blade.php    # Create new record form
├── edit.blade.php      # Edit existing record form
└── show.blade.php      # Display single record
```

**Blade Features Used:**
- **Components:** `<x-component-name />`
- **Directives:** `@if`, `@foreach`, `@auth`, `@csrf`
- **Includes:** `@include('partial.name')`
- **Layouts:** `@extends('layout')`, `@section`, `@yield`
- **Translations:** `{{ __('module::key') }}`
- **XSS Protection:** `{{ $variable }}` (auto-escaped)

**Example:**
```blade
@extends('customer::components.layouts.master')

@section('content')
    <h1>{{ __('customer::customer.customers') }}</h1>

    @foreach($customers as $customer)
        <div class="customer-card">
            <h2>{{ $customer->name }}</h2>
            <span class="badge badge-{{ $customer->type_color }}">
                {{ $customer->type_label }}
            </span>
        </div>
    @endforeach
@endsection
```

---

### 6. Migrations Layer
**Location:** `database/migrations/`

**Purpose:** Version control for database schema

**Structure:**
```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_name', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('parent_id')
                ->constrained('parents')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_name');
    }
};
```

**Naming Convention:**
- Format: `YYYY_MM_DD_HHMMSS_create_{table}_table.php`
- Ordered by timestamp for execution sequence
- Dependencies run before dependents

**Best Practices:**
- Always define `up()` and `down()` methods
- Use foreign key constraints for relationships
- Add indexes for frequently queried columns
- Use appropriate column types and sizes

---

### 7. Service Providers
**Location:** `app/Providers/`

**Purpose:** Bootstrap module and register services

**Types:**

#### a. Module Service Provider
```php
class CustomerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'customer');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'customer');
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
```

#### b. Route Service Provider
```php
class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Route::middleware('web')
            ->group(__DIR__.'/../../routes/web.php');

        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__.'/../../routes/api.php');
    }
}
```

---

## Design Patterns

### 1. Repository Pattern (Implicit via Eloquent)
Models act as repositories:
```php
// Instead of raw SQL
Customer::where('type', 'company')->active()->get();

// Chainable, expressive, testable
```

### 2. Form Request Pattern
Validation separated from controllers:
```php
public function store(StoreCustomerRequest $request)
{
    // $request->validated() contains only valid data
    Customer::create($request->validated());
}
```

### 3. Scope Pattern
Reusable query logic:
```php
// Define once
public function scopeOfType($query, string $type)
{
    return $query->where('type', $type);
}

// Use everywhere
Customer::ofType('company')->get();
```

### 4. Accessor/Mutator Pattern
Computed attributes and data transformation:
```php
// Accessor
public function getFullNameAttribute()
{
    return "{$this->first_name} {$this->last_name}";
}

// Usage: $model->full_name
```

### 5. Observer Pattern (via Eloquent Events)
React to model events:
```php
// In EventServiceProvider
Customer::creating(function ($customer) {
    $customer->created_by = auth()->id();
});
```

---

## Naming Conventions

### Files and Classes
| Type | Convention | Example |
|------|------------|---------|
| Controller | PascalCase + Controller suffix | `CustomerController` |
| Model | PascalCase (singular) | `Customer`, `SubWarehouse` |
| Request | PascalCase + Request suffix | `StoreCustomerRequest` |
| Migration | snake_case with timestamp | `2025_11_18_create_customers_table.php` |
| View | snake_case | `customers/index.blade.php` |
| Route Name | dot notation | `customer.customers.index` |

### Database
| Type | Convention | Example |
|------|------------|---------|
| Table | snake_case (plural) | `customers`, `sub_warehouses` |
| Column | snake_case | `created_at`, `is_active` |
| Foreign Key | singular_id | `warehouse_id`, `author_id` |
| Pivot Table | alphabetical_order | `author_book` |

### Variables and Methods
| Type | Convention | Example |
|------|------------|---------|
| Variable | camelCase | `$customerData`, `$subWarehouse` |
| Method | camelCase | `getFullName()`, `activate()` |
| Constant | UPPER_SNAKE_CASE | `MAX_QUANTITY` |
| Scope | camelCase (no prefix) | `scopeActive()` → `active()` |

---

## Module-Specific Patterns

### Customer Module
```
Features:
- CRUD operations for customers
- Type filtering (individual, company, online)
- Status management (active/inactive)
- Search across multiple fields
- Statistics dashboard

Key Files:
- Models/Customer.php
- Controllers/CustomerController.php
- Requests/StoreCustomerRequest.php
- Requests/UpdateCustomerRequest.php
```

### Product Module
```
Features:
- Multi-type products (book, ebook, journal, course, bundle)
- Extended book information
- Author management with contracts
- Hierarchical category system
- Contract payment tracking

Key Files:
- Models/Product.php
- Models/Book.php (extends Product)
- Models/Author.php
- Models/BookCategory.php (self-referencing)
- Models/Contract.php
- Models/ContractTransaction.php

Relationships:
- Product 1:1 Book
- Book N:1 Author
- Book N:1 Category (parent)
- Book N:1 Category (sub)
- Author 1:N Contracts
- Contract 1:N Transactions
```

### Warehouse Module
```
Features:
- Multi-level warehouse hierarchy
- Sub-warehouse types (main, branch, book_fair, temporary)
- Product inventory tracking
- Stock movement logging (transfer, inbound, outbound)
- Stock level monitoring

Key Files:
- Models/Warehouse.php
- Models/SubWarehouse.php
- Models/SubWarehouseProduct.php (pivot with quantity)
- Models/StockMovement.php

Relationships:
- Warehouse 1:N SubWarehouse
- SubWarehouse 1:N SubWarehouseProduct
- SubWarehouseProduct N:1 Product
- StockMovement N:1 Product
- StockMovement N:1 SubWarehouse (from)
- StockMovement N:1 SubWarehouse (to)
```

---

## Request Flow

### Standard CRUD Request Flow
```
1. HTTP Request
   ↓
2. Route (web.php)
   ↓
3. Middleware (auth, csrf, etc.)
   ↓
4. Controller Method
   ↓
5. Form Request Validation (if applicable)
   ↓
6. Model Query/Operation
   ↓
7. Database Transaction
   ↓
8. Response (View or Redirect)
   ↓
9. Blade Template Rendering
   ↓
10. HTTP Response
```

### Example: Creating a Customer
```
POST /customer/customers
   ↓
Route::post('/customers', [CustomerController::class, 'store'])
   ↓
CustomerController::store(StoreCustomerRequest $request)
   ↓
$request->validated() // Auto-validation
   ↓
Customer::create($validated)
   ↓
INSERT INTO customers ...
   ↓
redirect()->route('customer.customers.index')
         ->with('success', 'Customer created')
   ↓
Flash message + View rendering
   ↓
HTTP 302 Redirect
```

---

## Translation System

### Multi-Language Support
Each module supports English (en) and Arabic (ar):

```
resources/lang/
├── en/
│   ├── customer.php
│   ├── product.php
│   └── warehouse.php
└── ar/
    ├── customer.php
    ├── product.php
    └── warehouse.php
```

### Translation File Structure
```php
// resources/lang/en/customer.php
return [
    'customers' => 'Customers',
    'add_customer' => 'Add Customer',
    'types' => [
        'individual' => 'Individual',
        'company' => 'Company',
        'online' => 'Online',
    ],
    'messages' => [
        'created' => 'Customer created successfully',
        'updated' => 'Customer updated successfully',
    ],
];
```

### Usage in Code
```php
// In controllers
return redirect()->with('success', __('customer::customer.messages.created'));

// In views
{{ __('customer::customer.add_customer') }}
{{ __('customer::customer.types.individual') }}
```

**Format:** `__('module::file.key.nested')`

---

## Validation Rules

### Common Validation Patterns

```php
// String with max length
'name' => 'required|string|max:255',

// Email (nullable)
'email' => 'nullable|email',

// Enum validation
'type' => 'required|in:individual,company,online',

// Foreign key
'author_id' => 'required|exists:authors,id',

// Decimal with precision
'price' => 'required|numeric|min:0|max:999999.99',

// Date
'published_at' => 'nullable|date',

// Boolean
'is_active' => 'boolean',

// Unique (on create)
'sku' => 'required|unique:products,sku',

// Unique (on update, ignore current)
'sku' => "required|unique:products,sku,{$this->id}",

// Array
'items' => 'required|array',
'items.*.quantity' => 'required|integer|min:1',
```

---

## Error Handling

### Controller-Level
```php
public function destroy(Customer $customer)
{
    // Business logic check
    if ($customer->orders()->exists()) {
        return redirect()
            ->back()
            ->with('error', __('customer::customer.cannot_delete_has_orders'));
    }

    $customer->delete();

    return redirect()
        ->route('customer.customers.index')
        ->with('success', __('customer::customer.deleted'));
}
```

### Flash Messages
```php
// Success
return redirect()->with('success', 'Operation successful');

// Error
return redirect()->with('error', 'Operation failed');

// Info
return redirect()->with('info', 'Information message');

// Warning
return redirect()->with('warning', 'Warning message');
```

### View Display
```blade
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif
```

---

## Testing Structure

### Unit Tests
**Location:** `tests/Unit/`

```php
class BookTest extends TestCase
{
    public function test_book_belongs_to_product()
    {
        $book = Book::factory()->create();
        $this->assertInstanceOf(Product::class, $book->product);
    }

    public function test_translation_info_is_formatted_correctly()
    {
        $book = Book::factory()->create([
            'is_translated' => true,
            'translated_from' => 'English',
            'translator_name' => 'John Doe',
        ]);

        $this->assertStringContainsString('John Doe', $book->translation_info);
    }
}
```

### Feature Tests
**Location:** `tests/Feature/`

```php
class CustomerFeatureTest extends TestCase
{
    public function test_customer_can_be_created()
    {
        $response = $this->post('/customer/customers', [
            'name' => 'Test Customer',
            'type' => 'individual',
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'name' => 'Test Customer',
        ]);
    }
}
```

---

## Asset Management

### Frontend Assets
**Location:** `resources/assets/`

```
resources/assets/
├── js/
│   └── app.js
└── sass/
    └── app.scss
```

**Build Configuration:**
- `vite.config.js` - Vite bundler configuration
- `package.json` - NPM dependencies

**Commands:**
```bash
npm install          # Install dependencies
npm run dev          # Development build
npm run build        # Production build
npm run watch        # Watch for changes
```

---

## Security Best Practices

### 1. Mass Assignment Protection
```php
protected $fillable = ['name', 'email']; // Whitelist
// or
protected $guarded = ['id', 'password']; // Blacklist
```

### 2. SQL Injection Prevention
```php
// ✅ Good (parameterized)
Customer::where('email', $email)->get();

// ❌ Bad (vulnerable)
DB::select("SELECT * FROM customers WHERE email = '$email'");
```

### 3. XSS Prevention
```blade
{{-- ✅ Auto-escaped --}}
{{ $customer->name }}

{{-- ⚠️ Unescaped (use with caution) --}}
{!! $htmlContent !!}
```

### 4. CSRF Protection
```blade
<form method="POST">
    @csrf  {{-- Required for POST/PUT/DELETE --}}
    ...
</form>
```

### 5. Authorization
```php
public function authorize(): bool
{
    return $this->user()->can('update', $this->customer);
}
```

---

## Performance Optimization

### 1. Eager Loading (Avoid N+1)
```php
// ❌ Bad (N+1 queries)
$books = Book::all();
foreach ($books as $book) {
    echo $book->author->name;  // Query for each book
}

// ✅ Good (2 queries)
$books = Book::with('author')->get();
foreach ($books as $book) {
    echo $book->author->name;
}
```

### 2. Select Only Needed Columns
```php
// ❌ Bad
$customers = Customer::all();

// ✅ Good
$customers = Customer::select('id', 'name', 'email')->get();
```

### 3. Use Query Scopes
```php
// ❌ Bad (repeated logic)
Customer::where('is_active', true)->where('type', 'company')->get();

// ✅ Good (reusable scope)
Customer::active()->ofType('company')->get();
```

### 4. Caching
```php
// Cache expensive queries
$stats = Cache::remember('customer-stats', 3600, function () {
    return [
        'total' => Customer::count(),
        'active' => Customer::active()->count(),
    ];
});
```

---

## Code Organization Best Practices

### 1. Single Responsibility
Each class/method should have one job:
```php
// ✅ Good
public function activate() { }
public function deactivate() { }

// ❌ Bad
public function toggleStatusAndSendEmail() { }
```

### 2. DRY (Don't Repeat Yourself)
Use scopes, traits, and inheritance:
```php
// Define once
public function scopeActive($query) { }

// Use everywhere
Customer::active()->get();
Author::active()->get();
```

### 3. Meaningful Names
```php
// ✅ Good
$activeCustomers = Customer::active()->get();

// ❌ Bad
$data = Customer::where('is_active', 1)->get();
```

### 4. Consistent Formatting
- Use PSR-12 coding standard
- 4 spaces indentation
- Opening braces on same line
- Use type hints and return types

---

## Module Dependencies

### External Dependencies
```json
// composer.json
{
    "require": {
        "php": "^8.1",
        "laravel/framework": "^10.0",
        "nwidart/laravel-modules": "^10.0"
    }
}
```

### Inter-Module Dependencies
```
Product Module
   ↓ (products table)
Warehouse Module
   ↓ (references products)

Customer Module
   ↓ (standalone, future integration)

Future: Order Module
   ↓ (will integrate all three)
```

---

## Configuration

### Module Configuration
**Location:** `config/config.php`

```php
return [
    'name' => 'Customer',
    'enabled' => true,
    'features' => [
        'search' => true,
        'export' => true,
    ],
];
```

### Module Metadata
**Location:** `module.json`

```json
{
    "name": "Customer",
    "alias": "customer",
    "description": "Customer management module",
    "version": "1.0.0",
    "active": true,
    "providers": [
        "Modules\\Customer\\Providers\\CustomerServiceProvider"
    ]
}
```

---

## Development Workflow

### 1. Creating a New Feature
```bash
# 1. Create migration
php artisan make:migration create_table_name --path=Modules/ModuleName/database/migrations

# 2. Create model
# Manually create in Modules/ModuleName/app/Models/

# 3. Create controller
# Manually create in Modules/ModuleName/app/Http/Controllers/

# 4. Create form requests
# Manually create in Modules/ModuleName/app/Http/Requests/

# 5. Create views
# Manually create in Modules/ModuleName/resources/views/

# 6. Add routes
# Edit Modules/ModuleName/routes/web.php

# 7. Add translations
# Edit Modules/ModuleName/resources/lang/*/resource.php

# 8. Run migration
php artisan migrate

# 9. Test functionality
php artisan test
```

### 2. Database Operations
```bash
# Run migrations
php artisan migrate

# Rollback last batch
php artisan migrate:rollback

# Reset database
php artisan migrate:fresh

# Run specific migration
php artisan migrate --path=Modules/ModuleName/database/migrations

# Create seeder
php artisan make:seeder ModuleNameSeeder
```

---

## Debugging Tips

### 1. Query Debugging
```php
// Log all queries
DB::enableQueryLog();
$customers = Customer::active()->get();
dd(DB::getQueryLog());

// Dump query without executing
Customer::active()->toSql();
```

### 2. Model Debugging
```php
// Dump model data
dd($customer);

// Dump specific attribute
dd($customer->total_paid);

// Check relationships loaded
dump($book->relationLoaded('author'));
```

### 3. Request Debugging
```php
// In controller
dd($request->all());
dd($request->validated());
dd($request->input('field'));
```

---

## Version History

- **v1.0** (2025-12-21): Initial coding structure documentation
  - Module architecture
  - Layer descriptions
  - Design patterns
  - Naming conventions
  - Best practices
  - Security guidelines
  - Performance optimization
  - Development workflow
