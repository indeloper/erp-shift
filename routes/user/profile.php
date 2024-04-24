<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'Profile\ProfileController@show')
    ->name('show');
