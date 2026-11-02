<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Exports\BooksExport;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Controllers\CategoryController;
use Modules\Product\Http\Controllers\ContractorController;
use Modules\Product\Http\Controllers\BookController;
use Modules\Product\Http\Controllers\BookGridController;
use Modules\Product\Http\Controllers\BookGridHistoryController;

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
        Route::get('/export/books', [BookController::class, 'exportBooks'])->name('export');

        Route::post(
            'books/import',
            [BookController::class, 'import']
        )->name('import');
    });
});

// Books grid editor — spreadsheet-style source of truth for books (auth required)
Route::middleware(['web', 'auth'])->prefix('product/book-grid')->name('product.book-grid.')->group(function () {
    Route::get('/', [BookGridController::class, 'index'])->name('index');
    Route::get('/data', [BookGridController::class, 'data'])->name('data');
    Route::post('/preview', [BookGridController::class, 'preview'])->name('preview');
    Route::post('/apply', [BookGridController::class, 'apply'])->name('apply');

    // History of saved batches + restore (revert a batch)
    Route::get('/history', [BookGridHistoryController::class, 'index'])->name('history');
    Route::post('/history/restore-apply', [BookGridHistoryController::class, 'restoreApply'])->name('history.restore-apply');
    Route::get('/history/{batch}', [BookGridHistoryController::class, 'show'])->name('history.show');
    Route::post('/history/{batch}/restore-preview', [BookGridHistoryController::class, 'restorePreview'])->name('history.restore-preview');
});
