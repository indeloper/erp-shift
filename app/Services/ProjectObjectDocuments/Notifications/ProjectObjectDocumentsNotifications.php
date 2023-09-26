<?php

namespace App\Services\ProjectObjectDocuments\Notifications;

use App\Models\ActionLog;
use App\Models\Building\ObjectResponsibleUser;
use App\Models\Building\ObjectResponsibleUserRole;
use App\Models\Notification;
use App\Models\ProjectObject;
use App\Models\ProjectObjectDocuments\ProjectObjectDocument;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentsStatusType;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentStatus;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentType;
use Illuminate\Support\Facades\DB;
use Telegram\Bot\Laravel\Facades\Telegram;

class ProjectObjectDocumentsNotifications {

    protected $notificationsToSendArr;
    protected $twentyDaysBefore;

    public function __construct($notificationsToSendArr = [])
    {
        $this->notificationsToSendArr = $notificationsToSendArr;
        $this->twentyDaysBefore = now()->subDays(20)->format('Y-m-d');
    }

    public function handle(){

        if(!$this->checkNeedSendNotifications())
        return;

        // РД, Акты с площадки, Журналы, ППР
        $this->handleGroup1();

        // ИД
        $this->handleGroup2();

        // Выполнение
        $this->handleGroup3();

        // Акты с площадки, Журналы, ИД, Выполнение
        $this->handleGroup4();

        $this->notifyUsers();

        $this->addDataToActionLog();

    }

    public function notifyUsers()
    {
        foreach($this->notificationsToSendArr as $notification)
        {
            foreach($notification['notificationRecipients'] as $userId)
            {
                Notification::create([
                    'name' => $notification['notificationText'],
                    'user_id' => $userId,
                    'type' => 0,
                ]);
            }
        }
    }

    public function checkNeedSendNotifications() 
    {        
        $isNotificationsTodayAlreadySent = ActionLog::where([
            ['logable_type', 'App\Services\ProjectObjectDocuments\Notifications\ProjectObjectDocumentsNotifications'],
            ['created_at', '>', now()->today()]
        ])->exists();

        if(!$isNotificationsTodayAlreadySent && now()->format('H') >= 16) 
        return true;
                
        return false; 
    }

    public function addDataToActionLog()
    {
        $actions = new \stdClass;
        $actions->event = 'project-object-documents-notifications-sent';
        
        ActionLog::create([
            'logable_id' => 0,
            'logable_type' => 'App\Services\ProjectObjectDocuments\Notifications\ProjectObjectDocumentsNotifications',
            'actions' => $actions,
            'user_id' => 0,
        ]);
    }

    public function addNewElemToNotificationsToSendArrElem($documentsGroupedByObject, $notificationType, $responsiblesRoles)
    {
        foreach($documentsGroupedByObject as $object)
        {
            $rolesIds = ObjectResponsibleUserRole::whereIn('slug', $responsiblesRoles)->pluck('id')->toArray();
           
            $newNotificationsToSendArrElem = 
                [
                    'notificationRecipients' => 
                        ObjectResponsibleUser::query()
                            ->where('object_id', $object->project_object_id)
                            ->whereIn('object_responsible_user_role_id', $rolesIds)
                            ->pluck('user_id'),
                    
                    'notificationText' => $this->getNotificationText($object, $notificationType)
                        
                ];

            $this->notificationsToSendArr[] = $newNotificationsToSendArrElem;
        }
    }

    public function getNotificationText($object, $notificationType)
    {
        if($notificationType ==='handleGroup1')
        return 
            'Документооборот на объектах' . "\n" .
            'Документы не переданные в офис:' . "\n" .
            ProjectObject::find($object->project_object_id)->short_name . "\n" .
            'Количество: ' . $object->total . "\n";

        if($notificationType ==='handleGroup2')
        return 
            'Документооборот на объектах' . "\n" .
            'ИД не вернулось:' . "\n" .
            ProjectObject::find($object->project_object_id)->short_name . "\n" .
            'Количество: ' . $object->total . "\n";

        if($notificationType ==='handleGroup3')
        return 
            'Документооборот на объектах' . "\n" .
            'Выполнение не вернулось:' . "\n" .
            ProjectObject::find($object->project_object_id)->short_name . "\n" .
            'Количество: ' . $object->total . "\n";

        if($notificationType ==='handleGroup4')
        return 
            'Документооборот на объектах' . "\n" .
            'Накопились документы:' . "\n" .
            ProjectObject::find($object->project_object_id)->short_name . "\n" .
            'Количество: ' . $object->total . "\n";
    }

    public function getDocumentsGroupedByObject($documentTypesIds, $documentStatusesIds, $delayed=null)
    {
        return 
            ProjectObjectDocument::query()
                ->whereIn('document_type_id', $documentTypesIds)
                ->whereIn('document_status_id', $documentStatusesIds)
                ->when($delayed, function($query) use ($delayed) {
                    $query->where('created_at', '<=', $delayed);
                })
                ->groupBy('project_object_id')
                ->select('project_object_id', DB::raw('count(*) AS total'))
                ->get();
    }

    public function handleGroup1()
    {
        if(now()->format('d') != 15)
        return;

        $documentTypesIds = ProjectObjectDocumentType::query()
            ->where('name', 'РД')
            ->orWhere('name', 'Акт с площадки')
            ->orWhere('name', 'Журнал')
            ->orWhere('name', 'ППР')
            ->pluck('id')
            ->toArray();

        $documentStatusesIds = ProjectObjectDocumentStatus::query()
            ->where('status_type_id', ProjectObjectDocumentsStatusType::where('slug', 'work_with_document_not_started')->first()->id)
            ->orWhere('status_type_id', ProjectObjectDocumentsStatusType::where('slug', 'work_with_document_in_progress')->first()->id)
            ->pluck('id')
            ->toArray();
            

        $responsiblesRoles = ['TONGUE_PROJECT_MANAGER', 'TONGUE_PTO_ENGINEER', 'TONGUE_FOREMAN'];

        $documentsGroupedByObject = $this->getDocumentsGroupedByObject($documentTypesIds, $documentStatusesIds, $this->twentyDaysBefore);
        
        $this->addNewElemToNotificationsToSendArrElem($documentsGroupedByObject, __FUNCTION__, $responsiblesRoles);
    }

    public function handleGroup2()
    {
        if(now()->format('d') != 28)
        return;

        $documentTypesIds = ProjectObjectDocumentType::query()
            ->where('name', 'ИД')
            ->pluck('id')
            ->toArray();

        $documentStatusesIds = ProjectObjectDocumentStatus::query()
            ->where('status_type_id', ProjectObjectDocumentsStatusType::where('slug', 'work_with_document_in_progress')->first()->id)
            ->pluck('id')
            ->toArray();

        $responsiblesRoles = ['TONGUE_PROJECT_MANAGER', 'TONGUE_PTO_ENGINEER', 'TONGUE_FOREMAN'];

        $documentsGroupedByObject = $this->getDocumentsGroupedByObject($documentTypesIds, $documentStatusesIds, $this->twentyDaysBefore);
        
        $this->addNewElemToNotificationsToSendArrElem($documentsGroupedByObject, __FUNCTION__, $responsiblesRoles);
    }

    public function handleGroup3()
    {
        if(now()->format('d') != 28)
        return;

        $documentTypesIds = ProjectObjectDocumentType::query()
            ->where('name', 'Выполнение')
            ->pluck('id')
            ->toArray();

        $documentStatusesIds = ProjectObjectDocumentStatus::query()
            ->where('status_type_id', ProjectObjectDocumentsStatusType::where('slug', 'work_with_document_in_progress')->first()->id)
            ->pluck('id')
            ->toArray();

        $responsiblesRoles = ['TONGUE_PROJECT_MANAGER', 'TONGUE_PTO_ENGINEER'];

        $documentsGroupedByObject = $this->getDocumentsGroupedByObject($documentTypesIds, $documentStatusesIds, $this->twentyDaysBefore);
        
        $this->addNewElemToNotificationsToSendArrElem($documentsGroupedByObject, __FUNCTION__, $responsiblesRoles);
    }

    public function handleGroup4()
    {
        if(now()->format('d')%3)
        return;

        $documentTypesIds = ProjectObjectDocumentType::query()
            ->where('name', 'Акт с площадки')
            ->orWhere('name', 'Журнал')
            ->orWhere('name', 'ИД')
            ->orWhere('name', 'Выполнение')
            ->pluck('id')
            ->toArray();

        $documentStatusesIds = ProjectObjectDocumentStatus::query()
            ->where('name', 'Передан в офис')
            ->pluck('id')
            ->toArray();

        $responsiblesRoles = ['TONGUE_PTO_ENGINEER'];

        $documentsGroupedByObject = $this->getDocumentsGroupedByObject($documentTypesIds, $documentStatusesIds);
        
        $this->addNewElemToNotificationsToSendArrElem($documentsGroupedByObject, __FUNCTION__, $responsiblesRoles);
    }

    
}