<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BitrixWebhookController extends Controller
{
    public function handleIncomingRequest(Request $request)
    {
        return response()->json([], 200);
    }
}
