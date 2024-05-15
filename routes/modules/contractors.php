<?php

Route::middleware('can:contractors')->group(function () {
    Route::get('/', 'ContractorController@index')->name('index');
    Route::get('/card/{id}', 'ContractorController@card')->name('card');
    Route::get('/create', 'ContractorController@create')->name('create')->middleware('can:contractors_create');
    Route::get('/edit/{id}', 'ContractorController@edit')->name('edit')->middleware('can:contractors_edit');
    Route::get('/tasks/{id}', 'ContractorController@tasks')->name('tasks');

    Route::post('/store', 'ContractorController@store')->name('store')->middleware('can:contractors_create');
    Route::post('/update/{id}', 'ContractorController@update')->name('update')->middleware('can:contractors_edit');
    Route::post('/add-contact/{id}', 'ContractorController@add_contact')->name('add_contact')->middleware('can:contractors_contacts');
    Route::post('/edit-contact', 'ContractorController@edit_contact')->name('edit_contact')->middleware('can:contractors_contacts');
    Route::get('/search_inn', 'ContractorController@search_dadata')->name('search_dadata');
    Route::get('/is_unique', 'ContractorController@is_unique')->name('is_unique');
    Route::get('/remove_task/{id}', 'ContractorController@remove_task')->name('remove_task');

    Route::post('/contact-delete', 'ContractorController@contact_delete')->name('contact_delete')->middleware('can:contractors_contacts');
    Route::post('/contractor-delete-request', 'ContractorController@contractor_delete_request')->name('contractor_delete_request')->middleware('can:contractors_delete');
    Route::post('/get_by_type/', 'ContractorController@get_by_type')->name('get_by_type');
    Route::post('/solve_remove/{id}', 'ContractorController@solve_remove')->name('solve_remove')->middleware('can:contractors_delete');
    Route::post('/solve_task_check_contractor/{task_id}', 'ContractorController@solveTaskCheckContractor')->name('solve_task_check_contractor');
});

Route::get('/get_contractors', 'ContractorController@getContractors')->name('get_contractors');
