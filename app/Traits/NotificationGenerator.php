<?php

namespace App\Traits;

use App\Domain\Enum\NotificationType;
use App\Models\{Comment, Contract\Contract, Group, Notification, Task, User, Project};
use Illuminate\Support\Collection;
use Mockery\Matcher\Not;
use App\Models\{Comment, Group, Notification\Notification, Task, User};
use App\Models\TechAcc\{OurTechnic, OurTechnicTicket};
use App\Models\TechAcc\Defects\Defects;
use Illuminate\Support\Collection;

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

        foreach (array_filter($user_ids) as $user_id) {
            dispatchNotify(
                $user_id,
                "Новая заявка о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}",
                '',
                NotificationType::TECHNICAL_DEVICE_FAULT_REPORT_CREATED_NOTIFICATION,
                [
                    'additional_info' => '. Ссылка на заявку: ' . $defect->card_route()
                ]
            );
        }
    }

    public function generateDefectResponsibleAssignmentNotification(Task $task)
    {
        dispatchNotify(
            $task->responsible_user_id,
            "Новая задача «{$task->name}».",
            '',
            NotificationType::TECHNICAL_FAULT_REPORT_ASSIGNMENT_TASK_CREATION_NOTIFICATION,
            [
                'additional_info' => ' Ссылка на задачу: ' . $task->task_route(),
                'task_id' => $task->id,
                'created_at' => now(),
            ]
        );
    }

    public function generateDefectDeclineNotification(Defects $defect, $responsible_line = "")
    {
        $user_ids = $this->getNotifiedUsersAndAuthor($defect);

        if ($defect->responsible_user_id) $responsible_line = ", Исполнитель: {$defect->responsible_user->full_name}";
        foreach (array_unique($user_ids) as $user_id) {
            dispatchNotify(
                $user_id,
                "По заявке о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}{$responsible_line}, неисправность не выявлена, заявка отклонена",
                '',
                NotificationType::TECHNICAL_FAULT_REPORT_REJECTION_NOTIFICATION,
                [
                    'additional_info' => ' Ссылка на заявку: ' . $defect->card_route()
                ]
            );
        }
    }

    public function generateDefectAcceptNotification(Defects $defect)
    {
        $user_ids = $this->getNotifiedUsersAndAuthor($defect);

        foreach ($user_ids as $user_id) {
            dispatchNotify(
                $user_id,
                "По заявке о неисправности №{$defect->id} был установлен период ремонта с {$defect->repair_start} по {$defect->repair_end}, Автор заявки: {$defect->author->full_name}, Исполнитель: {$defect->responsible_user->full_name}",
                '',
                NotificationType::TECHNICAL_FAULT_REPORT_CONFIRMED_NOTIFICATION,
                [
                    'additional_info' => '. Ссылка на заявку: ' . $defect->card_route()
                ]
            );
        }
    }

    public function generateNoPrincipleMechanicNotification()
    {
        dispatchNotify(
            Group::find(5)->getUsers()->first()->id,
            'В системе отсутсвует сотрудник на позиции Главного Механика, без него учёт дефектов техники не будет работать',
            '',
            NotificationType::CHIEF_MECHANIC_MISSING_FOR_EQUIPMENT_DEFECT_TRACKING
        );
    }

    public function generateDefectResponsibleUserStoreNotification(Defects $defect)
    {
        $user_ids = $this->getAllNotifiedUsers($defect);

        foreach ($user_ids as $user_id) {
            dispatchNotify(
                $user_id,
                "Назначен исполнитель на заявку о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}, Исполнитель: {$defect->responsible_user->full_name}",
                '',
                NotificationType::TECHNICAL_FAULT_REPORT_ASSIGNEE_NOTIFICATION,
                [
                    'additional_info' => ' Ссылка на заявку: ' . $defect->card_route()
                ]
            );
        }
    }

    public function generateDefectControlTaskNotification(Task $task)
    {
        dispatchNotify(
            $task->responsible_user_id,
            "Новая задача «{$task->name}».",
            '',
            NotificationType::TECHNICAL_FAULT_CONTROL_TASK_NOTIFICATION,
            [
                'additional_info' => ' Ссылка на задачу: ' . $task->task_route(),
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
        foreach ($user_ids as $user_id) {
            dispatchNotify(
                $user_id,
                "Новый комментарий на заявку о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}{$resp}: $comment->comment",
                '',
                NotificationType::TECHNICAL_FAULT_REPORT_NEW_COMMENT_NOTIFICATION,
                [
                    'additional_info' => ' Ссылка на заявку: ' . $defect->card_route(),
                ]
            );
        }
    }

    public function generateDefectRepairDatesUpdateNotification(Defects $defect)
    {
        $user_ids = $this->getNotifiedUsersAndAuthor($defect);

        foreach ($user_ids as $user_id) {
            dispatchNotify(
                $user_id,
                "По заявке о неисправности №{$defect->id} был изменен период ремонта, новый период:
                       с {$defect->repair_start} по {$defect->repair_end}, Автор заявки: {$defect->author->full_name},
                       Исполнитель: {$defect->responsible_user->full_name}",
                '',
                NotificationType::TECHNICAL_FAULT_REPORT_REPAIR_PERIOD_CHANGE_NOTIFICATION,
                [
                    'additional_info' => ' Ссылка на заявку: ' . $defect->card_route()
                ]
            );
        }
    }

    public function generateDefectExpireNotification(Defects $defect)
    {
        $user_ids = [$defect->responsible_user_id];
        if ($principal_mechanic = Group::find(47)->getUsers()->first()) $user_ids[] = $principal_mechanic->id;

        foreach ($user_ids as $user_id) {
            dispatchNotify(
                $user_id,
                "По заявке о неисправности №{$defect->id} в течение 24ч заканчивается период ремонта,
                       Автор заявки: {$defect->author->full_name}, Исполнитель: {$defect->responsible_user->full_name}",
                '',
                NotificationType::TECHNICAL_FAULT_REPORT_REPAIR_PERIOD_ENDING_NOTIFICATION,
                [
                    'additional_info' => ' Ссылка на заявку: ' . $defect->card_route()
                ]
            );
        }
    }

    public function generateDefectRepairControlTaskNotification(Task $task)
    {
        dispatchNotify(
            $task->responsible_user_id,
            "Новая задача «{$task->name}».",
            '',
            NotificationType::TECHNICAL_FAULT_REPORT_COMPLETION_CONTROL_TASK_NOTIFICATION,
            [
                'additional_info' => ' Ссылка на задачу: ' . $task->task_route(),
                'task_id' => $task->id,
                'created_at' => now(),
            ]
        );
    }

    public function generateDefectRepairEndNotification(Defects $defect)
    {
        $user_ids = $this->getNotifiedUsersAndAuthor($defect);
        $location = (get_class($defect->defectable) == OurTechnic::class) ? $defect->defectable->start_location->location : $defect->defectable->object->location;

        foreach ($user_ids as $user_id) {
            dispatchNotify(
                $user_id,
                "По заявке о неисправности №{$defect->id} работы окончены, местоположение техники: {$location},
                       Исполнитель: {$defect->responsible_user->full_name}",
                '',
                NotificationType::TECHNICAL_FAULT_REPORT_WORK_COMPLETION_NOTIFICATION,
                [
                    'additional_info' => ' Ссылка на заявку: ' . $defect->card_route()
                ]
            );
        }
    }

    public function generateTicketFailureNotification(OurTechnicTicket $ourTechnicTicket, $result)
    {
        $all_ticket_users = $ourTechnicTicket->users->unique();
        $notification_text = "Заявка №{$ourTechnicTicket->id} ". ($result == 'hold' ? 'удержана' : 'отклонена') . ", инициатор: " . auth()->user()->full_name;

        foreach ($all_ticket_users as $ticket_user) {
            dispatchNotify(
                $ticket_user->id,
                $notification_text,
                '',
                NotificationType::TECHNICAL_FAULT_REPORT_DELETED_OR_REJECTED_NOTIFICATION,
                [
                    'additional_info' => "\nСсылка на заявку: " . route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]),
                    'created_at' => now(),
                    'target_id' => $ourTechnicTicket->id,
                ]
            );

        }
    }

    public function generateTicketProcessedNotification(OurTechnicTicket $ourTechnicTicket)
    {
        $all_ticket_users = $ourTechnicTicket->users->unique();
        $notification_text = "На заявку №{$ourTechnicTicket->id} назначен " . ($ourTechnicTicket->vehicles()->count() ? $ourTechnicTicket->vehicles()->first()->full_name . ' ' : '') .
            "время подачи {$ourTechnicTicket->sending_timestamps_text} плановое время прибытия {$ourTechnicTicket->getting_timestamps_text}.";

        foreach ($all_ticket_users as $ticket_user) {
            dispatchNotify(
                $ticket_user->id,
                $notification_text,
                '',
                NotificationType::REQUEST_PROCESSED_BY_LOGISTICIAN_NOTIFICATION,
                [
                    'additional_info' => "\nСсылка на заявку: " .
                        route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]),
                    'created_at' => now(),
                    'target_id' => $ourTechnicTicket->id,
                ]
            );
        }
    }

    public function generateTicketAcceptedNotification(OurTechnicTicket $ourTechnicTicket)
    {
        $all_ticket_users = $ourTechnicTicket->users()->wherePivot('type', '!=', 5)->get()->unique();
        $variable_text = $ourTechnicTicket->users()->ofType('author_user_id')->count() ? " Автор заявки: " . $ourTechnicTicket->users()->ofType('author_user_id')->first()->full_name : '';
        $notification_text = "Заявка на технику №{$ourTechnicTicket->id} согласована и ожидает назначения на рейс." . $variable_text;

        foreach ($all_ticket_users as $ticket_user) {
            dispatchNotify(
                $ticket_user->id,
                $notification_text,
                '',
                NotificationType::TECHNIC_REQUEST_APPROVAL_NOTIFICATION,
                [
                    'additional_info' => "\nСсылка на заявку: " .
                        route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]),
                    'created_at' => now(),
                    'target_id' => $ourTechnicTicket->id,
                ]
            );
        }
    }

    public function generateDefectDeleteNotification(Defects $defect)
    {
        $user_ids = $this->getNotifiedUsers($defect);

        foreach ($user_ids as $user_id) {
            dispatchNotify(
                $user_id,
                "Автор заявки {$defect->author->full_name} удалил заявку о неисправности №{$defect->id}.",
                '',
                NotificationType::TECHNICAL_FAULT_REPORT_DELETED_NOTIFICATION,
                [
                    'additional_info' => ' Ссылка на заявку: ' . $defect->card_route()
                ]
            );
        }
    }

    public function generateMovingNotificationsIfNeeded($task, OurTechnicTicket $ourTechnicTicket): void
    {
        if (in_array($task->status, [31, 32])) {
            $all_ticket_users = $ourTechnicTicket->users->unique();
            $notification_text = trim("По заявке №{$ourTechnicTicket->id} " . $task->get_result);

            foreach ($all_ticket_users as $ticket_user) {
                dispatchNotify(
                    $ticket_user->id,
                    $notification_text,
                    '',
                    $task->status == 31 ?
                        NotificationType::TECHNIC_DISPATCH_CONFIRMATION_NOTIFICATION :
                        NotificationType::TECHNIC_RECEIPT_CONFIRMATION_NOTIFICATION,
                    [
                        'additional_info' => "\nСсылка на заявку: " . route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]),
                        'created_at' => now(),
                        'target_id' => $ourTechnicTicket->id,
                    ]
                );
            }
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

        foreach ($user_ids as $user_id) {
            dispatchNotify(
                $user_id,
                "Работы с техникой {$ourTechnicTicket->our_technic->category_name} {$ourTechnicTicket->our_technic->name},
                       инвентарный номер: {$ourTechnicTicket->our_technic->inventory_number}" .
                " закончились на объекте: {$ourTechnicTicket->our_technic->start_location->location}.",
                '',
                NotificationType::TECHNIC_AVAILABLE_NOTIFICATION
            );
        }
    }

    public function generateOurTechnicTicketUseExtensionNotifications(OurTechnicTicket $ourTechnicTicket)
    {
        $user_ids = Group::find(47)->getUsers()->pluck('id')->toArray();
        // TODO remove hardcode from here somehow
        array_push($user_ids, User::HARDCODED_PERSONS['router']);

        foreach ($user_ids as $user_id) {
            $ourTechnicTicket->notifications()->save($notification);
            dispatchNotify(
                $user_id,
                "На объекте: {$ourTechnicTicket->our_technic->start_location->location}" .
                " изменилась дата окончания использования техники {$ourTechnicTicket->our_technic->category_name}" .
                " {$ourTechnicTicket->our_technic->name}, инвентарный номер: {$ourTechnicTicket->our_technic->inventory_number}.",
                '',
                NotificationType::TECHNIC_EXTENTION_APPROVED_NOTIFICATION
            );
        }
    }

    public function generateBirthdayTodayNotifications(Collection $users)
    {
        if (empty($users)) return;

        $recipients = User::whereNotIn('id', $users->pluck('id')->toArray())->pluck('id')->toArray();

        foreach ($users as $birthdayPerson) {
            foreach ($recipients as $user_id) {
                dispatchNotify(
                    $user_id,
                    "Сегодня празднует свой день рождения {$birthdayPerson->full_name}!",
                    '',
                    NotificationType::EMPLOYEE_BIRTHDAY_TODAY_NOTIFICATION
                );
            }
        }
    }

    public function generateBirthdayNextWeekNotifications(Collection $users)
    {
        if (empty($users)) return;

        $recipients = User::whereNotIn('id', $users->pluck('id')->toArray())->pluck('id')->toArray();
        $birthdayDate = now()->addDays(7)->format('d.m.Y');

        foreach ($users as $birthdayPerson) {
            foreach ($recipients as $user_id) {
                dispatchNotify(
                    $user_id,
                    "{$birthdayDate} празднует свой день рождения {$birthdayPerson->full_name}!",
                    '',
                    NotificationType::EMPLOYEE_BIRTHDAY_NEXT_WEEK_NOTIFICATION
                );
            }
        }
    }

    public function generateCertificateControlTaskNotification(Task $task)
    {
        dispatchNotify(
            $task->responsible_user_id,
            "Новая задача «{$task->name}».",
            '',
            NotificationType::CERTIFICATE_AVAILABILITY_CONTROL_TASK_NOTIFICATION,
            [
                'additional_info' => ' Ссылка на задачу: ' . $task->task_route(),
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

        foreach ($user_ids as $user_id) {
            dispatchNotify(
                $user_id,
                "Пользователь {$task->responsible_user->full_name} получил задачу «{$task->name}».",
                '',
                NotificationType::CERTIFICATE_AVAILABILITY_CONTROL_TASK_CREATED_NOTIFICATION,
                [
                    'additional_info' => ' Ссылка на задачу: ' . $task->task_route(),
                    'task_id' => $task->id,
                    'created_at' => now(),
                ]
            );
        }
    }

    public function generateCertificatelessOperationsNotification(Contract $contract)
    {
        $projectUsers = $contract->project->respUsers()->whereIn('role', [4, 5, 6])->pluck('user_id');
        $allRecipients = $projectUsers->push([
            User::HARDCODED_PERSONS['certificateWorker'],
            User::HARDCODED_PERSONS['subCEO'],
            User::HARDCODED_PERSONS['mainPTO']
        ])->flatten()->unique();
        foreach ($allRecipients as $recipient) {
            dispatchNotify(
                $recipient,
                "В системе существуют операции без сертификатов, связанные с договором {$contract->name_for_humans}.",
                '',
                NotificationType::OPERATIONS_WITHOUT_CERTIFICATES_NOTIFICATION,
                [
                    'additional_info' => ' Ознакомиться с ними можно по ссылке: ' . route('building::mat_acc::certificateless_operations', ['contract_id' => $contract->id]),
                    'created_at' => now(),
                    'notificationable_type' => Contract::class,
                    'notificationable_id' => $contract->id,
                ]
            );
        }
    }
}
