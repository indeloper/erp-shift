<?php

use App\Http\Controllers\System;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/1c-sync',
    [System\UpdateEmployeesInfoFrom1cController::class, 'uploadData']);
Route::post('/telegram/'.config('telegram.internal_bot_token'),
    [System\TelegramController::class, 'requestDispatching']);

Route::post('/bitrix',
    [System\BitrixWebhookController::class, 'handleIncomingRequest']);

