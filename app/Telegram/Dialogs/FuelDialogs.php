<?php

namespace App\Telegram\Dialogs;

use App\Actions\Fuel\FuelActions;
use App\Models\Permission;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use App\Models\User;
use App\Notifications\DefaultNotification;
use App\Notifications\Fuel\ConfirmFuelTankMovingPreviousResponsibleNotification;
use App\Notifications\Fuel\FuelTankMovingConfirmationForOfficeResponsiblesNotification;
use App\Services\Telegram\TelegramServiceInterface;

class FuelDialogs
{
    public function fuelTankMovementConfirmation($event)
    {
        $tank = FuelTank::find(json_decode($event['data'])->eventId);
        $user = User::where('chat_id', $event['from']['id'])->first();

        if(!$tank->awaiting_confirmation) {
            DefaultNotification::send(
                $user->id,
                [
                    'name' => 'Подтверждение об изменении ответственного топливной емкости № ' . $tank->tank_number . ' не требуется',
                    'tank_id' => $tank->id
                ]
            );
        }

        (new FuelActions)->handleMovingFuelTankConfirmation($tank, $user);
    }

    public function handleFuelTankMovingDialogMessages($tank)
    {
        $this->confirmFuelTankMovingNewResponsible($tank);
        $this->confirmFuelTankMovingPreviousResponsible($tank);
        $this->informFuelTankMovingOfficeResponsibles($tank);
    }

    public function confirmFuelTankMovingNewResponsible($tank)
    {
        $chatMessage = json_decode($tank->chat_message_tmp);

        if(!$chatMessage) {
            return [];
        }

        $telegramService = \app(TelegramServiceInterface::class);

        $chatId = $chatMessage->chatId;
        $messageId = $chatMessage->messageId;
        $text = $chatMessage->text;

        $telegramService->editMessageText(
            $chatId,
            $messageId,
            view('notifications.telegram.fuel.confirm-fuel-tank-moving-new-responsible', compact('text'))
        );

    }

    public function confirmFuelTankMovingPreviousResponsible($tank)
    {
        $lastTankTransferHistory = FuelTankTransferHistory::query()
            ->where('fuel_tank_id', $tank->id)
            ->whereNull('fuel_tank_flow_id')
            ->orderByDesc('id')
            ->first();

        $previousResponsible = User::find($lastTankTransferHistory->previous_responsible_id);

        $newResponsible = User::find($tank->responsible_id);

        ConfirmFuelTankMovingPreviousResponsibleNotification::send(
            $previousResponsible->id,
            [
                'name' => 'Перемещение топливной емкости',
                'tank' => $tank,
                'lastTankTransferHistory' => $lastTankTransferHistory,
                'newResponsible' => $newResponsible
            ]
        );
    }

    public function informFuelTankMovingOfficeResponsibles($tank)
    {
        $notificationRecipientsOffice = (new Permission)->getUsersIdsByCodename('notify_about_all_fuel_tanks_transfer');
        foreach ($notificationRecipientsOffice as $userId) {
            $user =  User::find($userId);


            $lastTankTransferHistory = FuelTankTransferHistory::query()
                ->where('fuel_tank_id', $tank->id)
                ->whereNull('fuel_tank_flow_id')
                ->orderByDesc('id')
                ->first();

            $newResponsible = User::find($tank->responsible_id);
            $previousResponsible = User::find($lastTankTransferHistory->previous_responsible_id);

            FuelTankMovingConfirmationForOfficeResponsiblesNotification::send(
                $user->id,
                [
                    'name' => 'Перемещение топливной емкости',
                    'tank' => $tank,
                    'lastTankTransferHistory' => $lastTankTransferHistory,
                    'newResponsible' => $newResponsible,
                    'previousResponsible' => $previousResponsible
                ]
            );
        }
    }
}
