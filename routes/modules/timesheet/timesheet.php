<?php
Route::group(['prefix' => 'timesheet', 'as' => 'timesheet::',  'namespace' => "Timesheet"], function () {

    Route::group([
        'prefix' => 'timesheet-card',
        'as' => 'timesheet-card::',
        // 'middleware' => 'can:fuel_tanks_access'
    ], function () {
        Route::get('/', 'TimesheetCardsController@getPageCore')->name('getPageCore');
        Route::apiResource('resource', 'TimesheetCardsController');
    });

    Route::group([
        'prefix' => 'posts',
        'as' => 'posts::',
        // 'middleware' => 'can:fuel_tanks_access'
    ], function () {
        Route::get('/', 'PostTariffsController@getPageCore')->name('getPageCore');
        Route::get('/by-key', 'PostTariffsController@byKey')->name('by-key');
        Route::apiResource('resource', 'PostTariffsController');
    });
});
