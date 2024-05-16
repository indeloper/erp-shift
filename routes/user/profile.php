<?php

use App\Http\Controllers\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', [Profile\ProfileController::class, 'show'])
    ->name('show');
