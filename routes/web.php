<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['activeuser', 'auth']], function () {

    Route::get('storage/{file_path?}', 'System\FileController@file')->where('file_path', '.*');

    Route::get('csrf/get-new', 'System\SystemController@refreshCsrf')->name('get-new-csrf');

    Route::any('contracts', 'Documents\ContractsController@index')->name('contracts::index')->middleware('can:contracts');
    Route::post('contracts_filtered', 'Documents\ContractsController@contractsFiltered')->name('contracts::filtered')->middleware('can:contracts');
    Route::any('contracts/get_contracts', 'Documents\ContractsController@get_contracts')->name('contracts::get_contracts');

    Route::group(['prefix' => 'objects', 'as' => 'objects::',  'namespace' => "Commerce", 'middleware' => 'can:objects'], function () {
        Route::get('/', 'ObjectController@index')->name('index');
        Route::post('/store', 'ObjectController@store')->name('store')->middleware('can:objects_create');
        Route::post('/update', 'ObjectController@update')->name('update')->middleware('can:objects_edit');

        Route::get('/get_contractors', 'ObjectController@get_contractors')->name('get_contractors');
        Route::get('/get_object_projects', 'ObjectController@get_object_projects')->name('get_object_projects');
    });
    Route::post('/get_objects', 'Commerce\ObjectController@getObjects')->name('objects::get_objects');

    Route::get('tasks/filter-tasks-report', 'Tasks\TasksController@showTasksReportFilterForm')->name('tasks.filter-tasks-report')->middleware('can:commercial_block_task_report_xlsx_export_access');
    Route::post('tasks/download-tasks-report', 'Tasks\TasksController@downloadTasksReport')->name('tasks.download-tasks-report')->middleware('can:commercial_block_task_report_xlsx_export_access');
    Route::get('tasks/current-user-tasks-project-objects-list', 'Tasks\TasksController@currentUserTasksProjectObjectsList')->name('tasks.current-user-tasks-project-objects.list');
    Route::get('tasks/current-user-tasks-contractors-list', 'Tasks\TasksController@currentUserTasksContractorsList')->name('tasks.current-user-tasks-contractors.list');
    Route::get('tasks/current-user-tasks-split-material-list', 'Tasks\TasksController@currentUserTasksSplitMaterialList')->name('tasks.current-user-tasks-split-material.list');

    Route::group(['prefix' => 'tasks', 'as' => 'tasks::', 'namespace' => "Tasks"], function () {
        Route::get('/', 'TasksController@index')->name('index');
        Route::get('/card/{id}', 'TasksController@card')->name('card');
        Route::get('search_projects', 'TasksController@searchProjects')->name('search_projects');


        Route::get('/get-users', 'TasksController@get_users')->name('get_users');
        Route::get('/get-contractors', 'TasksController@get_contractors')->name('get_contractors');
        Route::get('/get-projects', 'TasksController@get_projects')->name('get_projects');
        Route::post('/store', 'TasksController@store')->name('store')->middleware('can:tasks_default_myself,tasks_default_others');
        Route::post('/refresh', 'TasksController@refresh')->name('refresh');
        Route::post('/make_viewed', 'TasksController@make_viewed')->name('make_viewed');

        Route::get('/get-responsible-user/{id}', 'TasksController@get_responsible_users')->name('get_responsible_users');

        Route::post('/store', 'TasksController@store')->name('store');
        Route::post('/solve/{id}', 'TasksController@solve')->name('solve');
        Route::post('/update-resp-user/{id}', 'TasksController@update_resp_user')->name('update_resp_user');
        Route::post('choose_contractor', 'TaskCallController@choose_contractor')->name('choose_contractor');
        Route::post('choose_contact', 'TaskCallController@choose_contact')->name('choose_contact');
        Route::get('ajax/get_contacts/{contractor_id}', 'TaskCallController@get_contacts');

        Route::get('/new_call/{id}', 'TaskCallController@new_call')->name('new_call');
        Route::post('/close_call/{id}', 'TaskCallController@close_call')->name('close_call');

        Route::post('/postpone/{id}', 'TaskCommerceController@postpone')->name('postpone');
        Route::get('/common_task/{id}', 'TaskCommerceController@common_task')->name('common_task');
        Route::get('/usual/{id}', 'TaskCommerceController@slimTask')->name('slim_task');


        Route::any('/common_task/{id}/solve_task', 'TaskCommerceController@solve_task')->name('solve_task');

        Route::get('/tech_task/{id}', 'TechAccTasksController@tech_task')->name('tech_task');
        Route::get('/partial_36/{task}', 'TechAccTasksController@partial_36')->name('partial_36');

        Route::get('/make-test-call/{id}', 'TaskCallController@makeTestCall');
        Route::post('decline_request', 'TaskCommerceController@declineRequest')->name('decline_request');
    });

    Route::group(['prefix' => 'building', 'as' => 'building::', 'namespace' => "Building"], function () {

        Route::group(['prefix' => 'materials', 'as' => 'materials::', 'middleware' => 'can:manual_materials'], function () {
            Route::get('/', 'ManualMaterialCategoryController@index')->name('index');

            Route::post('/store', 'ManualMaterialCategoryController@store')->name('category::store')->middleware('can:manual_materials_edit');
            Route::post('/update', 'ManualMaterialCategoryController@update')->name('category::update')->middleware('can:manual_materials_edit');
            Route::post('/delete', 'ManualMaterialCategoryController@delete')->name('category::delete')->middleware('can:manual_materials_edit');
            Route::post('/clone', 'ManualMaterialCategoryController@clone')->name('category::clone')->middleware('can:manual_materials_edit');
            // api get need attrs to fill them next
            Route::post('/get_need_attrs', 'ManualMaterialCategoryController@getNeedAttributes')->name('category::get_need_attrs');
            Route::post('/get_need_attrs_values', 'ManualMaterialCategoryController@getNeedAttributesValues')->name('category::get_need_attrs_values');

            Route::get('/card/{id}', 'ManualMaterialController@card')->name('card');
            Route::post('/card/{id}/store', 'ManualMaterialController@store')->name('store')->middleware('can:manual_materials_edit');
            Route::post('/card/{id}/update', 'ManualMaterialController@update')->name('update')->middleware('can:manual_materials_edit');
            Route::post('/card/{id}/clone', 'ManualMaterialController@clone')->name('clone')->middleware('can:manual_materials_edit');
            Route::post('/card/delete', 'ManualMaterialController@delete')->name('delete')->middleware('can:materials_remove');
            Route::post('/card/restore', 'ManualMaterialController@restore')->name('restore')->middleware('can:materials_remove');

            Route::post('/select_work', 'ManualMaterialController@select_work')->name('select_work');
            Route::post('/select_attr_value', 'ManualMaterialController@select_attr_value')->name('select_attr_value');
            Route::post('/search_by_attributes', 'ManualMaterialController@search_by_attributes')->name('search_by_attributes');
            Route::post('/get_all_materials', 'ManualMaterialController@get_all_materials')->name('get_all_materials');
            Route::get('/get_references', 'ManualMaterialController@getReferences')->name('get_references');

        });

        Route::group(['prefix' => 'nodes', 'as' => 'nodes::', 'middleware' => 'can:manual_nodes'], function () {
            Route::get('/', 'ManualNodesController@index')->name('index');

            Route::post('/store', 'ManualNodesController@category_store')->name('category::store')->middleware('can:manual_nodes_edit');
            Route::post('/update', 'ManualNodesController@category_update')->name('category::update')->middleware('can:manual_nodes_edit');
            Route::post('/delete', 'ManualNodesController@category_delete')->name('category::delete')->middleware('can:manual_nodes_edit');

            Route::get('/view/{id}', 'ManualNodesController@view_category')->name('category::view');
            Route::post('/node/store', 'ManualNodesController@store')->name('node::store')->middleware('can:manual_nodes_edit');
            Route::post('/node/update', 'ManualNodesController@update')->name('node::update')->middleware('can:manual_nodes_edit');
            Route::post('/node/clone', 'ManualNodesController@clone')->name('node::clone')->middleware('can:manual_nodes_edit');
            Route::post('/node/delete', 'ManualNodesController@delete')->name('node::delete')->middleware('can:manual_nodes_edit');

            Route::get('ajax/get_materials', 'ManualNodesController@get_materials')->name('node::get_materials');


            /*Route::post('/select_work', 'ManualMaterialController@select_work')->name('select_work');
            Route::post('/select_attr_value', 'ManualMaterialController@select_attr_value')->name('select_attr_value');
            Route::post('/search_by_attributes', 'ManualMaterialController@search_by_attributes')->name('search_by_attributes');
            Route::post('/get_all_materials', 'ManualMaterialController@get_all_materials')->name('get_all_materials');*/
        });

        Route::group(['prefix' => 'works', 'as' => 'works::', 'middleware' => 'can:manual_works'], function () {
            Route::get('/', 'ManualWorkController@index')->name('index');
            Route::any('/card/{id}', 'ManualWorkController@card')->name('card');
            Route::get('/edit/{id}', 'ManualWorkController@edit')->name('edit')->middleware('can:manual_works_edit');
            Route::get('/type/{id}', 'ManualWorkController@type')->name('type');

            Route::post('/store', 'ManualWorkController@store')->name('store')->middleware('can:manual_works_edit');
            Route::post('/update', 'ManualWorkController@update')->name('update')->middleware('can:manual_works_edit');
            Route::post('/delete', 'ManualWorkController@delete')->name('delete')->middleware('can:works_remove');
            Route::post('/restore', 'ManualWorkController@restore')->name('restore')->middleware('can:works_remove');
            Route::post('/select_material', 'ManualWorkController@select_material')->name('select_material');

            Route::post('/get_materials', 'ManualWorkController@get_materials')->name('get_materials');
            Route::post('/get_attrs', 'ManualWorkController@get_attributes')->name('get_attributes');
            Route::post('/get_values', 'ManualWorkController@get_values')->name('get_values');
            Route::post('/search_by_attributes', 'ManualWorkController@search_by_attributes')->name('search_by_attributes');
            Route::post('/get_all_materials', 'ManualWorkController@get_all_materials')->name('get_all_materials');
        });
    });

    Route::group(['prefix' => 'project_documents', 'as' => 'project_documents::', 'namespace' => "Documents", 'middleware' => 'can:project_documents'], function () {
        Route::get('/', 'ProjectDocumentationController@index')->name('index');
        Route::get('/card/{id}/create', 'ProjectDocumentationController@create')->name('create');
        Route::get('/card/{id}', 'ProjectDocumentationController@card')->name('card');

        Route::post('/store/{id}', 'ProjectDocumentationController@store')->name('store');
        Route::post('/update', 'ProjectDocumentationController@update')->name('update');
    });

    Route::group(['prefix' => 'commercial_offers', 'as' => 'commercial_offers::', 'namespace' => "Documents", 'middleware' => 'can:commercial_offers'], function () {
        Route::any('/', 'CommercialOffersController@index')->name('index');
    });

    Route::group(['prefix' => 'work_volumes', 'as' => 'work_volumes::', 'namespace' => "Documents", 'middleware' => 'can:work_volumes'], function () {
        Route::any('/', 'WorkVolumesController@index')->name('index');
    });

    Route::group(['prefix' => 'users', 'as' => 'users::', 'namespace' => "Common", ], function () {
        Route::get('/', 'UserController@index')->name('index')->middleware('can:users');
        Route::get('/create', 'UserController@create')->name('create')->middleware('can:users_create');
        Route::get('/card/{id}', 'UserController@card')->name('card');
        Route::get('/edit/{id}', 'UserController@edit')->name('edit');
        Route::get('/get_users_for_tech_tickets', 'UserController@get_users_for_tech_tickets')->name('get_users_for_tech_tickets');
        Route::get('/get_users_for_tech_select2', 'UserController@get_users_for_tech_select2')->name('get_users_for_tech_select2');
        Route::get('/sidebar', 'UserController@sidebar')->name('sidebar');
        Route::get('/department_permissions', 'UserController@department_permissions')->name('department_permissions')->middleware('can:users_permissions');
        Route::get('/group_permissions/{department_id}', 'UserController@group_permissions')->name('group_permissions')->middleware('can:users_permissions');
        Route::get('/user_permissions/{group_id}', 'UserController@user_permissions')->name('user_permissions')->middleware('can:users_permissions');
        Route::get('/get_authors_for_defects', 'UserController@get_authors_for_defects')->name('get_authors_for_defects');
        Route::get('/get_foreman_for_brigades', 'UserController@getBrigadeForemans')->name('get_foreman_for_brigades');
        Route::get('/get_responsible_users_for_defects', 'UserController@get_responsible_users_for_defects')->name('get_responsible_users_for_defects');
        Route::get('/getUserSetting', 'UserController@getSetting')->name('get-user-setting');

        Route::post('/store', 'UserController@store')->name('store')->middleware('can:users_create');
        Route::post('/update/{id}', 'UserController@update')->name('update');
        Route::post('/department', 'UserController@department')->name('department');
        Route::post('/change_password/{id}', 'UserController@change_password')->name('change_password');
        Route::post('/to_vacation/{id}', 'UserController@to_vacation')->name('to_vacation')->middleware('can:users_vacations');
        Route::post('/from_vacation/{id}', 'UserController@from_vacation')->name('from_vacation')->middleware('can:users_vacations');
        Route::post('/remove/{id}', 'UserController@remove')->name('remove')->middleware('can:users_delete');
        Route::post('/apply', 'UserController@update_notifications')->name('update_notifications');
        Route::post('/add_permissions', 'UserController@add_permissions')->name('add_permissions')->middleware('can:users_permissions');
        Route::post('/update_job_category', 'UserController@updateJobCategory')->name('update_job_category');
        Route::post('users_paginated', 'UserController@getUsersPaginated')->name('paginated');
        Route::post('/setUserSetting', 'UserController@setSetting')->name('set-user-setting');
    });

    Route::group(['prefix' => 'notifications', 'as' => 'notifications::', 'namespace' => "Common"], function () {
        Route::get('/', 'NotificationController@index')->name('index');
        Route::post('/view', 'NotificationController@view')->name('view');
        Route::post('/view/all', 'NotificationController@view_all')->name('view_all');
        Route::post('/delete', 'NotificationController@delete')->name('delete');
        Route::get('/redirect/{encoded_url}', 'NotificationController@redirect')->name('redirect');
    });

    Route::group(['prefix' => 'document_templates', 'as' => 'document_templates::', 'namespace' => "Documents"], function () {
        Route::get('/', 'DocumentTemplateController@index')->name('index');
        Route::get('/create_offer_template', 'DocumentTemplateController@create_offer_template')->name('create_offer_template');
        Route::post('/create_offer_template/store', 'DocumentTemplateController@create_offer_template_store')->name('create_offer_template::store');
    });

    Route::group(['prefix' => 'support', 'as' => 'support::', 'namespace' => "System"], function () {
        Route::get('/', 'SupportController@index')->name('index');
        Route::post('/support_send_mail', 'SupportController@support_send_mail')->name('support_send_mail');
        Route::post('/update_ticket_async', 'SupportController@update_ticket_async')->name('update_ticket_async');

        Route::post('/update_solved_at', 'SupportController@update_solved_at')->name('update_solved_at');
        Route::post('/update_link', 'SupportController@updateLink')->name('update_link');
        Route::post('/task_agreed/{task_id}', 'SupportController@task_agreed')->name('task_agreed');
        Route::get('report', 'SupportController@report')->name('report');
    });

    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'System', 'middleware' => ['can:that_noone_can']], function() {
        Route::get('notifications', 'AdminController@admin')->name('notifications');
        Route::get('validate-material-accounting-data', 'AdminController@validateMaterialAccountingData')->name('validate-material-accounting_data');
        Route::get('get-material-accounting-data-validation-result', 'AdminController@getMaterialAccountingDataValidationResult')->name('get-material-accounting-data-validation-result');
        Route::post('/send_tech_update_notify', 'AdminController@sendTechUpdateNotify')->name('send_tech_update_notify');
        Route::post('/auth_hack', 'AdminController@loginAsUserId')->name('login_as');
    });

    Route::resource('file_entry', 'FileEntryController')
        ->only(['destroy', 'store']);

    Route::resource('comments', 'System\CommentController')
        ->only(['store', 'destroy', 'update']);

    // Route::group(['prefix' => 'versions', 'as' => 'versions::', 'namespace' => "System"], function () {
    //     Route::get('/', 'VersionController@index')->name('index');
    //     Route::post('/store', 'VersionController@edit')->name('edit');
    // });

    Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

    Route::get('/', 'Tasks\TasksController@redirect');

    Route::get('/home', 'Tasks\TasksController@redirect');

    Route::get('/error', 'Tasks\TasksController@error')->name('request_error');

    // route for bot
    Route::get('/updated-activity', 'Tasks\TasksController@updatedActivity');

    //Q3W Routing
    //Common
    Route::get('/project-objects/list', 'q3wMaterial\q3wCommonController@projectObjectsList')->name('project-objects.list');
    Route::get('/project-objects/which-participates-in-material-accounting/list', 'q3wMaterial\q3wCommonController@projectObjectsListWhichParticipatesInMaterialAccounting')->name('project-objects.which-participates-in-material-accounting.list');
    Route::get('/project-objects/material-accounting-types/lookup-list', 'q3wMaterial\q3wCommonController@projectObjectMaterialAccountingTypesLookupList')->name('material.material-accounting-types.lookup-list');
    Route::get('/contractors/list', 'q3wMaterial\q3wCommonController@contractorsList')->name('contractors.list');
    Route::get('/users/list', 'q3wMaterial\q3wCommonController@usersList')->name('users.list');
    Route::get('/users-with-material-list-access.list/list', 'q3wMaterial\q3wCommonController@usersWithMaterialListAccess')->name('users-with-material-list-access.list');

    Route::get('/material/measure-units/list', 'q3wMaterial\q3wCommonController@measureUnitsList')->name('material.measure-units.list');
    Route::get('/material/accounting-types/list', 'q3wMaterial\q3wCommonController@materialAccountingTypesList')->name('material.accounting-types.list');
    Route::get('/material/operations/routes/list', 'q3wMaterial\q3wCommonController@operationRoutesList')->name('material.operation.routes.list');
    Route::get('/material/operations/route-stages/list', 'q3wMaterial\q3wCommonController@operationRouteStagesList')->name('material.operation.route-stages.list');
    Route::get('/material/operations/route-stages-without-notifications/list', 'q3wMaterial\q3wCommonController@operationRouteStagesWithoutNotificationsList')->name('material.operation.route-stages-without-notifications.list');
    Route::get('/material/types/lookup-list', 'q3wMaterial\q3wCommonController@materialTypesLookupList')->name('material.types.lookup-list');
    Route::get('/material/transformation-types/lookup-list', 'q3wMaterial\q3wCommonController@materialTransformationTypesLookupList')->name('material.transformation-types.lookup-list');

    //Materials
    Route::get('/materials/', 'q3wMaterial\q3wMaterialController@index')->name('materials.index');
    Route::get('/materials/table', 'q3wMaterial\q3wMaterialController@table')->name('materials.table');
    Route::get('/materials/table/list', 'q3wMaterial\q3wMaterialController@materialsTableList')->name('materials.table.list');
    Route::post('/materials/table/print', 'q3wMaterial\q3wMaterialController@printMaterialsTable')->name('materials.table.print');
    Route::get('/materials/remains', 'q3wMaterial\q3wMaterialController@remains')->name('materials.remains')->middleware('can:material_accounting_material_remains_report_access');
    Route::get('/materials/remains/list', 'q3wMaterial\q3wMaterialController@materialRemainsList')->name('materials.remains.list')->middleware('can:material_accounting_material_remains_report_access');
    Route::post('/materials/remains/print', 'q3wMaterial\q3wMaterialController@exportMaterialRemains')->name('materials.remains.print')->middleware('can:material_accounting_material_remains_report_access') ;
    Route::get('/materials/list', 'q3wMaterial\q3wMaterialController@show')->name('materials.list');
    Route::get('/materials/actual/list', 'q3wMaterial\q3wMaterialController@actualProjectObjectMaterialsList')->name('materials.actual.list');
    Route::get('/materials/reserved/list/', 'q3wMaterial\q3wMaterialController@reservedMaterialsList')->name('materials.reserved.list');
    Route::get('/materials/all-with-actual-amount/list', 'q3wMaterial\q3wMaterialController@allProjectObjectMaterialsWithActualAmountList')->name('materials.all-with-actual-amount.list');
    Route::get('/materials/snapshots-materials/list', 'q3wMaterial\q3wMaterialController@snapshot')->name('materials.snapshots-materials.list');
    Route::get('/materials/snapshots/list/', 'q3wMaterial\q3wMaterialController@snapshotList')->name('materials.snapshots.list');
    Route::get('/materials/standard-history/list/', 'q3wMaterial\q3wMaterialController@standardHistoryList')->name('materials.standard-history.list');

    //Material Types
    Route::get('/materials/material-type', 'q3wMaterial\q3wMaterialTypeController@index')->name('materials.types.index')->middleware('can:material_accounting_materials_types_editing');
    Route::get('/materials/material-type/list', 'q3wMaterial\q3wMaterialTypeController@show')->name('materials.types.list'); //!!!
    Route::get('/materials/material-type/by-key', 'q3wMaterial\q3wMaterialTypeController@byKey')->name('materials.types.by-key');//!!!

    Route::put('/materials/material-type/', 'q3wMaterial\q3wMaterialTypeController@update')->name('materials.types.update')->middleware('can:material_accounting_materials_types_editing');
    Route::post('/materials/material-type/', 'q3wMaterial\q3wMaterialTypeController@store')->name('materials.types.store')->middleware('can:material_accounting_materials_types_editing');
    Route::delete('/materials/material-type/', 'q3wMaterial\q3wMaterialTypeController@delete')->name('materials.types.delete')->middleware('can:material_accounting_materials_types_editing');

    //Material Standards
    Route::get('/materials/material-standard', 'q3wMaterial\q3wMaterialStandardController@index')->name('materials.standards.index')->middleware('can:material_accounting_materials_standards_editing');
    Route::get('/materials/material-standard/list', 'q3wMaterial\q3wMaterialStandardController@show')->name('materials.standards.list');//!!!
    Route::get('/materials/material-standard/listex', 'q3wMaterial\q3wMaterialStandardController@list')->name('materials.standards.listex');//!!!
    Route::get('/materials/standard-properties/list', 'q3wMaterial\q3wMaterialStandardController@standardPropertiesList')->name('materials.standard-properties.list');
    Route::get('/materials/standard-brands/list', 'q3wMaterial\q3wMaterialStandardController@brandsList')->name('materials.brands.list');
    Route::put('/materials/material-standard/', 'q3wMaterial\q3wMaterialStandardController@update')->name('materials.standards.update')->middleware('can:material_accounting_materials_standards_editing');
    Route::post('/materials/material-standard/', 'q3wMaterial\q3wMaterialStandardController@store')->name('materials.standards.store')->middleware('can:material_accounting_materials_standards_editing');
    Route::delete('/materials/material-standard/', 'q3wMaterial\q3wMaterialStandardController@delete')->name('materials.standards.delete')->middleware('can:material_accounting_materials_standards_editing');
    Route::post('/materials/standard/incriminate-selection-counter', 'q3wMaterial\q3wMaterialStandardController@incriminateSelectionCounter')->name('materials.standard.incriminate-selection-counter');


    //Material Operations
    Route::get('/materials/operations/all', 'q3wMaterial\operations\q3wMaterialOperationController@index')->name('materials.operations.index')->middleware('can:material_accounting_operation_list_access');
    Route::get('/materials/operations/all/list', 'q3wMaterial\operations\q3wMaterialOperationController@show')->name('materials.operations.list')->middleware('can:material_accounting_operation_list_access');
    Route::get('/materials/operations/comment-history/list', 'q3wMaterial\operations\q3wMaterialOperationController@commentHistoryList')->name('materials.operations.comment-history.list')->middleware('can:material_accounting_material_list_access'); //!!!
    Route::get('/materials/operations/file-history/list', 'q3wMaterial\operations\q3wMaterialOperationController@filesHistoryList')->name('materials.operations.file-history.list')->middleware('can:material_accounting_material_list_access'); //!!!
    Route::post('/materials/operations/all/print', 'q3wMaterial\operations\q3wMaterialOperationController@print')->name('materials.operations.print')->middleware('can:material_accounting_operation_list_access'); //!!!
    Route::get('/materials/operations/all/list/active-in-project-object', 'q3wMaterial\operations\q3wMaterialOperationController@projectObjectActiveOperations')->name('materials.operations.list.project-object.active')->middleware('can:material_accounting_material_list_access'); //!!!
    Route::post('/materials/operations/upload-file', 'q3wMaterial\operations\q3wMaterialOperationController@uploadAttachedFile')->name('materials.operations.upload-file'); //!!!

    //Material supply
    Route::get('/materials/supply/new', 'q3wMaterial\operations\q3wMaterialSupplyOperationController@create')->name('materials.operations.supply.new')->middleware('can:material_accounting_operations_creating');
    Route::post('/materials/supply/new', 'q3wMaterial\operations\q3wMaterialSupplyOperationController@store')->name('materials.operations.supply.new')->middleware('can:material_accounting_operations_creating');
    Route::post('/materials/supply/new/validate-material-list', 'q3wMaterial\operations\q3wMaterialSupplyOperationController@validateMaterialList')->name('materials.operations.supply.new.validate-material-list')->middleware('can:material_accounting_operations_creating');
    Route::get('/materials/supply/completed', 'q3wMaterial\operations\q3wMaterialSupplyOperationController@completed')->name('materials.operations.supply.completed')->middleware('can:material_accounting_material_list_access');

    //Material transfer
    Route::get('/materials/transfer/new', 'q3wMaterial\operations\q3wMaterialTransferOperationController@create')->name('materials.operations.transfer.new')->middleware('can:material_accounting_operations_creating');
    Route::post('/materials/transfer/new', 'q3wMaterial\operations\q3wMaterialTransferOperationController@store')->name('materials.operations.transfer.new')->middleware('can:material_accounting_operations_creating');
    Route::post('/materials/transfer/update', 'q3wMaterial\operations\q3wMaterialTransferOperationController@update')->name('materials.operations.transfer.update')->middleware('can:material_accounting_operations_creating');
    Route::get('/materials/transfer/view', 'q3wMaterial\operations\q3wMaterialTransferOperationController@show')->name('materials.operations.transfer.view')->middleware('can:material_accounting_operations_creating');
    Route::post('/materials/transfer/move', 'q3wMaterial\operations\q3wMaterialTransferOperationController@move')->name('materials.operations.transfer.move')->middleware('can:material_accounting_operations_creating');
    Route::post('/materials/transfer/cancel', 'q3wMaterial\operations\q3wMaterialTransferOperationController@cancelOperation')->name('materials.operations.transfer.cancel')->middleware('can:material_accounting_operations_creating');
    Route::post('/materials/transfer/validate-material-list', 'q3wMaterial\operations\q3wMaterialTransferOperationController@validateMaterialList')->name('materials.operations.transfer.validate-material-list')->middleware('can:material_accounting_operations_creating');
    Route::get('/materials/transfer/completed', 'q3wMaterial\operations\q3wMaterialTransferOperationController@completed')->name('materials.operations.transfer.completed')->middleware('can:material_accounting_material_list_access');

    //Material transformation
    Route::get('/materials/transformation/new', 'q3wMaterial\operations\q3wMaterialTransformationOperationController@create')->name('materials.operations.transformation.new');
    Route::get('/materials/transformation/new2', 'q3wMaterial\operations\q3wMaterialTransformationOperationController@create')->name('materials.operations.transformation.new2'); //Small fix to develop on remote dev
    Route::get('/materials/transformation/view', 'q3wMaterial\operations\q3wMaterialTransformationOperationController@view')->name('materials.operations.transformation.view')->middleware('can:material_accounting_operations_creating');
    Route::post('/materials/transformation/new/validate-material-list', 'q3wMaterial\operations\q3wMaterialTransformationOperationController@validateMaterialList')->name('materials.operations.transformation.new.validate-material-list')->middleware('can:material_accounting_operations_creating');
    Route::post('/materials/transformation/new', 'q3wMaterial\operations\q3wMaterialTransformationOperationController@store')->name('materials.operations.transformation.new')->middleware('can:material_accounting_operations_creating');
    Route::post('/materials/transformation/cancel', 'q3wMaterial\operations\q3wMaterialTransformationOperationController@cancelOperation')->name('materials.operations.transformation.cancel')->middleware('can:material_accounting_operations_creating');
    Route::post('/materials/transformation/move', 'q3wMaterial\operations\q3wMaterialTransformationOperationController@confirmOperation')->name('materials.operations.transformation.move')->middleware('can:material_accounting_operations_creating');
    Route::get('/materials/transformation/completed', 'q3wMaterial\operations\q3wMaterialTransformationOperationController@completed')->name('materials.operations.transformation.completed')->middleware('can:material_accounting_material_list_access');
    Route::post('/materials/transformation/is-user-responsible-for-material-accounting', 'q3wMaterial\operations\q3wMaterialTransformationOperationController@isUserResponsibleForMaterialAccountingWebRequest')->name('materials.transformation.is-user-responsible-for-material-accounting')->middleware('can:material_accounting_operations_creating');

    //Material write-off
    Route::get('/materials/write-off/new', 'q3wMaterial\operations\q3wMaterialWriteOffOperationController@create')->name('materials.operations.write-off.new')->middleware('can:material_accounting_operations_creating');
    Route::get('/materials/write-off/view', 'q3wMaterial\operations\q3wMaterialWriteOffOperationController@view')->name('materials.operations.write-off.view')->middleware('can:material_accounting_operations_creating');
    Route::post('/materials/write-off/new/validate-material-list', 'q3wMaterial\operations\q3wMaterialWriteOffOperationController@validateMaterialList')->name('materials.operations.write-off.new.validate-material-list')->middleware('can:material_accounting_operations_creating');
    Route::post('/materials/write-off/new', 'q3wMaterial\operations\q3wMaterialWriteOffOperationController@store')->name('materials.operations.write-off.new')->middleware('can:material_accounting_operations_creating');
    Route::post('/materials/write-off/cancel', 'q3wMaterial\operations\q3wMaterialWriteOffOperationController@cancelOperation')->name('materials.operations.write-off.cancel')->middleware('can:material_accounting_operations_creating');
    Route::post('/materials/write-off/move', 'q3wMaterial\operations\q3wMaterialWriteOffOperationController@confirmOperation')->name('materials.operations.write-off.move')->middleware('can:material_accounting_operations_creating');
    Route::get('/materials/write-off/completed', 'q3wMaterial\operations\q3wMaterialWriteOffOperationController@completed')->name('materials.operations.write-off.completed')->middleware('can:material_accounting_material_list_access');

    //Material Supply Planning
    Route::get('/materials/supply-planning', 'q3wMaterial\q3wMaterialSupplyPlanningController@index')->name('materials.supply-planning.index')->middleware('can:material_supply_planning_access');
    Route::get('/materials/supply-planning/list', 'q3wMaterial\q3wMaterialSupplyPlanningController@list')->name('materials.supply-planning.list')->middleware('can:material_supply_planning_access');
    Route::get('/materials/supply-planning/get-materials-for-supply-planning-details', 'q3wMaterial\q3wMaterialSupplyPlanningController@getMaterialsForSupplyPlanningDetails')->name('materials.supply-planning.get-materials-for-supply-planning-details')->middleware('can:material_supply_planning_access');
    //Route::get('/materials/supply-planning/by-key', 'q3wMaterial\q3wMaterialSupplyPlanningController@byKey')->name('materials.supply-planning.by-key');//!!!
    Route::put('/materials/supply-planning/', 'q3wMaterial\q3wMaterialSupplyPlanningController@update')->name('materials.supply-planning.update')->middleware('can:material_supply_planning_editing');
    Route::post('/materials/supply-planning/', 'q3wMaterial\q3wMaterialSupplyPlanningController@store')->name('materials.supply-planning.store')->middleware('can:material_supply_planning_editing');
    Route::delete('/materials/supply-planning/', 'q3wMaterial\q3wMaterialSupplyPlanningController@delete')->name('materials.supply-planning.delete')->middleware('can:material_supply_planning_editing');

    Route::get('/materials/supply-planning/expected-delivery/list', 'q3wMaterial\q3wMaterialSupplyExpectedDeliveryController@list')->name('materials.supply-planning.expected-delivery.list')->middleware('can:material_supply_planning_access');
    Route::put('/materials/supply-planning/expected-delivery/', 'q3wMaterial\q3wMaterialSupplyExpectedDeliveryController@update')->name('materials.supply-planning.expected-delivery.update')->middleware('can:material_supply_planning_editing');
    Route::post('/materials/supply-planning/expected-delivery/', 'q3wMaterial\q3wMaterialSupplyExpectedDeliveryController@store')->name('materials.supply-planning.expected-delivery.store')->middleware('can:material_supply_planning_editing');
    Route::delete('/materials/supply-planning/expected-delivery/', 'q3wMaterial\q3wMaterialSupplyExpectedDeliveryController@delete')->name('materials.supply-planning.expected-delivery.delete')->middleware('can:material_supply_planning_editing');

    require base_path('routes/modules/labor-safety/labor-safety.php');
    require base_path('routes/modules/common/company.php');
    require base_path('routes/modules/employees/employees.php');
});


Auth::routes();
