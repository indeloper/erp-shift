<?php

Route::resource('vehicle_categories', 'VehicleCategoriesController');
Route::get('vehicle_categories_trashed', 'VehicleCategoriesController@display_trashed')->name('vehicle_categories.display_trashed');
Route::get('vehicle_categories_trashed/{vehicle_category}', 'VehicleCategoriesController@show_trashed')->name('vehicle_categories.show_trashed');

Route::resource('vehicle_categories.our_vehicles', 'OurVehiclesController')->except([
    'create', 'show', 'edit'
]);
Route::get('vehicle_categories/{vehicle_categories}/our_vehicles_trashed', 'OurVehiclesController@index_trashed')->name('vehicle_categories.our_vehicles.index_trashed');

Route::get('get_vehicles', 'OurVehiclesController@get_vehicles')->name('get_vehicles');
