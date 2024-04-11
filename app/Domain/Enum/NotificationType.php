<?php

declare(strict_types=1);

namespace App\Domain\Enum;

use App\Notifications\DefaultNotification;
use App\Notifications\Fuel\ConfirmFuelTankMovingPreviousResponsibleNotification;
use App\Notifications\Fuel\FuelOfficeResponsiblesAboutTankMovingConfirmationDelayedNotification;
use App\Notifications\Fuel\FuelTankMovingConfirmationForOfficeResponsiblesNotification;
use App\Notifications\Fuel\FuelTanksLevelCheckNotification;
use App\Notifications\Fuel\NewFuelTankResponsibleNotification;
use App\Notifications\OnlyTelegramNotification;

final class NotificationType
{
    const DEFAULT = 0;
    const ONLY_TELEGRAM = 1;



    const FUEL_NEW_TANK_RESPONSIBLE = 2;
    const FUEL_NOT_AWAITING_CONFIRMATION = 3;
    const FUEL_CONFIRM_TANK_MOVING_PREVIOUS_RESPONSIBLE = 4;
    const FUEL_TANK_MOVING_CONFIRMATION_OFFICE_RESPONSIBLES = 5;
    const FUEL_NOTIFY_OFFICE_RESPONSIBLES_ABOUT_TANK_MOVING_CONFIRMATION_DELAYED = 6;
    const FUEL_TANKS_LEVEL_CHECK = 7;





    public static function determinateNotificationClassByType(int $type): string
    {
        switch ($type) {
            case NotificationType::ONLY_TELEGRAM:
                return OnlyTelegramNotification::class;
            case NotificationType::FUEL_NEW_TANK_RESPONSIBLE:
                return NewFuelTankResponsibleNotification::class;
            case NotificationType::FUEL_CONFIRM_TANK_MOVING_PREVIOUS_RESPONSIBLE:
                return ConfirmFuelTankMovingPreviousResponsibleNotification::class;
            case NotificationType::FUEL_TANK_MOVING_CONFIRMATION_OFFICE_RESPONSIBLES:
                return FuelTankMovingConfirmationForOfficeResponsiblesNotification::class;
            case NotificationType::FUEL_NOTIFY_OFFICE_RESPONSIBLES_ABOUT_TANK_MOVING_CONFIRMATION_DELAYED:
                return FuelOfficeResponsiblesAboutTankMovingConfirmationDelayedNotification::class;
            case NotificationType::FUEL_TANKS_LEVEL_CHECK:
                return FuelTanksLevelCheckNotification::class;
            case NotificationType::FUEL_NOT_AWAITING_CONFIRMATION:
            case NotificationType::DEFAULT:
            default:
                return DefaultNotification::class;
        }
    }
}