<?php

namespace App\Notifications\Technic;

use App\Domain\Enum\NotificationType;
use App\Models\Building\ObjectResponsibleUser;
use App\Models\Building\ObjectResponsibleUserRole;
use App\Models\Notification\Notification;
use App\Models\ProjectObject;
use App\Models\TechAcc\OurTechnic;
use App\Models\TechAcc\TechnicCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use morphos\Russian\Cases;
use morphos\Russian\RussianLanguage;

use function morphos\Russian\pluralize;

class TechnicMovementNotifications
{
    public function notifyAboutTechnicMovementCreated($data, $entity, $notificationRecipientsIds)
    {
        $responsible = User::find(Auth::id());
        $responsibleFIO = $responsible->format('L f. p.', 'именительный') ?? null;
        $responsibleUrl = $responsible->getExternalUserUrl();

        $inflectedCategoryName = $this->getInflectedCategoryName($entity->technic_category_id, Cases::RODIT);

        $notificationText =
            '<b>Перемещение техники</b>'
            ."\n"
            .'<i>'
            ."<a href='{$responsibleUrl}'>{$responsibleFIO}</a> "
            .RussianLanguage::verb('создал', mb_strtolower($responsible->gender))
            .'<u>'
            .' заявку #'.$entity->id
            .'</u> '
            .' на перемещение'
            ."\n"
            .'<u>'
            .$inflectedCategoryName
            .'</u> '
            .OurTechnic::find($entity->technic_id)->name
            .'</i>'
            ."\n"."\n"
            .'<b>С объекта: </b>'.ProjectObject::find($entity->object_id)->short_name
            ."\n"
            .'<b>На объект: </b>'.ProjectObject::find($entity->previous_object_id)->short_name
        ;

        $this->notifyUsers($notificationRecipientsIds, $notificationText);
    }

    public function notifyAboutTechnicMovementPlanned($data, $entity, $notificationRecipientsIds)
    {
        $responsible = User::find(Auth::id());
        $responsibleFIO = $responsible->format('L f. p.', 'именительный') ?? null;
        $responsibleUrl = $responsible->getExternalUserUrl();

        $inflectedCategoryName = $this->getInflectedCategoryName($entity->technic_category_id, Cases::RODIT);

        $notificationText =
            '<b>Перемещение техники</b>'
            ."\n"
            .'<i>'
            .'По заявке #'.$entity->id
            .' <u>'
            ."<a href='{$responsibleUrl}'>{$responsibleFIO}</a> "
            .'</u> '
            .RussianLanguage::verb('назначил', mb_strtolower($responsible->gender))
            .' транспортировку'
            ."\n"
            .'<u>'
            .$inflectedCategoryName
            .'</u> '
            .OurTechnic::find($entity->technic_id)->name
            .' на '
            .'<u>'
            .Carbon::create($data['movement_start_datetime'])->format('d.m.Y в H:i')
            .'</u> '
            .'</i>'
            ."\n"."\n"
            .'<b>С объекта: </b>'.ProjectObject::find($entity->object_id)->short_name
            ."\n"
            .'<b>На объект: </b>'.ProjectObject::find($entity->previous_object_id)->short_name
        ;

        $this->notifyUsers($notificationRecipientsIds, $notificationText);
    }

    public function notifyAboutTechnicMovementCompleted($data, $entity, $notificationRecipientsIds)
    {
        $responsible = User::find($entity->responsible_id);
        $responsibleFIO = $responsible->format('L f. p.', 'творительный') ?? null;
        $responsibleUrl = $responsible->getExternalUserUrl();

        $inflectedCategoryName = $this->getInflectedCategoryName($entity->technic_category_id, Cases::RODIT);

        $notificationText =
            '<b>Перемещение техники</b>'
            ."\n"
            .'<i>'
            .'Заявка #'.$entity->id.' по перевозке'
            ."\n"
            .'<u>'
            .$inflectedCategoryName
            .'</u> '
            .OurTechnic::find($entity->technic_id)->name
            ."\n"
            .'<u>'
            .' исполнена '."<a href='{$responsibleUrl}'>{$responsibleFIO}</a>"
            .'</u> '
            .'</i>'
            ."\n"."\n"
            .'<b>С объекта: </b>'.ProjectObject::find($entity->object_id)->short_name
            ."\n"
            .'<b>На объект: </b>'.ProjectObject::find($entity->previous_object_id)->short_name
        ;

        $this->notifyUsers($notificationRecipientsIds, $notificationText);
    }

    public function notifyAboutTechnicMovementCancelled($data, $entity, $notificationRecipientsIds)
    {
        $responsible = User::find(Auth::id());
        $responsibleFIO = $responsible->format('L f. p.', 'творительный') ?? null;
        $responsibleUrl = $responsible->getExternalUserUrl();

        $inflectedCategoryName = $this->getInflectedCategoryName($entity->technic_category_id, Cases::RODIT);

        $notificationText =
            '<b>Перемещение техники</b>'
            ."\n"
            .'<i>'
            .'Заявка #'.$entity->id
            .' на транспортировку '
            .'<u>'
            .$inflectedCategoryName
            .'</u> '
            .OurTechnic::find($entity->technic_id)->name
            ."\n"
            .'<u>'
            .' отменена '."<a href='{$responsibleUrl}'>{$responsibleFIO}</a>"
            .'</u> '
            .'</i>'
            ."\n"."\n"
            .'<b>С объекта: </b>'.ProjectObject::find($entity->object_id)->short_name
            ."\n"
            .'<b>На объект: </b>'.ProjectObject::find($entity->previous_object_id)->short_name
        ;

        $this->notifyUsers($notificationRecipientsIds, $notificationText);
    }

    public function notifyAboutTechnicMovementPlannedForTommorow($data, $entity, $notificationRecipientsIds)
    {
        $responsible = User::find(Auth::id());
        $responsibleFIO = $responsible->format('L f. p.', 'дательный') ?? null;
        $responsibleUrl = $responsible->getExternalUserUrl();

        $inflectedCategoryName = $this->getInflectedCategoryName($entity->technic_category_id, Cases::RODIT);

        $notificationText =
            '<b>Перемещение техники</b>'
            ."\n"
            .'<i>'
            .'По заявке #'.$entity->id
            .'<u>'
            .' транспортировка'
            ."\n"
            .$inflectedCategoryName
            .'</u> '
            .OurTechnic::find($entity->technic_id)->name
            ."\n"
            .' запланирована '
            .'<u>'
            ."<a href='{$responsibleUrl}'>{$responsibleFIO}</a>"
            .' на завтра '
            .'</u> '
            . ' ('. Carbon::create($entity->movement_start_datetime)->format('d.m.Y в H:i') .')'
            .'</i>'
            ."\n"."\n"
            .'<b>С объекта: </b>'.ProjectObject::find($entity->object_id)->short_name
            ."\n"
            .'<b>На объект: </b>'.ProjectObject::find($entity->previous_object_id)->short_name
        ;

        $this->notifyUsers($notificationRecipientsIds, $notificationText);
    }


    public function notifyUsers($notificationRecipientsIds, $notificationText)
    {
        foreach($notificationRecipientsIds as $id) {
            dispatchNotify(
                $id,
                $notificationText,
                '',
                NotificationType::EQUIPMENT_MOVEMENT_NOTIFICATION
            );
        }
    }

    public function getInflectedCategoryName($categoryId, $case)
    {
        $nameTmp = pluralize(1, $this->getCategoryName($categoryId), false, $case);
        $arr = explode(' ', $nameTmp);
        unset($arr[0]);
        return mb_strtolower(implode(' ', $arr));
    }

    public function notifyNewTechnicMovementResponsibleUser($dataObj)
    {
        // $dataObj = $this->getDataObj($newData, $dbData);

        $notificationText =
            '<b>Перемещение техники</b>'
            ."\n"
            ."<i>Вы назначены ответственным за перемещение</i>"
            ."\n"."\n"
            . "<b>Техника:</b> " . $this->getCategoryName($dataObj->technic_category_id)
            ."\n"
            ."<b>Объект назначения:</b> ". ProjectObject::find($dataObj->object_id)->short_name
            ."\n"
            ."<b>Дата:</b> ". Carbon::create($dataObj->order_start_date)->format('d.m.Y')
            ."\n"."\n"
        ;

        if($dataObj->previous_object_id) {
            $notificationText = $notificationText.
            "<b>Объект отправки:</b> ". ProjectObject::find($dataObj->previous_object_id)->short_name;
        }

        if($dataObj->previous_object_id) {
            $notificationText = $notificationText.
            "\n";
        }

        if($dataObj->order_comment) {
            $notificationText = $notificationText.
            "<b>Комментарий:</b> ". $dataObj->order_comment;
        }

        $objectPMs = $this->getobjectPMs($dataObj->object_id);

        if($objectPMs->count()) {

            if($dataObj->previous_object_id || $dataObj->order_comment) {
                $notificationText = $notificationText.
                "\n"."\n";
            }

            $notificationText = $notificationText
            ."<b>РП:</b> ";

            $i=1;
            foreach($objectPMs as $manager) {
                $notificationText = $notificationText.
                "<a href='{$manager->getExternalUserUrl()}'>{$manager->format('L f. p.', 'именительный')}</a>";

                if($i < $objectPMs->count()) {
                    $notificationText = $notificationText.
                    ", ";
                }

                $i++;
            }
        }

        dispatchNotify(
            $dataObj->responsible_id,
            $notificationText,
            '',
            NotificationType::EQUIPMENT_MOVEMENT_NOTIFICATION
        );
    }

    // public function getDataObj($newData, $dbData)
    // {
    //     $dataObj = new \stdClass;
    //     $dataObj->technic_category_id = $newData['technic_category_id'] ?? $dbData->technic_category_id ?? null;
    //     $dataObj->technic_id = $newData['technic_id'] ?? $dbData->technic_id ?? null;
    //     $dataObj->order_start_date = $newData['order_start_date'] ?? $dbData->order_start_date ?? null;
    //     $dataObj->order_end_date = $newData['order_end_date'] ?? $dbData->order_end_date ?? null;
    //     $dataObj->responsible_id = $newData['responsible_id'] ?? $dbData->responsible_id ?? null;
    //     $dataObj->previous_responsible_id = $newData['previous_responsible_id'] ?? $dbData->previous_responsible_id ?? null;
    //     $dataObj->object_id = $newData['object_id'] ?? $dbData->object_id ?? null;
    //     $dataObj->previous_object_id = $newData['previous_object_id'] ?? $dbData->previous_object_id ?? null;
    //     $dataObj->order_comment = $newData['order_comment'] ?? $dbData->order_comment ?? null;
    //     $dataObj->finish_result = $newData['finish_result'] ?? $dbData->finish_result ?? null;

    //     return $dataObj;
    // }

    public function getCategoryName($id)
    {
        $categoryName = TechnicCategory::find($id)->name;

        foreach (self::nameAttrs as $elem) {
            if(
                str_starts_with(mb_strtolower($categoryName), mb_strtolower($elem['starts']))
                && str_contains(mb_strtolower($categoryName), mb_strtolower($elem['contains'])))
            return $elem['result'];
        }

        return $categoryName;
    }

    public function getobjectPMs($id)
    {
        $objectPMsIds = ObjectResponsibleUser::where('object_id', $id)
            ->where(
                'object_responsible_user_role_id',
                (new ObjectResponsibleUserRole)->getRoleIdBySlug('TONGUE_PROJECT_MANAGER'))
            ->pluck('user_id')
            ->toArray();

        return User::whereIn('id', $objectPMsIds)->get();
    }

    const nameAttrs = [
        [
            'starts' => 'кранов',
            'contains' => ' вибропогр',
            'result' => 'крановый вибропогружатель'
        ],
        [
            'starts' => 'экскават',
            'contains' => ' вибропогр',
            'result' => 'экскаваторный вибропогружатель'
        ],
        [
            'starts' => 'установ',
            'contains' => ' вдавливания шпунтов',
            'result' => 'установка вдавливания шпунтовой сваи'
        ],
        [
            'starts' => 'гусеничн',
            'contains' => ' кран',
            'result' => 'гусеничный кран'
        ],
        [
            'starts' => 'сваевдавл',
            'contains' => ' устан',
            'result' => 'сваевдавливающая установка'
        ],
        [
            'starts' => 'дизел',
            'contains' => ' электрост',
            'result' => 'дизельная электростанция'
        ],
        [
            'starts' => 'буров',
            'contains' => ' устан',
            'result' => 'буровая установка'
        ],
        [
            'starts' => 'автомобил',
            'contains' => ' кран',
            'result' => 'автомобильный кран'
        ],
    ];
}
