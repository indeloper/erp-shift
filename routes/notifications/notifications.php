<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'notifications', 'as' => 'notifications::', 'namespace' => "Common"], function () {
    Route::get('/', 'NotificationController@index')->name('index');
    Route::get('/load', 'NotificationController@loadNotifications')->name('load-notifications');
    Route::post('/view', 'NotificationController@view')->name('view');
    Route::post('/view/all', 'NotificationController@viewAll')->name('view_all');
    Route::post('/delete', 'NotificationController@delete')->name('delete');
    Route::get('/redirect/{encoded_url}', 'NotificationController@redirect')->name('redirect');
});