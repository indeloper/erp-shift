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
});
