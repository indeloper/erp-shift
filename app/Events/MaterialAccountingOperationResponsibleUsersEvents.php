<?php

namespace App\Events;

use App\Domain\Enum\NotificationType;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationResponsibleUsers;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class MaterialAccountingOperationResponsibleUsersEvents
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

    public function respUserCreated(MaterialAccountingOperationResponsibleUsers $user)
    {
        $operation = MaterialAccountingOperation::find($user->operation_id);

        if ($operation->isConflict()) return;

        // RP
        $additional_user = is_array($user->additional_info) ? false : $user->additional_info;
        if ($additional_user == 'skip') return;

        dispatchNotify(
            $additional_user ? $additional_user : $user->user_id,
            $this->generateNotificationText($operation),
            '',
            $this->operationIsDraft($operation) ?
                    NotificationType::OPERATION_CREATION_APPROVAL_REQUEST_NOTIFICATION :
                    NotificationType::RESPONSIBLE_APPOINTMENT_IN_OPERATION_NOTIFICATION,
            [
                'additional_info' => 'Перейти к операции можно по ссылке: ',
                'url' => $operation->general_url,
                'target_id' => $operation->id,
                'status' => 7,
            ]
        );


        if ($operation->isWriteOffOperation() and ! $this->operationIsDraft($operation) and Auth::id() != 13) {
            // create notification for Alexander Ismagilov. Now it is Konstantin Samsonov
            dispatchNotify(
                13,
                $this->generateNotificationText($operation),
                '',
                NotificationType::RESPONSIBLE_APPOINTMENT_IN_OPERATION_NOTIFICATION,
                [
                    'additional_info' => 'Перейти к операции можно по ссылке: ',
                    'url' => $operation->general_url,
                    'target_id' => $operation->id,
                    'status' => 7,
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

    public function generateNotificationText($operation)
    {
        if ($operation->status == 4) return;
        $typeLowered = mb_strtolower($operation->type_name);

        if ($this->operationIsDraft($operation))
            return $this->generateDraftOperationNotification($operation, $typeLowered);

        return $this->generateCreatedOperationNotification($operation, $typeLowered);
    }


    public function operationIsDraft($operation): bool
    {
        return $operation->status == 5;
    }


    public function operationIsMoving($operation): bool
    {
        return $operation->type == 4;
    }


    public function generateCreatedOperationNotification($operation, $typeLowered): string
    {
        $text = "Запланирована новая операция {$typeLowered} материалов на объекте: {$operation->short_name};" .
                " Период выполнения: {$operation->planned_date_from}" . (!in_array($operation->type, [2, 3]) ? " - {$operation->planned_date_to}" : '');

        if ($this->operationIsMoving($operation))
            $text = "Запланирована новая операция {$typeLowered} материалов c объекта {$operation->object_from->name_tag} на объект {$operation->object_to->name_tag};" .
                " Период выполнения: {$operation->planned_date_from} - {$operation->planned_date_to}";

        return $text;
    }


    public function generateDraftOperationNotification($operation, $typeLowered): string
    {
        $text = "Пользователь {$operation->author->long_full_name} запрашивает ваше согласование на {$typeLowered} материалов на объекте: {$operation->short_name};" .
                " Период выполнения: {$operation->planned_date_from}" . (!in_array($operation->type, [2, 3]) ? " - {$operation->planned_date_to}" : '');

        if ($this->operationIsMoving($operation))
            $text = "Пользователь {$operation->author->long_full_name} запрашивает ваше согласование на {$typeLowered} материалов c объекта {$operation->object_from->name_tag} на объект {$operation->object_to->name_tag};" .
                " Период выполнения: {$operation->planned_date_from} - {$operation->planned_date_to}";

        return $text;
    }
}
