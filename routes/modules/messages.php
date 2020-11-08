<?php

Route::get('/', 'MessagesController@index')->name('index');
Route::get('/get-users/', 'MessagesController@get_users')->name('get_users');

Route::post('/thread/store', 'MessagesController@thread_store')->name('thread_store');
Route::post('/thread/update/{id}', 'MessagesController@thread_update')->name('thread_update');
Route::post('/store/{id}', 'MessagesController@message_store')->name('message_store');
Route::post('/thread/read/{id}', 'MessagesController@read')->name('read_thread');

Route::post('/send_messages', 'MessagesController@send_messages')->name('send_messages');
Route::post('/thread/load', 'MessagesController@load_thread')->name('load_thread');
Route::post('/thread/message_count', 'MessagesController@thread_message_count')->name('thread_message_count'); //?
Route::post('/thread/leave', 'MessagesController@leave_thread')->name('leave_thread');
Route::post('/thread/{id}/creator_leave', 'MessagesController@creator_leave')->name('creator_leave');
Route::post('/thread/join', 'MessagesController@join_thread')->name('join_thread');
Route::post('/message/files', 'MessagesController@message_files')->name('message_files');
Route::post('/message/files/delete', 'MessagesController@message_files_delete')->name('message_files_delete');
Route::post('/message/update', 'MessagesController@update_message')->name('update_message');
Route::post('/message/delete', 'MessagesController@delete_message')->name('delete_message');
Route::post('/message/related_messages', 'MessagesController@related_messages')->name('show_related_messages');
Route::post('/message/render', 'MessagesController@message_render')->name('message_render');
Route::post('/message/info', 'MessagesController@message_info')->name('message_info');
