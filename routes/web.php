<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchSelectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/search-select/{resource}', [SearchSelectController::class, 'search'])
     ->name('search-select')->middleware('auth');

Route::get('test-form' , function() {
    return view('test-form');
})->name('test-form');

Route::get('test-d' , function() {
    return view('test-dashboard');
})->name('test-form');


Route::get('eljanah' , function() {
    return view('eljanah2');
});

require __DIR__.'/auth.php';
