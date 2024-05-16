<?php

use App\Http\Controllers\Common;
use Illuminate\Support\Facades\Route;

Route::prefix('notifications')->name('notifications::')->group(function () {
    Route::get('/', [Common\NotificationController::class, 'index'])->name('index');
    Route::get('/items', [Common\NotificationController::class, 'items'])->name('items');
    Route::post('/settings/items', [Common\NotificationController::class, 'settings'])->name('settings::items');
    Route::get('/load', [Common\NotificationController::class, 'loadNotifications'])->name('load-notifications');
    Route::post('/view', [Common\NotificationController::class, 'view'])->name('view');
    Route::post('/view/all', [Common\NotificationController::class, 'viewAll'])->name('view_all');
    Route::post('/delete', [Common\NotificationController::class, 'delete'])->name('delete');
    Route::get('/redirect/{encoded_url}', [Common\NotificationController::class, 'redirect'])->name('redirect');
});
