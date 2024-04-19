<?php

namespace App\Traits;

use App\Domain\Enum\NotificationType;
use App\Models\{Comment, Group, Notification, Task, User, Project};
use Illuminate\Support\Collection;
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

        foreach (array_filter($user_ids) as $user_id) {
            $notification = new Notification([
                'name' => "Новая заявка о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}",
                'user_id' => $user_id,
                'type' => 65
            ]);
            $notification->additional_info = '. Ссылка на заявку: ' . $defect->card_route();
            $notification->save();

            $defect->notifications()->save($notification);
        }
    }

    public function generateDefectResponsibleAssignmentNotification(Task $task)
    {
        $notification = new Notification([
            'name' => "Новая задача «{$task->name}».",
            'user_id' => $task->responsible_user_id,
            'task_id' => $task->id,
            'created_at' => now(),
            'type' => 66,
        ]);
        $notification->additional_info = ' Ссылка на задачу: ' . $task->task_route();
        $notification->save();
        $task->notifications()->save($notification);
    }

    public function generateDefectDeclineNotification(Defects $defect, $responsible_line = "")
    {
        $user_ids = $this->getNotifiedUsersAndAuthor($defect);

        if ($defect->responsible_user_id) $responsible_line = ", Исполнитель: {$defect->responsible_user->full_name}";
        foreach (array_unique($user_ids) as $user_id) {
            $notification = new Notification([
                'name' => "По заявке о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}{$responsible_line}, неисправность не выявлена, заявка отклонена",
                'user_id' => $user_id,
                'type' => 73
            ]);
            $notification->additional_info = ' Ссылка на заявку: ' . $defect->card_route();
            $notification->save();

            $defect->notifications()->save($notification);
        }
    }

    public function generateDefectAcceptNotification(Defects $defect)
    {
        $user_ids = $this->getNotifiedUsersAndAuthor($defect);

        foreach ($user_ids as $user_id) {
            $notification = new Notification([
                'name' => "По заявке о неисправности №{$defect->id} был установлен период ремонта с {$defect->repair_start} по {$defect->repair_end}, Автор заявки: {$defect->author->full_name}, Исполнитель: {$defect->responsible_user->full_name}",
                'user_id' => $user_id,
                'type' => 74
            ]);
            $notification->additional_info = '. Ссылка на заявку: ' . $defect->card_route();
            $notification->save();

            $defect->notifications()->save($notification);
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
            $notification = new Notification([
                'name' => "Назначен исполнитель на заявку о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}, Исполнитель: {$defect->responsible_user->full_name}",
                'user_id' => $user_id,
                'type' => 67,
            ]);
            $notification->additional_info = ' Ссылка на заявку: ' . $defect->card_route();
            $notification->save();

            $defect->notifications()->save($notification);
        }
    }

    public function generateDefectControlTaskNotification(Task $task)
    {
        $notification = new Notification([
            'name' => "Новая задача «{$task->name}».",
            'user_id' => $task->responsible_user_id,
            'task_id' => $task->id,
            'created_at' => now(),
            'type' => 75,
        ]);
        $notification->additional_info = ' Ссылка на задачу: ' . $task->task_route();
        $notification->save();
        $task->notifications()->save($notification);
    }

    public function generateDefectNewCommentNotifications(Comment $comment, $resp = "")
    {
        $defect = $comment->commentable;
        $user_ids = $this->getAllNotifiedUsers($defect);

        $user_ids = array_diff($user_ids, [$comment->author_id]);

        if ($defect->responsible_user_id) $resp = ", Исполнитель: {$defect->responsible_user->full_name}";
        foreach ($user_ids as $user_id) {
            $notification = new Notification([
                'name' => "Новый комментарий на заявку о неисправности №{$defect->id}, Автор заявки: {$defect->author->full_name}{$resp}: $comment->comment",
                'user_id' => $user_id,
                'type' => 76,
            ]);
            $notification->additional_info = ' Ссылка на заявку: ' . $defect->card_route();
            $notification->save();

            $defect->notifications()->save($notification);
        }
    }

    public function generateDefectRepairDatesUpdateNotification(Defects $defect)
    {
        $user_ids = $this->getNotifiedUsersAndAuthor($defect);

        foreach ($user_ids as $user_id) {
            $notification = new Notification([
                'name' => "По заявке о неисправности №{$defect->id} был изменен период ремонта, новый период: с {$defect->repair_start} по {$defect->repair_end}, Автор заявки: {$defect->author->full_name}, Исполнитель: {$defect->responsible_user->full_name}",
                'user_id' => $user_id,
                'type' => 77,
            ]);
            $notification->additional_info = ' Ссылка на заявку: ' . $defect->card_route();
            $notification->save();

            $defect->notifications()->save($notification);
        }
    }

    public function generateDefectExpireNotification(Defects $defect)
    {
        $user_ids = [$defect->responsible_user_id];
        if ($principal_mechanic = Group::find(47)->getUsers()->first()) $user_ids[] = $principal_mechanic->id;

        foreach ($user_ids as $user_id) {
            $notification = new Notification([
                'name' => "По заявке о неисправности №{$defect->id} в течение 24ч заканчивается период ремонта, Автор заявки: {$defect->author->full_name}, Исполнитель: {$defect->responsible_user->full_name}",
                'user_id' => $user_id,
                'type' => 78,
            ]);
            $notification->additional_info = ' Ссылка на заявку: ' . $defect->card_route();
            $notification->save();

            $defect->notifications()->save($notification);
        }
    }

    public function generateDefectRepairControlTaskNotification(Task $task)
    {
        $notification = new Notification([
            'name' => "Новая задача «{$task->name}».",
            'user_id' => $task->responsible_user_id,
            'task_id' => $task->id,
            'created_at' => now(),
            'type' => 79,
        ]);
        $notification->additional_info = ' Ссылка на задачу: ' . $task->task_route();
        $notification->save();
        $task->notifications()->save($notification);
    }

    public function generateDefectRepairEndNotification(Defects $defect)
    {
        $user_ids = $this->getNotifiedUsersAndAuthor($defect);
        $location = (get_class($defect->defectable) == OurTechnic::class) ? $defect->defectable->start_location->location : $defect->defectable->object->location;

        foreach ($user_ids as $user_id) {
            $notification = new Notification([
                'name' => "По заявке о неисправности №{$defect->id} работы окончены, местоположение техники: {$location}, Исполнитель: {$defect->responsible_user->full_name}",
                'user_id' => $user_id,
                'type' => 80,
            ]);
            $notification->additional_info = ' Ссылка на заявку: ' . $defect->card_route();
            $notification->save();

            $defect->notifications()->save($notification);
        }
    }

    public function generateTicketFailureNotification(OurTechnicTicket $ourTechnicTicket, $result)
    {
        $all_ticket_users = $ourTechnicTicket->users->unique();
        $notification_text = "Заявка №{$ourTechnicTicket->id} ". ($result == 'hold' ? 'удержана' : 'отклонена') . ", инициатор: " . auth()->user()->full_name;

        foreach ($all_ticket_users as $ticket_user) {
            $notification = new Notification([
                'name' => $notification_text,
                'user_id' => $ticket_user->id,
                'created_at' => now(),
                'target_id' => $ourTechnicTicket->id,
                'type' => 85,
            ]);
            $notification->additional_info = "\n" .
                "Ссылка на заявку: " . route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]);
            $notification->save();
        }
    }

    public function generateTicketProcessedNotification(OurTechnicTicket $ourTechnicTicket)
    {
        $all_ticket_users = $ourTechnicTicket->users->unique();
        $notification_text = "На заявку №{$ourTechnicTicket->id} назначен " . ($ourTechnicTicket->vehicles()->count() ? $ourTechnicTicket->vehicles()->first()->full_name . ' ' : '') .
            "время подачи {$ourTechnicTicket->sending_timestamps_text} плановое время прибытия {$ourTechnicTicket->getting_timestamps_text}.";

        foreach ($all_ticket_users as $ticket_user) {
            $notification = new Notification([
                'name' => $notification_text,
                'user_id' => $ticket_user->id,
                'created_at' => now(),
                'target_id' => $ourTechnicTicket->id,
                'type' => 84,
            ]);
            $notification->additional_info = "\n" .
                "Ссылка на заявку: " . route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]);
            $notification->save();
        }
    }

    public function generateTicketAcceptedNotification(OurTechnicTicket $ourTechnicTicket)
    {
        $all_ticket_users = $ourTechnicTicket->users()->wherePivot('type', '!=', 5)->get()->unique();
        $variable_text = $ourTechnicTicket->users()->ofType('author_user_id')->count() ? " Автор заявки: " . $ourTechnicTicket->users()->ofType('author_user_id')->first()->full_name : '';
        $notification_text = "Заявка на технику №{$ourTechnicTicket->id} согласована и ожидает назначения на рейс." . $variable_text;

        foreach ($all_ticket_users as $ticket_user) {
            $notification = new Notification([
                'name' => $notification_text,
                'user_id' => $ticket_user->id,
                'created_at' => now(),
                'target_id' => $ourTechnicTicket->id,
                'type' => 68,
            ]);
            $notification->additional_info = "\n" .
                "Ссылка на заявку: " . route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]);
            $notification->save();
        }
    }

    public function generateDefectDeleteNotification(Defects $defect)
    {
        $user_ids = $this->getNotifiedUsers($defect);

        foreach ($user_ids as $user_id) {
            $notification = new Notification([
                'name' => "Автор заявки {$defect->author->full_name} удалил заявку о неисправности №{$defect->id}.",
                'user_id' => $user_id,
                'type' => 81,
            ]);
            $notification->additional_info = ' Ссылка на заявку: ' . $defect->card_route();
            $notification->save();

            $defect->notifications()->save($notification);
        }
    }

    public function generateMovingNotificationsIfNeeded($task, OurTechnicTicket $ourTechnicTicket): void
    {
        if (in_array($task->status, [31, 32])) {
            $all_ticket_users = $ourTechnicTicket->users->unique();
            $notification_text = trim("По заявке №{$ourTechnicTicket->id} " . $task->get_result);

            foreach ($all_ticket_users as $ticket_user) {
                $notification = new Notification([
                    'name' => $notification_text,
                    'user_id' => $ticket_user->id,
                    'created_at' => now(),
                    'target_id' => $ourTechnicTicket->id,
                    'type' => $task->status == 31 ? 71 : 72,
                ]);
                $notification->additional_info = "\n" .
                    "Ссылка на заявку: " . route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]);
                $notification->save();
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
            $notification = new Notification([
                'name' => "Работы с техникой {$ourTechnicTicket->our_technic->category_name} {$ourTechnicTicket->our_technic->name}, инвентарный номер: {$ourTechnicTicket->our_technic->inventory_number}" .
                    " закончились на объекте: {$ourTechnicTicket->our_technic->start_location->location}.",
                'user_id' => $user_id,
                'type' => 86,
            ]);
            $notification->save();

            $ourTechnicTicket->notifications()->save($notification);
        }
    }

    public function generateOurTechnicTicketUseExtensionNotifications(OurTechnicTicket $ourTechnicTicket)
    {
        $user_ids = Group::find(47)->getUsers()->pluck('id')->toArray();
        // TODO remove hardcode from here somehow
        array_push($user_ids, User::HARDCODED_PERSONS['router']);

        foreach ($user_ids as $user_id) {
            $notification = new Notification([
                'name' => "На объекте: {$ourTechnicTicket->our_technic->start_location->location}" .
                    " изменилась дата окончания использования техники {$ourTechnicTicket->our_technic->category_name}" .
                    " {$ourTechnicTicket->our_technic->name}, инвентарный номер: {$ourTechnicTicket->our_technic->inventory_number}.",
                'user_id' => $user_id,
                'type' => 87,
            ]);
            $notification->save();

            $ourTechnicTicket->notifications()->save($notification);
        }
    }

    public function generateBirthdayTodayNotifications(Collection $users)
    {
        if (empty($users)) return;

        $recipients = User::whereNotIn('id', $users->pluck('id')->toArray())->pluck('id')->toArray();

        foreach ($users as $birthdayPerson) {
            foreach ($recipients as $user_id) {
                $notification = new Notification([
                    'name' => "Сегодня празднует свой день рождения {$birthdayPerson->full_name}!",
                    'user_id' => $user_id,
                    'type' => 89,
                ]);
                $notification->save();
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
                $notification = new Notification([
                    'name' => "{$birthdayDate} празднует свой день рождения {$birthdayPerson->full_name}!",
                    'user_id' => $user_id,
                    'type' => 88,
                ]);
                $notification->save();
            }
        }
    }

    public function generateCertificateControlTaskNotification(Task $task)
    {
        $notification = new Notification([
            'name' => "Новая задача «{$task->name}».",
            'user_id' => $task->responsible_user_id,
            'task_id' => $task->id,
            'created_at' => now(),
            'type' => 104,
        ]);
        $notification->additional_info = ' Ссылка на задачу: ' . $task->task_route();
        $notification->save();
        $task->notifications()->save($notification);
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
            $notification = new Notification([
                'name' => "Пользователь {$task->responsible_user->full_name} получил задачу «{$task->name}».",
                'user_id' => $user_id,
                'task_id' => $task->id,
                'created_at' => now(),
                'type' => 105,
            ]);
            $notification->additional_info = ' Ссылка на задачу: ' . $task->task_route();
            $notification->save();
            $task->notifications()->save($notification);
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
            $notification = new Notification([
                'name' => "В системе существуют операции без сертификатов, связанные с договором {$contract->name_for_humans}.",
                'user_id' => $recipient,
                'created_at' => now(),
                'type' => 106,
                'notificationable_type' => Contract::class,
                'notificationable_id' => $contract->id,
            ]);
            $notification->additional_info = ' Ознакомиться с ними можно по ссылке: ' . route('building::mat_acc::certificateless_operations', ['contract_id' => $contract->id]);
            $notification->save();
        }
    }
}
