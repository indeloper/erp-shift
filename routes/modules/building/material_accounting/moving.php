<?php
Route::group(['middleware' => 'can:mat_acc_operation_log'], function () {
    Route::get('/complete/{id}', 'MatAccMovingController@complete')->name('complete');
    Route::get('/confirm/{id}', 'MatAccMovingController@confirm')->name('confirm');
    Route::get('/create', 'MatAccMovingController@create')->name('create');
    Route::get('/work/{id}', 'MatAccMovingController@work')->name('work');
    Route::get('/conflict/{id}', 'MatAccMovingController@conflict')->name('conflict');
    Route::get('/edit/{id}', 'MatAccMovingController@edit')->name('edit');
    Route::get('/draft/{id}', 'MatAccMovingController@draft')->name('draft');

    Route::any('/store', 'MatAccMovingController@store')->name('store');
    Route::post('/make_ttn', 'MatAccMovingController@make_ttn')->name('make_ttn');
    Route::post('/update/{id}', 'MatAccMovingController@update')->name('update');
    Route::post('/send/{id}', 'MatAccMovingController@send')->name('send');
    Route::post('/part_send/{id}', 'MatAccMovingController@part_send')->name('part_send');
    Route::post('/accept/{id}', 'MatAccMovingController@accept')->name('accept');
});
