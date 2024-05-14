<?php

namespace App\Telegram\Dialogs;

use App\Actions\Fuel\FuelActions;
use App\Models\Permission;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\User;
use App\Telegram\TelegramApi;
use App\Telegram\TelegramServices;

class FuelDialogs
{
    public function fuelTankMovementConfirmation($event)
    {
        $tank = FuelTank::find(json_decode($event['data'])->eventId);

        if (! $tank->awaiting_confirmation) {
            $message =
                [
                    'chat_id' => $event['message']['chat']['id'],
                    'parse_mode' => 'HTML',
                    'text' => 'Подтверждение об изменении ответственного топливной емкости № '.$tank->tank_number.' не требуется',
                ];

            new TelegramApi('sendMessage', $message);
            (new TelegramServices)->closeDialog($event['message']['chat']['id']);
        }

        $user = User::where('chat_id', $event['from']['id'])->first();
        (new FuelActions)->handleMovingFuelTankConfirmation($tank, $user);
    }

    public function handleFuelTankMovingDialogMessages(
        $newResponsibleMessageParams, $previousResponsibleMessageParams, $officeResponsiblesMessageParams
    ) {
        $this->confirmFuelTankMovingNewResponsible($newResponsibleMessageParams);
        $this->confirmFuelTankMovingPreviousResponsible($previousResponsibleMessageParams);
        $this->informFuelTankMovingOfficeResponsibles($officeResponsiblesMessageParams);
    }

    public function confirmFuelTankMovingNewResponsible($newResponsibleMessageParams)
    {
        if (empty($newResponsibleMessageParams)) {
            return;
        }

        new TelegramApi('editMessageText', $newResponsibleMessageParams['message']);
        (new TelegramServices)->closeDialog($newResponsibleMessageParams['message']['chat_id']);
    }

    public function confirmFuelTankMovingPreviousResponsible($previousResponsibleMessageParams)
    {
        if (empty($previousResponsibleMessageParams)) {
            return;
        }

        new TelegramApi('sendMessage', $previousResponsibleMessageParams['message']);
        (new TelegramServices)->closeDialog($previousResponsibleMessageParams['message']['chat_id']);
    }

    public function informFuelTankMovingOfficeResponsibles($officeResponsiblesMessageParams)
    {
        if (empty($officeResponsiblesMessageParams)) {
            return;
        }

        $notificationRecipientsOffice = (new Permission)->getUsersIdsByCodename('notify_about_all_fuel_tanks_transfer');
        foreach ($notificationRecipientsOffice as $userId) {
            $user = User::find($userId);
            $message = $officeResponsiblesMessageParams['message'];
            $message['chat_id'] = $user->chat_id;
            new TelegramApi('sendMessage', $message);
            (new TelegramServices)->closeDialog($message['chat_id']);
        }
    }
}
