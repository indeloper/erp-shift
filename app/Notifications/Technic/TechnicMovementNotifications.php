<?php

namespace App\Notifications\Technic;

use App\Models\Building\ObjectResponsibleUser;
use App\Models\Building\ObjectResponsibleUserRole;
use App\Models\Notification;
use App\Models\ProjectObject;
use App\Models\TechAcc\TechnicCategory;
use App\Models\User;
use Carbon\Carbon;
use function morphos\Russian\pluralize;

class TechnicMovementNotifications 
{
    public function notifyNewTechnicMovementResponsibleUser($dataObj)
    {
        // $dataObj = $this->getDataObj($newData, $dbData);

        $notificationText = 
            '<b>Перемещение техники</b>'
            ."\n"
            ."<i>Вы назначены ответственным за перемещение</i>"
            ."\n"."\n"
            ."<b>Техника:</b> ". $this->getCategoryName($dataObj->technic_category_id) 
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

        Notification::create([
            'name' => $notificationText,
            'user_id' =>$dataObj->responsible_id,
            'type' => 0,
        ]);
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
    ];
}