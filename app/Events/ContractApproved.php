<?php

namespace App\Events;

use App\Models\Contract\Contract;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\Task;
use App\Notifications\Operation\ContractControlInOperationsTaskNotice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContractApproved
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

    public function createTasksForMatAcc(Contract $contract)
    {
        $object_id = $contract->project->object_id;

        $operations = MaterialAccountingOperation::where('object_id_to', $object_id)
            ->whereNotIn('object_id_to', [76, 192])
            ->whereIn('type', [1, 4])
            ->where('contract_id', null)
            ->doesntHave('contractTask')
            ->get();

        foreach ($operations as $operation) {
            $task = Task::create([
                'name' => 'Контроль договора в операции ' . mb_strtolower($operation->type_name),
                'project_id' => $contract->project_id,
                'responsible_user_id' => $operation->author_id,
                'target_id' => $operation->id,
                'expired_at' => now()->addHours(24),
                'status' => 45,
            ]);

            ContractControlInOperationsTaskNotice::send(
                $task->responsible_user_id,
                [
                    'name' => 'Создана задача: ' . $task->name,
                    'additional_info' => 'Перейти к задаче можно по ссылке: ',
                    'url' => $task->task_route(),
                    'task_id' => $task->id,
                    'object_id' => $object_id,
                ]
            );
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
