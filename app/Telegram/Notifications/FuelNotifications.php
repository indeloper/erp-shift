<?php

namespace App\Telegram\Notifications;

use App\Models\TechAcc\FuelTank\FuelTank;
use App\Telegram\TelegramServices;

class FuelNotifications {
    public function getFuelTankNewResponsibleMessageParametrs($text)
    {
        $tankId = (new TelegramServices)->getHookTypeAndId($text)['id'];
        $tank = FuelTank::find($tankId);

        $callbackData = [
            'eventName' => 'fuelTankMovementConfirmation',
            'eventId' => $tankId
        ];

        $inlineKeyboard = [
            [
                [
                    'callback_data' => json_encode($callbackData),
                    'text' => 'Подтвердить',
                ]
            ]
        ];

        $keyboard = [
            'inline_keyboard' => $inlineKeyboard,
        ];

        $textWithoutHook = explode('notificationHook_', $text)[0];
        $messageText = $textWithoutHook .
            "\n" . '. Остаток топлива ' . $tank->fuel_level . ' литров';

        return [
            'text' => $messageText,
            'reply_markup' => json_encode($keyboard)
        ];
    }
}

