<?php

namespace App\Traits;

use App\Notifications\Contract\CertificateAvailabilityControlTaskCreatedNotice;
use App\Notifications\Contract\CertificateAvailabilityControlTaskNotice;
use App\Notifications\Contract\OperationsWithoutCertificatesNotice;
use App\Notifications\Employee\EmployeeBirthdayNextWeekNotice;
use App\Notifications\Employee\EmployeeBirthdayTodayNotice;
use App\Notifications\Equipment\ChiefMechanicMissingForEquipmentDefectTrackingNotice;
use App\Notifications\Technic\RequestProcessedByLogisticianNotice;
use App\Notifications\Technic\TechnicalDeviceFaultReportCreatedNotice;
use App\Notifications\Technic\TechnicalFaultControlTaskNotice;
use App\Notifications\Technic\TechnicalFaultReportAssigneeNotice;
use App\Notifications\Technic\TechnicalFaultReportAssignmentTaskCreationNotice;
use App\Notifications\Technic\TechnicalFaultReportCompletionControlTaskNotice;
use App\Notifications\Technic\TechnicalFaultReportConfirmedNotice;
use App\Notifications\Technic\TechnicalFaultReportDeletedNotice;
use App\Notifications\Technic\TechnicalFaultReportDeletedOrRejectedNotice;
use App\Notifications\Technic\TechnicalFaultReportNewCommentNotice;
use App\Notifications\Technic\TechnicalFaultReportRejectionNotice;
use App\Notifications\Technic\TechnicalFaultReportRepairPeriodChangeNotice;
use App\Notifications\Technic\TechnicalFaultReportRepairPeriodEndingNotice;
use App\Notifications\Technic\TechnicalFaultReportWorkCompletionNotice;
use App\Notifications\Technic\TechnicAvailableNotice;
use App\Notifications\Technic\TechnicDispatchConfirmationNotice;
use App\Notifications\Technic\TechnicExtentionApprovedNotice;
use App\Notifications\Technic\TechnicReceiptConfirmationNotice;
use App\Notifications\Technic\TechnicRequestApprovalNotice;
use Illuminate\Support\Collection;
use App\Models\{Comment, Contract\Contract, Group, Notification\Notification, Task, User, Project};
use App\Models\TechAcc\{OurTechnic, OurTechnicTicket};
use App\Models\TechAcc\Defects\Defects;

trait NotificationGenerator
{
    public function generateDefectCreateNotification(Defects $defect, array $user_ids = [])
    {
        $group_ids = [5, 6, 47];

        foreach ($group_ids as $group_id) {
            $user = Group::find($group_id)->getUsers()->first();
            if ($user) $user_ids[] = $user->id;
        }

        if (get_class($defect->defectable) == OurTechnic::class && $defect->defectable->isVacated())
            $user_ids = array_merge($user_ids, [$defect->defectable->getResponsibleUser()->id]);

        TechnicalDeviceFaultReportCreatedNotice::send(
            $user_ids,
            [
                'name' => "Новая заявка о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}",
                'additional_info' => 'Ссылка на заявку:',
                'url' => $defect->card_route()
            ]
        );
    }

    public function generateDefectResponsibleAssignmentNotification(Task $task)
    {
        TechnicalFaultReportAssignmentTaskCreationNotice::send(
            $task->responsible_user_id,
            [
                'name' => "Новая задача «{$task->name}».",
                'additional_info' => 'Ссылка на задачу: ',
                'url' => $task->task_route(),
                'task_id' => $task->id,
                'created_at' => now(),
            ]
        );
    }

    public function generateDefectDeclineNotification(Defects $defect, $responsible_line = "")
    {
        $user_ids = $this->getNotifiedUsersAndAuthor($defect);

        if ($defect->responsible_user_id) $responsible_line = ", Исполнитель: {$defect->responsible_user->full_name}";
        TechnicalFaultReportRejectionNotice::send(
            $user_ids,
            [
                'name' => "По заявке о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}{$responsible_line}, неисправность не выявлена, заявка отклонена",
                'additional_info' => 'Ссылка на заявку: ',
                'url' => $defect->card_route()
            ]
        );
    }

    public function generateDefectAcceptNotification(Defects $defect)
    {
        $user_ids = $this->getNotifiedUsersAndAuthor($defect);
        TechnicalFaultReportConfirmedNotice::send(
            $user_ids,
            [
                'name' => "По заявке о неисправности №{$defect->id} был установлен период ремонта с {$defect->repair_start} по {$defect->repair_end}, Автор заявки: {$defect->author->full_name}, Исполнитель: {$defect->responsible_user->full_name}",
                'additional_info' => 'Ссылка на заявку: ',
                'url' => $defect->card_route()
            ]
        );
    }

    public function generateNoPrincipleMechanicNotification()
    {
        ChiefMechanicMissingForEquipmentDefectTrackingNotice::send(
            Group::find(5)->getUsers()->first()->id,
            [
                'name' => 'В системе отсутсвует сотрудник на позиции Главного Механика, без него учёт дефектов техники не будет работать',
            ]
        );
    }

    public function generateDefectResponsibleUserStoreNotification(Defects $defect)
    {
        $user_ids = $this->getAllNotifiedUsers($defect);

        TechnicalFaultReportAssigneeNotice::send(
            $user_ids,
            [
                'name' => "Назначен исполнитель на заявку о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}, Исполнитель: {$defect->responsible_user->full_name}",
                'additional_info' => 'Ссылка на заявку:',
                'url' => $defect->card_route()
            ]
        );
    }

    public function generateDefectControlTaskNotification(Task $task)
    {
        TechnicalFaultControlTaskNotice::send(
            $task->responsible_user_id,
            [
                'name' => "Новая задача «{$task->name}».",
                'additional_info' => ' Ссылка на задачу: ',
                'url' => $task->task_route(),
                'task_id' => $task->id,
                'created_at' => now(),
            ]
        );
    }

    public function generateDefectNewCommentNotifications(Comment $comment, $resp = "")
    {
        $defect = $comment->commentable;
        $user_ids = $this->getAllNotifiedUsers($defect);

        $user_ids = array_diff($user_ids, [$comment->author_id]);

        if ($defect->responsible_user_id) $resp = ", Исполнитель: {$defect->responsible_user->full_name}";

        TechnicalFaultReportNewCommentNotice::send(
            $user_ids,
            [
                'name' => "Новый комментарий на заявку о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}{$resp}: $comment->comment",
                'additional_info' => 'Ссылка на заявку: ',
                'url' => $defect->card_route(),
            ]
        );
    }

    public function generateDefectRepairDatesUpdateNotification(Defects $defect)
    {
        $user_ids = $this->getNotifiedUsersAndAuthor($defect);

        TechnicalFaultReportRepairPeriodChangeNotice::send(
            $user_ids,
            [
                'name' => "По заявке о неисправности №{$defect->id} был изменен период ремонта, новый период:
                          с {$defect->repair_start} по {$defect->repair_end}, Автор заявки: {$defect->author->full_name},
                          Исполнитель: {$defect->responsible_user->full_name}",
                'additional_info' => 'Ссылка на заявку: ',
                'url' => $defect->card_route()
            ]
        );
    }

    public function generateDefectExpireNotification(Defects $defect)
    {
        $user_ids = [$defect->responsible_user_id];
        if ($principal_mechanic = Group::find(47)->getUsers()->first()) $user_ids[] = $principal_mechanic->id;

        TechnicalFaultReportRepairPeriodEndingNotice::send(
            $user_ids,
            [
                'name' => "По заявке о неисправности №{$defect->id} в течение 24ч заканчивается период ремонта,
                          Автор заявки: {$defect->author->full_name}, Исполнитель: {$defect->responsible_user->full_name}",
                'additional_info' => 'Ссылка на заявку: ',
                'url' => $defect->card_route()
            ]
        );
    }

    public function generateDefectRepairControlTaskNotification(Task $task)
    {
        TechnicalFaultReportCompletionControlTaskNotice::send(
            $task->responsible_user_id,
            [
                'name' => "Новая задача «{$task->name}».",
                'additional_info' => 'Ссылка на задачу: ',
                'url' => $task->task_route(),
                'task_id' => $task->id,
                'created_at' => now(),
            ]
        );
    }

    public function generateDefectRepairEndNotification(Defects $defect)
    {
        $user_ids = $this->getNotifiedUsersAndAuthor($defect);
        $location = (get_class($defect->defectable) == OurTechnic::class) ? $defect->defectable->start_location->location : $defect->defectable->object->location;

        TechnicalFaultReportWorkCompletionNotice::send(
            $user_ids,
            [
                'name' => "По заявке о неисправности №{$defect->id} работы окончены, местоположение техники: {$location},
                          Исполнитель: {$defect->responsible_user->full_name}",
                'additional_info' => 'Ссылка на заявку: ',
                'url' => $defect->card_route()
            ]
        );
    }

    public function generateTicketFailureNotification(OurTechnicTicket $ourTechnicTicket, $result)
    {
        $all_ticket_users = $ourTechnicTicket->users->unique();
        $notification_text = "Заявка №{$ourTechnicTicket->id} ". ($result == 'hold' ? 'удержана' : 'отклонена') . ", инициатор: " . auth()->user()->full_name;

        $user_ids = $all_ticket_users->pluck('id')->toArray();

        TechnicalFaultReportDeletedOrRejectedNotice::send(
            $user_ids,
            [
                'name' => $notification_text,
                'additional_info' => "Ссылка на заявку: ",
                'url' => route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]),
                'created_at' => now(),
                'target_id' => $ourTechnicTicket->id,
            ]
        );
    }

    public function generateTicketProcessedNotification(OurTechnicTicket $ourTechnicTicket)
    {
        $all_ticket_users = $ourTechnicTicket->users->unique();
        $notification_text = "На заявку №{$ourTechnicTicket->id} назначен " . ($ourTechnicTicket->vehicles()->count() ? $ourTechnicTicket->vehicles()->first()->full_name . ' ' : '') .
            "время подачи {$ourTechnicTicket->sending_timestamps_text} плановое время прибытия {$ourTechnicTicket->getting_timestamps_text}.";

        $user_ids = $all_ticket_users->pluck('id')->toArray();
        RequestProcessedByLogisticianNotice::send(
            $user_ids,
            [
                'name' => $notification_text,
                'additional_info' => "Ссылка на заявку: ",
                'url' => route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]),
                'created_at' => now(),
                'target_id' => $ourTechnicTicket->id,
            ]
        );
    }

    public function generateTicketAcceptedNotification(OurTechnicTicket $ourTechnicTicket)
    {
        $all_ticket_users = $ourTechnicTicket->users()->wherePivot('type', '!=', 5)->get()->unique();
        $variable_text = $ourTechnicTicket->users()->ofType('author_user_id')->count() ? " Автор заявки: " . $ourTechnicTicket->users()->ofType('author_user_id')->first()->full_name : '';
        $notification_text = "Заявка на технику №{$ourTechnicTicket->id} согласована и ожидает назначения на рейс." . $variable_text;

        $user_ids = $all_ticket_users->pluck('id')->toArray();

        TechnicRequestApprovalNotice::send(
            $user_ids,
            [
                'name' => $notification_text,
                'additional_info' => "\nСсылка на заявку: ",
                'url' => route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]),
                'created_at' => now(),
                'target_id' => $ourTechnicTicket->id,
            ]
        );
    }

    public function generateDefectDeleteNotification(Defects $defect)
    {
        $user_ids = $this->getNotifiedUsers($defect);

        TechnicalFaultReportDeletedNotice::send(
            $user_ids,
            [
                'name' => "Автор заявки {$defect->author->full_name} удалил заявку о неисправности №{$defect->id}.",
                'additional_info' => 'Ссылка на заявку: ',
                'url' => $defect->card_route()
            ]
        );
    }

    public function generateMovingNotificationsIfNeeded($task, OurTechnicTicket $ourTechnicTicket): void
    {
        if (in_array($task->status, [31, 32])) {
            $all_ticket_users = $ourTechnicTicket->users->unique();
            $notification_text = trim("По заявке №{$ourTechnicTicket->id} " . $task->get_result);
            $user_ids = $all_ticket_users->pluck('id')->toArray();

            $notificationClass = $task->status == 31 ?
                TechnicDispatchConfirmationNotice::class :
                TechnicReceiptConfirmationNotice::class;

            $notificationClass::send(
                $user_ids,
                [
                    'name' => $notification_text,
                    'additional_info' => "Ссылка на заявку: ",
                    'url' => route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]),
                    'created_at' => now(),
                    'target_id' => $ourTechnicTicket->id,
                ]
            );
        }
    }

    /**
     * Function find notified users and author
     * @param Defects $defect
     * @param $user_ids
     * @return array
     */
    public function getNotifiedUsersAndAuthor(Defects $defect, $user_ids = []): array
    {
        $group_ids = [5, 6, 47];

        foreach ($group_ids as $group_id) {
            $user = Group::find($group_id)->getUsers()->first();
            if ($user) $user_ids[] = $user->id;
        }

        $user_ids[] = $defect->user_id;

        if (get_class($defect->defectable) == OurTechnic::class && $defect->defectable->isVacated())
            $user_ids = array_merge($user_ids, [$defect->defectable->getResponsibleUser()->id]);

        return array_unique(array_filter($user_ids));
    }

    /**
     * Function find notified users
     * @param Defects $defect
     * @param $user_ids
     * @return array
     */
    public function getNotifiedUsers(Defects $defect, $user_ids = []): array
    {
        $group_ids = [5, 6, 47];

        foreach ($group_ids as $group_id) {
            $user = Group::find($group_id)->getUsers()->first();
            if ($user) $user_ids[] = $user->id;
        }

        if (get_class($defect->defectable) == OurTechnic::class && $defect->defectable->isVacated())
            $user_ids = array_merge($user_ids, [$defect->defectable->getResponsibleUser()->id]);

        return array_unique(array_filter($user_ids));
    }

    /**
     * Function find notified users, author and responsible
     * @param Defects $defect
     * @param $user_ids
     * @return array
     */
    public function getAllNotifiedUsers(Defects $defect, $user_ids = []): array
    {
        $group_ids = [5, 6, 47];

        foreach ($group_ids as $group_id) {
            $user = Group::find($group_id)->getUsers()->first();
            if ($user) $user_ids[] = $user->id;
        }

        $user_ids[] = $defect->user_id;
        $user_ids[] = $defect->responsible_user_id;

        if (get_class($defect->defectable) == OurTechnic::class && $defect->defectable->isVacated())
            $user_ids = array_merge($user_ids, [$defect->defectable->getResponsibleUser()->id]);

        return array_unique(array_filter($user_ids));
    }

    public function generateOurTechnicTicketCloseNotifications(OurTechnicTicket $ourTechnicTicket)
    {
        $user_ids = Group::find(47)->getUsers()->pluck('id')->toArray();
        // TODO remove hardcode from here somehow
        array_push($user_ids, User::HARDCODED_PERSONS['router']);
        $user_ids = array_unique($user_ids);

        TechnicAvailableNotice::send(
            $user_ids,
            [
                'name' => "Работы с техникой {$ourTechnicTicket->our_technic->category_name} {$ourTechnicTicket->our_technic->name},
                          инвентарный номер: {$ourTechnicTicket->our_technic->inventory_number} " .
                          " закончились на объекте: {$ourTechnicTicket->our_technic->start_location->location}.",
            ]
        );
    }

    public function generateOurTechnicTicketUseExtensionNotifications(OurTechnicTicket $ourTechnicTicket)
    {
        $user_ids = Group::find(47)->getUsers()->pluck('id')->toArray();
        // TODO remove hardcode from here somehow
        array_push($user_ids, User::HARDCODED_PERSONS['router']);
        $user_ids = array_unique($user_ids);

        TechnicExtentionApprovedNotice::send(
            $user_ids,
            [
                'name' => "На объекте: {$ourTechnicTicket->our_technic->start_location->location}" .
                    " изменилась дата окончания использования техники {$ourTechnicTicket->our_technic->category_name}" .
                    " {$ourTechnicTicket->our_technic->name}, инвентарный номер: {$ourTechnicTicket->our_technic->inventory_number}.",
            ]
        );
    }

    public function generateBirthdayTodayNotifications(Collection $users)
    {
        if (empty($users)) return;

        $recipients = User::whereNotIn('id', $users->pluck('id')->toArray())->pluck('id')->toArray();

        foreach ($users as $birthdayPerson) {
            EmployeeBirthdayTodayNotice::send(
                $recipients,
                [
                    'name' => "Сегодня празднует свой день рождения {$birthdayPerson->full_name}!",
                ]
            );
        }
    }

    public function generateBirthdayNextWeekNotifications(Collection $users)
    {
        if (empty($users)) return;

        $recipients = User::whereNotIn('id', $users->pluck('id')->toArray())->pluck('id')->toArray();
        $birthdayDate = now()->addDays(7)->format('d.m.Y');

        foreach ($users as $birthdayPerson) {
            EmployeeBirthdayNextWeekNotice::send(
                $recipients,
                [
                    'name' => "{$birthdayDate} празднует свой день рождения {$birthdayPerson->full_name}!",
                ]
            );
        }
    }

    public function generateCertificateControlTaskNotification(Task $task)
    {
        CertificateAvailabilityControlTaskNotice::send(
            $task->responsible_user_id,
            [
                'name' => "Новая задача «{$task->name}».",
                'additional_info' => ' Ссылка на задачу: ',
                'url' => $task->task_route(),
                'task_id' => $task->id,
                'created_at' => now(),
            ]
        );
    }

    public function generateNewCertificateControlTaskNotifications(Task $task)
    {
        $user_ids = [];
        $group_ids = [5, 6];

        foreach ($group_ids as $group_id) {
            $user = Group::find($group_id)->getUsers()->first();
            if ($user) $user_ids[] = $user->id;
        }

        CertificateAvailabilityControlTaskCreatedNotice::send(
            $user_ids,
            [
                'name' => "Пользователь {$task->responsible_user->full_name} получил задачу «{$task->name}».",
                'additional_info' => ' Ссылка на задачу: ',
                'url' => $task->task_route(),
                'task_id' => $task->id,
                'created_at' => now(),
            ]
        );
    }

    public function generateCertificatelessOperationsNotification(Contract $contract)
    {
        $projectUsers = $contract->project->respUsers()->whereIn('role', [4, 5, 6])->pluck('user_id');
        $allRecipients = $projectUsers->push([
            User::HARDCODED_PERSONS['certificateWorker'],
            User::HARDCODED_PERSONS['subCEO'],
            User::HARDCODED_PERSONS['mainPTO']
        ])->flatten()->unique();

        OperationsWithoutCertificatesNotice::send(
            $allRecipients,
            [
                'name' => "В системе существуют операции без сертификатов, связанные с договором {$contract->name_for_humans}.",
                'additional_info' => ' Ознакомиться с ними можно по ссылке: ',
                'url' => route('building::mat_acc::certificateless_operations', ['contract_id' => $contract->id]),
                'created_at' => now(),
                'notificationable_type' => Contract::class,
                'notificationable_id' => $contract->id,
            ]
        );
    }
}
