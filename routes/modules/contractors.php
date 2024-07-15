<?php

use App\Http\Controllers\Commerce\ContractorController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:contractors')->group(function () {
    Route::get('/', [ContractorController::class, 'index'])->name('index');
    Route::get('/load', [ContractorController::class, 'load'])->name('load');
    Route::get('/card/{id}', [ContractorController::class, 'card'])->name('card');
    Route::get('/create', [ContractorController::class, 'create'])->name('create')->middleware('can:contractors_create');
    Route::get('/edit/{id}', [ContractorController::class, 'edit'])->name('edit')->middleware('can:contractors_edit');
    Route::get('/tasks/{id}', [ContractorController::class, 'tasks'])->name('tasks');

    Route::post('/store', [ContractorController::class, 'store'])->name('store')->middleware('can:contractors_create');
    Route::post('/update/{id}', [ContractorController::class, 'update'])->name('update')->middleware('can:contractors_edit');
    Route::post('/add-contact/{id}', [ContractorController::class, 'add_contact'])->name('add_contact')->middleware('can:contractors_contacts');
    Route::post('/edit-contact', [ContractorController::class, 'edit_contact'])->name('edit_contact')->middleware('can:contractors_contacts');
    Route::get('/search_inn', [ContractorController::class, 'search_dadata'])->name('search_dadata');
    Route::get('/is_unique', [ContractorController::class, 'is_unique'])->name('is_unique');
    Route::get('/remove_task/{id}', [ContractorController::class, 'remove_task'])->name('remove_task');

    Route::post('/contact-delete', [ContractorController::class, 'contact_delete'])->name('contact_delete')->middleware('can:contractors_contacts');
    Route::post('/contractor-delete-request', [ContractorController::class, 'contractor_delete_request'])->name('contractor_delete_request')->middleware('can:contractors_delete');
    Route::post('/get_by_type/', [ContractorController::class, 'get_by_type'])->name('get_by_type');
    Route::post('/solve_remove/{id}', [ContractorController::class, 'solve_remove'])->name('solve_remove')->middleware('can:contractors_delete');
    Route::post('/solve_task_check_contractor/{task_id}', [ContractorController::class, 'solveTaskCheckContractor'])->name('solve_task_check_contractor');
});

Route::get('/get_contractors', [ContractorController::class, 'getContractors'])->name('get_contractors');
