<?php

namespace app\Telegram\MessageTemplates;

use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use App\Models\User;
use App\Telegram\TelegramServices;
use Carbon\Carbon;
use morphos\Russian\FirstNamesInflection;
use morphos\Russian\RussianLanguage;

class FuelMessageTemplates
{
    public function getFuelTankNewResponsibleMessage($params)
    {
        $text = $params['text'];
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
        $messageText = $textWithoutHook;

        return [
            'text' => $messageText,
            'reply_markup' => json_encode($keyboard),
            'options' => ['tankId' => $tankId]
        ];
    }

    public function getFuelTankMovingConfirmationForNewResponsibleMessageParams($params)
    {
        $chatMessage = json_decode($params['tank']->chat_message_tmp);
        $chatId = $chatMessage->chatId;
        $messageId = $chatMessage->messageId;
        $text = $chatMessage->text;

        $message = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => []]),
            'text' => 
                $text
                . "\n"."\n".
                "✅"
                . " Перемещение подтверждено "
                . Carbon::create(now())->format('d.m.Y в H:m')
        ]; 
        
        return [
            'message' => $message,
        ];
    }

    public function getFuelTankMovingConfirmationForPreviousResponsibleMessageParams($params)
    {
         $lastTankTransferHistory = FuelTankTransferHistory::query()
            ->where('fuel_tank_id', $params['tank']->id)
            ->whereNull('fuel_tank_flow_id')
            ->orderByDesc('id')
            ->first();
        
        $newResponsible = User::find($params['tank']->responsible_id);
        $newResponsibleFIO = $newResponsible->format('L f. p.', 'именительный') ?? null;
        $newResponsibleUrl = $newResponsible->getExternalUserUrl();
        
        $text = 
            '<b>Перемещение топливной емкости</b>'
            ."\n".
            "<i><a href='{$newResponsibleUrl}'>{$newResponsibleFIO}</a> "
            . RussianLanguage::verb('подтвердил', mb_strtolower($newResponsible->gender)). " перемещение " 
            . Carbon::create(now())->format('d.m.Y в H:m') 
            . "</i>"
            ."\n"."\n"
            ."<b>Номер емкости:</b> {$params['tank']->tank_number}"
            ."\n"
            ."<b>Остаток топлива:</b> {$params['tank']->fuel_level} л"
            ."\n"
            ."<b>С объекта:</b> ". (ProjectObject::find($lastTankTransferHistory->previous_object_id)->short_name ?? null)
            ."\n"
            ."<b>На объект:</b> ". (ProjectObject::find($params['tank']->object_id)->short_name ?? null )    
        ;

        $previousResponsible = User::find($lastTankTransferHistory->previous_responsible_id);
        $chatId = $previousResponsible->chat_id ?? null;

        $message = [
            'chat_id' => $chatId, 
            'parse_mode' => 'HTML',
            'text' => $text
        ]; 
        
        return [
            'message' => $message
        ];
    }

    public function getFuelTankMovingConfirmationForOfficeResponsiblesMessageParams($params)
    {
        $lastTankTransferHistory = FuelTankTransferHistory::query()
            ->where('fuel_tank_id', $params['tank']->id)
            ->whereNull('fuel_tank_flow_id')
            ->orderByDesc('id')
            ->first();
        
        $newResponsible = User::find($params['tank']->responsible_id);
        $previousResponsible = User::find($lastTankTransferHistory->previous_responsible_id);
        $newResponsibleFIO = $newResponsible->format('L f. p.', 'именительный') ?? null;
        $previousResponsibleFIO = $previousResponsible->format('L f. p.', 'именительный') ?? null;

        $newResponsibleUrl = $newResponsible->getExternalUserUrl();

        $previousResponsibleUrl = $previousResponsible->getExternalUserUrl();
        
        $text = 
            '<b>Перемещение топливной емкости</b>'
            ."\n".
            "<i>"
            . Carbon::create(now())->format('d.m.Y в H:m') 
            ." завершено перемещение топливной емкости"
            . "</i>"
            ."\n"."\n"
            ."<b>Номер емкости:</b> {$params['tank']->tank_number}"
            ."\n"
            ."<b>Остаток топлива:</b> {$params['tank']->fuel_level} л"
            ."\n"
            ."<b>С объекта:</b> ". (ProjectObject::find($lastTankTransferHistory->previous_object_id)->short_name ?? null) . " (<a href='{$previousResponsibleUrl}'>{$previousResponsibleFIO})</a>"
            ."\n"
            ."<b>На объект:</b> ". (ProjectObject::find($params['tank']->object_id)->short_name ?? null ) . " (<a href='{$newResponsibleUrl}'>{$newResponsibleFIO}</a>)"
        ;

        $message = [
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => []]),
            'text' => $text
        ]; 
        
        return [
            'message' => $message
        ];
    }
}