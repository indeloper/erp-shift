<?php

namespace App\Notifications\Fuel;

use App\Domain\Enum\NotificationType;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;

class FuelNotifications
{
    private $userRepository;

    public function __construct()
    {
        $this->userRepository = app(UserRepositoryInterface::class);
    }

    public function notifyAdminsAboutFuelBalanceMissmatches($data)
    {
            $admins = User::where([
                ['is_su', true],
                ['chat_id', '<>', NULL],
            ])->get();

            foreach($admins as $admin){
                dispatchNotify(
                    $admin->id,
                    'Ошибка в топливных остатках',
                    'Ошибка в топливных остатках',
                    NotificationType::FUEL_TANKS_LEVEL_CHECK,
                    $data
                );
            }
    }

    public function renderNewFuelTankResponsible(FuelTank $tank)
    {
        $lastFuelHistoryTransfer = FuelTankTransferHistory::query()
            ->where('fuel_tank_id', $tank->id)
            ->orderByDesc('id')
            ->first();

        $previousResponsible = $this->userRepository->getUserById(
            $lastFuelHistoryTransfer->previous_responsible_id
        );

        $previousResponsibleFIO = $previousResponsible->format('L f. p.', 'родительный') ?? null;
        $previousResponsibleUrl = $previousResponsible->getExternalUserUrl();


        $fromObject = ProjectObject::find(
            $lastFuelHistoryTransfer->previous_object_id
        )->short_name ?? null;

        $toObject = ProjectObject::find($tank->object_id)->short_name ?? null;

        return view('notifications.telegram.fuel.new-fuel-tank-responsible', compact(
            'previousResponsibleFIO',
            'previousResponsibleUrl',
            'tank',
            'fromObject',
            'toObject'
        ));
    }

}
