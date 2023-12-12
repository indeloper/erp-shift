<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Telegram\Dialogs\FuelDialogs;
use App\Telegram\TelegramApi;

class TelegramController extends Controller
{
    public function requestDispatching(Request $request)
    {
        $event = $request->callback_query ?? null;
        if (isset($event['data'])) {

            $eventData = json_decode($event['data']);

            if ($eventData->eventName === 'fuelTankMovementConfirmation') {
                $this->removeInlineButton(
                    $event['message']['chat']['id'],
                    $event['message']['message_id'],
                    $event['message']['text']
                );

                (new FuelDialogs)
                    ->fuelTankMovementConfirmation(
                        $eventData->eventId,
                        $event['from']['id']
                    );

                $this->closeDialog($event['message']['chat']['id']);
            }
        }
    }

    public function removeInlineButton($chatId, $messageId, $text)
    {
        $data = array(
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => json_encode(['inline_keyboard' => []])
        );

        new TelegramApi('editMessageText', $data);
    }

    public function closeDialog($chatId)
    {
        $data = array(
            'chat_id' => $chatId,
            'text' => '',
        );

        new TelegramApi('sendMessage', $data);
    }
}
