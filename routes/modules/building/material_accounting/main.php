<?php

Route::get('/operations', 'MaterialAccountingController@operations')->name('operations')->middleware('can:mat_acc_operation_log');
Route::get('/report_card', 'MaterialAccountingController@report_card')->name('report_card')->middleware('can:mat_acc_report_card');
Route::get('/manual_transfer', 'MaterialAccountingController@manual_transfer')->name('manual_transfer');
Route::get('/closed_operation/{operation_id}', 'MaterialAccountingController@closed_operation')->name('closed_operation')->middleware('can:mat_acc_operation_log');
Route::get('/{operation_id}/view', 'MaterialAccountingController@redirector')->name('redirector')->middleware('can:mat_acc_operation_log');
Route::get('/certificateless_operations', 'MaterialAccountingController@certificatelessOperations')->name('certificateless_operations')->middleware('can:see_certificateless_operations');
Route::post('/print_operations', 'MaterialAccountingController@print_operations')->name('operations::print');
Route::post('/report_card/get_search_values', 'MaterialAccountingController@get_search_values')->name('report_card::get_search_values');
Route::post('/operations/get_search_values', 'MaterialAccountingController@operations_get_search_values')->name('operations::get_search_values');
Route::post('/operations/suggest_solution', 'MaterialAccountingController@suggestSolution')->name('suggest_solution');
Route::post('/operations/do_solutions', 'MaterialAccountingController@doSolutions')->name('do_solutions');
Route::get('/print_bad_objects', 'MaterialAccountingController@print_bad_objects')->name('print_bad_objects');
Route::post('/delete_object_from_session', 'MaterialAccountingController@delete_object_from_session')->name('delete_object_from_session');

Route::post('/report_card/filter', 'MaterialAccountingController@filter')->name('report_card::filter');
Route::post('/report_card/filter_base', 'MaterialAccountingController@filter_base')->name('report_card::filter_base');
Route::post('/print_report', 'MaterialAccountingController@print_report')->name('report::print');

Route::any('/report_card/get_objects', 'MaterialAccountingController@get_objects')->name('report_card::get_objects');
Route::any('/report_card/get_suppliers', 'MaterialAccountingController@get_suppliers')->name('report_card::get_suppliers');
Route::any('/report_card/get_materials', 'MaterialAccountingController@get_materials')->name('report_card::get_materials');
Route::any('/report_card/get_bases', 'MaterialAccountingController@get_bases')->name('report_card::get_bases');
Route::get('/report_card/get_base_comments', 'MaterialAccountingController@get_base_comments')->name('report_card::get_base_comments');
Route::post('/check_problem/{operation_id}', 'MaterialAccountingController@check_problem')->name('check_problem');
Route::post('/close_operation/{operation_id}', 'MaterialAccountingController@close_operation')->name('close_operation');
Route::post('/report/operations_actions', 'MaterialAccountingController@export_object_actions')->name('export_object_actions');

Route::post('/operation/upload/{id}', 'MaterialAccountingController@upload')->name('upload');
Route::post('/operation/delete_file/{id}', 'MaterialAccountingController@delete_file')->name('delete_file');

Route::post('/operation/part_upload/{id}', 'MaterialAccountingController@part_upload')->name('part_upload');
Route::post('/operation/delete_part_file/{id}', 'MaterialAccountingController@delete_part_file')->name('delete_part_file');
Route::post('/operation/delete_part_operation/{task_id?}', 'MaterialAccountingController@delete_part_operation')->name('delete_part_operation');
Route::post('/operation/store_deletion_task', 'MaterialAccountingController@store_deletion_task')->name('store_deletion_task');
Route::get('/operation/delete_part_task/{task_id}', 'MaterialAccountingController@delete_part_task')->name('delete_part_task');

Route::get('/operation/update_part_task/{task_id}', 'MaterialAccountingController@update_part_task')->name('update_part_task');
Route::get('/certificateless_task/{task_id}', 'MaterialAccountingController@certificatelessTask')->name('certificateless_task');
Route::post('/operation/store_update_task', 'MaterialAccountingController@store_update_task')->name('store_update_task');
Route::post('/operation/update_part_operation/{task_id?}', 'MaterialAccountingController@update_part_operation')->name('update_part_operation');

Route::post('/get_users', 'MaterialAccountingController@get_users')->name('get_users');
Route::any('/get_RPs', 'MaterialAccountingController@get_RPs')->name('get_RPs');
Route::post('/get_statuses', 'MaterialAccountingController@get_statuses')->name('get_statuses');
Route::post('/get_types', 'MaterialAccountingController@get_types')->name('get_types');
Route::post('/get_materials_from_base', 'MaterialAccountingController@getMaterialsFromBase')->name('get_materials_from_base');
Route::post('/get_material_category_description', 'MaterialAccountingController@getMaterialCategoryDescription')->name('get_material_category_description');

Route::post('/materials_count', 'MaterialAccountingController@materials_count')->name('materials_count');
Route::post('/attach_material', 'MaterialAccountingController@attach_material')->name('attach_material');

Route::post('/get_siblings', 'MaterialAccountingController@getSiblings')->name('get_siblings');
Route::post('/split_base', 'MaterialAccountingController@splitBase')->name('split_base');
Route::post('/used', 'MaterialAccountingController@moveToUsed')->name('move_to_used');
Route::post('/new', 'MaterialAccountingController@moveToNew')->name('move_to_new');

Route::post('/attach_contract', 'MaterialAccountingController@attach_contract')->name('attach_contract');
