<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Telegram\Dialogs\FuelDialogs;
use App\Telegram\TelegramApi;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function requestDispatching(Request $request)
    {
        $event = $request->callback_query ?? null;

        if (isset($event['data'])) {

            // $eventData = json_decode($event['data']);

            if (json_decode($event['data'])->eventName === 'fuelTankMovementConfirmation') {
                (new FuelDialogs)->fuelTankMovementConfirmation($event);
                // $this->handleFuelTankMovementConfirmation($event);
            }
        }
    }

    // public function handleFuelTankMovementConfirmation($event)
    // {
    //     // $this->removeInlineButton(
    //     //     $event['message']['chat']['id'],
    //     //     $event['message']['message_id'],
    //     //     $event['message']['text']
    //     // );

    //     (new FuelDialogs)->fuelTankMovementConfirmation($event);

    //     // $eventData = json_decode($event['data']);
    //     // (new FuelDialogs)
    //     //     ->fuelTankMovementConfirmation(
    //     //         $eventData->eventId,
    //     //         $event['from']['id']
    //     //     );

    //     // $this->closeDialog($event['message']['chat']['id']);
    // }

    // public function removeInlineButton($chatId, $messageId, $text)
    // {
    //     $data = array(
    //         'chat_id' => $chatId,
    //         'message_id' => $messageId,
    //         'text' => $text,
    //         'reply_markup' => json_encode(['inline_keyboard' => []])
    //     );

    //     new TelegramApi('editMessageText', $data);
    // }

}
