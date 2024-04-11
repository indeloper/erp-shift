<?php

namespace App\Telegram;

class TelegramServices {

    const customMessageTemplates = [
        'laborSafetyNewOrderRequestNotificationTemplate' => [
            'class' => 'App\Telegram\MessageTemplates\LaborSafety\LaborSafetyMessageTemplates',
            'method' => 'getLaborSafetyNewOrderRequestNotificationTemplateParams'
        ]
    ];

    public function defineNotificationTemplateClassMethod($template)
    {
        return self::customMessageTemplates[$template] ?? [];
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

        $notificationTemplateClass = new $templateClassMethod['class']();
        $notificationTemplateMethod = $templateClassMethod['method'];

        $tmp = (new $notificationTemplateClass)->$notificationTemplateMethod($params);

        return (new $notificationTemplateClass)->$notificationTemplateMethod($params);
    }
}
