<?php

declare(strict_types=1);

namespace App\Domain\Enum;

use App\Notifications\DefaultNotification;
use App\Notifications\Fuel\ConfirmFuelTankMovingPreviousResponsibleNotification;
use App\Notifications\Fuel\FuelOfficeResponsiblesAboutTankMovingConfirmationDelayedNotification;
use App\Notifications\Fuel\FuelTankMovingConfirmationForOfficeResponsiblesNotification;
use App\Notifications\Fuel\FuelTanksLevelCheckNotification;
use App\Notifications\Fuel\NewFuelTankResponsibleNotification;
use App\Notifications\Labor\LaborCancelNotification;
use App\Notifications\Labor\LaborSafetyNotification;
use App\Notifications\Labor\LaborSignedNotification;
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



    const LABOR_CANCEL = 8;
    const LABOR_SAFETY = 9;
    const LABOR_SIGNED = 10;





    public static function determinateNotificationClassByType(int $type): string
    {
        switch ($type) {
            case self::ONLY_TELEGRAM:
                return OnlyTelegramNotification::class;


            case self::FUEL_NEW_TANK_RESPONSIBLE:
                return NewFuelTankResponsibleNotification::class;
            case self::FUEL_CONFIRM_TANK_MOVING_PREVIOUS_RESPONSIBLE:
                return ConfirmFuelTankMovingPreviousResponsibleNotification::class;
            case self::FUEL_TANK_MOVING_CONFIRMATION_OFFICE_RESPONSIBLES:
                return FuelTankMovingConfirmationForOfficeResponsiblesNotification::class;
            case self::FUEL_NOTIFY_OFFICE_RESPONSIBLES_ABOUT_TANK_MOVING_CONFIRMATION_DELAYED:
                return FuelOfficeResponsiblesAboutTankMovingConfirmationDelayedNotification::class;
            case self::FUEL_TANKS_LEVEL_CHECK:
                return FuelTanksLevelCheckNotification::class;


            case self::LABOR_CANCEL:
                return LaborCancelNotification::class;
            case self::LABOR_SAFETY:
                return LaborSafetyNotification::class;
            case self::LABOR_SIGNED:
                return LaborSignedNotification::class;



            case self::FUEL_NOT_AWAITING_CONFIRMATION:
            case self::DEFAULT:
            default:
                return DefaultNotification::class;
        }
    }
}