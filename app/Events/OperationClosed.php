<?php

namespace App\Events;

use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\Task;
use App\Notifications\Operation\ContractControlInOperationsTaskNotice;
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

            ContractControlInOperationsTaskNotice::send(
                $task->responsible_user_id,
                [
                    'name' => 'Создана задача: '.$task->name,
                    'additional_info' => 'Перейти к задаче можно по ссылке: ',
                    'url' => $task->task_route(),
                    'task_id' => $task->id,
                    'object_id' => $operation->object_id_to,
                ]
            );
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name')
        ];
    }
}
