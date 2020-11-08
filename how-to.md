How to add a new dict?
1) Add a new model with migration
    `php artisan make:model models/q3wMaterial/q3wMaterialStandard -m`
2) Add a new controller for a model
    `php artisan make:controller q3wMaterial/q3wMaterialStandardController -m models/q3wMaterial/q3wMaterialStandard`
3) Add a new view if needed
    `resources->views->materials->material-standard.blade.php`
4) Add few routes in `resources->routes->web.php`
    `Route::get('/materials/material-standard', 'q3wMaterial\q3wMaterialStandardController@index')->name('materials.standards.index');`
    `Route::get('/materials/material-standard/list', 'q3wMaterial\q3wMaterialStandardController@show')->name('materials.standards.list');`
    `Route::put('/materials/material-standard/', 'q3wMaterial\q3wMaterialStandardController@update')->name('materials.standards.update');`
    `Route::post('/materials/material-standard/', 'q3wMaterial\q3wMaterialStandardController@store')->name('materials.standards.store');`
    `Route::delete('/materials/material-standard/', 'q3wMaterial\q3wMaterialStandardController@delete')->name('materials.standards.delete');`
5) Set up table fields in migration and run it
     `php artisan migrate`
    Rollback if needed
     `php artisan migrate:rollback`
6) Set up model - add safe-delete id needed and fill guarded array
7) Set up controller - routes functions must be filled
