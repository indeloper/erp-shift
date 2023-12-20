<?php

namespace App\Actions\Fuel;

use App\Models\Notification;
use App\Models\Permission;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use App\Telegram\Dialogs\FuelDialogs;
use App\Telegram\TelegramServices;
use Illuminate\Support\Facades\DB;

class FuelActions {

    public function handleMovingFuelTankConfirmation($tank, $user)
    {
        $lastTankTransferHistory = FuelTankTransferHistory::query()
            ->where('fuel_tank_id', $tank->id)
            ->whereNull('fuel_tank_flow_id')
            // ->whereNull('tank_moving_confirmation')
            ->orderByDesc('id')
            ->first();

        FuelTankTransferHistory::create([
            'author_id' => $user->id,
            'fuel_tank_id' => $tank->id,
            'previous_object_id' => $lastTankTransferHistory->previous_object_id ?? null,
            'object_id' => $tank->object_id,
            'previous_responsible_id' => $lastTankTransferHistory->previous_responsible_id ?? null,
            'responsible_id' => $tank->responsible_id,
            'fuel_level' => $tank->fuel_level,
            'event_date' => now(),
            'tank_moving_confirmation' => true
        ]);

                
        // подтверждение новому ответственному
        $newResponsibleMessageParams = (new TelegramServices)->getMessageParams(
            [
                'template' => 'fuelTankMovingConfirmationTextForNewResponsible',
                'tank' => $tank
            ]
        );

        $previousResponsibleMessageParams = (new TelegramServices)->getMessageParams(
            [
                'template' => 'fuelTankMovingConfirmationTextForPreviousResponsible',
                'tank' => $tank
            ]
        );

        $officeResponsiblesMessageParams = (new TelegramServices)->getMessageParams(
            [
                'template' => 'fuelTankMovingConfirmationTextForOfficeResponsibles',
                'tank' => $tank
            ]
        );

        (new FuelDialogs)->handleFuelTankMovingDialogMessages(
            $newResponsibleMessageParams, $previousResponsibleMessageParams, $officeResponsiblesMessageParams
        );  
        
        $tank->awaiting_confirmation = false;
        $tank->comment_movement_tmp = null;
        $tank->chat_message_tmp = null;
        $tank->save();

        $notificationHook = 'notificationHook_confirmFuelTankRecieve-id-' . $tank->id . '_endNotificationHook';
        $notification = Notification::where([
            ['user_id', $user->id],
            ['name', 'LIKE', '%' . $notificationHook . '%']
        ])->orderByDesc('id')->first();

        if ($notification) {
            $notificationWithoutHook = str_replace($notificationHook, '', $notification->name);
            DB::table('notifications')
                ->where('id', $notification->id)
                ->update(['name' => $notificationWithoutHook]);
            // в этой версии laravel не работает saveQuetly, поэтому пришлось делать через DB, чтобы не отправлялось лишнее сообщение
        }
    }

    public function storeFuelTankChatMessageTmp($tankId, $chatId, $text, $messageId)
    {
        $tank = FuelTank::find($tankId);
        $tank->chat_message_tmp = json_encode([
            'chatId' => $chatId,
            'messageId' => $messageId,
            'text' => $text
        ]);
        $tank->save();
    }
}
