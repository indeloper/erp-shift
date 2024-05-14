<?php

Route::group(['middleware' => 'can:mat_acc_operation_log'], function () {
    Route::get('/complete/{id}', 'MatAccArrivalController@complete')->name('complete');
    Route::get('/confirm/{id}', 'MatAccArrivalController@confirm')->name('confirm');
    Route::get('/draft/{id}', 'MatAccArrivalController@draft')->name('draft');

    Route::get('/create', 'MatAccArrivalController@create')->name('create');
    Route::get('/work/{id}', 'MatAccArrivalController@work')->name('work');
    Route::get('/edit/{id}', 'MatAccArrivalController@edit')->name('edit');

    Route::any('/store', 'MatAccArrivalController@store')->name('store');
    Route::post('/update/{id}', 'MatAccArrivalController@update')->name('update');
    Route::post('/send/{id}', 'MatAccArrivalController@send')->name('send');
    Route::post('/part_send/{id}', 'MatAccArrivalController@part_send')->name('part_send');
    Route::post('/accept/{id}', 'MatAccArrivalController@accept')->name('accept');
});
