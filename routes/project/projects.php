<?php

use App\Http\Controllers\Commerce\Project\ProjectController;
use App\Http\Controllers\Commerce\Project\ProjectObjectContactsController;
use App\Http\Controllers\Commerce\Project\ProjectObjectContractosController;
use App\Http\Controllers\Commerce\Project\ProjectObjectController;
use Illuminate\Support\Facades\Route;

Route::get('/load', [ProjectController::class, 'index'])
    ->name('load');
Route::post('/store', [ProjectController::class, 'store'])
    ->name('store');

Route::get('/{project_id}', [ProjectController::class, 'show'])
    ->name('show');
Route::put('/{project_id}', [ProjectController::class, 'update'])
    ->name('update');

Route::prefix('objects')->group(function () {
    Route::get('/load', [ProjectObjectController::class, 'index'])
        ->name('object::index');
    Route::prefix('/{projectObject}')->group(function () {
        Route::get('/history-changes',
            [ProjectObjectController::class, 'historyChanges'])
            ->name('object::history_changes');

        Route::prefix('contractos')->group(function () {
            Route::get('/',
                [ProjectObjectContractosController::class, 'index'])
                ->name('object::contractos::index');

            Route::get('/{id}',
                [ProjectObjectContractosController::class, 'show'])
                ->name('object::contractos::show');

            Route::post('/',
                [ProjectObjectContractosController::class, 'store'])
                ->name('object::contractos::store');
        });

        Route::prefix('contacts')->group(function () {
            Route::get('/',
                [ProjectObjectContactsController::class, 'index'])
                ->name('object::contacts::index');

            Route::get('/{id}',
                [ProjectObjectContactsController::class, 'show'])
                ->name('object::contacts::show');

            Route::post('/',
                [ProjectObjectContactsController::class, 'store'])
                ->name('object::contacts::store');
        });
    });
});

