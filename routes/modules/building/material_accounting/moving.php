<?php

use App\Http\Controllers\MatAccMovingController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:mat_acc_operation_log')->group(function () {
    Route::get('/complete/{id}', [MatAccMovingController::class, 'complete'])->name('complete');
    Route::get('/confirm/{id}', [MatAccMovingController::class, 'confirm'])->name('confirm');
    Route::get('/create', [MatAccMovingController::class, 'create'])->name('create');
    Route::get('/work/{id}', [MatAccMovingController::class, 'work'])->name('work');
    Route::get('/conflict/{id}', [MatAccMovingController::class, 'conflict'])->name('conflict');
    Route::get('/edit/{id}', [MatAccMovingController::class, 'edit'])->name('edit');
    Route::get('/draft/{id}', [MatAccMovingController::class, 'draft'])->name('draft');

    Route::any('/store', [MatAccMovingController::class, 'store'])->name('store');
    Route::post('/make_ttn', [MatAccMovingController::class, 'make_ttn'])->name('make_ttn');
    Route::post('/update/{id}', [MatAccMovingController::class, 'update'])->name('update');
    Route::post('/send/{id}', [MatAccMovingController::class, 'send'])->name('send');
    Route::post('/part_send/{id}', [MatAccMovingController::class, 'part_send'])->name('part_send');
    Route::post('/accept/{id}', [MatAccMovingController::class, 'accept'])->name('accept');
});
