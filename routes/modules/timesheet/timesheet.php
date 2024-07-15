<?php

use App\Http\Controllers\Timesheet;
use Illuminate\Support\Facades\Route;

Route::prefix('timesheet')->name('timesheet::')->group(function () {

    Route::prefix('timesheet-card')->name('timesheet-card::')->group(function () {
        Route::get('/', [Timesheet\TimesheetCardsController::class, 'getPageCore'])->name('getPageCore');
        Route::apiResource('resource', Timesheet\TimesheetCardsController::class);
    });

    Route::prefix('posts')->name('posts::')->group(function () {
        Route::get('/', [Timesheet\PostTariffsController::class, 'getPageCore'])->name('getPageCore');
        Route::get('/by-key', [Timesheet\PostTariffsController::class, 'byKey'])->name('by-key');
        Route::apiResource('resource', Timesheet\PostTariffsController::class);
    });
});
