<?php

Route::prefix('timesheet')->name('timesheet::')->namespace('Timesheet')->group(function () {

    Route::prefix('timesheet-card')->name('timesheet-card::')->group(function () {
        Route::get('/', 'TimesheetCardsController@getPageCore')->name('getPageCore');
        Route::apiResource('resource', 'TimesheetCardsController');
    });

    Route::prefix('posts')->name('posts::')->group(function () {
        Route::get('/', 'PostTariffsController@getPageCore')->name('getPageCore');
        Route::get('/by-key', 'PostTariffsController@byKey')->name('by-key');
        Route::apiResource('resource', 'PostTariffsController');
    });
});
