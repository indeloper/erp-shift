<?php

namespace app\Telegram\MessageTemplates;

use App\Models\Company\Company;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use App\Models\User;
use App\Telegram\TelegramServices;
use Carbon\Carbon;
use morphos\Russian\FirstNamesInflection;
use morphos\Russian\RussianLanguage;

class LaborSafetyMessageTemplates
{
    public function getLaborSafetyNewOrderRequestNotificationTemplate($params) {
        $orderRequestId = $params['orderRequest']->id;


        $orderRequestAuthor = User::find($params['orderRequest']->author_user_id);
        $orderRequestAuthorName = $orderRequestAuthor->format('L f. p.', 'именительный') ?? null;
        $orderRequestAuthorUrl = $orderRequestAuthor->getExternalUserUrl();

        $company = Company::find($params['orderRequest']->company_id);
        $projectObject = ProjectObject::find($params['orderRequest']->project_object_id);

        $text =
            '<b>Заявка на формирование приказов</b>'
            ."\n".
            "<i>"
            . "<a href='$orderRequestAuthorUrl'>$orderRequestAuthorName</a> "
            . RussianLanguage::verb('создал', mb_strtolower($orderRequestAuthor->gender))
            ." заявку <u>#$orderRequestId</u>"
            . "</i>"
            ."\n"."\n"
            ."<b>Организация:</b> $company->name"
            ."\n"
            ."<b>Адрес объекта:</b> $projectObject->short_name";

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