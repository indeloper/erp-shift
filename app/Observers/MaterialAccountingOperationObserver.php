<?php

namespace App\Observers;

use App\Models\Group;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\Notification;
use App\Models\Task;
use Carbon\Carbon;

class MaterialAccountingOperationObserver
{
    public function saved(MaterialAccountingOperation $operation)
    {
        if ($operation->isDirty('planned_date_to') || $operation->isDirty('planned_date_from')) {
            if (! Carbon::parse($operation->planned_date_from)->gt(now()->subDays(3)) && ! Carbon::parse($operation->planned_date_to)->gt(now()->subDays(3))) {
                if ($operation->type != 2) {
                    MaterialAccountingOperation::where('id', $operation->id)->update(['status' => 8]);

                    $task = Task::create([
                        'name' => 'Контроль операции '.mb_strtolower($operation->type_name),
                        'responsible_user_id' => Group::find(8)->getUsers()->first()->id, //stinky place
                        'target_id' => $operation->id,
                        'expired_at' => now()->addHours(24),
                        'status' => 38,
                    ]);

                    Notification::create([
                        'name' => 'Создана задача: '.$task->name,
                        'task_id' => $task->id,
                        'user_id' => $task->responsible_user_id,
                        'type' => 95,
                    ]);
                }
            }
        }

        if ($operation->isDirty('is_close') and $operation->is_close == 1) {
            Notification::whereIn('type', [9, 10, 11, 12, 55, 56, 57, 59, 60, 62, 64])->where('target_id', $operation->id)->update(['is_seen' => 1]);
        }
    }

    public function updated(MaterialAccountingOperation $operation): void
    {
        // if operation is complete or closed
        if (in_array($operation->status, [3, 7])) {
            // solve all unsolved tasks
            $operation->unsolved_tasks->each(function (Task $task) {
                $task->solve_n_notify();
            });
        }

        // if arrival operation is completed
        if (($operation->status == 3) && $operation->type == 1) {
            if ($operation->materialsPartTo()->doesntHave('certificates')->exists() and $operation->contract_id) {
                $operation->makeCertificateControlTask();
            }
        }
    }
}
