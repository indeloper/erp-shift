<?php

use App\Http\Controllers\MessagesController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MessagesController::class, 'index'])->name('index');
Route::get('/get-users/', [MessagesController::class, 'get_users'])->name('get_users');

Route::post('/thread/store', [MessagesController::class, 'thread_store'])->name('thread_store');
Route::post('/thread/update/{id}', [MessagesController::class, 'thread_update'])->name('thread_update');
Route::post('/store/{id}', [MessagesController::class, 'message_store'])->name('message_store');
Route::post('/thread/read/{id}', [MessagesController::class, 'read'])->name('read_thread');

Route::post('/send_messages', [MessagesController::class, 'send_messages'])->name('send_messages');
Route::post('/thread/load', [MessagesController::class, 'load_thread'])->name('load_thread');
Route::post('/thread/message_count', [MessagesController::class, 'thread_message_count'])->name('thread_message_count'); //?
Route::post('/thread/leave', [MessagesController::class, 'leave_thread'])->name('leave_thread');
Route::post('/thread/{id}/creator_leave', [MessagesController::class, 'creator_leave'])->name('creator_leave');
Route::post('/thread/join', [MessagesController::class, 'join_thread'])->name('join_thread');
Route::post('/message/files', [MessagesController::class, 'message_files'])->name('message_files');
Route::post('/message/files/delete', [MessagesController::class, 'message_files_delete'])->name('message_files_delete');
Route::post('/message/update', [MessagesController::class, 'update_message'])->name('update_message');
Route::post('/message/delete', [MessagesController::class, 'delete_message'])->name('delete_message');
Route::post('/message/related_messages', [MessagesController::class, 'related_messages'])->name('show_related_messages');
Route::post('/message/render', [MessagesController::class, 'message_render'])->name('message_render');
Route::post('/message/info', [MessagesController::class, 'message_info'])->name('message_info');
