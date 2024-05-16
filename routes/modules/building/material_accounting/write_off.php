<?php

use App\Http\Controllers\Building\MaterialAccounting\MatAccWriteOffController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:mat_acc_operation_log')->group(function () {
    Route::get('/complete/{id}', [MatAccWriteOffController::class, 'complete'])->name('complete');
    Route::get('/confirm/{id}', [MatAccWriteOffController::class, 'confirm'])->name('confirm');
    Route::get('/create', [MatAccWriteOffController::class, 'create'])->name('create');
    Route::get('/work/{id}', [MatAccWriteOffController::class, 'work'])->name('work');
    Route::get('/conflict/{id}', [MatAccWriteOffController::class, 'conflict'])->name('conflict');
    Route::get('/edit/{id}', [MatAccWriteOffController::class, 'edit'])->name('edit');
    Route::get('/draft/{id}', [MatAccWriteOffController::class, 'draft'])->name('draft');
    Route::get('/control/{id}', [MatAccWriteOffController::class, 'control'])->name('control');

    Route::post('/store', [MatAccWriteOffController::class, 'store'])->name('store');
    Route::post('/update/{id}', [MatAccWriteOffController::class, 'update'])->name('update');
    Route::post('/send/{id}', [MatAccWriteOffController::class, 'send'])->name('send');
    Route::post('/part_send/{id}', [MatAccWriteOffController::class, 'part_send'])->name('part_send');
    Route::post('/accept/{id}', [MatAccWriteOffController::class, 'accept'])->name('accept');
    Route::post('/control/send/{id}', [MatAccWriteOffController::class, 'solve_control'])->name('solve_control');
});
