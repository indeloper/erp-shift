<?php

use App\Http\Controllers\Commerce\Project\ProjectController;
use App\Http\Controllers\Commerce\Project\ProjectObjectCommercialController;
use App\Http\Controllers\Commerce\Project\ProjectObjectContactsController;
use App\Http\Controllers\Commerce\Project\ProjectObjectContractosController;
use App\Http\Controllers\Commerce\Project\ProjectObjectController;
use App\Http\Controllers\Commerce\Project\ProjectObjectEventsController;
use App\Http\Controllers\Commerce\Project\ProjectObjectResponsiblesController;
use App\Http\Controllers\Commerce\Project\ProjectObjectWorkVolumesController;
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

        Route::prefix('responsibles')->group(function () {
            Route::get('/',
                [ProjectObjectResponsiblesController::class, 'index'])
                ->name('object::responsibles::index');

            Route::get('/{id}',
                [ProjectObjectResponsiblesController::class, 'show'])
                ->name('object::responsibles::show');

            Route::post('/',
                [ProjectObjectResponsiblesController::class, 'store'])
                ->name('object::responsibles::store');
        });

        Route::prefix('events')->group(function () {
            Route::get('/',
                [ProjectObjectEventsController::class, 'index'])
                ->name('object::events::index');

            Route::get('/{id}',
                [ProjectObjectEventsController::class, 'show'])
                ->name('object::events::show');

            Route::post('/',
                [ProjectObjectEventsController::class, 'store'])
                ->name('object::events::store');
        });

        Route::prefix('work-volumes')->group(function () {
            Route::get('/',
                [ProjectObjectWorkVolumesController::class, 'index'])
                ->name('object::work_volumes::index');

            Route::get('/{id}',
                [ProjectObjectWorkVolumesController::class, 'show'])
                ->name('object::work_volumes::show');

            Route::post('/',
                [ProjectObjectWorkVolumesController::class, 'store'])
                ->name('object::work_volumes::store');
        });

        Route::prefix('commercial')->group(function () {
            Route::get('/',
                [ProjectObjectCommercialController::class, 'index'])
                ->name('object::commercial::index');

            Route::get('/{id}',
                [ProjectObjectCommercialController::class, 'show'])
                ->name('object::commercial::show');

            Route::post('/',
                [ProjectObjectCommercialController::class, 'store'])
                ->name('object::commercial::store');
        });
    });
});

