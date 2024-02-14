<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Telegram\Dialogs\FuelDialogs;
use App\Telegram\TelegramApi;
use Illuminate\Http\Response;

class BitrixWebhookController extends Controller
{
    public function handleIncomingRequest(Request $request)
    {
        return response()->json([],200);
    }
}
