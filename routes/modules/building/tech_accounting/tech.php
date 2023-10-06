<?php
Route::resource('technic_category', 'Technic\old\TechnicCategoryController');
Route::get('technic_category_trashed', 'Technic\old\TechnicCategoryController@display_trashed')->name('technic_category.display_trashed');
Route::get('technic_category_trashed/{technic_category}', 'Technic\old\TechnicCategoryController@show_trashed')->name('technic_category.show_trashed');

Route::resource('technic_category.our_technic', 'Technic\old\OurTechnicController')->except([
    'create', 'show', 'edit'
]);
Route::get('technic_category/{technic_category}/our_technic_trashed', 'Technic\old\OurTechnicController@display_trashed')->name('technic_category.our_technic.trashed_index');

Route::resource('our_technic_tickets', 'Technic\old\OurTechnicTicketController')->except([
    'create',
    'edit'
]);
Route::post('our_technic_tickets/reassignment/{our_technic_ticket}', 'Technic\old\OurTechnicTicketController@reassignment')->name('our_technic_tickets.reassignment');

Route::post('get_technic_tickets', 'Technic\old\OurTechnicTicketController@getTechnicTickets')->name('get_technic_tickets');

Route::resource('our_technic_tickets.report', 'Technic\old\OurTechnicTicketReportController')->only([
    'store',
    'destroy',
    'update',
])->parameters([
    'report' => 'our_technic_ticket_report'
]);


Route::get('get_technics', 'Technic\old\OurTechnicController@get_technics')->name('get_technics');
Route::get('get_all_technics', 'Technic\old\OurTechnicController@get_all_technics')->name('get_all_technics');
Route::post('get_technics_paginated/{our_technic_category_id}', 'Technic\old\OurTechnicController@getTechnicsPaginated')->name('get_technics_paginated');
Route::post('get_trashed_technics_paginated/{our_technic_category_id}', 'Technic\old\OurTechnicController@getTrashedTechnicsPaginated')->name('get_trashed_technics_paginated');

Route::resource('defects', 'Technic\old\DefectsController')->except([
    'create',
    'update',
    'edit'
]);
Route::put('defects/{defects}/select_responsible', 'Technic\old\DefectsController@select_responsible')->name('defects.select_responsible');
Route::put('defects/{defects}/decline', 'Technic\old\DefectsController@decline')->name('defects.decline');
Route::put('defects/{defects}/accept', 'Technic\old\DefectsController@accept')->name('defects.accept');
Route::put('defects/{defects}/update_repair_dates', 'Technic\old\DefectsController@update_repair_dates')->name('defects.update_repair_dates');
Route::put('defects/{defects}/end_repair', 'Technic\old\DefectsController@end_repair')->name('defects.end_repair');
Route::post('paginated_defects/', 'Technic\old\DefectsController@paginated_defects')->name('defects.paginated');

Route::post('our_technic_tickets/{our_technic_ticket}/close', 'Technic\old\OurTechnicTicketActionsController@close')->name('our_technic_tickets.close');

Route::post('our_technic_tickets/{our_technic_ticket}/close', 'Technic\old\OurTechnicTicketActionsController@close')->name('our_technic_tickets.close');
Route::post('our_technic_tickets/{our_technic_ticket}/request_extension', 'Technic\old\OurTechnicTicketActionsController@request_extension')->name('our_technic_tickets.request_extension');
Route::post('our_technic_tickets/{our_technic_ticket}/agree_extension', 'Technic\old\OurTechnicTicketActionsController@agree_extension')->name('our_technic_tickets.agree_extension');
Route::post('our_technic_tickets/{our_technic_ticket}/make_ttn', 'Technic\old\OurTechnicTicketActionsController@make_ttn')->name('our_technic_tickets.make_ttn');


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

// Новый раздел учета техники

Route::group(['prefix' => 'technic', 'as' => 'technic::',  'namespace' => "Technic"], function () {
    Route::group(['prefix' => 'ourTechnicList', 'as' => 'ourTechnicList::'], function () {
        Route::get('getPermissions', 'OurTechnicController@getPermissions')->name('getPermissions');
        Route::get('/', 'OurTechnicController@getPageCore')->name('getPageCore');
        Route::apiResource('resource', 'OurTechnicController');
    });
    Route::group(['prefix' => 'technicCategory', 'as' => 'technicCategory::'], function () {
        Route::get('getPermissions', 'TechnicCategoryController@getPermissions')->name('getPermissions');
        Route::get('/', 'TechnicCategoryController@getPageCore')->name('getPageCore');
        Route::apiResource('resource', 'TechnicCategoryController');
    });
    Route::group(['prefix' => 'technicBrand', 'as' => 'technicBrand::'], function () {
        Route::get('getPermissions', 'TechnicBrandController@getPermissions')->name('getPermissions');
        Route::get('/', 'TechnicBrandController@getPageCore')->name('getPageCore');
        Route::apiResource('resource', 'TechnicBrandController');
    });
    Route::group(['prefix' => 'technicBrandModel', 'as' => 'technicBrandModel::'], function () {
        Route::get('getPermissions', 'TechnicBrandModelController@getPermissions')->name('getPermissions');
        Route::get('/', 'TechnicBrandModelController@getPageCore')->name('getPageCore');
        Route::apiResource('resource', 'TechnicBrandModelController');
    });

    Route::get('getTechnicBrands', 'TechnicBrandController@getTechnicBrands')->name('getTechnicBrands');
    Route::get('getTechnicCategories', 'TechnicCategoryController@getTechnicCategories')->name('getTechnicCategories');
    Route::get('getTechnicResponsibles', 'OurTechnicController@getTechnicResponsibles')->name('getTechnicResponsibles');
});

// КОНЕЦ Новый раздел учета техники

// Новый раздел учета топлива

Route::group(['prefix' => 'fuel', 'as' => 'fuel::',  'namespace' => "Fuel"], function () {
    
    Route::group(['prefix' => 'tanks', 'as' => 'tanks::'], function () {
        Route::get('getProjectObjects', 'FuelTankController@getProjectObjects')->name('getProjectObjects');

        Route::get('getPermissions', 'FuelTankController@getPermissions')->name('getPermissions');
        Route::get('/', 'FuelTankController@getPageCore')->name('getPageCore');
        Route::apiResource('resource', 'FuelTankController');
    });

    Route::group(['prefix' => 'fuelFlow', 'as' => 'fuelFlow::'], function () {
        Route::get('getFuelResponsibles', 'FuelTankFlowController@getFuelResponsibles')->name('getFuelResponsibles');
        Route::get('getFuelTanks', 'FuelTankFlowController@getFuelTanks')->name('getFuelTanks');
        Route::get('getFuelContractors', 'FuelTankFlowController@getFuelContractors')->name('getFuelContractors');
        Route::get('getFuelConsumers', 'FuelTankFlowController@getFuelConsumers')->name('getFuelConsumers');
        Route::get('getFuelFlowTypes', 'FuelTankFlowController@getFuelFlowTypes')->name('getFuelFlowTypes');

        Route::get('getPermissions', 'FuelTankFlowController@getPermissions')->name('getPermissions');
        Route::get('/', 'FuelTankFlowController@getPageCore')->name('getPageCore');
        Route::apiResource('resource', 'FuelTankFlowController');
    });

    Route::group(['prefix' => 'reports', 'as' => 'reports::'], function () {
        Route::group(['prefix' => 'fuelFlowMacroReport', 'as' => 'fuelFlowMacroReport::'], function () {
            Route::get('getPageCore', 'FuelReportController@fuelFlowMacroReportPageCore')->name('getPageCore');
            Route::get('data', 'FuelReportController@fuelFlowMacroReportData')->name('resource.index');
            Route::get('getPermissions', 'FuelReportController@fuelFlowMacroReportPermissions')->name('getPermissions');
        });
        // Route::group(['prefix' => 'fuelFlowDetailedReport', 'as' => 'fuelFlowDetailedReport::'], function () {
        //     Route::get('getPageCore', 'FuelReportController@fuelFlowDetailedReportPageCore')->name('getPageCore');
        //     Route::get('data', 'FuelReportController@fuelFlowDetailedReportData')->name('resource.index');
        //     Route::get('getPermissions', 'FuelReportController@fuelFlowDetailedReporttPermissions')->name('getPermissions');
        // });
        Route::group(['prefix' => 'tanksMovementReport', 'as' => 'tanksMovementReport::'], function () {
            Route::get('getPageCore', 'FuelReportController@tanksMovementReportPageCore')->name('getPageCore');
            Route::get('data', 'FuelReportController@tanksMovementReportData')->name('resource.index');
            Route::get('getPermissions', 'FuelReportController@tanksMovementReportPermissions')->name('getPermissions');
        });
        Route::get('getProjectObjects', 'FuelReportController@getProjectObjects')->name('getProjectObjects');
    });

});
