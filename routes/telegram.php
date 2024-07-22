<?php

use App\Http\Controllers\Telegram\WebApps\ProfileController;
use App\Http\Middleware\WebAppDataValidationMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('web-apps')
    ->middleware([
        WebAppDataValidationMiddleware::class,
    ])
    ->name('web-apps::')->group(function () {
        Route::get('/profile', [ProfileController::class, 'show'])
            ->name('show');

        Route::post('profile', [ProfileController::class, 'update'])
            ->name('update');
    });

Route::prefix('web-apps')
    ->name('web-apps::')
    ->group(function () {
        Route::view('main', 'telegram.web-apps.main');
    });
