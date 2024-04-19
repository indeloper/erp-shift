<?php

namespace App\Observers;

use App\Models\Notification\Notification;
use App\Models\Task;

class TaskObserver
{
    /**
     * Handle the task "stored" event.
     *
     * @param  Task  $task
     * @return void
     */
    public function saved(Task $task)
    {
        if ($task->wasRecentlyCreated) {
            if ($task->status == 21) {
                return $this->notificationForWriteOffControlTask($task);
            }
        }
    }

    public function notificationForWriteOffControlTask(Task $task)
    {
        $notification = new Notification();
        $notification->save();
        $notification->additional_info = ' Ссылка на задачу: ' . $task->task_route();
        $notification->update([
            'name' => 'Новая задача «' . $task->name . '»',
            'task_id' => $task->id,
            'user_id' => $task->responsible_user_id,
            'type' => 8,
        ]);
    }
}
