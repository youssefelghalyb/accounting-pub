<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Exports\BooksExport;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Controllers\CategoryController;
use Modules\Product\Http\Controllers\ContractorController;
use Modules\Product\Http\Controllers\BookController;

Route::middleware(['web'])->prefix('product')->name('product.')->group(function () {

    // Product Routes
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{id}', [ProductController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy');
    });

    // Category Routes
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{id}', [CategoryController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    // Contractor Routes
    Route::prefix('contractors')->name('contractors.')->group(function () {
        Route::get('/', [ContractorController::class, 'index'])->name('index');
        Route::get('/create', [ContractorController::class, 'create'])->name('create');
        Route::post('/', [ContractorController::class, 'store'])->name('store');
        Route::get('/{contractor}', [ContractorController::class, 'show'])->name('show');
        Route::get('/{contractor}/edit', [ContractorController::class, 'edit'])->name('edit');
        Route::put('/{contractor}', [ContractorController::class, 'update'])->name('update');
        Route::delete('/{contractor}', [ContractorController::class, 'destroy'])->name('destroy');
        Route::post('/{contractor}/register-as-client', [ContractorController::class, 'registerAsClient'])
            ->name('register-as-client');
        Route::post('/{contractor}/gift', [ContractorController::class, 'storeGift'])->name('gift');
        Route::post('/{contractor}/transactions', [ContractorController::class, 'storeTransaction'])
            ->name('transactions.store');
        Route::delete('/transactions/{transaction}', [ContractorController::class, 'destroyTransaction'])
            ->name('transactions.destroy');
    });

    // Book Routes
    Route::prefix('books')->name('books.')->group(function () {
        Route::get('/', [BookController::class, 'index'])->name('index');
        Route::get('/create', [BookController::class, 'create'])->name('create');
        Route::post('/', [BookController::class, 'store'])->name('store');
        Route::get('/{id}', [BookController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [BookController::class, 'edit'])->name('edit');
        Route::put('/{id}', [BookController::class, 'update'])->name('update');
        Route::delete('/{id}', [BookController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-price-update', [BookController::class, 'bulkPriceUpdate'])
            ->name('bulk-price-update');
        Route::get('/export/books', [BookController::class, 'exportBooks'])->name('export');

        Route::post(
            'books/import',
            [BookController::class, 'import']
        )->name('import');
    });
});
