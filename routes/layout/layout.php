<?php

use App\Http\Controllers\Layout;
use Illuminate\Support\Facades\Route;

Route::prefix('menu')
    ->name('menu::')
    ->group(function () {

        Route::get('/', [Layout\MenuController::class, 'index'])
            ->name('index');

        Route::get('/favorites', [Layout\MenuFavoriteController::class, 'favorites'])
            ->name('favorites');

        Route::prefix('{menu_item}')
            ->group(function () {
                Route::prefix('favorite')
                    ->name('favorite::')
                    ->group(function () {
                        Route::post('/', [Layout\MenuFavoriteController::class, 'toggle'])
                            ->name('toggle');
                    });
            });

    });
