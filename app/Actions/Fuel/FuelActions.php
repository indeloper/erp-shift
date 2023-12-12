<?php

namespace App\Actions\Fuel;

use App\Models\Notification;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use Illuminate\Support\Facades\DB;

class FuelActions {

    public function handleMovingFuelTankConfirmation($tank, $user)
    {
        $lastTankTransferHistory = FuelTankTransferHistory::query()
            ->where('fuel_tank_id', $tank->id)
            ->whereNull('fuel_tank_flow_id')
            // ->whereNull('tank_moving_confirmation')
            ->orderByDesc('id')
            ->firstOrFail();

        FuelTankTransferHistory::create([
            'author_id' => $user->id,
            'fuel_tank_id' => $tank->id,
            'previous_object_id' => $lastTankTransferHistory->previous_object_id,
            'object_id' => $tank->object_id,
            'previous_responsible_id' => $lastTankTransferHistory->previous_responsible_id,
            'responsible_id' => $tank->responsible_id,
            'fuel_level' => $tank->fuel_level,
            'event_date' => now(),
            'tank_moving_confirmation' => true
        ]);

        $tank->awaiting_confirmation = false;
        $tank->comment_movement_tmp = null;
        $tank->save();

        Notification::create([
            'name' => 'Топливная емкость № '.$tank->id.' закреплена за ответственным: '.$user->full_name,
            'user_id' => $user->id,
            'type' => 0,
        ]);

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
}
