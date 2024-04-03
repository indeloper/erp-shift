<?php

use Illuminate\Support\Facades\Route;

Route::prefix('menu')
    ->name('menu::')
    ->group(function () {

        Route::get('/', 'Layout\MenuController@index')
            ->name('index');

        Route::get('/favorites', 'Layout\MenuFavoriteController@favorites')
            ->name('favorites');

        Route::prefix('{menu_item}')
            ->group(function () {
                Route::prefix('favorite')
                    ->name('favorite::')
                    ->group(function () {
                        Route::post('/', 'Layout\MenuFavoriteController@toggle')
                            ->name('toggle');
                    });
            });



    });
