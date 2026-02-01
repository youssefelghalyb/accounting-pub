<?php

use Illuminate\Support\Facades\Route;
use Modules\SearchDrawer\Http\Controllers\Api\ProductSearchController;

Route::
    prefix('v1/drawer')
    ->group(function () {

        Route::get('/products/search', [ProductSearchController::class, 'index'])
            ->name('drawer.products.search');

        Route::get('/products/{id}', [ProductSearchController::class, 'show'])
            ->name('drawer.products.show');
});