<?php

use Illuminate\Support\Facades\Route;

Route::prefix('menu')
    ->name('menu::')
    ->group(function () {
        Route::get('/', 'Layout\LayoutController@menu')
            ->name('index');
    });
