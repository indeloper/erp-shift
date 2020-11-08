<?php
Route::group(['middleware' => 'can:mat_acc_operation_log'], function () {
    Route::get('/complete/{id}', 'MatAccWriteOffController@complete')->name('complete');
    Route::get('/confirm/{id}', 'MatAccWriteOffController@confirm')->name('confirm');
    Route::get('/create', 'MatAccWriteOffController@create')->name('create');
    Route::get('/work/{id}', 'MatAccWriteOffController@work')->name('work');
    Route::get('/conflict/{id}', 'MatAccWriteOffController@conflict')->name('conflict');
    Route::get('/edit/{id}', 'MatAccWriteOffController@edit')->name('edit');
    Route::get('/draft/{id}', 'MatAccWriteOffController@draft')->name('draft');
    Route::get('/control/{id}', 'MatAccWriteOffController@control')->name('control');

    Route::post('/store', 'MatAccWriteOffController@store')->name('store');
    Route::post('/update/{id}', 'MatAccWriteOffController@update')->name('update');
    Route::post('/send/{id}', 'MatAccWriteOffController@send')->name('send');
    Route::post('/part_send/{id}', 'MatAccWriteOffController@part_send')->name('part_send');
    Route::post('/accept/{id}', 'MatAccWriteOffController@accept')->name('accept');
    Route::post('/control/send/{id}', 'MatAccWriteOffController@solve_control')->name('solve_control');
});
