<?php

use App\Http\Controllers\Companies;
use Illuminate\Support\Facades\Route;

Route::get('/compamies/list', [Companies\CompanyController::class, 'list'])->name('companies.list');
