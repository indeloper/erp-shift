<?php

Route::middleware('can:mat_acc_operation_log')->group(function () {
    Route::get('/complete/{id}', 'MatAccTransformationController@complete')->name('complete');
    Route::get('/confirm/{id}', 'MatAccTransformationController@confirm')->name('confirm');
    Route::get('/create', 'MatAccTransformationController@create')->name('create');
    Route::get('/work/{id}', 'MatAccTransformationController@work')->name('work');
    Route::get('/conflict/{id}', 'MatAccTransformationController@conflict')->name('conflict');
    Route::get('/edit/{id}', 'MatAccTransformationController@edit')->name('edit');
    Route::get('/draft/{id}', 'MatAccTransformationController@draft')->name('draft');

    Route::post('/store', 'MatAccTransformationController@store')->name('store');
    Route::post('/update/{id}', 'MatAccTransformationController@update')->name('update');
    Route::post('/send/{id}', 'MatAccTransformationController@send')->name('send');
    Route::post('/part_send/{id}', 'MatAccTransformationController@part_send')->name('part_send');
    Route::post('/accept/{id}', 'MatAccTransformationController@accept')->name('accept');
});
