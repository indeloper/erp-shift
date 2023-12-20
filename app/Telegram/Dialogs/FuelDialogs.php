<?php

namespace App\Telegram\Dialogs;

use App\Actions\Fuel\FuelActions;
use App\Http\Controllers\Building\TechAccounting\Fuel\FuelTankController;
use App\Models\Notification;
use App\Models\Permission;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use App\Models\User;
use App\Telegram\TelegramApi;
use App\Telegram\TelegramServices;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Telegram\Bot\Laravel\Facades\Telegram;
use morphos\Russian\RussianLanguage;

class FuelDialogs
{
    public function fuelTankMovementConfirmation($event)
    {
        $tank = FuelTank::find(json_decode($event['data'])->eventId);

        if(!$tank->awaiting_confirmation) {
            $message = 
                [
                    'chat_id' => $event['message']['chat']['id'],
                    'parse_mode' => 'HTML',
                    'text' => 'Подтверждение об изменении ответственного топливной емкости № ' . $tank->tank_number . ' не требуется'
                ];

            new TelegramApi('sendMessage', $message);
            (new TelegramServices)->closeDialog($event['message']['chat']['id']);   
        }

        $user = User::where('chat_id', $event['from']['id'])->first();
        (new FuelActions)->handleMovingFuelTankConfirmation($tank, $user);
    }

    public function handleFuelTankMovingDialogMessages(
        $newResponsibleMessageParams, $previousResponsibleMessageParams, $officeResponsiblesMessageParams
    )
    {
        $this->confirmFuelTankMovingNewResponsible($newResponsibleMessageParams);
        $this->confirmFuelTankMovingPreviousResponsible($previousResponsibleMessageParams);
        $this->informFuelTankMovingOfficeResponsibles($officeResponsiblesMessageParams);
    }

    public function confirmFuelTankMovingNewResponsible($newResponsibleMessageParams)
    {
        new TelegramApi('editMessageText', $newResponsibleMessageParams['message']);
        (new TelegramServices)->closeDialog($newResponsibleMessageParams['message']['chat_id']);
    }

    public function confirmFuelTankMovingPreviousResponsible($previousResponsibleMessageParams)
    {
        new TelegramApi('sendMessage', $previousResponsibleMessageParams['message']);
        (new TelegramServices)->closeDialog($previousResponsibleMessageParams['message']['chat_id']);
    }

    public function informFuelTankMovingOfficeResponsibles($officeResponsiblesMessageParams)
    {
        $notificationRecipientsOffice = (new Permission)->getUsersIdsByCodename('notify_about_all_fuel_tanks_transfer');
        foreach ($notificationRecipientsOffice as $userId) { 
            $user =  User::find($userId);
            $message = $officeResponsiblesMessageParams['message'];
            $message['chat_id'] = $user->chat_id;
            new TelegramApi('sendMessage', $message);
            (new TelegramServices)->closeDialog($message['chat_id']);
        }
    }
}
