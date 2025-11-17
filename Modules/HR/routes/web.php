<?php

use Illuminate\Support\Facades\Route;
use Modules\HR\Http\Controllers\AdvanceController;
use Modules\HR\Http\Controllers\DeductionController;
use Modules\HR\Http\Controllers\DepartmentController;
use Modules\HR\Http\Controllers\EmployeeController;
use Modules\HR\Http\Controllers\LeaveController;
use Modules\HR\Http\Controllers\LeaveTypeController;

Route::prefix('hr')->name('hr.')->group(function () {

    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('index');
        Route::get('/create', [DepartmentController::class, 'create'])->name('create');
        Route::post('/', [DepartmentController::class, 'store'])->name('store');
        Route::get('/{department}', [DepartmentController::class, 'show'])->name('show');
        Route::get('/{department}/edit', [DepartmentController::class, 'edit'])->name('edit');
        Route::put('/{department}', [DepartmentController::class, 'update'])->name('update');
        Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('destroy');
    });
    // Employee Routes
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('', [EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('create');
        Route::post('', [EmployeeController::class, 'store'])->name('store');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('leave-types')->name('leave-types.')->group(function () {
        Route::get('/', [LeaveTypeController::class, 'index'])->name('index');
        Route::get('/create', [LeaveTypeController::class, 'create'])->name('create');
        Route::post('/', [LeaveTypeController::class, 'store'])->name('store');
        Route::get('/{leaveType}', [LeaveTypeController::class, 'show'])->name('show');
        Route::get('/{leaveType}/edit', [LeaveTypeController::class, 'edit'])->name('edit');
        Route::put('/{leaveType}', [LeaveTypeController::class, 'update'])->name('update');
        Route::delete('/{leaveType}', [LeaveTypeController::class, 'destroy'])->name('destroy');
    });

    // Leave Request Management
    Route::prefix('leaves')->name('leaves.')->group(function () {
        Route::get('/', [LeaveController::class, 'index'])->name('index');
        Route::get('/create', [LeaveController::class, 'create'])->name('create');
        Route::post('/', [LeaveController::class, 'store'])->name('store');
        Route::get('/{leave}', [LeaveController::class, 'show'])->name('show');
        Route::get('/{leave}/edit', [LeaveController::class, 'edit'])->name('edit');
        Route::put('/{leave}', [LeaveController::class, 'update'])->name('update');
        Route::delete('/{leave}', [LeaveController::class, 'destroy'])->name('destroy');

        // Approval actions
        Route::post('/{leave}/approve', [LeaveController::class, 'approve'])->name('approve');
        Route::post('/{leave}/reject', [LeaveController::class, 'reject'])->name('reject');
    });

    // Deduction Management
    Route::prefix('deductions')->name('deductions.')->group(function () {
        Route::get('/', [DeductionController::class, 'index'])->name('index');
        Route::get('/create', [DeductionController::class, 'create'])->name('create');
        Route::post('/', [DeductionController::class, 'store'])->name('store');
        Route::get('/{deduction}', [DeductionController::class, 'show'])->name('show');
        Route::get('/{deduction}/edit', [DeductionController::class, 'edit'])->name('edit');
        Route::put('/{deduction}', [DeductionController::class, 'update'])->name('update');
        Route::delete('/{deduction}', [DeductionController::class, 'destroy'])->name('destroy');
    });


    Route::prefix('advances')->name('advances.')->group(function () {
        Route::get('/', [AdvanceController::class, 'index'])->name('index');
        Route::get('/create', [AdvanceController::class, 'create'])->name('create');
        Route::post('/', [AdvanceController::class, 'store'])->name('store');
        Route::get('/{id}', [AdvanceController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AdvanceController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdvanceController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdvanceController::class, 'destroy'])->name('destroy');

        // New actions
        Route::post('/{id}/convert-to-deduction', [AdvanceController::class, 'convertToDeduction'])->name('convert-to-deduction');
        Route::post('/{id}/add-to-salary', [AdvanceController::class, 'addToSalary'])->name('add-to-salary');

        // Settlements
        Route::prefix('settlements')->name('settlements.')->group(function () {
            Route::get('/create', [AdvanceController::class, 'createSettlement'])->name('create');
            Route::post('/', [AdvanceController::class, 'storeSettlement'])->name('store');
            Route::get('/{id}/edit', [AdvanceController::class, 'editSettlement'])->name('edit');
            Route::put('/{id}', [AdvanceController::class, 'updateSettlement'])->name('update');
            Route::delete('/{id}', [AdvanceController::class, 'destroySettlement'])->name('destroy');
        });
    });
});
