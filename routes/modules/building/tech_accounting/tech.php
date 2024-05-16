<?php

use App\Http\Controllers\Building\TechAccounting\Fuel\Old\FuelTankController;
use App\Http\Controllers\Building\TechAccounting\Fuel\Old\FuelTankOperationController;
use App\Http\Controllers\Building\TechAccounting\Technic;
use Illuminate\Support\Facades\Route;

Route::resource('technic_category', 'Technic\old\TechnicCategoryController');
Route::get('technic_category_trashed', [Technic\old\TechnicCategoryController::class, 'display_trashed'])->name('technic_category.display_trashed');
Route::get('technic_category_trashed/{technic_category}', [Technic\old\TechnicCategoryController::class, 'show_trashed'])->name('technic_category.show_trashed');

Route::resource('technic_category.our_technic', 'Technic\old\OurTechnicController')->except([
    'create', 'show', 'edit',
]);
Route::get('technic_category/{technic_category}/our_technic_trashed', [Technic\old\OurTechnicController::class, 'display_trashed'])->name('technic_category.our_technic.trashed_index');

Route::resource('our_technic_tickets', Technic\old\OurTechnicTicketController::class)->except([
    'create',
    'edit',
]);
Route::post('our_technic_tickets/reassignment/{our_technic_ticket}', [Technic\old\OurTechnicTicketController::class, 'reassignment'])->name('our_technic_tickets.reassignment');

Route::post('get_technic_tickets', [Technic\old\OurTechnicTicketController::class, 'getTechnicTickets'])->name('get_technic_tickets');

Route::resource('our_technic_tickets.report', Technic\old\OurTechnicTicketReportController::class)->only([
    'store',
    'destroy',
    'update',
])->parameters([
    'report' => 'our_technic_ticket_report',
]);

Route::get('get_technics', [Technic\old\OurTechnicController::class, 'get_technics'])->name('get_technics');
Route::get('get_all_technics', [Technic\old\OurTechnicController::class, 'get_all_technics'])->name('get_all_technics');
Route::post('get_technics_paginated/{our_technic_category_id}', [Technic\old\OurTechnicController::class, 'getTechnicsPaginated'])->name('get_technics_paginated');
Route::post('get_trashed_technics_paginated/{our_technic_category_id}', [Technic\old\OurTechnicController::class, 'getTrashedTechnicsPaginated'])->name('get_trashed_technics_paginated');

Route::resource('defects', Technic\old\DefectsController::class)->except([
    'create',
    'update',
    'edit',
]);
Route::put('defects/{defects}/select_responsible', [Technic\old\DefectsController::class, 'select_responsible'])->name('defects.select_responsible');
Route::put('defects/{defects}/decline', [Technic\old\DefectsController::class, 'decline'])->name('defects.decline');
Route::put('defects/{defects}/accept', [Technic\old\DefectsController::class, 'accept'])->name('defects.accept');
Route::put('defects/{defects}/update_repair_dates', [Technic\old\DefectsController::class, 'update_repair_dates'])->name('defects.update_repair_dates');
Route::put('defects/{defects}/end_repair', [Technic\old\DefectsController::class, 'end_repair'])->name('defects.end_repair');
Route::post('paginated_defects/', [Technic\old\DefectsController::class, 'paginated_defects'])->name('defects.paginated');

Route::post('our_technic_tickets/{our_technic_ticket}/close', [Technic\old\OurTechnicTicketActionsController::class, 'close'])->name('our_technic_tickets.close');

Route::post('our_technic_tickets/{our_technic_ticket}/close', [Technic\old\OurTechnicTicketActionsController::class, 'close'])->name('our_technic_tickets.close');
Route::post('our_technic_tickets/{our_technic_ticket}/request_extension', [Technic\old\OurTechnicTicketActionsController::class, 'request_extension'])->name('our_technic_tickets.request_extension');
Route::post('our_technic_tickets/{our_technic_ticket}/agree_extension', [Technic\old\OurTechnicTicketActionsController::class, 'agree_extension'])->name('our_technic_tickets.agree_extension');
Route::post('our_technic_tickets/{our_technic_ticket}/make_ttn', [Technic\old\OurTechnicTicketActionsController::class, 'make_ttn'])->name('our_technic_tickets.make_ttn');

// ТОПЛИВО СТАРОЕ

Route::get('fuel_tank_operations/report', [FuelTankOperationController::class, 'createReport'])->name('fuel_tank_operation.report');

Route::resource('fuel_tank_operations', FuelTankOperationController::class)->except([
    'create',
    'edit',
]);

Route::post('fuel_tank_operations_paginated', [FuelTankOperationController::class, 'getFuelTankOperationsPaginated'])->name('fuel_tank_operations_paginated');
Route::post('get_fuel_tank_operations', [FuelTankOperationController::class, 'getFuelTanksOperations'])->name('get_fuel_tank_operations');
Route::post('fuel_tank/{fuel_tank}/change_fuel_level', [FuelTankController::class, 'changeFuelLevel'])->name('fuel_tank.change_fuel_level');
Route::get('fuel_tank_trashed', [FuelTankController::class, 'display_trashed'])->name('fuel_tank.display_trashed');
Route::get('trashed_fuel_tank/{fuel_tank}', [FuelTankController::class, 'show_trashed'])->name('fuel_tank.show_trashed');

Route::resource('fuel_tank', 'Fuel\Old\FuelTankController')->except([
    'create',
    'edit',
]);

Route::post('get_fuel_tanks', [FuelTankController::class, 'getFuelTanks'])->name('get_fuel_tanks');
Route::post('get_fuel_tanks_by_object', [FuelTankController::class, 'getFuelTanksByObject'])->name('get_fuel_tanks_by_object');
Route::post('get_fuel_tanks_paginated', [FuelTankController::class, 'getFuelTanksPaginated'])->name('get_fuel_tanks_paginated');
Route::post('get_trashed_fuel_tanks_paginated', [FuelTankController::class, 'getTrashedFuelTanksPaginated'])->name('get_trashed_fuel_tanks_paginated');

// ТОПЛИВО СТАРОЕ КОНЕЦ

// Новый раздел учета техники

Route::prefix('technic')->name('technic::')->group(function () {
    Route::prefix('ourTechnicList')->name('ourTechnicList::')->group(function () {
        Route::registerBaseRoutes('App\Http\Controllers\Building\TechAccounting\Technic\OurTechnicController', $attachmentsRoutes = false);
    });
    Route::prefix('technicCategory')->name('technicCategory::')->middleware('can:technics_brands_models_categories_read_create_update_delete')->group(function () {
        Route::registerBaseRoutes('App\Http\Controllers\Building\TechAccounting\Technic\TechnicCategoryController', $attachmentsRoutes = false);
    });
    Route::prefix('technicBrand')->name('technicBrand::')->middleware('can:technics_brands_models_categories_read_create_update_delete')->group(function () {
        Route::registerBaseRoutes('App\Http\Controllers\Building\TechAccounting\Technic\TechnicBrandController', $attachmentsRoutes = false);
    });
    Route::prefix('mtechnicBrandModel')->name('technicBrandModel::')->middleware('can:technics_brands_models_categories_read_create_update_delete')->group(function () {
        Route::registerBaseRoutes('App\Http\Controllers\Building\TechAccounting\Technic\TechnicBrandModelController', $attachmentsRoutes = false);
    });
    Route::prefix('movements')->name('movements::')->group(function () {
        Route::registerBaseRoutes('App\Http\Controllers\Building\TechAccounting\Technic\TechnicMovementController', $attachmentsRoutes = true);
    });
});

// КОНЕЦ Новый раздел учета техники

// Новый раздел учета топлива

Route::prefix('fuel')->name('fuel::')->group(function () {

    Route::prefix('tanks')->name('tanks::')->group(function () {
        Route::get('validateTankNumberUnique', [App\Http\Controllers\Building\TechAccounting\Fuel\FuelTankController::class, 'validateTankNumberUnique'])->name('validateTankNumberUnique');
        Route::post('moveFuelTank', [App\Http\Controllers\Building\TechAccounting\Fuel\FuelTankController::class, 'moveFuelTank'])->name('moveFuelTank');
        Route::post('confirmMovingFuelTank', [App\Http\Controllers\Building\TechAccounting\Fuel\FuelTankController::class, 'confirmMovingFuelTank'])->name('confirmMovingFuelTank');
        Route::get('getFuelTankConfirmationFormData', [App\Http\Controllers\Building\TechAccounting\Fuel\FuelTankController::class, 'getFuelTankConfirmationFormData'])->name('getFuelTankConfirmationFormData');

        Route::registerBaseRoutes('App\Http\Controllers\Building\TechAccounting\Fuel\FuelTankController', $attachmentsRoutes = false);
    });

    Route::prefix('fuelFlow')->name('fuelFlow::')->group(function () {
        Route::registerBaseRoutes('App\Http\Controllers\Building\TechAccounting\Fuel\FuelTankFlowController', $attachmentsRoutes = true);
    });

    Route::prefix('reports')->name('reports::')->group(function () {
        Route::prefix('fuelTankPeriodReport')->name('fuelTankPeriodReport::')->group(function () {
            Route::registerBaseRoutes('App\Http\Controllers\Building\TechAccounting\Fuel\Reports\FuelTankPeriodReportController', $attachmentsRoutes = false);
            Route::get('getPdf', [App\Http\Controllers\Building\TechAccounting\Fuel\Reports\FuelTankPeriodReportController::class, 'getPdf'])->name('getPdf');
        });
        Route::prefix('tanksMovementReport')->name('tanksMovementReport::')->middleware('can:fuel_tanks_movements_report_access')->group(function () {
            Route::registerBaseRoutes('App\Http\Controllers\Building\TechAccounting\Fuel\Reports\FuelTanksMovementsReportController', $attachmentsRoutes = false);
        });
    });
});
