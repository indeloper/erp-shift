<?php
Route::resource('technic_category', 'TechnicCategoryController');
Route::get('technic_category_trashed', 'TechnicCategoryController@display_trashed')->name('technic_category.display_trashed');
Route::get('technic_category_trashed/{technic_category}', 'TechnicCategoryController@show_trashed')->name('technic_category.show_trashed');

Route::resource('technic_category.our_technic', 'OurTechnicController')->except([
    'create', 'show', 'edit'
]);
Route::get('technic_category/{technic_category}/our_technic_trashed', 'OurTechnicController@display_trashed')->name('technic_category.our_technic.trashed_index');

Route::resource('our_technic_tickets', 'OurTechnicTicketController')->except([
    'create',
    'edit'
]);
Route::post('our_technic_tickets/reassignment/{our_technic_ticket}', 'OurTechnicTicketController@reassignment')->name('our_technic_tickets.reassignment');

Route::post('get_technic_tickets', 'OurTechnicTicketController@getTechnicTickets')->name('get_technic_tickets');

Route::resource('our_technic_tickets.report', 'OurTechnicTicketReportController')->only([
    'store',
    'destroy',
    'update',
])->parameters([
    'report' => 'our_technic_ticket_report'
]);


Route::get('get_technics', 'OurTechnicController@get_technics')->name('get_technics');
Route::get('get_all_technics', 'OurTechnicController@get_all_technics')->name('get_all_technics');
Route::post('get_technics_paginated/{our_technic_category_id}', 'OurTechnicController@getTechnicsPaginated')->name('get_technics_paginated');
Route::post('get_trashed_technics_paginated/{our_technic_category_id}', 'OurTechnicController@getTrashedTechnicsPaginated')->name('get_trashed_technics_paginated');

Route::resource('defects', 'DefectsController')->except([
    'create',
    'update',
    'edit'
]);
Route::put('defects/{defects}/select_responsible', 'DefectsController@select_responsible')->name('defects.select_responsible');
Route::put('defects/{defects}/decline', 'DefectsController@decline')->name('defects.decline');
Route::put('defects/{defects}/accept', 'DefectsController@accept')->name('defects.accept');
Route::put('defects/{defects}/update_repair_dates', 'DefectsController@update_repair_dates')->name('defects.update_repair_dates');
Route::put('defects/{defects}/end_repair', 'DefectsController@end_repair')->name('defects.end_repair');
Route::post('paginated_defects/', 'DefectsController@paginated_defects')->name('defects.paginated');

Route::post('our_technic_tickets/{our_technic_ticket}/close', 'OurTechnicTicketActionsController@close')->name('our_technic_tickets.close');

Route::post('our_technic_tickets/{our_technic_ticket}/close', 'OurTechnicTicketActionsController@close')->name('our_technic_tickets.close');
Route::post('our_technic_tickets/{our_technic_ticket}/request_extension', 'OurTechnicTicketActionsController@request_extension')->name('our_technic_tickets.request_extension');
Route::post('our_technic_tickets/{our_technic_ticket}/agree_extension', 'OurTechnicTicketActionsController@agree_extension')->name('our_technic_tickets.agree_extension');
Route::post('our_technic_tickets/{our_technic_ticket}/make_ttn', 'OurTechnicTicketActionsController@make_ttn')->name('our_technic_tickets.make_ttn');


// ТОПЛИВО СТАРОЕ

Route::get('fuel_tank_operations/report', 'Fuel\Old\FuelTankOperationController@createReport')->name('fuel_tank_operation.report');

Route::resource('fuel_tank_operations', 'Fuel\Old\FuelTankOperationController')->except([
    'create',
    'edit',
]);

Route::post('fuel_tank_operations_paginated', 'Fuel\Old\FuelTankOperationController@getFuelTankOperationsPaginated')->name('fuel_tank_operations_paginated');
Route::post('get_fuel_tank_operations', 'Fuel\Old\FuelTankOperationController@getFuelTanksOperations')->name('get_fuel_tank_operations');
Route::post('fuel_tank/{fuel_tank}/change_fuel_level', 'Fuel\Old\FuelTankController@changeFuelLevel')->name('fuel_tank.change_fuel_level');
Route::get('fuel_tank_trashed', 'Fuel\Old\FuelTankController@display_trashed')->name('fuel_tank.display_trashed');
Route::get('trashed_fuel_tank/{fuel_tank}', 'Fuel\Old\FuelTankController@show_trashed')->name('fuel_tank.show_trashed');


Route::resource('fuel_tank', 'Fuel\Old\FuelTankController')->except([
    'create',
    'edit',
]);

Route::post('get_fuel_tanks', 'Fuel\Old\FuelTankController@getFuelTanks')->name('get_fuel_tanks');
Route::post('get_fuel_tanks_by_object', 'Fuel\Old\FuelTankController@getFuelTanksByObject')->name('get_fuel_tanks_by_object');
Route::post('get_fuel_tanks_paginated', 'Fuel\Old\FuelTankController@getFuelTanksPaginated')->name('get_fuel_tanks_paginated');
Route::post('get_trashed_fuel_tanks_paginated', 'Fuel\Old\FuelTankController@getTrashedFuelTanksPaginated')->name('get_trashed_fuel_tanks_paginated');

// ТОПЛИВО СТАРОЕ КОНЕЦ

// Новый раздел учета топлива

Route::group(['prefix' => 'fuel', 'as' => 'fuel::',  'namespace' => "Fuel"], function () {
    
    Route::group(['prefix' => 'tanks', 'as' => 'tanks::'], function () {
        Route::get('/', 'FuelTankController@getPageCore')->name('getPageCore');
        Route::get('getProjectObjects', 'FuelTankController@getProjectObjects')->name('getProjectObjects');
        Route::get('getPermissions', 'FuelTankController@getPermissions')->name('getPermissions');
        Route::apiResource('resource', 'FuelTankController');
        Route::apiResource('movement', 'FuelTankMovementController');
    });

    Route::group(['prefix' => 'fuelFlow', 'as' => 'fuelFlow::'], function () {
        Route::get('/', 'FuelFlowController@getPageCore')->name('getPageCore');
        Route::get('getPermissions', 'FuelFlowController@getPermissions')->name('getPermissions');
        Route::apiResource('resource', 'FuelFlowController');
    });

    Route::group(['prefix' => 'reports', 'as' => 'reports::'], function () {
        Route::get('fuelFlowMacroReport', 'FuelReportController@fuelFlowMacroReport')->name('fuelFlowMacroReport');
        Route::get('fuelFlowDetailedReport', 'FuelReportController@fuelFlowDetailedReport')->name('fuelFlowDetailedReport');
        Route::get('tanksMovementReport', 'FuelReportController@tanksMovementReport')->name('tanksMovementReport');
        Route::get('getPermissions', 'FuelReportController@getPermissions')->name('getPermissions');
    });

});
