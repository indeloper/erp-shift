<?php

namespace App\Http\Controllers\System;

use App\Domain\Enum\TelegramEventType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Telegram\Dialogs\FuelDialogs;

class TelegramController extends Controller
{
    public function requestDispatching(Request $request)
    {
        $event = $request->callback_query ?? null;

        if (isset($event['data'])) {

            $eventName = json_decode($event['data'])->eventName;

            if ($eventName === TelegramEventType::FUEL_TANK_MOVEMENT_CONFIRMATION) {
                (new FuelDialogs)->fuelTankMovementConfirmation($event);
            }
        }
    }

    
}
