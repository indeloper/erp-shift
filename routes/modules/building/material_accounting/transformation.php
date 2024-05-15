<?php

use App\Http\Controllers\MatAccTransformationController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:mat_acc_operation_log')->group(function () {
    Route::get('/complete/{id}', [MatAccTransformationController::class, 'complete'])->name('complete');
    Route::get('/confirm/{id}', [MatAccTransformationController::class, 'confirm'])->name('confirm');
    Route::get('/create', [MatAccTransformationController::class, 'create'])->name('create');
    Route::get('/work/{id}', [MatAccTransformationController::class, 'work'])->name('work');
    Route::get('/conflict/{id}', [MatAccTransformationController::class, 'conflict'])->name('conflict');
    Route::get('/edit/{id}', [MatAccTransformationController::class, 'edit'])->name('edit');
    Route::get('/draft/{id}', [MatAccTransformationController::class, 'draft'])->name('draft');

    Route::post('/store', [MatAccTransformationController::class, 'store'])->name('store');
    Route::post('/update/{id}', [MatAccTransformationController::class, 'update'])->name('update');
    Route::post('/send/{id}', [MatAccTransformationController::class, 'send'])->name('send');
    Route::post('/part_send/{id}', [MatAccTransformationController::class, 'part_send'])->name('part_send');
    Route::post('/accept/{id}', [MatAccTransformationController::class, 'accept'])->name('accept');
});
