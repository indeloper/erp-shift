<?php
namespace App\Services\TechAccounting;


use App\Models\Notification;
use App\Models\Task;
use App\Models\TechAcc\OurTechnicTicket;
use App\Models\User;
use App\Traits\TimeCalculator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class TechnicTicketReportService
{
    use TimeCalculator;

    public function checkAndCloseTaskForUserIdForDate($user_id, $date = null)
    {
        $date = $date ?? Carbon::now();
        $user = User::find($user_id);
        $tickets = $tickets ?? $user->technic_tickets()->wherePivot('type', 4)->where('status', 7)->get();

        if ($this->taskIsNotNeededForUserForDate($user, $tickets, $date)) {
            $user->tasks()->where('is_solved', 0)->where('status', 36)->whereDate('created_at', $date)->each(function($task) {
                $task->solve_n_notify();
            });
            return true;
        }
        return false;
    }

    public function checkAndCreateTaskForUserId($user_id, $tickets = null, $date = null)
    {
        $user = User::find($user_id);
        $date = $date ?? Carbon::now();

        $tickets = $tickets ?? $user->technic_tickets()->wherePivot('type', 4)->where('status', 7)->get();

        if ($this->taskIsNeededForUser($user, $tickets, $date)) {
            $task = $user->tasks()->create([
                'name' => "Отметка времени использования техники за " . $date->clone()->isoFormat('DD.MM.YYYY'),
                'expired_at' => $this->addHours(15, $date->clone()),
                'status' => 36,
            ]);
            $task->update(['created_at' => $date]);
            $notification = new Notification([
                'name' => 'Была создана задача ' . '"Ответка времени использования техники за ' . $date->clone()->isoFormat('DD.MM.YYYY') . '"',
                'user_id' => $task->responsible_user_id,
                'created_at' => now(),
                'type' => 110,
                'task_id' => $task->id,
            ]);
            $notification->additional_info = ' Ссылка на задачу: ' . $task->task_route();
            $notification->save();
        }
    }

    public function taskIsNeededForUser($user, $tickets, $date = null)
    {
        $date = $date ?? Carbon::now();
        $task_created = $user->tasks()->where('is_solved', 0)->where('status', 36)->whereDate('created_at', $date)->exists();

        if ($task_created) {
            return false;
        }

        foreach ($tickets as $ticket) {
            $today_report_exists = $ticket->reports()->whereDate('date', $date)->exists();
            if (!$today_report_exists) {
                return true;
            }
        }
        return false;
    }

    public function taskIsNotNeededForUserForDate($user, $tickets, $date = null)
    {
        $date = $date ?? Carbon::now();
        $task_created = $user->tasks()->where('is_solved', 0)->where('status', 36)->whereDate('created_at', $date)->exists();

        if ($date->startOfDay()->greaterThanOrEqualTo(Carbon::now()->startOfDay()) and $this->userIsActiveRespSomewhere($tickets, $user, $date)) {
            return false;
        }
        if (!$task_created) {
            return false;
        }
        if ($this->userIsActiveRespSomewhere($tickets, $user, $date)) {
            foreach ($tickets as $ticket) {
                $today_report_exists = $ticket->reports()->whereDate('date', $date)->exists();
                if (!$today_report_exists) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param $tickets
     * @param $user
     * @return bool
     */
    public function userIsActiveRespSomewhere($tickets, $user, $date = null): bool
    {
        foreach ($tickets as $ticket) {
            if ($date) {
                if ($ticket->users()->ofType('usage_resp_user_id')->whereDate('deactivated_at', '>', $date)->where('id', $user->id)->exists() or $ticket->users()->ofType('usage_resp_user_id')->activeResp()->whereDate('our_technic_ticket_user.created_at', '<=', $date)->where('id', $user->id)->exists()) {
                    return true;
                }
            } else {
                if ($ticket->users()->ofType('usage_resp_user_id')->activeResp()->where('id', $user->id)->exists()) {
                    return true;
                }
            }

        }
        return false;
    }


    public function createCloseTasksForEveryoneEveryday()
    {
        $tickets = OurTechnicTicket::where('status', 7)->get();
        $users = collect();
        foreach ($tickets as $ticket) {
            $users = $users->merge($ticket->users()->ofType('usage_resp_user_id')->get());
        }
        $users = $users->unique('id');

        foreach ($users as $user) {
            $start_period = $user->ticket_responsible->created_at;
            $period = CarbonPeriod::create($start_period, Carbon::now());
            foreach ($period as $date) {
                $this->checkAndCreateTaskForUserId($user->id, $tickets, $date);
                $this->checkAndCloseTaskForUserIdForDate($user->id, $date);
            }
        }

        Task::where('status', 36)->where('is_solved', 1)->whereDate('updated_at', '>=', Carbon::now()->subMinutes(1))->whereTime('updated_at', '>=', Carbon::now()->subMinutes(1))->delete();
    }
}
