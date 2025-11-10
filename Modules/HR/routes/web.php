<?php

use Illuminate\Support\Facades\Route;
use Modules\HR\Http\Controllers\DepartmentController;

Route::prefix('hr')->name('hr.')->group(function () {
    
    // Department Routes
    Route::resource('departments', DepartmentController::class);
    
    // // Employee Routes
    // Route::resource('employees', EmployeeController::class);
    
    // // Leave Routes
    // Route::resource('leaves', LeaveController::class);
    // Route::post('leaves/{leave}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
    // Route::post('leaves/{leave}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');
    
    // // Deduction Routes
    // Route::resource('deductions', DeductionController::class);
});
