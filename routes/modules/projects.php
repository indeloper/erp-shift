<?php
Route::group(['middleware' => 'can:projects'], function () {
    Route::get('/', 'ProjectController@index')->name('index');
    Route::get('/card/{id}', 'ProjectController@card')->name('card');
    Route::get('/card/{project}/users', 'ProjectController@users')->name('users');
    Route::get('/create', 'ProjectController@create')->name('create')->middleware('can:projects_create');
    Route::get('/edit/{id}', 'ProjectController@edit')->name('edit');
    Route::get('/tasks/{id}', 'ProjectController@tasks')->name('tasks');
    Route::post('/select-contacts/{id}', 'ProjectController@select_contacts')->name('select_contacts');
    Route::post('/store', 'ProjectController@store')->name('store')->middleware('can:projects_create');
    Route::post('/update/{id}', 'ProjectController@update')->name('update');
    Route::post('/add-contact/{id}', 'ProjectController@add_contact')->name('add_contact');
    Route::post('/render-contact', 'ProjectController@render_contact')->name('render_contact');
    Route::post('{project_id}/change_status', 'ProjectController@change_status')->name('change_status');
    Route::post('{project_id}/add_contractors', 'ProjectController@add_contractors')->name('add_contractors');
    Route::post('/importance-toggler', 'ProjectController@importance_toggler')->name('importance_toggler');
    Route::post('/update_time_responsible', 'ProjectController@updateTimeResponsibleUser')->name('update_time_responsible');
});


Route::post('/delete-resp-user', 'ProjectController@delete_resp_user')->name('delete_resp_user');
Route::post('/select-user/{id}', 'ProjectController@select_user')->name('select_user');
Route::post('/close_project/{id}', 'ProjectController@close_project')->name('close_project');

Route::get('/important', 'ProjectDashboardController@importantProjects')->name('important');
Route::get('/stats', 'ProjectDashboardController@projectStats')->name('stats');

Route::get('/get-contractors', 'ProjectController@get_contractors');
Route::get('/get-projects', 'ProjectController@getProjects')->name('get_projects');
Route::post('/delete-contact', 'ProjectController@contact_delete')->name('contact_delete');
Route::post('/store-temp-contact', 'ProjectController@store_temp_contact')->name('store_temp_contact');
Route::post('/get-projects-for-human-accounting', 'ProjectController@getProjectsForHumanAccounting')->name('get_projects_for_human');

Route::get('ajax/get-contractors', 'ProjectController@get_contractors');
Route::any('ajax/get-contractors-contacts/', 'ProjectController@get_contractors_contacts');
Route::get('ajax/get-contacts/{contractor_id}', 'ProjectController@get_contacts');
Route::get('ajax/get-users', 'ProjectController@get_users');
Route::get('ajax/get-objects', 'ProjectController@get_objects');
Route::get('ajax/get_project_documents/{project_id}', 'ProjectController@get_project_documents');
Route::get('ajax/get_project_options', 'ProjectController@get_project_options')->name('get_options');

// work volumes
Route::get('{id}/work_volume/{project_volume_id}/card_tongue', 'ProjectWorkVolumeController@card_tongue')->name('work_volume::card_tongue');
Route::get('{id}/work_volume/{project_volume_id}/card_pile', 'ProjectWorkVolumeController@card_pile')->name('work_volume::card_pile');
Route::get('{id}/work_volume/{project_volume_id}/edit_tongue', 'ProjectWorkVolumeController@card_tongue')->name('work_volume::edit_tongue');
Route::get('{id}/work_volume/{project_volume_id}/edit_pile', 'ProjectWorkVolumeController@card_pile')->name('work_volume::edit_pile');
Route::get('/stop_edit', 'ProjectWorkVolumeController@stop_edit')->name('work_volume::stop_edit');
Route::post('work_volume/{project_volume_id}/save_one', 'ProjectWorkVolumeController@save_one')->name('work_volume::save_one');
Route::post('work_volume/edit_one', 'ProjectWorkVolumeController@edit_one')->name('work_volume::edit_one');
Route::post('{project_id}/work_volume/create_new', 'ProjectWorkVolumeController@create_new')->name('work_volume::create_new');
Route::post('work_volume/delete_work', 'ProjectWorkVolumeController@delete_work')->name('work_volume::delete_work');
Route::post('work_volume/{project_volume_id}/send', 'ProjectWorkVolumeController@send_work_volume')->name('work_volume::send');
Route::post('work_volume/{project_volume_id}/close', 'ProjectWorkVolumeController@close_work_volume')->name('work_volume::close');
Route::post('work_volume/{project_volume_id}/change_depth', 'ProjectWorkVolumeController@change_depth')->name('work_volume::change_depth');
Route::post('work_volume/{work_volume_id}/delete_works/{work_group}', 'ProjectWorkVolumeController@delete_works')->name('work_volume::delete_works');
Route::any('work_volume/replace_material', 'ProjectWorkVolumeController@replace_material')->name('work_volume::replace_material');
Route::any('work_volume/count_nodes', 'ProjectWorkVolumeController@count_nodes')->name('work_volume::count_nodes');
Route::any('work_volume/{work_volume_id}/complect_materials', 'ProjectWorkVolumeController@complect_materials')->name('work_volume::complect_materials');
Route::any('work_volume/{work_volume_id}/detach_compile', 'ProjectWorkVolumeController@detach_compile')->name('work_volume::detach_compile');

// calculation
Route::post('work_volume/{work_volume_id}/create_tongue_calc', 'WVCalvulatorController@create_tongue_calc')->name('work_volume::create_tongue_calc');
Route::post('work_volume/{work_volume_id}/create_mount_calc', 'WVCalvulatorController@create_mount_calc')->name('work_volume::create_mount_calc');
Route::any('work_volume/{work_volume_id}/calc_tongue_count', 'WVCalvulatorController@calc_tongue_count')->name('work_volume::calc_tongue_count');
Route::get('ajax/get_angle', 'WVCalvulatorController@get_angle');
Route::get('ajax/get_tongue', 'WVCalvulatorController@get_tongue');
Route::get('ajax/get_pipe', 'WVCalvulatorController@get_pipe');
Route::get('ajax/get_beam', 'WVCalvulatorController@get_beam');
Route::get('ajax/get_detail', 'WVCalvulatorController@get_detail');
Route::get('ajax/get_nodes', 'WVCalvulatorController@get_nodes');

Route::post('ajax/count_weight', 'WVCalvulatorController@count_weight')->name('work_volume::count_weight');


Route::post('ajax/get_pile_name', 'WVCalvulatorController@create_mount_calc')->name('work_volume::create_mount_calc');
Route::post('ajax/get-one-work', 'ProjectWorkVolumeController@get_one_work')->name('work_volume::get_one_work');
Route::post('ajax/get-one-work-manual', 'ProjectWorkVolumeController@get_one_work_manual')->name('work_volume::get_one_work_manual');
Route::any('ajax/get-work-count', 'ProjectWorkVolumeController@get_work_count')->name('work_volume::get_work_count');
Route::get('ajax/get-work', 'ProjectWorkVolumeController@get_work');
Route::get('ajax/get-material', 'ProjectWorkVolumeController@get_material')->name('get_material');
Route::get('ajax/get-material-work', 'ProjectWorkVolumeController@get_material_work');
Route::get('ajax/get_composite_pile', 'ProjectWorkVolumeController@get_composite_pile');
Route::post('ajax/get_pile_name', 'ProjectWorkVolumeController@get_pile_name')->name('work_volume::get_pile_name');




Route::post('/work_volume/{wv_id}/attach-material', 'ProjectWorkVolumeController@attach_material')->name('work_volume::attach_material');
Route::post('/work_volume/detach-material', 'ProjectWorkVolumeController@detach_material')->name('work_volume::detach_material');

Route::post('{id}/work_volume/request/store', 'ProjectWorkVolumeController@request_store')->name('work_volume_request::store');
Route::post('{id}/work_volume/{project_volume_id}/request/update', 'ProjectWorkVolumeController@request_update')->name('work_volume_request::update');
Route::post('{wv_request_id}/update', 'ProjectWorkVolumeController@request_wv_update')->name('work_volume_request::wv_update');
Route::post('/work_volume/{wv_id}/create_composite_pile', 'ProjectWorkVolumeController@create_composite_pile')->name('work_volume::create_composite_pile');

// commercial offers
Route::get('{id}/commercial_offer/{offer_id}/card_tongue', 'ProjectCommercialOfferController@card_tongue')->name('commercial_offer::card_tongue');
Route::get('{id}/commercial_offer/{offer_id}/card_pile', 'ProjectCommercialOfferController@card_pile')->name('commercial_offer::card_pile');
Route::get('{id}/commercial_offer/{offer_id}/card_double', 'ProjectCommercialOfferController@card_double')->name('commercial_offer::card_double');
Route::post('{id}/commercial_offer/{offer_id}/make_copy', 'ProjectCommercialOfferController@make_copy')->name('commercial_offer::make_copy');
Route::post('{id}/commercial_offer/upload', 'ProjectCommercialOfferController@upload')->name('commercial_offer::upload');
Route::post('{id}/commercial_offer/upload_signed_pdf', 'ProjectCommercialOfferController@upload_signed_pdf')->name('commercial_offer::upload_signed_pdf');

Route::get('{id}/commercial_offer/{offer_id}/edit', 'ProjectCommercialOfferController@edit')->name('commercial_offer::edit');
Route::get('commercial_offer/{offer_id}/create_offer_pdf', 'ProjectCommercialOfferController@create_offer_pdf')->name('commercial_offer::create_pdf');

Route::get('commercial_offer/{offer_id}/gantt', 'ProjectCommercialOfferController@gantt')->name('commercial_offer::gantt');
Route::post('commercial_offer/{offer_id}/gantt/send', 'ProjectCommercialOfferController@gantt_send')->name('commercial_offer::gantt_send');


Route::post('{id}/commercial_offer/{offer_id}/add_advancement', 'ProjectCommercialOfferController@add_advancement')->name('commercial_offer::add_advancement');
Route::post('{id}/commercial_offer/{offer_id}/set_contract_number', 'ProjectCommercialOfferController@set_contract_number')->name('commercial_offer::set_contract_number');
Route::post('{id}/commercial_offer/request/store', 'ProjectCommercialOfferController@request_store')->name('commercial_offer::requests::store');
Route::post('commercial_offer/request/update', 'ProjectCommercialOfferController@request_update')->name('commercial_offer::requests::update');
Route::post('commercial_offer/set_work_price', 'ProjectCommercialOfferController@set_work_price')->name('commercial_offer::set_work_price');
Route::post('commercial_offer/set_work_term', 'ProjectCommercialOfferController@set_work_term')->name('commercial_offer::set_work_term');
Route::post('commercial_offer/set_material_used', 'ProjectCommercialOfferController@set_material_used')->name('commercial_offer::set_material_used');
Route::post('commercial_offer/set_material_price', 'ProjectCommercialOfferController@set_material_price')->name('commercial_offer::set_material_price');
Route::post('commercial_offer/set_nds', 'ProjectCommercialOfferController@set_nds')->name('commercial_offer::set_nds');
Route::post('commercial_offer/{wv_id}/attach_subcontractor', 'ProjectCommercialOfferController@attach_subcontractor')->name('commercial_offer::attach_subcontractor');
Route::post('commercial_offer/detach_subcontractors', 'ProjectCommercialOfferController@detach_subcontractors')->name('commercial_offer::detach_subcontractors');
Route::post('commercial_offer/{offer_id}/attach_document', 'ProjectCommercialOfferController@attach_document')->name('commercial_offer::attach_document');
Route::post('commercial_offer/{offer_id}/save_commercial_offer', 'ProjectCommercialOfferController@save_commercial_offer')->name('commercial_offer::save_commercial_offer');
Route::post('commercial_offer/{offer_id}/agree_commercial_offer', 'ProjectCommercialOfferController@agree_commercial_offer')->name('commercial_offer::agree_commercial_offer');
Route::post('commercial_offer/{offer_id}/add_manual_note', 'ProjectCommercialOfferController@add_manual_note')->name('commercial_offer::add_manual_note');
Route::post('commercial_offer/{offer_id}/add_manual_requirement', 'ProjectCommercialOfferController@add_manual_requirement')->name('commercial_offer::add_manual_requirement');
Route::post('commercial_offer/create_double_kp', 'ProjectCommercialOfferController@create_double_kp')->name('commercial_offer::create_double_kp');

Route::post('commercial_offer/{offer_id}/set_signer', 'ProjectCommercialOfferController@set_signer')->name('commercial_offer::set_signer');
Route::post('commercial_offer/{offer_id}/set_contact', 'ProjectCommercialOfferController@set_contact')->name('commercial_offer::set_contact');

Route::post('commercial_offer/{offer_id}/split_material', 'ProjectCommercialOfferController@split_material')->name('commercial_offer::split_material');

Route::any('commercial_offer/delete_securuty_payment', 'ProjectCommercialOfferController@delete_securuty_payment')->name('commercial_offer::delete_securuty_payment');
Route::any('commercial_offer/add_security_pay', 'ProjectCommercialOfferController@add_security_pay')->name('commercial_offer::add_security_pay');
Route::any('commercial_offer/change_security_pay', 'ProjectCommercialOfferController@change_security_pay')->name('commercial_offer::change_security_pay');
Route::post('commercial_offer/delete_advancement', 'ProjectCommercialOfferController@delete_advancement')->name('commercial_offer::delete_advancement');
Route::post('commercial_offer/delete_comment', 'ProjectCommercialOfferController@delete_comment')->name('commercial_offer::delete_comment');
Route::post('commercial_offer/set_gantt_prior', 'ProjectCommercialOfferController@set_gantt_prior')->name('commercial_offer::set_gantt_prior');
Route::post('commercial_offer/delete_require', 'ProjectCommercialOfferController@delete_require')->name('commercial_offer::delete_require');
Route::post('commercial_offer/toggle_work_mat', 'ProjectCommercialOfferController@toggle_work_mat')->name('commercial_offer::toggle_work_mat');
Route::post('commercial_offer/{id}/change_advancement', 'ProjectCommercialOfferController@change_advancement')->name('commercial_offer::change_advancement');
Route::post('commercial_offer/{id}/change_comment', 'ProjectCommercialOfferController@change_comment')->name('commercial_offer::change_comment');
Route::post('commercial_offer/{id}/change_require', 'ProjectCommercialOfferController@change_require')->name('commercial_offer::change_require');
Route::get('commercial_offer/{id}/store_review', 'ProjectCommercialOfferController@store_review')->name('store_review');
Route::get('ajax/get_review', 'ProjectCommercialOfferController@get_review')->name('get_review');

Route::get('ajax/get-subcontractors', 'ProjectCommercialOfferController@get_subcontractors');
Route::post('ajax/get_latest_com_offer', 'ProjectCommercialOfferController@get_offer')->name('get_com_offer');
Route::post('ajax/update_title', 'ProjectCommercialOfferController@update_title')->name('update_title');
Route::post('ajax/use_as_main', 'ProjectController@use_as_main')->name('use_as_main');
Route::post('ajax/remove_relation', 'ProjectController@remove_relation')->name('remove_relation');

// contracts
Route::group(['middleware' => 'can:contracts'], function () {
    Route::get('{project_id}/contracts/{contract_id}/card', 'ProjectContractController@card')->name('contract::card');
    Route::post('{project_id}/contracts/{contract_id}/decline', 'ProjectContractController@decline')->name('contract::decline')->middleware('can:contracts_create');
    Route::post('{project_id}/contracts/{contract_id}/approve', 'ProjectContractController@approve')->name('contract::approve')->middleware('can:contracts_create');
    Route::post('contracts/{contract_id}/update', 'ProjectContractController@update')->name('contract::update')->middleware('can:contracts_create');
    Route::post('{project_id}/contracts', 'ProjectContractController@store')->name('contract::store')->middleware('can:contracts_create');

    Route::group(['middleware' => 'can:contracts_create'], function () {
        Route::post('contracts/delete_thesis', 'ProjectContractController@delete_thesis')->name('contract::delete_thesis');
        Route::post('contracts/delete_file', 'ProjectContractController@delete_file')->name('contract::delete_file');
        Route::post('contracts/update_thesis', 'ProjectContractController@update_thesis')->name('contract::update_thesis');
        Route::post('contracts/{contract_id}/add_thesis', 'ProjectContractController@add_thesis')->name('contract::add_thesis');
        Route::post('contracts/{contract_id}/add_files', 'ProjectContractController@add_files')->name('contract::add_files');
        Route::post('contracts/{contract_id}/send_contract', 'ProjectContractController@send_contract')->name('contract::send_contract');
    });

    Route::post('contracts/{thesis_id}/agree_thesis', 'ProjectContractController@agree_thesis')->name('contract::agree_thesis');
    Route::post('thesis/reject_thesis', 'ProjectContractController@reject_thesis')->name('contract::reject_thesis');

    Route::post('contracts/get_reject_info', 'ProjectContractController@get_reject_info')->name('contract::get_reject_info');
    Route::get('project_main_contracts', 'ProjectContractController@get_projects_contracts')->name('contracts::get_projects_contracts');

    Route::post('{project_id}/contracts/requests', 'ProjectContractController@request_store')->name('contracts::requests::store');
    Route::post('{project_id}/contracts/requests/{request_id}', 'ProjectContractController@request_update')->name('contracts::requests::update')->middleware('can:contracts_create');
    Route::post('{project_id}/contract-delete-request', 'ProjectContractController@contract_delete_request')->name('contracts::contract_delete_request')->middleware('can:contracts_delete_request');

    Route::post('contracts/{contract_id}/key_date_fork', 'ProjectContractController@key_date_fork')->name('contract::key_date_fork');
    Route::post('contracts/remove_key_date', 'ProjectContractController@remove_key_date')->name('contract::remove_key_date');
    Route::get('contracts/key_dates_names', 'ProjectContractController@key_names')->name('contract::key_names');
    Route::post('contracts/attach_com_offers/{id}', 'ProjectContractController@attach_com_offers')->name('contracts::attach_com_offers')->middleware('can:contractors_edit');
});
