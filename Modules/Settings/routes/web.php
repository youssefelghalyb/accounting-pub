<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\LanguageController;
use Modules\Settings\Http\Controllers\OrganizationSettingsController;

Route::get('/settings/organization', [OrganizationSettingsController::class, 'index'])
->name('settings.organization');

Route::post('/settings/organization', [OrganizationSettingsController::class, 'update'])
->name('settings.organization.update');


Route::get('language/{locale}', [LanguageController::class, 'switch'])
    ->name('language.switch');
