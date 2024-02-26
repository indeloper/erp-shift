<?php

namespace App\Notifications\Fuel;

use App\Models\Notification;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use App\Models\User;

class FuelNotifications 
{
    public function notifyNewFuelTankResponsibleUser($tank)
    {
        $lastFuelHistoryTransfer = FuelTankTransferHistory::query()
            ->where('fuel_tank_id', $tank->id)->orderByDesc('id')->first();
    
        $previousResponsible = User::find($lastFuelHistoryTransfer->previous_responsible_id);
        $previousResponsibleFIO = $previousResponsible->format('L f. p.', 'родительный') ?? null;
        $previousResponsibleUrl = $previousResponsible->getExternalUserUrl();
               
        $notificationHook = 'notificationHook_confirmFuelTankRecieve-id-' . $tank->id . '_endNotificationHook';
        
        $notificationText = 
            '<b>Перемещение топливной емкости</b>'
            ."\n".
            "<i>Подтвердите перемещение от <a href='{$previousResponsibleUrl}'>{$previousResponsibleFIO}</a> </i>"
            ."\n"."\n"
            ."<b>Номер емкости:</b> {$tank->tank_number}"
            ."\n"
            ."<b>Остаток топлива:</b> {$tank->fuel_level} л"
            ."\n"
            ."<b>С объекта:</b> ". (ProjectObject::find($lastFuelHistoryTransfer->previous_object_id)->short_name ?? null)
            ."\n"
            ."<b>На объект:</b> ". (ProjectObject::find($tank->object_id)->short_name ?? null )
            . ' ' . $notificationHook
            ;
        
        Notification::create([
            'name' => $notificationText,
            'user_id' =>$tank->responsible_id,
            'type' => 0,
        ]);
    }

    public function notifyOfficeResponsiblesAboutFuelTankMovingConfirmationDelayed($tank, $userId)
    { 
        $lastTankTransferHistory = FuelTankTransferHistory::query()
            ->where('fuel_tank_id', $tank->id)
            ->whereNull('fuel_tank_flow_id')
            ->orderByDesc('id')
            ->first();
        
        $newResponsible = User::find($tank->responsible_id);
        $previousResponsible = User::find($lastTankTransferHistory->previous_responsible_id);
        $newResponsibleFIO = $newResponsible->format('L f. p.', 'именительный') ?? null;
        $previousResponsibleFIO = $previousResponsible->format('L f. p.', 'родительный') ?? null;

        $newResponsibleUrl = $newResponsible->getExternalUserUrl();

        $previousResponsibleUrl = $previousResponsible->getExternalUserUrl();
        
        $notificationText = 
            '<b>Перемещение топливной емкости</b>'
            ."\n".
            "<i><a href='{$newResponsibleUrl}'>{$newResponsibleFIO}</a> <u>вовремя не подтвердил</u> перемещение топливной емкости от "
            ." <a href='{$previousResponsibleUrl}'>{$previousResponsibleFIO}</a>"
            . "</i>"
            ."\n"."\n"
            ."<b>Номер емкости:</b> {$tank->tank_number}"
            ."\n"
            ."<b>Остаток топлива:</b> {$tank->fuel_level} л"
            ."\n"
            ."<b>С объекта:</b> ". (ProjectObject::find($lastTankTransferHistory->previous_object_id)->short_name ?? null) . " (<a href='{$previousResponsibleUrl}'>{$previousResponsibleFIO})</a>"
            ."\n"
            ."<b>На объект:</b> ". (ProjectObject::find($tank->object_id)->short_name ?? null ) . " (<a href='{$newResponsibleUrl}'>{$newResponsibleFIO}</a>)"
        ;

        Notification::create([
            'name' => $notificationText,
            'user_id' => $userId,
            'type' => 0,
        ]);
    }

    public function notifyAdminsAboutFuelBalanceMissmatches($data)
    {
        $notificationText = 
            '<b>Ошибка в топливных остатках</b>'
            ."\n"."\n"
            ."<b>Номер емкости:</b> {$data['tank']->tank_number}"
            ."\n"
            ."<b>Id емкости:</b> {$data['tank']->id}"
            ."\n"
            ."<b>Начальная дата:</b> {$data['dateFrom']}"
            ."\n"."\n"
            ."<b>Остатки:</b>"
            ."\n"
            ."Таблица fuel_tanks: {$data['tank']->fuel_level}"
            ."\n"
            ."Последняя запись в TransferHistories: {$data['periodReportTankFuelLevel']}"
            ."\n"
            ."Расчет по сумме топливных операций: {$data['calculatedTankFuelLevel']}"
            ;

            $admins = User::where([
                ['is_su', true],
                ['chat_id', '<>', NULL]
            ])->get();

            foreach($admins as $admin){
                Notification::create([
                    'name' => $notificationText,
                    'user_id' => $admin->id,
                    'type' => 0,
                ]);
            }
    }
}