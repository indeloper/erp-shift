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

use App\Http\Controllers\Auth;
use App\Http\Controllers\Building;
use App\Http\Controllers\Commerce;
use App\Http\Controllers\Commerce\Project\ProjectObjectController;
use App\Http\Controllers\Common;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Documents;
use App\Http\Controllers\FileEntryController;
use App\Http\Controllers\q3wMaterial;
use App\Http\Controllers\System;
use App\Http\Controllers\Tasks;
use Illuminate\Support\Facades\Route;

Route::middleware('activeuser', 'auth')->group(function () {
    Route::get('/contacts', [ContactController::class, 'index'])
        ->name('contacts');

    Route::get('storage/{file_path?}', [System\FileController::class, 'file'])
        ->where('file_path', '.*');

    Route::get('csrf/get-new', [System\SystemController::class, 'refreshCsrf'])
        ->name('get-new-csrf');

    Route::any('contracts', [Documents\ContractsController::class, 'index'])
        ->name('contracts::index')->middleware('can:contracts');
    Route::post('contracts_filtered',
        [Documents\ContractsController::class, 'contractsFiltered'])
        ->name('contracts::filtered')->middleware('can:contracts');
    Route::any('contracts/get_contracts',
        [Documents\ContractsController::class, 'get_contracts'])
        ->name('contracts::get_contracts');

    Route::prefix('objects')->name('objects::')->middleware('can:objects')
        ->group(function () {
            Route::get('/',
                [Commerce\ObjectController::class, 'returnPageCore'])
                ->name('base-template');

            Route::get('/index', [Commerce\ObjectController::class, 'index'])
                ->name('index');
            Route::post('/store', [Commerce\ObjectController::class, 'store'])
                ->name('store')->middleware('can:objects_create');
            Route::put('/{id}',
                [Commerce\ObjectController::class, 'update'])->name('update')
                ->middleware('can:objects_edit');

            Route::get('/getMaterialAccountingTypes', [
                Commerce\ObjectController::class, 'getMaterialAccountingTypes',
            ])->name('getMaterialAccountingTypes::index');

            Route::get('/{id}', [
                ProjectObjectController::class, 'show',
            ])->name('getMaterialAccountingTypes');

            Route::get('/getMaterialAccountingTypes/{id}', [
                Commerce\ObjectController::class,
                'getMaterialAccountingTypesItem',
            ])->name('get-material-accounting-types-item');
            Route::get('/getObjectInfoByID',
                [Commerce\ObjectController::class, 'getObjectInfoByID'])
                ->name('getObjectInfoByID');
            Route::get('/getPermissions',
                [Commerce\ObjectController::class, 'getPermissions'])
                ->name('getPermissions');
            Route::get('/get_contractors',
                [Commerce\ObjectController::class, 'get_contractors'])
                ->name('get_contractors');
            Route::get('/get_object_projects',
                [Commerce\ObjectController::class, 'get_object_projects'])
                ->name('get_object_projects');
        });
    Route::post('/get_objects',
        [Commerce\ObjectController::class, 'getObjects'])
        ->name('objects::get_objects');

    Route::get('tasks/filter-tasks-report',
        [Tasks\TasksController::class, 'showTasksReportFilterForm'])
        ->name('tasks.filter-tasks-report')
        ->middleware('can:commercial_block_task_report_xlsx_export_access');
    Route::post('tasks/download-tasks-report',
        [Tasks\TasksController::class, 'downloadTasksReport'])
        ->name('tasks.download-tasks-report')
        ->middleware('can:commercial_block_task_report_xlsx_export_access');
    Route::get('tasks/current-user-tasks-project-objects-list',
        [Tasks\TasksController::class, 'currentUserTasksProjectObjectsList'])
        ->name('tasks.current-user-tasks-project-objects.list');
    Route::get('tasks/current-user-tasks-contractors-list',
        [Tasks\TasksController::class, 'currentUserTasksContractorsList'])
        ->name('tasks.current-user-tasks-contractors.list');
    Route::get('tasks/current-user-tasks-split-material-list',
        [Tasks\TasksController::class, 'currentUserTasksSplitMaterialList'])
        ->name('tasks.current-user-tasks-split-material.list');

    Route::prefix('tasks')->name('tasks::')->group(function () {
        Route::get('/', [Tasks\TasksController::class, 'index'])->name('index');
        Route::get('/card/{id}', [Tasks\TasksController::class, 'card'])
            ->name('card');
        Route::get('search_projects',
            [Tasks\TasksController::class, 'searchProjects'])
            ->name('search_projects');

        Route::get('/get-users', [Tasks\TasksController::class, 'get_users'])
            ->name('get_users');
        Route::get('/get-contractors',
            [Tasks\TasksController::class, 'get_contractors'])
            ->name('get_contractors');
        Route::get('/get-projects',
            [Tasks\TasksController::class, 'get_projects'])
            ->name('get_projects');
        Route::post('/store', [Tasks\TasksController::class, 'store'])
            ->name('store')
            ->middleware('can:tasks_default_myself,tasks_default_others');
        Route::post('/refresh', [Tasks\TasksController::class, 'refresh'])
            ->name('refresh');
        Route::post('/make_viewed',
            [Tasks\TasksController::class, 'make_viewed'])->name('make_viewed');

        Route::get('/get-responsible-user/{id}',
            [Tasks\TasksController::class, 'get_responsible_users'])
            ->name('get_responsible_users');

        Route::post('/store', [Tasks\TasksController::class, 'store'])
            ->name('store');
        Route::post('/solve/{id}', [Tasks\TasksController::class, 'solve'])
            ->name('solve');
        Route::post('/update-resp-user/{id}',
            [Tasks\TasksController::class, 'update_resp_user'])
            ->name('update_resp_user');
        Route::post('choose_contractor',
            [Tasks\TaskCallController::class, 'choose_contractor'])
            ->name('choose_contractor');
        Route::post('choose_contact',
            [Tasks\TaskCallController::class, 'choose_contact'])
            ->name('choose_contact');
        Route::get('ajax/get_contacts/{contractor_id}',
            [Tasks\TaskCallController::class, 'get_contacts']);

        Route::get('/new_call/{id}',
            [Tasks\TaskCallController::class, 'new_call'])->name('new_call');
        Route::post('/close_call/{id}',
            [Tasks\TaskCallController::class, 'close_call'])
            ->name('close_call');

        Route::post('/postpone/{id}',
            [Tasks\TaskCommerceController::class, 'postpone'])
            ->name('postpone');
        Route::get('/common_task/{id}',
            [Tasks\TaskCommerceController::class, 'common_task'])
            ->name('common_task');
        Route::get('/usual/{id}',
            [Tasks\TaskCommerceController::class, 'slimTask'])
            ->name('slim_task');

        Route::any('/common_task/{id}/solve_task',
            [Tasks\TaskCommerceController::class, 'solve_task'])
            ->name('solve_task');

        Route::get('/tech_task/{id}',
            [Tasks\TechAccTasksController::class, 'tech_task'])
            ->name('tech_task');
        Route::get('/partial_36/{task}',
            [Tasks\TechAccTasksController::class, 'partial_36'])
            ->name('partial_36');

        Route::get('/make-test-call/{id}',
            [Tasks\TaskCallController::class, 'makeTestCall']);
        Route::post('decline_request',
            [Tasks\TaskCommerceController::class, 'declineRequest'])
            ->name('decline_request');
    });

    Route::prefix('building')->name('building::')->group(function () {
        Route::prefix('materials')->name('materials::')
            ->middleware('can:manual_materials')->group(function () {
                Route::get('/',
                    [Building\ManualMaterialCategoryController::class, 'index'])
                    ->name('index');

                Route::post('/store',
                    [Building\ManualMaterialCategoryController::class, 'store'])
                    ->name('category::store')
                    ->middleware('can:manual_materials_edit');
                Route::post('/update',
                    [
                        Building\ManualMaterialCategoryController::class,
                        'update',
                    ])
                    ->name('category::update')
                    ->middleware('can:manual_materials_edit');
                Route::post('/delete',
                    [
                        Building\ManualMaterialCategoryController::class,
                        'delete',
                    ])
                    ->name('category::delete')
                    ->middleware('can:manual_materials_edit');
                Route::post('/clone',
                    [Building\ManualMaterialCategoryController::class, 'clone'])
                    ->name('category::clone')
                    ->middleware('can:manual_materials_edit');
                // api get need attrs to fill them next
                Route::post('/get_need_attrs', [
                    Building\ManualMaterialCategoryController::class,
                    'getNeedAttributes',
                ])->name('category::get_need_attrs');
                Route::post('/get_need_attrs_values', [
                    Building\ManualMaterialCategoryController::class,
                    'getNeedAttributesValues',
                ])->name('category::get_need_attrs_values');

                Route::get('/card/{id}',
                    [Building\ManualMaterialController::class, 'card'])
                    ->name('card');
                Route::post('/card/{id}/store',
                    [Building\ManualMaterialController::class, 'store'])
                    ->name('store')->middleware('can:manual_materials_edit');
                Route::post('/card/{id}/update',
                    [Building\ManualMaterialController::class, 'update'])
                    ->name('update')->middleware('can:manual_materials_edit');
                Route::post('/card/{id}/clone',
                    [Building\ManualMaterialController::class, 'clone'])
                    ->name('clone')->middleware('can:manual_materials_edit');
                Route::post('/card/delete',
                    [Building\ManualMaterialController::class, 'delete'])
                    ->name('delete')->middleware('can:materials_remove');
                Route::post('/card/restore',
                    [Building\ManualMaterialController::class, 'restore'])
                    ->name('restore')->middleware('can:materials_remove');

                Route::post('/select_work',
                    [Building\ManualMaterialController::class, 'select_work'])
                    ->name('select_work');
                Route::post('/select_attr_value',
                    [
                        Building\ManualMaterialController::class,
                        'select_attr_value',
                    ])
                    ->name('select_attr_value');
                Route::post('/search_by_attributes', [
                    Building\ManualMaterialController::class,
                    'search_by_attributes',
                ])->name('search_by_attributes');
                Route::post('/get_all_materials',
                    [
                        Building\ManualMaterialController::class,
                        'get_all_materials',
                    ])
                    ->name('get_all_materials');
                Route::get('/get_references',
                    [Building\ManualMaterialController::class, 'getReferences'])
                    ->name('get_references');
            });

        Route::prefix('nodes')->name('nodes::')->middleware('can:manual_nodes')
            ->group(function () {
                Route::get('/',
                    [Building\ManualNodesController::class, 'index'])
                    ->name('index');

                Route::post('/store',
                    [Building\ManualNodesController::class, 'category_store'])
                    ->name('category::store')
                    ->middleware('can:manual_nodes_edit');
                Route::post('/update',
                    [Building\ManualNodesController::class, 'category_update'])
                    ->name('category::update')
                    ->middleware('can:manual_nodes_edit');
                Route::post('/delete',
                    [Building\ManualNodesController::class, 'category_delete'])
                    ->name('category::delete')
                    ->middleware('can:manual_nodes_edit');

                Route::get('/view/{id}',
                    [Building\ManualNodesController::class, 'view_category'])
                    ->name('category::view');
                Route::post('/node/store',
                    [Building\ManualNodesController::class, 'store'])
                    ->name('node::store')->middleware('can:manual_nodes_edit');
                Route::post('/node/update',
                    [Building\ManualNodesController::class, 'update'])
                    ->name('node::update')->middleware('can:manual_nodes_edit');
                Route::post('/node/clone',
                    [Building\ManualNodesController::class, 'clone'])
                    ->name('node::clone')->middleware('can:manual_nodes_edit');
                Route::post('/node/delete',
                    [Building\ManualNodesController::class, 'delete'])
                    ->name('node::delete')->middleware('can:manual_nodes_edit');

                Route::get('ajax/get_materials',
                    [Building\ManualNodesController::class, 'get_materials'])
                    ->name('node::get_materials');
                /*Route::post('/select_work', 'ManualMaterialController@select_work')->name('select_work');
                Route::post('/select_attr_value', 'ManualMaterialController@select_attr_value')->name('select_attr_value');
                Route::post('/search_by_attributes', 'ManualMaterialController@search_by_attributes')->name('search_by_attributes');
                Route::post('/get_all_materials', 'ManualMaterialController@get_all_materials')->name('get_all_materials');*/
            });

        Route::prefix('works')->name('works::')->middleware('can:manual_works')
            ->group(function () {
                Route::get('/', [Building\ManualWorkController::class, 'index'])
                    ->name('index');
                Route::any('/card/{id}',
                    [Building\ManualWorkController::class, 'card'])
                    ->name('card');
                Route::get('/edit/{id}',
                    [Building\ManualWorkController::class, 'edit'])
                    ->name('edit')->middleware('can:manual_works_edit');
                Route::get('/type/{id}',
                    [Building\ManualWorkController::class, 'type'])
                    ->name('type');

                Route::post('/store',
                    [Building\ManualWorkController::class, 'store'])
                    ->name('store')->middleware('can:manual_works_edit');
                Route::post('/update',
                    [Building\ManualWorkController::class, 'update'])
                    ->name('update')->middleware('can:manual_works_edit');
                Route::post('/delete',
                    [Building\ManualWorkController::class, 'delete'])
                    ->name('delete')->middleware('can:works_remove');
                Route::post('/restore',
                    [Building\ManualWorkController::class, 'restore'])
                    ->name('restore')->middleware('can:works_remove');
                Route::post('/select_material',
                    [Building\ManualWorkController::class, 'select_material'])
                    ->name('select_material');

                Route::post('/get_materials',
                    [Building\ManualWorkController::class, 'get_materials'])
                    ->name('get_materials');
                Route::post('/get_attrs',
                    [Building\ManualWorkController::class, 'get_attributes'])
                    ->name('get_attributes');
                Route::post('/get_values',
                    [Building\ManualWorkController::class, 'get_values'])
                    ->name('get_values');
                Route::post('/search_by_attributes', [
                    Building\ManualWorkController::class,
                    'search_by_attributes',
                ])->name('search_by_attributes');
                Route::post('/get_all_materials',
                    [Building\ManualWorkController::class, 'get_all_materials'])
                    ->name('get_all_materials');
            });
    });

    Route::prefix('project_documents')->name('project_documents::')
        ->middleware('can:project_documents')->group(function () {
            Route::get('/',
                [Documents\ProjectDocumentationController::class, 'index'])
                ->name('index');
            Route::get('/card/{id}/create',
                [Documents\ProjectDocumentationController::class, 'create'])
                ->name('create');
            Route::get('/card/{id}',
                [Documents\ProjectDocumentationController::class, 'card'])
                ->name('card');

            Route::post('/store/{id}',
                [Documents\ProjectDocumentationController::class, 'store'])
                ->name('store');
            Route::post('/update',
                [Documents\ProjectDocumentationController::class, 'update'])
                ->name('update');
        });

    Route::prefix('commercial_offers')->name('commercial_offers::')
        ->middleware('can:commercial_offers')->group(function () {
            Route::any('/',
                [Documents\CommercialOffersController::class, 'index'])
                ->name('index');
        });

    Route::prefix('work_volumes')->name('work_volumes::')
        ->middleware('can:work_volumes')->group(function () {
            Route::any('/', [Documents\WorkVolumesController::class, 'index'])
                ->name('index');
        });

    Route::prefix('users')->name('users::')->group(function () {
        Route::get('/', [Common\UserController::class, 'index'])->name('index')
            ->middleware('can:users');
        Route::get('/create', [Common\UserController::class, 'create'])
            ->name('create')->middleware('can:users_create');
        Route::get('/card/{id}', [Common\UserController::class, 'card'])
            ->name('card');
        Route::get('/edit/{id}', [Common\UserController::class, 'edit'])
            ->name('edit');
        Route::get('/get_users_for_tech_tickets',
            [Common\UserController::class, 'get_users_for_tech_tickets'])
            ->name('get_users_for_tech_tickets');
        Route::get('/get_users_for_tech_select2',
            [Common\UserController::class, 'get_users_for_tech_select2'])
            ->name('get_users_for_tech_select2');
        Route::get('/sidebar', [Common\UserController::class, 'sidebar'])
            ->name('sidebar');
        Route::get('/department_permissions',
            [Common\UserController::class, 'department_permissions'])
            ->name('department_permissions')
            ->middleware('can:users_permissions');
        Route::get('/group_permissions/{department_id}',
            [Common\UserController::class, 'group_permissions'])
            ->name('group_permissions')->middleware('can:users_permissions');
        Route::get('/user_permissions/{group_id}',
            [Common\UserController::class, 'user_permissions'])
            ->name('user_permissions')->middleware('can:users_permissions');
        Route::get('/get_authors_for_defects',
            [Common\UserController::class, 'get_authors_for_defects'])
            ->name('get_authors_for_defects');
        Route::get('/get_responsible_users_for_defects',
            [Common\UserController::class, 'get_responsible_users_for_defects'])
            ->name('get_responsible_users_for_defects');
        Route::get('/getUserSetting',
            [Common\UserController::class, 'getSetting'])
            ->name('get-user-setting');
        Route::get('/getAvailableUsersForReplaceEmployeeDuringVacation', [
            Common\UserController::class,
            'getAvailableUsersForReplaceEmployeeDuringVacation',
        ])->name('getAvailableUsersForReplaceEmployeeDuringVacation');

        Route::post('/store', [Common\UserController::class, 'store'])
            ->name('store')->middleware('can:users_create');
        Route::post('/update/{id}', [Common\UserController::class, 'update'])
            ->name('update');
        Route::post('/department', [Common\UserController::class, 'department'])
            ->name('department');
        Route::post('/change_password/{id}',
            [Common\UserController::class, 'change_password'])
            ->name('change_password');
        Route::post('/to_vacation/{id}',
            [Common\UserController::class, 'to_vacation'])->name('to_vacation')
            ->middleware('can:users_vacations');
        Route::post('/from_vacation/{id}',
            [Common\UserController::class, 'from_vacation'])
            ->name('from_vacation')->middleware('can:users_vacations');
        Route::post('/remove/{id}', [Common\UserController::class, 'remove'])
            ->name('remove')->middleware('can:users_delete');
        Route::post('/apply',
            [Common\UserController::class, 'update_notifications'])
            ->name('update_notifications');
        Route::post('/add_permissions',
            [Common\UserController::class, 'add_permissions'])
            ->name('add_permissions')->middleware('can:users_permissions');
        Route::post('users_paginated',
            [Common\UserController::class, 'getUsersPaginated'])
            ->name('paginated');
        Route::post('/setUserSetting',
            [Common\UserController::class, 'setSetting'])
            ->name('set-user-setting');
    });

    Route::prefix('document_templates')->name('document_templates::')
        ->group(function () {
            Route::get('/',
                [Documents\DocumentTemplateController::class, 'index'])
                ->name('index');
            Route::get('/create_offer_template', [
                Documents\DocumentTemplateController::class,
                'create_offer_template',
            ])->name('create_offer_template');
            Route::post('/create_offer_template/store', [
                Documents\DocumentTemplateController::class,
                'create_offer_template_store',
            ])->name('create_offer_template::store');
        });

    Route::prefix('support')->name('support::')->group(function () {
        Route::get('/', [System\SupportController::class, 'index'])
            ->name('index');
        Route::post('/support_send_mail',
            [System\SupportController::class, 'support_send_mail'])
            ->name('support_send_mail');
        Route::post('/update_ticket_async',
            [System\SupportController::class, 'update_ticket_async'])
            ->name('update_ticket_async');

        Route::post('/update_solved_at',
            [System\SupportController::class, 'update_solved_at'])
            ->name('update_solved_at');
        Route::post('/update_link',
            [System\SupportController::class, 'updateLink'])
            ->name('update_link');
        Route::post('/task_agreed/{task_id}',
            [System\SupportController::class, 'task_agreed'])
            ->name('task_agreed');
        Route::get('report', [System\SupportController::class, 'report'])
            ->name('report');
    });

    Route::prefix('admin')->name('admin.')->middleware('can:that_noone_can')
        ->group(function () {
            Route::get('notifications',
                [System\AdminController::class, 'admin'])
                ->name('notifications');
            Route::get('validate-material-accounting-data', [
                System\AdminController::class, 'validateMaterialAccountingData',
            ])->name('validate-material-accounting_data');
            Route::get('get-material-accounting-data-validation-result', [
                System\AdminController::class,
                'getMaterialAccountingDataValidationResult',
            ])->name('get-material-accounting-data-validation-result');
            Route::post('/send_tech_update_notify',
                [System\AdminController::class, 'sendTechUpdateNotify'])
                ->name('send_tech_update_notify');
            Route::post('/auth_hack',
                [System\AdminController::class, 'loginAsUserId'])
                ->name('login_as');

            Route::view('/permissions', 'admin.permissions')
                ->name('permissions');
            Route::get('/permission/categories',
                [System\PermissionsController::class, 'getCategories'])
                ->name('permission.categories');
            Route::apiResource('permission',
                System\PermissionsController::class);
        });

    Route::post('file_entry/downloadAttachments',
        [FileEntryController::class, 'downloadAttachments'])
        ->name('fileEntry.downloadAttachments');
    Route::resource('file_entry', FileEntryController::class)
        ->only(['destroy', 'store']);

    Route::resource('comments', System\CommentController::class)
        ->only(['store', 'destroy', 'update']);

    // Route::group(['prefix' => 'versions', 'as' => 'versions::', 'namespace' => "System"], function () {
    //     Route::get('/', 'VersionController@index')->name('index');
    //     Route::post('/store', 'VersionController@edit')->name('edit');
    // });

    Route::get('/logout', [Auth\LoginController::class, 'logout'])
        ->name('logout');

    Route::get('/', [Tasks\TasksController::class, 'redirect']);

    Route::get('/home', [Tasks\TasksController::class, 'redirect']);

    Route::get('/error', [Tasks\TasksController::class, 'error'])
        ->name('request_error');

    // route for bot
    Route::get('/updated-activity',
        [Tasks\TasksController::class, 'updatedActivity']);

    //Q3W Routing
    //Common
    Route::get('/project-objects/list',
        [q3wMaterial\q3wCommonController::class, 'projectObjectsList'])
        ->name('project-objects.list');
    Route::get('/project-objects/which-participates-in-material-accounting/list',
        [
            q3wMaterial\q3wCommonController::class,
            'projectObjectsListWhichParticipatesInMaterialAccounting',
        ])
        ->name('project-objects.which-participates-in-material-accounting.list');
    Route::get('/project-objects/material-accounting-types/lookup-list', [
        q3wMaterial\q3wCommonController::class,
        'projectObjectMaterialAccountingTypesLookupList',
    ])->name('material.material-accounting-types.lookup-list');
    Route::get('/contractors/list',
        [q3wMaterial\q3wCommonController::class, 'contractorsList'])
        ->name('contractors.list');
    Route::get('/users/list',
        [q3wMaterial\q3wCommonController::class, 'usersList'])
        ->name('users.list');
    Route::get('/users-with-material-list-access.list/list',
        [q3wMaterial\q3wCommonController::class, 'usersWithMaterialListAccess'])
        ->name('users-with-material-list-access.list');

    Route::get('/material/measure-units/list',
        [q3wMaterial\q3wCommonController::class, 'measureUnitsList'])
        ->name('material.measure-units.list');
    Route::get('/material/accounting-types/list',
        [q3wMaterial\q3wCommonController::class, 'materialAccountingTypesList'])
        ->name('material.accounting-types.list');
    Route::get('/material/operations/routes/list',
        [q3wMaterial\q3wCommonController::class, 'operationRoutesList'])
        ->name('material.operation.routes.list');
    Route::get('/material/operations/route-stages/list',
        [q3wMaterial\q3wCommonController::class, 'operationRouteStagesList'])
        ->name('material.operation.route-stages.list');
    Route::get('/material/operations/route-stages-without-notifications/list', [
        q3wMaterial\q3wCommonController::class,
        'operationRouteStagesWithoutNotificationsList',
    ])->name('material.operation.route-stages-without-notifications.list');
    Route::get('/material/types/lookup-list',
        [q3wMaterial\q3wCommonController::class, 'materialTypesLookupList'])
        ->name('material.types.lookup-list');
    Route::get('/material/transformation-types/lookup-list', [
        q3wMaterial\q3wCommonController::class,
        'materialTransformationTypesLookupList',
    ])->name('material.transformation-types.lookup-list');

    //Materials
    Route::get('/strmaterials/',
        [q3wMaterial\q3wMaterialController::class, 'index'])
        ->name('materials.index');
    Route::get('/strmaterials/table',
        [q3wMaterial\q3wMaterialController::class, 'table'])
        ->name('materials.table');
    Route::get('/strmaterials/table/list',
        [q3wMaterial\q3wMaterialController::class, 'materialsTableList'])
        ->name('materials.table.list');
    Route::post('/strmaterials/table/print',
        [q3wMaterial\q3wMaterialController::class, 'printMaterialsTable'])
        ->name('materials.table.print');
    Route::get('/strmaterials/remains',
        [q3wMaterial\q3wMaterialController::class, 'remains'])
        ->name('materials.remains')
        ->middleware('can:material_accounting_material_remains_report_access');
    Route::get('/strmaterials/remains/list',
        [q3wMaterial\q3wMaterialController::class, 'materialRemainsList'])
        ->name('materials.remains.list')
        ->middleware('can:material_accounting_material_remains_report_access');
    Route::post('/strmaterials/remains/print',
        [q3wMaterial\q3wMaterialController::class, 'exportMaterialRemains'])
        ->name('materials.remains.print')
        ->middleware('can:material_accounting_material_remains_report_access');
    Route::get('/strmaterials/obj-remains',
        [q3wMaterial\q3wMaterialController::class, 'objectsRemains'])
        ->name('materials.objects.remains')
        ->middleware('can:material_accounting_objects_remains_report_access');
    Route::get('/strmaterials/obj-remains/list',
        [q3wMaterial\q3wMaterialController::class, 'objectsRemainsList'])
        ->name('materials.objects.remains.list')
        ->middleware('can:material_accounting_objects_remains_report_access');
    Route::post('/strmaterials/obj-remains/print',
        [q3wMaterial\q3wMaterialController::class, 'exportObjectsRemains'])
        ->name('materials.objects.remains.print')
        ->middleware('can:material_accounting_objects_remains_report_access');
    Route::get('/strmaterials/list',
        [q3wMaterial\q3wMaterialController::class, 'show'])
        ->name('materials.list');
    Route::get('/strmaterials/actual/list', [
        q3wMaterial\q3wMaterialController::class,
        'actualProjectObjectMaterialsList',
    ])->name('materials.actual.list');
    Route::get('/strmaterials/reserved/list/',
        [q3wMaterial\q3wMaterialController::class, 'reservedMaterialsList'])
        ->name('materials.reserved.list');
    Route::get('/strmaterials/all-with-actual-amount/list', [
        q3wMaterial\q3wMaterialController::class,
        'allProjectObjectMaterialsWithActualAmountList',
    ])->name('materials.all-with-actual-amount.list');
    Route::get('/strmaterials/snapshots-materials/list',
        [q3wMaterial\q3wMaterialController::class, 'snapshot'])
        ->name('materials.snapshots-materials.list');
    Route::get('/strmaterials/snapshots/list/',
        [q3wMaterial\q3wMaterialController::class, 'snapshotList'])
        ->name('materials.snapshots.list');
    Route::get('/strmaterials/standard-history/list/',
        [q3wMaterial\q3wMaterialController::class, 'standardHistoryList'])
        ->name('materials.standard-history.list');

    //Material Types
    Route::get('/strmaterials/material-type',
        [q3wMaterial\q3wMaterialTypeController::class, 'index'])
        ->name('materials.types.index')
        ->middleware('can:material_accounting_materials_types_editing');
    Route::get('/strmaterials/material-type/list',
        [q3wMaterial\q3wMaterialTypeController::class, 'show'])
        ->name('materials.types.list'); //!!!
    Route::get('/strmaterials/material-type/by-key',
        [q3wMaterial\q3wMaterialTypeController::class, 'byKey'])
        ->name('materials.types.by-key'); //!!!

    Route::put('/strmaterials/material-type/',
        [q3wMaterial\q3wMaterialTypeController::class, 'update'])
        ->name('materials.types.update')
        ->middleware('can:material_accounting_materials_types_editing');
    Route::post('/strmaterials/material-type/',
        [q3wMaterial\q3wMaterialTypeController::class, 'store'])
        ->name('materials.types.store')
        ->middleware('can:material_accounting_materials_types_editing');
    Route::delete('/strmaterials/material-type/',
        [q3wMaterial\q3wMaterialTypeController::class, 'delete'])
        ->name('materials.types.delete')
        ->middleware('can:material_accounting_materials_types_editing');

    //Material Standards
    Route::get('/strmaterials/material-standard',
        [q3wMaterial\q3wMaterialStandardController::class, 'index'])
        ->name('materials.standards.index')
        ->middleware('can:material_accounting_materials_standards_editing');
    Route::get('/strmaterials/material-standard/list',
        [q3wMaterial\q3wMaterialStandardController::class, 'show'])
        ->name('materials.standards.list'); //!!!
    Route::get('/strmaterials/material-standard/listex',
        [q3wMaterial\q3wMaterialStandardController::class, 'list'])
        ->name('materials.standards.listex'); //!!!
    Route::get('/strmaterials/standard-properties/list', [
        q3wMaterial\q3wMaterialStandardController::class,
        'standardPropertiesList',
    ])->name('materials.standard-properties.list');
    Route::get('/strmaterials/standard-brand-types/list',
        [q3wMaterial\q3wMaterialStandardController::class, 'brandTypesList'])
        ->name('materials.brand-types.list');
    Route::get('/strmaterials/standard-brands/list',
        [q3wMaterial\q3wMaterialStandardController::class, 'brandsList'])
        ->name('materials.brands.list');
    Route::put('/strmaterials/material-standard/',
        [q3wMaterial\q3wMaterialStandardController::class, 'update'])
        ->name('materials.standards.update')
        ->middleware('can:material_accounting_materials_standards_editing');
    Route::post('/strmaterials/material-standard/',
        [q3wMaterial\q3wMaterialStandardController::class, 'store'])
        ->name('materials.standards.store')
        ->middleware('can:material_accounting_materials_standards_editing');
    Route::delete('/strmaterials/material-standard/',
        [q3wMaterial\q3wMaterialStandardController::class, 'delete'])
        ->name('materials.standards.delete')
        ->middleware('can:material_accounting_materials_standards_editing');
    Route::post('/strmaterials/standard/incriminate-selection-counter', [
        q3wMaterial\q3wMaterialStandardController::class,
        'incriminateSelectionCounter',
    ])->name('materials.standard.incriminate-selection-counter');

    //Material Operations
    Route::get('/strmaterials/operations/all',
        [q3wMaterial\operations\q3wMaterialOperationController::class, 'index'])
        ->name('materials.operations.index')
        ->middleware('can:material_accounting_operation_list_access');
    Route::get('/strmaterials/operations/all/list',
        [q3wMaterial\operations\q3wMaterialOperationController::class, 'show'])
        ->name('materials.operations.list')
        ->middleware('can:material_accounting_operation_list_access');
    Route::get('/strmaterials/operations/comment-history/list', [
        q3wMaterial\operations\q3wMaterialOperationController::class,
        'commentHistoryList',
    ])->name('materials.operations.comment-history.list')
        ->middleware('can:material_accounting_material_list_access'); //!!!
    Route::get('/strmaterials/operations/file-history/list', [
        q3wMaterial\operations\q3wMaterialOperationController::class,
        'filesHistoryList',
    ])->name('materials.operations.file-history.list')
        ->middleware('can:material_accounting_material_list_access'); //!!!
    Route::post('/strmaterials/operations/all/print',
        [q3wMaterial\operations\q3wMaterialOperationController::class, 'print'])
        ->name('materials.operations.print')
        ->middleware('can:material_accounting_operation_list_access'); //!!!
    Route::get('/strmaterials/operations/all/list/active-in-project-object', [
        q3wMaterial\operations\q3wMaterialOperationController::class,
        'projectObjectActiveOperations',
    ])->name('materials.operations.list.project-object.active')
        ->middleware('can:material_accounting_material_list_access'); //!!!
    Route::post('/strmaterials/operations/upload-file', [
        q3wMaterial\operations\q3wMaterialOperationController::class,
        'uploadAttachedFile',
    ])->name('materials.operations.upload-file'); //!!!

    //Material supply
    Route::get('/strmaterials/supply/new', [
        q3wMaterial\operations\q3wMaterialSupplyOperationController::class,
        'create',
    ])->name('materials.operations.supply.new')
        ->middleware('can:material_accounting_operations_creating');
    Route::post('/strmaterials/supply/new', [
        q3wMaterial\operations\q3wMaterialSupplyOperationController::class,
        'store',
    ])->name('materials.operations.supply.new')
        ->middleware('can:material_accounting_operations_creating');
    Route::post('/strmaterials/supply/new/validate-material-list', [
        q3wMaterial\operations\q3wMaterialSupplyOperationController::class,
        'validateMaterialList',
    ])->name('materials.operations.supply.new.validate-material-list')
        ->middleware('can:material_accounting_operations_creating');
    Route::get('/strmaterials/supply/completed', [
        q3wMaterial\operations\q3wMaterialSupplyOperationController::class,
        'completed',
    ])->name('materials.operations.supply.completed')
        ->middleware('can:material_accounting_material_list_access');

    //Material transfer
    Route::get('/strmaterials/transfer/new', [
        q3wMaterial\operations\q3wMaterialTransferOperationController::class,
        'create',
    ])->name('materials.operations.transfer.new')
        ->middleware('can:material_accounting_operations_creating');
    Route::post('/strmaterials/transfer/new', [
        q3wMaterial\operations\q3wMaterialTransferOperationController::class,
        'store',
    ])->name('materials.operations.transfer.new')
        ->middleware('can:material_accounting_operations_creating');
    Route::post('/strmaterials/transfer/update', [
        q3wMaterial\operations\q3wMaterialTransferOperationController::class,
        'update',
    ])->name('materials.operations.transfer.update')
        ->middleware('can:material_accounting_operations_creating');
    Route::get('/strmaterials/transfer/view', [
        q3wMaterial\operations\q3wMaterialTransferOperationController::class,
        'show',
    ])->name('materials.operations.transfer.view')
        ->middleware('can:material_accounting_operations_creating');
    Route::post('/strmaterials/transfer/move', [
        q3wMaterial\operations\q3wMaterialTransferOperationController::class,
        'move',
    ])->name('materials.operations.transfer.move')
        ->middleware('can:material_accounting_operations_creating');
    Route::post('/strmaterials/transfer/cancel', [
        q3wMaterial\operations\q3wMaterialTransferOperationController::class,
        'cancelOperation',
    ])->name('materials.operations.transfer.cancel')
        ->middleware('can:material_accounting_operations_creating');
    Route::post('/strmaterials/transfer/validate-material-list', [
        q3wMaterial\operations\q3wMaterialTransferOperationController::class,
        'validateMaterialList',
    ])->name('materials.operations.transfer.validate-material-list')
        ->middleware('can:material_accounting_operations_creating');
    Route::get('/strmaterials/transfer/completed', [
        q3wMaterial\operations\q3wMaterialTransferOperationController::class,
        'completed',
    ])->name('materials.operations.transfer.completed')
        ->middleware('can:material_accounting_material_list_access');

    //Material transformation
    Route::get('/strmaterials/transformation/new', [
        q3wMaterial\operations\q3wMaterialTransformationOperationController::class,
        'create',
    ])->name('materials.operations.transformation.new');
    Route::get('/strmaterials/transformation/new2', [
        q3wMaterial\operations\q3wMaterialTransformationOperationController::class,
        'create',
    ])
        ->name('materials.operations.transformation.new2'); //Small fix to develop on remote dev
    Route::get('/strmaterials/transformation/view', [
        q3wMaterial\operations\q3wMaterialTransformationOperationController::class,
        'view',
    ])->name('materials.operations.transformation.view')
        ->middleware('can:material_accounting_operations_creating');
    Route::post('/strmaterials/transformation/new/validate-material-list', [
        q3wMaterial\operations\q3wMaterialTransformationOperationController::class,
        'validateMaterialList',
    ])->name('materials.operations.transformation.new.validate-material-list')
        ->middleware('can:material_accounting_operations_creating');
    Route::post('/strmaterials/transformation/new', [
        q3wMaterial\operations\q3wMaterialTransformationOperationController::class,
        'store',
    ])->name('materials.operations.transformation.new')
        ->middleware('can:material_accounting_operations_creating');
    Route::post('/strmaterials/transformation/cancel', [
        q3wMaterial\operations\q3wMaterialTransformationOperationController::class,
        'cancelOperation',
    ])->name('materials.operations.transformation.cancel')
        ->middleware('can:material_accounting_operations_creating');
    Route::post('/strmaterials/transformation/move', [
        q3wMaterial\operations\q3wMaterialTransformationOperationController::class,
        'confirmOperation',
    ])->name('materials.operations.transformation.move')
        ->middleware('can:material_accounting_operations_creating');
    Route::get('/strmaterials/transformation/completed', [
        q3wMaterial\operations\q3wMaterialTransformationOperationController::class,
        'completed',
    ])->name('materials.operations.transformation.completed')
        ->middleware('can:material_accounting_material_list_access');
    Route::post('/strmaterials/transformation/is-user-responsible-for-material-accounting',
        [
            q3wMaterial\operations\q3wMaterialTransformationOperationController::class,
            'isUserResponsibleForMaterialAccountingWebRequest',
        ])
        ->name('materials.transformation.is-user-responsible-for-material-accounting')
        ->middleware('can:material_accounting_operations_creating');

    //Material write-off
    Route::get('/strmaterials/write-off/new', [
        q3wMaterial\operations\q3wMaterialWriteOffOperationController::class,
        'create',
    ])->name('materials.operations.write-off.new')
        ->middleware('can:material_accounting_operations_creating');
    Route::get('/strmaterials/write-off/view', [
        q3wMaterial\operations\q3wMaterialWriteOffOperationController::class,
        'view',
    ])->name('materials.operations.write-off.view')
        ->middleware('can:material_accounting_operations_creating');
    Route::post('/strmaterials/write-off/new/validate-material-list', [
        q3wMaterial\operations\q3wMaterialWriteOffOperationController::class,
        'validateMaterialList',
    ])->name('materials.operations.write-off.new.validate-material-list')
        ->middleware('can:material_accounting_operations_creating');
    Route::post('/strmaterials/write-off/new', [
        q3wMaterial\operations\q3wMaterialWriteOffOperationController::class,
        'store',
    ])->name('materials.operations.write-off.new')
        ->middleware('can:material_accounting_operations_creating');
    Route::post('/strmaterials/write-off/cancel', [
        q3wMaterial\operations\q3wMaterialWriteOffOperationController::class,
        'cancelOperation',
    ])->name('materials.operations.write-off.cancel')
        ->middleware('can:material_accounting_operations_creating');
    Route::post('/strmaterials/write-off/move', [
        q3wMaterial\operations\q3wMaterialWriteOffOperationController::class,
        'confirmOperation',
    ])->name('materials.operations.write-off.move')
        ->middleware('can:material_accounting_operations_creating');
    Route::get('/strmaterials/write-off/completed', [
        q3wMaterial\operations\q3wMaterialWriteOffOperationController::class,
        'completed',
    ])->name('materials.operations.write-off.completed')
        ->middleware('can:material_accounting_material_list_access');

    //Material Supply Planning
    Route::get('/strmaterials/supply-planning',
        [q3wMaterial\q3wMaterialSupplyPlanningController::class, 'index'])
        ->name('materials.supply-planning.index')
        ->middleware('can:material_supply_planning_access');
    Route::get('/strmaterials/supply-planning/object-list',
        [q3wMaterial\q3wMaterialSupplyPlanningController::class, 'list'])
        ->name('materials.supply-planning.list')
        ->middleware('can:material_supply_planning_access');
    Route::get('/strmaterials/supply-planning/get-materials-for-supply-planning/{planningObjectId}',
        [
            q3wMaterial\q3wMaterialSupplyPlanningController::class,
            'getMaterialsForSupplyPlanning',
        ])->name('materials.supply-planning.get-materials-for-supply-planning')
        ->middleware('can:material_supply_planning_access');
    Route::get('/strmaterials/supply-planning/available-material-list', [
        q3wMaterial\q3wMaterialSupplyPlanningController::class,
        'getAvailableMaterialList',
    ])->name('materials.supply-planning.available-material-list')
        ->middleware('can:material_supply_planning_access');
    Route::get('/strmaterials/supply-planning/get-summary',
        [q3wMaterial\q3wMaterialSupplyPlanningController::class, 'getSummary'])
        ->name('materials.supply-planning.get-summary')
        ->middleware('can:material_supply_planning_access');
    Route::put('/strmaterials/supply-planning/',
        [q3wMaterial\q3wMaterialSupplyPlanningController::class, 'update'])
        ->name('materials.supply-planning.update')
        ->middleware('can:material_supply_planning_editing');
    Route::post('/strmaterials/supply-planning/',
        [q3wMaterial\q3wMaterialSupplyPlanningController::class, 'store'])
        ->name('materials.supply-planning.store')
        ->middleware('can:material_supply_planning_editing');
    Route::delete('/strmaterials/supply-planning/',
        [q3wMaterial\q3wMaterialSupplyPlanningController::class, 'delete'])
        ->name('materials.supply-planning.delete')
        ->middleware('can:material_supply_planning_editing');

    Route::get('/strmaterials/supply-planning/planning-objects/list',
        [q3wMaterial\q3wMaterialSupplyObjectController::class, 'list'])
        ->name('materials.supply-planning.planning-objects.list')
        ->middleware('can:material_supply_planning_access');
    Route::put('/strmaterials/supply-planning/planning-objects/{id}',
        [q3wMaterial\q3wMaterialSupplyObjectController::class, 'update'])
        ->name('materials.supply-planning.planning-objects.update')
        ->middleware('can:material_supply_planning_editing');
    Route::post('/strmaterials/supply-planning/planning-objects/',
        [q3wMaterial\q3wMaterialSupplyObjectController::class, 'store'])
        ->name('materials.supply-planning.planning-objects.store')
        ->middleware('can:material_supply_planning_editing');
    Route::delete('/strmaterials/supply-planning/planning-objects/{id}',
        [q3wMaterial\q3wMaterialSupplyObjectController::class, 'delete'])
        ->name('materials.supply-planning.planning-objects.delete')
        ->middleware('can:material_supply_planning_editing');

    Route::get('/strmaterials/supply-planning/expected-delivery/list', [
        q3wMaterial\q3wMaterialSupplyExpectedDeliveryController::class, 'list',
    ])->name('materials.supply-planning.expected-delivery.list')
        ->middleware('can:material_supply_planning_access');
    Route::put('/strmaterials/supply-planning/expected-delivery/', [
        q3wMaterial\q3wMaterialSupplyExpectedDeliveryController::class,
        'update',
    ])->name('materials.supply-planning.expected-delivery.update')
        ->middleware('can:material_supply_planning_editing');
    Route::post('/strmaterials/supply-planning/expected-delivery/', [
        q3wMaterial\q3wMaterialSupplyExpectedDeliveryController::class, 'store',
    ])->name('materials.supply-planning.expected-delivery.store')
        ->middleware('can:material_supply_planning_editing');
    Route::delete('/strmaterials/supply-planning/expected-delivery/', [
        q3wMaterial\q3wMaterialSupplyExpectedDeliveryController::class,
        'delete',
    ])->name('materials.supply-planning.expected-delivery.delete')
        ->middleware('can:material_supply_planning_editing');

    require base_path('routes/modules/labor-safety/labor-safety.php');
    require base_path('routes/modules/common/company.php');
    require base_path('routes/modules/employees/employees.php');
    require base_path('routes/modules/project_object_documents/project_object_documents.php');
    require base_path('routes/modules/timesheet/timesheet.php');
});

Illuminate\Support\Facades\Auth::routes();
