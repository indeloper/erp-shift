<?php

namespace App\Console\Commands;

use App\Events\NotificationCreated;
use Illuminate\Console\Command;

use Carbon\Carbon;

use App\Models\Task;
use App\Models\User;
use App\Models\Notification;
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
        $tasks = Task::where('is_solved', 0)->with('project.object')->get();

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
                    $notification = new Notification();
                    $notification->save();
                    $notification->additional_info = ' Ссылка на задачу: ' . $task->task_route();
                    $notification->update([
                        'name' => 'Задача «' . $task->name . '» скоро будет просрочена.',
                        'user_id' => $task->responsible_user_id,
                        'task_id' => $task->id,
                        'contractor_id' => $task->contractor_id,
                        'project_id' => $task->project_id,
                        'object_id' => isset($task->project->object->id) ? $task->project->object->id : null,
                        'created_at' => now(),
                        'type' => 1
                    ]);

                    $task->update(['notify_send' => 1]);
                }
            }
            if ($task->notify_send != 2) {
                if ($diff == 1) {
                    $notification = new Notification();
                    $notification->save();
                    $notification->additional_info = ' Ссылка на задачу: ' . $task->task_route();
                    $notification->update([
                        'name' => 'Задача «' . $task->name . '» просрочена.',
                        'user_id' => $task->responsible_user_id,
                        'task_id' => $task->id,
                        'contractor_id' => $task->contractor_id,
                        'project_id' => $task->project_id,
                        'object_id' => isset($task->project->object->id) ? $task->project->object->id : null,
                        'created_at' => now(),
                        'type' => 2
                    ]);

                    if ($task->project_id) {
                        $route = route('projects::tasks', $task->project_id);
                    } elseif ($task->contractor_id) {
                        $route = route('contractors::tasks', $task->contractor_id);
                    } else {
                        $route = route('tasks::index');
                    }

                    if ($task->chief()) {
                        $notification = new Notification();
                        $notification->save();
                        $notification->additional_info = ' Ссылка на события проекта: ' . $route;
                        $notification->update([
                            'name' => 'Задача исполнителя ' . User::find($task->responsible_user_id)->long_full_name . ' «' . $task->name . '» просрочена.',
                            'user_id' => $task->chief(),
                            'task_id' => $task->id,
                            'contractor_id' => $task->contractor_id,
                            'project_id' => $task->project_id,
                            'object_id' => isset($task->project->object->id) ? $task->project->object->id : null,
                            'created_at' => now(),
                            'type' => 5
                        ]);
                    }

                    $ceo = User::where('group_id', 5/*3*/)->first();

                    if ($task->chief() != $ceo->id and $ceo->id != $task->responsible_user_id){
                        $notification = new Notification();
                        $notification->save();
                        $notification->additional_info = ' Ссылка на события проекта: ' . $route;
                        $notification->update([
                            'name' => 'Задача исполнителя ' . User::find($task->responsible_user_id)->user_name() . ' «' . $task->name . '» просрочена.',
                            'user_id' => $ceo->id,
                            'task_id' => $task->id,
                            'contractor_id' => $task->contractor_id,
                            'project_id' => $task->project_id,
                            'object_id' => isset($task->project->object->id) ? $task->project->object->id : null,
                            'created_at' => Carbon::now(),
                            'type' => 5
                        ]);
                    }

                    $task->update(['notify_send' => 2]);
                }
            }
        }

        DB::commit();
    }
}
