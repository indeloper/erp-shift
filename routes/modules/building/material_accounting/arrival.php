<?php

use App\Http\Controllers\Building\MaterialAccounting\MatAccArrivalController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:mat_acc_operation_log')->group(function () {
    Route::get('/complete/{id}', [MatAccArrivalController::class, 'complete'])->name('complete');
    Route::get('/confirm/{id}', [MatAccArrivalController::class, 'confirm'])->name('confirm');
    Route::get('/draft/{id}', [MatAccArrivalController::class, 'draft'])->name('draft');

    Route::get('/create', [MatAccArrivalController::class, 'create'])->name('create');
    Route::get('/work/{id}', [MatAccArrivalController::class, 'work'])->name('work');
    Route::get('/edit/{id}', [MatAccArrivalController::class, 'edit'])->name('edit');

    Route::any('/store', [MatAccArrivalController::class, 'store'])->name('store');
    Route::post('/update/{id}', [MatAccArrivalController::class, 'update'])->name('update');
    Route::post('/send/{id}', [MatAccArrivalController::class, 'send'])->name('send');
    Route::post('/part_send/{id}', [MatAccArrivalController::class, 'part_send'])->name('part_send');
    Route::post('/accept/{id}', [MatAccArrivalController::class, 'accept'])->name('accept');
});
