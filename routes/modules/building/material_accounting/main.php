<?php

use App\Http\Controllers\Building\MaterialAccounting\MaterialAccountingController;
use Illuminate\Support\Facades\Route;

Route::get('/operations', [MaterialAccountingController::class, 'operations'])->name('operations')->middleware('can:mat_acc_operation_log');
Route::get('/report_card', [MaterialAccountingController::class, 'report_card'])->name('report_card')->middleware('can:mat_acc_report_card');
Route::get('/manual_transfer', [MaterialAccountingController::class, 'manual_transfer'])->name('manual_transfer');
Route::get('/closed_operation/{operation_id}', [MaterialAccountingController::class, 'closed_operation'])->name('closed_operation')->middleware('can:mat_acc_operation_log');
Route::get('/{operation_id}/view', [MaterialAccountingController::class, 'redirector'])->name('redirector')->middleware('can:mat_acc_operation_log');
Route::get('/certificateless_operations', [MaterialAccountingController::class, 'certificatelessOperations'])->name('certificateless_operations')->middleware('can:see_certificateless_operations');
Route::post('/print_operations', [MaterialAccountingController::class, 'print_operations'])->name('operations::print');
Route::post('/report_card/get_search_values', [MaterialAccountingController::class, 'get_search_values'])->name('report_card::get_search_values');
Route::post('/operations/get_search_values', [MaterialAccountingController::class, 'operations_get_search_values'])->name('operations::get_search_values');
Route::post('/operations/suggest_solution', [MaterialAccountingController::class, 'suggestSolution'])->name('suggest_solution');
Route::post('/operations/do_solutions', [MaterialAccountingController::class, 'doSolutions'])->name('do_solutions');
Route::get('/print_bad_objects', [MaterialAccountingController::class, 'print_bad_objects'])->name('print_bad_objects');
Route::post('/delete_object_from_session', [MaterialAccountingController::class, 'delete_object_from_session'])->name('delete_object_from_session');

Route::post('/report_card/filter', [MaterialAccountingController::class, 'filter'])->name('report_card::filter');
Route::post('/report_card/filter_base', [MaterialAccountingController::class, 'filter_base'])->name('report_card::filter_base');
Route::post('/print_report', [MaterialAccountingController::class, 'print_report'])->name('report::print');

Route::any('/report_card/get_objects', [MaterialAccountingController::class, 'get_objects'])->name('report_card::get_objects');
Route::any('/report_card/get_suppliers', [MaterialAccountingController::class, 'get_suppliers'])->name('report_card::get_suppliers');
Route::any('/report_card/get_materials', [MaterialAccountingController::class, 'get_materials'])->name('report_card::get_materials');
Route::any('/report_card/get_bases', [MaterialAccountingController::class, 'get_bases'])->name('report_card::get_bases');
Route::get('/report_card/get_base_comments', [MaterialAccountingController::class, 'get_base_comments'])->name('report_card::get_base_comments');
Route::post('/check_problem/{operation_id}', [MaterialAccountingController::class, 'check_problem'])->name('check_problem');
Route::post('/close_operation/{operation_id}', [MaterialAccountingController::class, 'close_operation'])->name('close_operation');
Route::post('/report/operations_actions', [MaterialAccountingController::class, 'export_object_actions'])->name('export_object_actions');

Route::post('/operation/upload/{id}', [MaterialAccountingController::class, 'upload'])->name('upload');
Route::post('/operation/delete_file/{id}', [MaterialAccountingController::class, 'delete_file'])->name('delete_file');

Route::post('/operation/part_upload/{id}', [MaterialAccountingController::class, 'part_upload'])->name('part_upload');
Route::post('/operation/delete_part_file/{id}', [MaterialAccountingController::class, 'delete_part_file'])->name('delete_part_file');
Route::post('/operation/delete_part_operation/{task_id?}', [MaterialAccountingController::class, 'delete_part_operation'])->name('delete_part_operation');
Route::post('/operation/store_deletion_task', [MaterialAccountingController::class, 'store_deletion_task'])->name('store_deletion_task');
Route::get('/operation/delete_part_task/{task_id}', [MaterialAccountingController::class, 'delete_part_task'])->name('delete_part_task');

Route::get('/operation/update_part_task/{task_id}', [MaterialAccountingController::class, 'update_part_task'])->name('update_part_task');
Route::get('/certificateless_task/{task_id}', [MaterialAccountingController::class, 'certificatelessTask'])->name('certificateless_task');
Route::post('/operation/store_update_task', [MaterialAccountingController::class, 'store_update_task'])->name('store_update_task');
Route::post('/operation/update_part_operation/{task_id?}', [MaterialAccountingController::class, 'update_part_operation'])->name('update_part_operation');

Route::post('/get_users', [MaterialAccountingController::class, 'get_users'])->name('get_users');
Route::any('/get_RPs', [MaterialAccountingController::class, 'get_RPs'])->name('get_RPs');
Route::post('/get_statuses', [MaterialAccountingController::class, 'get_statuses'])->name('get_statuses');
Route::post('/get_types', [MaterialAccountingController::class, 'get_types'])->name('get_types');
Route::post('/get_materials_from_base', [MaterialAccountingController::class, 'getMaterialsFromBase'])->name('get_materials_from_base');
Route::post('/get_material_category_description', [MaterialAccountingController::class, 'getMaterialCategoryDescription'])->name('get_material_category_description');

Route::post('/materials_count', [MaterialAccountingController::class, 'materials_count'])->name('materials_count');
Route::post('/attach_material', [MaterialAccountingController::class, 'attach_material'])->name('attach_material');

Route::post('/get_siblings', [MaterialAccountingController::class, 'getSiblings'])->name('get_siblings');
Route::post('/split_base', [MaterialAccountingController::class, 'splitBase'])->name('split_base');
Route::post('/used', [MaterialAccountingController::class, 'moveToUsed'])->name('move_to_used');
Route::post('/new', [MaterialAccountingController::class, 'moveToNew'])->name('move_to_new');

Route::post('/attach_contract', [MaterialAccountingController::class, 'attach_contract'])->name('attach_contract');
