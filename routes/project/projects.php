<?php

use App\Http\Controllers\Commerce\Project\ProjectController;
use App\Http\Controllers\Commerce\Project\ProjectObjectContractosController;
use App\Http\Controllers\Commerce\Project\ProjectObjectController;
use Illuminate\Support\Facades\Route;

Route::get('/load', [ProjectController::class, 'index'])
    ->name('project.index');
Route::get('/{project_id}', [ProjectController::class, 'show'])
    ->name('project.index');

Route::put('/{project_id}', [ProjectController::class, 'update'])
    ->name('project.index');

Route::post('/store', [ProjectController::class, 'store'])
    ->name('project.store');

Route::get('/objects/load', [ProjectObjectController::class, 'index'])
    ->name('project.object.index');

Route::get('/objects/{projectObject}/history-changes',
    [ProjectObjectController::class, 'historyChanges'])
    ->name('project.object.history_changes');

Route::get('/objects/{projectObject}/contractos',
    [ProjectObjectContractosController::class, 'index'])
    ->name('project.object.contractos.index');