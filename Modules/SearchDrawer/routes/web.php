<?php

use Illuminate\Support\Facades\Route;

Route::get('/search-drawer', function () {
    $categories = \Modules\Product\Models\BookCategory::all();
    $subCategories = \Modules\Product\Models\BookCategory::all();
    $authors = \Modules\Product\Models\Author::all();
    return view('searchdrawer::index', compact('categories', 'subCategories', 'authors'));
})->name('search-drawer.index');