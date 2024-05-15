<?php

use App\Http\Controllers\Building\TechAccounting\Technic;
use Illuminate\Support\Facades\Route;

Route::resource('vehicle_categories', Technic\old\VehicleCategoriesController::class);
Route::get('vehicle_categories_trashed', [Technic\old\VehicleCategoriesController::class, 'display_trashed'])->name('vehicle_categories.display_trashed');
Route::get('vehicle_categories_trashed/{vehicle_category}', [Technic\old\VehicleCategoriesController::class, 'show_trashed'])->name('vehicle_categories.show_trashed');

Route::resource('vehicle_categories.our_vehicles', Technic\old\OurVehiclesController::class)->except([
    'create', 'show', 'edit',
]);
Route::get('vehicle_categories/{vehicle_categories}/our_vehicles_trashed', [Technic\old\OurVehiclesController::class, 'index_trashed'])->name('vehicle_categories.our_vehicles.index_trashed');

Route::get('get_vehicles', [Technic\old\OurVehiclesController::class, 'get_vehicles'])->name('get_vehicles');
