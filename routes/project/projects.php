<?php

use App\Http\Controllers\Commerce\Project\ProjectController;
use App\Http\Controllers\Commerce\Project\ProjectObjectContractosController;
use App\Http\Controllers\Commerce\Project\ProjectObjectController;
use Illuminate\Support\Facades\Route;

Route::get('/load', [ProjectController::class, 'index'])
    ->name('load');
Route::get('/{project_id}', [ProjectController::class, 'show'])
    ->name('show');

Route::put('/{project_id}', [ProjectController::class, 'update'])
    ->name('update');

Route::post('/store', [ProjectController::class, 'store'])
    ->name('store');

Route::get('/objects/load', [ProjectObjectController::class, 'index'])
    ->name('object::index');

Route::get('/objects/{projectObject}/history-changes',
    [ProjectObjectController::class, 'historyChanges'])
    ->name('object::history_changes');

Route::get('/objects/{projectObject}/contractos',
    [ProjectObjectContractosController::class, 'index'])
    ->name('object::contractos::index');