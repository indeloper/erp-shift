<?php
//
//Route::resource('job_category', 'JobCategoryController');
//Route::get('job_categories', 'JobCategoryController@getCategories')->name('job_category.get');
//Route::get('job_category/{job_category}/users', 'JobCategoryController@users')->name('job_category.users');
//Route::post('job_categories_paginated', 'JobCategoryController@getCategoriesPaginated')->name('job_category.paginated');
//Route::put('job_category/{job_category}/update_users', 'JobCategoryController@updateUsers')->name('job_category.update_users');
//
//Route::resource('report_group', 'ReportGroupController');
//Route::get('report_groups', 'ReportGroupController@getGroups')->name('report_groups.get');
//Route::post('report_groups_paginated', 'ReportGroupController@getGroupsPaginated')->name('report_group.paginated');
//
//Route::resource('brigade', 'BrigadeController')->middleware(['can:human_resources_brigade_view']);
//Route::get('brigade/{brigade}/users', 'BrigadeController@users')->name('brigade.users');
//Route::get('brigades', 'BrigadeController@getBrigades')->name('brigade.get_brigades');
//Route::post('brigades_paginated', 'BrigadeController@getBrigadesPaginated')->name('brigade.paginated');
//Route::post('{brigade}/update_users', 'BrigadeController@updateUsers')->name('brigade.update_users');
//
//Route::put('timecard/{timecard}/update_openness', 'TimecardController@updateOpenness')->name('timecard.update_openness');
//Route::put('timecard/{timecard}/update_ktu', 'TimecardController@updateKtu')->name('timecard.update_ktu');
//Route::put('timecard/{timecard}/update_compensations', 'TimecardController@updateCompensations')->name('timecard.update_compensations');
//Route::put('timecard/{timecard}/update_bonuses', 'TimecardController@updateBonuses')->name('timecard.update_bonuses');
//Route::put('timecard/{timecard}/update_fines', 'TimecardController@updateFines')->name('timecard.update_fines');
//Route::put('timecard/{timecard}/update_deals_group', 'TimecardController@updateDealsGroup')->name('timecard.update_deals_group');
//Route::delete('timecard/destroy_deals_group', 'TimecardController@destroyDealsGroup')->name('timecard.destroy_deals_group');
//Route::post('timecard/get_addition_names', 'TimecardController@getAdditionNames')->name('timecard.get_addition_names');
//Route::post('timecard/get_summary_report', 'TimecardController@getSummaryReport')->name('timecard.get_summary_report');
//Route::put('timecard_day/{timecard_day}/update_time_periods', 'TimecardDayController@updateTimePeriods')->name('timecard_day.update_time_periods');
//Route::put('timecard_day/{timecard_day}/update_deals', 'TimecardDayController@updateDeals')->name('timecard_day.update_deals');
//Route::put('timecard_day/{timecard_day}/update_working_hours', 'TimecardDayController@updateWorkingHours')->name('timecard_day.update_working_hours');
//Route::put('timecard_day/update_day_deals_group', 'TimecardDayController@updateDayDealsGroup')->name('timecard_day.update_day_deals_group');
//Route::delete('timecard_day/destroy_day_deals_group', 'TimecardDayController@destroyDayDealsGroup')->name('timecard_day.destroy_day_deals_group');
//Route::post('timecard_day/get', 'TimecardDayController@get')->name('timecard_day.get');
//Route::get('timecard_day/appearance_task/{task}', 'TimecardDayController@appearanceTask')->name('timecard_day.appearance_task');
//Route::get('timecard_day/working_time_task/{task}', 'TimecardDayController@workingTimeTask')->name('timecard_day.working_time_task');
//Route::post('timecard_day/working_time_task/solve', 'TimecardDayController@solveWorkingTimeTask')->name('timecard_day.solve_working_time_task');
//
//
//Route::resource('payment', 'PaymentController');
//Route::get('report/daily', 'ReportController@dailyReport')->name('report.daily_report');
//Route::post('report/dailyData', 'ReportController@getDailyData')->name('report.daily_data');
//Route::get('report/summary', 'ReportController@summaryReport')->name('report.summary_report');
//Route::post('report/summaryData', 'ReportController@getSummaryData')->name('report.summary_data');
//Route::get('report/detailed', 'ReportController@detailedReport')->name('report.detailed_report');
//Route::post('report/detailedData', 'ReportController@getDetailedData')->name('report.detailed_data');
//
//Route::get('report/work_time', 'ReportController@generateWorkTimeReport')->name('work_time_report');
