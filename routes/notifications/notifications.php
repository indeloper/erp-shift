<?php

use Illuminate\Support\Facades\Route;

Route::prefix('notifications')->name('notifications::')->namespace('Common')->group(function () {
    Route::get('/', 'NotificationController@index')->name('index');
    Route::get('/items', 'NotificationController@items')->name('items');
    Route::post('/settings/items', 'NotificationController@settings')->name('settings::items');
    Route::get('/load', 'NotificationController@loadNotifications')->name('load-notifications');
    Route::post('/view', 'NotificationController@view')->name('view');
    Route::post('/view/all', 'NotificationController@viewAll')->name('view_all');
    Route::post('/delete', 'NotificationController@delete')->name('delete');
    Route::get('/redirect/{encoded_url}', 'NotificationController@redirect')->name('redirect');
});
