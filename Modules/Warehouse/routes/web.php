<?php

use Illuminate\Support\Facades\Route;
use Modules\Warehouse\Http\Controllers\StockController;
use Modules\Warehouse\Http\Controllers\StockMovementController;

Route::middleware(['web'])->prefix('warehouse')->name('warehouse.')->group(function () {

    // Stock Routes
    Route::prefix('stocks')->name('stocks.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/create', [StockController::class, 'create'])->name('create');
        Route::post('/', [StockController::class, 'store'])->name('store');
        Route::get('/{id}', [StockController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [StockController::class, 'edit'])->name('edit');
        Route::put('/{id}', [StockController::class, 'update'])->name('update');
        Route::delete('/{id}', [StockController::class, 'destroy'])->name('destroy');
    });

    // Stock Movement Routes
    Route::prefix('movements')->name('movements.')->group(function () {
        Route::get('/', [StockMovementController::class, 'index'])->name('index');
        Route::get('/create', [StockMovementController::class, 'create'])->name('create');
        Route::post('/', [StockMovementController::class, 'store'])->name('store');
        Route::get('/{id}', [StockMovementController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [StockMovementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [StockMovementController::class, 'update'])->name('update');
        Route::delete('/{id}', [StockMovementController::class, 'destroy'])->name('destroy');
    });
});
