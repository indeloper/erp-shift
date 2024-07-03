<?php

use App\Http\Controllers\Employees;
use Illuminate\Support\Facades\Route;

Route::get('/employees/list', [Employees\EmployeesController::class, 'list'])->name('employees.list');
