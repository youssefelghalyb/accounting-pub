<?php

use Illuminate\Support\Facades\Route;
use Modules\Warehouse\Http\Controllers\StockMovementController;
use Modules\Warehouse\Http\Controllers\SubWarehouseController;
use Modules\Warehouse\Http\Controllers\WarehouseController;

Route::middleware(['web'])
    ->prefix('warehouse')
    ->name('warehouse.')
    ->group(function () {

        // Warehouse routes
        Route::prefix('warehouses')
            ->name('warehouses.')
            ->group(function () {
                Route::get('/', [WarehouseController::class, 'index'])->name('index');
                Route::get('/create', [WarehouseController::class, 'create'])->name('create');
                Route::post('/', [WarehouseController::class, 'store'])->name('store');
                Route::get('/{id}', [WarehouseController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [WarehouseController::class, 'edit'])->name('edit');
                Route::put('/{id}', [WarehouseController::class, 'update'])->name('update');
                Route::delete('/{id}', [WarehouseController::class, 'destroy'])->name('destroy');
            });

        // Sub-Warehouse routes
        Route::prefix('sub-warehouses')
            ->name('sub_warehouses.')
            ->group(function () {
                Route::get('/', [SubWarehouseController::class, 'index'])->name('index');
                Route::get('/create', [SubWarehouseController::class, 'create'])->name('create');
                Route::post('/', [SubWarehouseController::class, 'store'])->name('store');
                Route::get('/{id}', [SubWarehouseController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [SubWarehouseController::class, 'edit'])->name('edit');
                Route::put('/{id}', [SubWarehouseController::class, 'update'])->name('update');
                Route::delete('/{id}', [SubWarehouseController::class, 'destroy'])->name('destroy');

                // Stock management routes
                Route::get('/{id}/add-stock', [SubWarehouseController::class, 'addStock'])->name('add-stock');
                Route::post('/{id}/add-stock', [SubWarehouseController::class, 'storeStock'])->name('store-stock');
                Route::get('/{id}/stock/{warehouseProductId}/edit', [SubWarehouseController::class, 'editStock'])->name('edit-stock');
                Route::put('/{id}/stock/{warehouseProductId}', [SubWarehouseController::class, 'updateStock'])->name('update-stock');
            });

        // Stock Movement routes
        Route::prefix('stock-movements')
            ->name('stock_movements.')
            ->group(function () {
                Route::get('/', [StockMovementController::class, 'index'])->name('index');
                Route::get('/create', [StockMovementController::class, 'create'])->name('create');
                Route::post('/', [StockMovementController::class, 'store'])->name('store');
                Route::get('/{id}', [StockMovementController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [StockMovementController::class, 'edit'])->name('edit');
                Route::put('/{id}', [StockMovementController::class, 'update'])->name('update');
                Route::delete('/{id}', [StockMovementController::class, 'destroy'])->name('destroy');
            });
    });
