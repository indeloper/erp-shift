<?php

namespace App\Events;

use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\Notification;
use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OperationClosed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function withOutContract(MaterialAccountingOperation $operation)
    {
        $project = $operation->object_to->projects()->whereHas('ready_contracts')->first();

        if ($project and ! in_array($operation->object_id_to, [76, 192])) {
            $task = Task::create([
                'name' => 'Контроль договора в операции '.mb_strtolower($operation->type_name),
                'project_id' => $project->id,
                'responsible_user_id' => $operation->author_id,
                'target_id' => $operation->id,
                'expired_at' => now()->addHours(24),
                'status' => 45,
            ]);

            $notification = new Notification();
            $notification->save();
            $notification->additional_info = '. Перейти к задаче можно по ссылке: '.PHP_EOL.$task->task_route();
            $notification->update([
                'name' => 'Создана задача: '.$task->name,
                'task_id' => $task->id,
                'object_id' => $operation->object_id_to,
                'user_id' => $task->responsible_user_id,
                'type' => 109,
            ]);
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
