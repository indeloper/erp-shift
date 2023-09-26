<?php
/*Route::group(['middleware' => 'can:contractors'], function () {
    Route::get('/', 'ContractorController@index')->name('index');
    Route::get('/card/{id}', 'ContractorController@card')->name('card');
});*/

Route::get('/labor-safety/statuses-list', 'LaborSafety\LaborSafetyRequestController@statusesList')->name('labor-safety.statuses.list');

Route::get('/labor-safety/templates', 'LaborSafety\LaborSafetyOrderTypeController@index')->name('labor-safety.order-types.index');
Route::get('/labor-safety/templates/list', 'LaborSafety\LaborSafetyOrderTypeController@list')->name('labor-safety.order-types.list');
Route::get('/labor-safety/templates/short-names-list', 'LaborSafety\LaborSafetyOrderTypeController@shortNameList')->name('labor-safety.order-types.short-name-list');
Route::put('/labor-safety/templates', 'LaborSafety\LaborSafetyOrderTypeController@update')->name('labor-safety.order-types.update');

Route::get('/labor-safety/orders-and-requests', 'LaborSafety\LaborSafetyRequestController@index')->name('labor-safety.orders-and-requests.index');
Route::get('/labor-safety/orders-and-requests/list', 'LaborSafety\LaborSafetyRequestController@list')->name('labor-safety.orders-and-requests.list');
Route::post('/labor-safety/orders-and-requests', 'LaborSafety\LaborSafetyRequestController@store')->name('labor-safety.orders-and-requests.store');
Route::put('/labor-safety/orders-and-requests', 'LaborSafety\LaborSafetyRequestController@update')->name('labor-safety.orders-and-requests.update');
Route::delete('/labor-safety/orders-and-requests', 'LaborSafety\LaborSafetyRequestController@delete')->name('labor-safety.orders-and-requests.delete');

Route::post('/labor-safety/orders-and-requests/download', 'LaborSafety\LaborSafetyRequestController@download')->name('labor-safety.orders-and-requests.download');

Route::get('/labor-safety/request-workers/list', 'LaborSafety\LaborSafetyRequestController@getRequestWorkers')->name('labor-safety.request-workers.list');
Route::get('/labor-safety/request-workers/worker-types', 'LaborSafety\LaborSafetyRequestController@getRequestWorkersTypes')->name('labor-safety.request-workers.worker-types');

