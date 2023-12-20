<?php

namespace App\Telegram;

class TelegramServices {

    const customMessageTemplates = [
        'confirmFuelTankRecieve' => [
            'class' => 'App\Telegram\MessageTemplates\FuelMessageTemplates',
            'method' => 'getFuelTankNewResponsibleMessage'
        ],
        'fuelTankMovingConfirmationTextForNewResponsible' => [
            'class' => 'App\Telegram\MessageTemplates\FuelMessageTemplates',
            'method' => 'getFuelTankMovingConfirmationForNewResponsibleMessageParams'
        ],
        'fuelTankMovingConfirmationTextForPreviousResponsible' => [
            'class' => 'App\Telegram\MessageTemplates\FuelMessageTemplates',
            'method' => 'getFuelTankMovingConfirmationForPreviousResponsibleMessageParams'
        ],
        'fuelTankMovingConfirmationTextForOfficeResponsibles' => [
            'class' => 'App\Telegram\MessageTemplates\FuelMessageTemplates',
            'method' => 'getFuelTankMovingConfirmationForOfficeResponsiblesMessageParams'
        ]
    ];

    // public function defineNotificationTemplate($text)
    // {
    //     if(str_contains($text, 'confirmFuelTankRecieve')){
    //         return 'confirmFuelTankRecieve';
    //     }
    //     return null;        
    // }

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

    public function closeDialog($chatId)
    {
        $data = array(
            'chat_id' => $chatId,
            'text' => '',
        );

        new TelegramApi('sendMessage', $data);
    }

    public function defineTemplateByText($text)
    {
        if(str_contains($text, 'notificationHook')) {
            return $this->getHookTypeAndId($text)['type'];
        } 
        else {
            return 'underfined';
        }
        
    }

    public function getMessageParams($params)
    {
        if(isset($params['template'])) {
            $template = $params['template'];
        }
        elseif(isset($params['text'])) {
            $template = $this->defineTemplateByText($params['text']);
        }
        else {
            $template = 'undefined';
        }

        if(!isset(self::customMessageTemplates[$template])) {
            return ['text' => $params['text']];
        }

        $templateClassMethod = $this->defineNotificationTemplateClassMethod($template);

        if (!count($templateClassMethod)) {
            return ['text' => $params['text']];
        }

        $notificationTemlateClass = new $templateClassMethod['class']();
        $notificationTemlateMethod = $templateClassMethod['method'];

        $tmp = (new $notificationTemlateClass)->$notificationTemlateMethod($params); 

        return (new $notificationTemlateClass)->$notificationTemlateMethod($params);        
    }
}