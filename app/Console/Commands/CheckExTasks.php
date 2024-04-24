<?php

namespace App\Console\Commands;

use App\Domain\Enum\NotificationType;
use App\Events\NotificationCreated;
use Illuminate\Console\Command;

use Carbon\Carbon;

use App\Models\Notification\Notification;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckExTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:ex-task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify expired tasks to managers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tasks = Task::whereNotIn('status', [40, 41])->where('is_solved', 0)->with('project.object')->get();

        DB::beginTransaction();

        foreach ($tasks as $task) {
            $created = Carbon::parse($task->created_at);
            $expired = Carbon::parse($task->expired_at);
            $diffCreatedExpired = $expired->diffInSeconds($created);
            $diff = $expired <= Carbon::parse(Carbon::now()) ? 1 : 0;
            $diffNow = $expired->diffInSeconds(Carbon::parse(Carbon::now()));

            if ($task->notify_send == 0) {
                $percent = round($diffNow / $diffCreatedExpired, 2);

                if ($percent <= 0.3 && $percent > 0) {
                    dispatchNotify(
                        $task->responsible_user_id,
                        'Задача «' . $task->name . '» скоро будет просрочена.',
                        '',
                        NotificationType::TASK_COMPLETION_DEADLINE_APPROACHING_NOTIFICATION,
                        [
                            'additional_info' => ' Ссылка на задачу: ' . $task->task_route(),
                            'task_id' => $task->id,
                            'contractor_id' => $task->contractor_id,
                            'project_id' => $task->project_id,
                            'object_id' => isset($task->project->object->id) ? $task->project->object->id : null,
                            'created_at' => now(),
                        ]
                    );

                    $task->update(['notify_send' => 1]);
                }
            }
            if ($task->notify_send != 2) {
                if ($diff == 1) {
                    dispatchNotify(
                        $task->responsible_user_id,
                        'Задача «' . $task->name . '» просрочена.',
                        '',
                        NotificationType::TASK_COMPLETION_DEADLINE_NOTIFICATION,
                        [
                            'additional_info' => ' Ссылка на задачу: ' . $task->task_route(),
                            'task_id' => $task->id,
                            'contractor_id' => $task->contractor_id,
                            'project_id' => $task->project_id,
                            'object_id' => isset($task->project->object->id) ? $task->project->object->id : null,
                            'created_at' => now(),
                        ]
                    );

                    if ($task->project_id) {
                        $route = route('projects::tasks', $task->project_id);
                    } elseif ($task->contractor_id) {
                        $route = route('contractors::tasks', $task->contractor_id);
                    } else {
                        $route = route('tasks::index');
                    }

                    if ($task->chief()) {
                        dispatchNotify(
                            $task->chief(),
                            'Задача исполнителя ' . User::find($task->responsible_user_id)->long_full_name . ' «' . $task->name . '» просрочена.',
                            '',
                            NotificationType::USER_OVERDUE_TASK_NOTIFICATION,
                            [
                                'additional_info' => ' Ссылка на события проекта: ' . $route,
                                'task_id' => $task->id,
                                'contractor_id' => $task->contractor_id,
                                'project_id' => $task->project_id,
                                'object_id' => isset($task->project->object->id) ? $task->project->object->id : null,
                                'created_at' => now(),
                            ]
                        );
                    }

                    $ceo = User::where('group_id', 5/*3*/)->first();

                    if ($task->chief() != $ceo->id and $ceo->id != $task->responsible_user_id){
                        dispatchNotify(
                            $ceo->id,
                            'Задача исполнителя ' . User::find($task->responsible_user_id)->user_name() . ' «' . $task->name . '» просрочена.',
                            '',
                            NotificationType::USER_OVERDUE_TASK_NOTIFICATION,
                            [
                                'additional_info' => ' Ссылка на события проекта: ' . $route,
                                'task_id' => $task->id,
                                'contractor_id' => $task->contractor_id,
                                'project_id' => $task->project_id,
                                'object_id' => isset($task->project->object->id) ? $task->project->object->id : null,
                                'created_at' => Carbon::now(),
                            ]
                        );
                    }

                    $task->update(['notify_send' => 2]);
                }
            }
        }

        DB::commit();
    }
}
