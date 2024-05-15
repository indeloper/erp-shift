<?php

use App\Http\Controllers\LaborSafety;
use Illuminate\Support\Facades\Route;
/*Route::group(['middleware' => 'can:contractors'], function () {
    Route::get('/', 'ContractorController@index')->name('index');
    Route::get('/card/{id}', 'ContractorController@card')->name('card');
});*/

Route::get('/labor-safety/statuses-list', [LaborSafety\LaborSafetyRequestController::class, 'statusesList'])->name('labor-safety.statuses.list');

Route::get('/labor-safety/templates', [LaborSafety\LaborSafetyOrderTypeController::class, 'index'])->name('labor-safety.order-types.index');
Route::get('/labor-safety/templates/list', [LaborSafety\LaborSafetyOrderTypeController::class, 'list'])->name('labor-safety.order-types.list');
Route::get('/labor-safety/templates/short-names-list', [LaborSafety\LaborSafetyOrderTypeController::class, 'shortNameList'])->name('labor-safety.order-types.short-name-list');
Route::put('/labor-safety/templates', [LaborSafety\LaborSafetyOrderTypeController::class, 'update'])->name('labor-safety.order-types.update');

Route::get('/labor-safety/orders-and-requests', [LaborSafety\LaborSafetyRequestController::class, 'index'])->name('labor-safety.orders-and-requests.index');
Route::get('/labor-safety/orders-and-requests/list', [LaborSafety\LaborSafetyRequestController::class, 'list'])->name('labor-safety.orders-and-requests.list');
Route::post('/labor-safety/orders-and-requests', [LaborSafety\LaborSafetyRequestController::class, 'store'])->name('labor-safety.orders-and-requests.store');
Route::put('/labor-safety/orders-and-requests', [LaborSafety\LaborSafetyRequestController::class, 'update'])->name('labor-safety.orders-and-requests.update');
Route::delete('/labor-safety/orders-and-requests', [LaborSafety\LaborSafetyRequestController::class, 'delete'])->name('labor-safety.orders-and-requests.delete');

Route::post('/labor-safety/orders-and-requests/download', [LaborSafety\LaborSafetyRequestController::class, 'download'])->name('labor-safety.orders-and-requests.download');

Route::get('/labor-safety/request-workers/list', [LaborSafety\LaborSafetyRequestController::class, 'getRequestWorkers'])->name('labor-safety.request-workers.list');
Route::get('/labor-safety/request-workers/worker-types', [LaborSafety\LaborSafetyRequestController::class, 'getRequestWorkersTypes'])->name('labor-safety.request-workers.worker-types');
