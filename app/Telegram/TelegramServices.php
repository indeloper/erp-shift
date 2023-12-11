<?php

namespace App\Telegram;

class TelegramServices {

    const customMessageTemplates = [
        'confirmFuelTankRecieve' => [
            'class' => 'App\Telegram\Notifications\FuelNotifications',
            'method' => 'getFuelTankNewResponsibleMessageParametrs'
        ]
    ];

    public function defineNotificationTemplate($text)
    {
        if(str_contains($text, 'confirmFuelTankRecieve')){
            return 'confirmFuelTankRecieve';
        }
        return null;        
    }

    public function defineNotificationTemplateClassMethod($template)
    {
        return self::customMessageTemplates[$template] ?? [];
    }

    public function setNotificationHookLink($text)
    {
        $hookTypeAndId = explode('notificationHook_', explode('_endNotificationHook', $text)[0])[1];
        $notificationHookLink = asset('/notifications').'?notificationHook='.$hookTypeAndId;
        $text = str_replace('notificationHook_'.$hookTypeAndId.'_endNotificationHook', '', $text);
        return $text.' '.$notificationHookLink;
    }

    public function getHookTypeAndId($text)
    {
        $hookTypeAndId = explode('notificationHook_', explode('_endNotificationHook', $text)[0])[1];
        $typeAndIdArr = explode('-', $hookTypeAndId);
        return [
            'type' => $typeAndIdArr[0],
            'id' => $typeAndIdArr[2]
        ];
    }
}