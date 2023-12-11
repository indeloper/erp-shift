<?php

namespace App\Telegram\Dialogs;

use App\Actions\Fuel\FuelActions;
use App\Http\Controllers\Building\TechAccounting\Fuel\FuelTankController;
use App\Models\Notification;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use App\Models\User;
use App\Telegram\TelegramApi;
use Illuminate\Support\Facades\DB;
use Telegram\Bot\Laravel\Facades\Telegram;

class FuelDialogs
{

    public function fuelTankMovementConfirmation($fuelTankId, $chatId)
    {
        $tank = FuelTank::find($fuelTankId);
        $user = User::where('chat_id', $chatId)->first();

        $message = [
            'chat_id' => $chatId,
            'parse_mode' => 'HTML',
        ];

        if (!$tank->awaiting_confirmation) {
            $message['text'] = 'Подтверждение об изменении ответственного топливной емкости № ' . $tank->id . ' не требуется';
            new TelegramApi('sendMessage', $message);
        } else {
            (new FuelActions)->handleMovingFuelTankConfirmation($tank, $user);
        }
    }
}
