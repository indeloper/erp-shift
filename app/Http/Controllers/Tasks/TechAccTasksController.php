<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\TechAccounting\TechnicTicketReportService;
use App\Traits\NotificationGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TechAccTasksController extends Controller
{
    use NotificationGenerator;

    public function tech_task(Request $request, $id)
    {
        $task = Task::findOrfail($id)->load('taskable');
        $tickets = $task->responsible_user->technic_tickets()->where('status', 7)->whereDoesntHave('reports', function ($q) {
            return $q->whereDate('date', Carbon::now());
        })->get()->unique();

        return view('tasks.tech_task', [
            'task' => $task,
            'tickets' => $tickets,
        ]);
    }

    public function partial_36(Task $task)
    {
        if ($task->is_solved) {
            return redirect(route('tasks::index'));
        }
        $task_date = Carbon::parse($task->created_at);
        $tickets = $task->responsible_user->technic_tickets()
            ->wherePivot('created_at', '<=', $task_date->endOfDay())
            ->wherePivot('type', '4')
            ->where('status', 7)
            ->whereDoesntHave('reports', function ($q) use ($task_date) {
                return $q->whereDate('date', $task_date);
            })->get()->unique();

        $tickets = $tickets->filter(function ($q) use ($task_date) {
            if ($q->ticket_responsible->deactivated_at) {
                return Carbon::parse($q->ticket_responsible->deactivated_at)->endOfDay()->greaterThanOrEqualTo($task_date);
            }

            return true;
        })->values();

        $reported_tickets = $task->responsible_user->technic_tickets()->wherePivot('created_at', '<=', $task_date->endOfDay())->wherePivot('type', '4')->where('status', 7)
            ->with(['reports' => function ($q) use ($task_date) {
                return $q->whereDate('date', $task_date);
            }])
            ->whereHas('reports', function ($q) use ($task_date) {
                return $q->whereDate('date', $task_date);
            })->get()->unique();

        $reported_tickets = $reported_tickets->filter(function ($q) use ($task_date) {
            if ($q->ticket_responsible->deactivated_at) {
                return Carbon::parse($q->ticket_responsible->deactivated_at)->endOfDay()->greaterThanOrEqualTo($task_date);
            }

            return true;
        })->values();

        $task_closed = (new TechnicTicketReportService())->checkAndCloseTaskForUserIdForDate($task->responsible_user->id, $task_date);
        if ($task_closed) {
            return back();
        }

        $tickets->load('our_technic');
        $reported_tickets->load('our_technic');

        return view('tasks.tech_task', [
            'task' => $task,
            'tickets' => $tickets,
            'reported_tickets' => $reported_tickets,
        ]);
    }
}
