<?php

declare(strict_types=1);

namespace App\Domain\Enum;

use App\Notifications\Contractor\ContractorDeletionControlTaskResolutionNotice;
use App\Notifications\DefaultNotification;
use App\Notifications\DocumentFlow\DocumentFlowOnObjectsParticipatesInDocumentFlowNotice;
use App\Notifications\Employee\EmployeeTerminationNotice;
use App\Notifications\Employee\NewEmployeeArrivalNotice;
use App\Notifications\Fuel\ConfirmFuelTankMovingPreviousResponsibleNotification;
use App\Notifications\Fuel\FuelOfficeResponsiblesAboutTankMovingConfirmationDelayedNotification;
use App\Notifications\Fuel\FuelTankMovingConfirmationForOfficeResponsiblesNotification;
use App\Notifications\Fuel\FuelTanksLevelCheckNotification;
use App\Notifications\Fuel\NewFuelTankResponsibleNotification;
use App\Notifications\IncomingCallProcessingNotice;
use App\Notifications\Labor\LaborCancelNotification;
use App\Notifications\Labor\LaborSafetyNotification;
use App\Notifications\Labor\LaborSignedNotification;
use App\Notifications\Object\ObjectParticipatesInWorkProductionNotice;
use App\Notifications\Object\ProjectLeaderAppointedToObjectNotice;
use App\Notifications\Object\ResponsibleAddedToObjectNotice;
use App\Notifications\OnlyTelegramNotification;
use App\Notifications\Operation\OperationApprovalNotice;
use App\Notifications\Operation\OperationRejectionNotice;
use App\Notifications\Operation\WriteOffOperationRejectionNotice;
use App\Notifications\TechnicalMaintence\TechnicalMaintenanceCompletionNotice;
use App\Notifications\TechnicalMaintence\TechnicalMaintenanceNotice;
use App\Notifications\TimestampTechniqueUsageNotice;

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

    const WRITE_OFF_OPERATION_REJECTION_NOTIFICATION = 13;
    const TECHNICAL_MAINTENANCE_NOTICE = 14;
    const TECHNICAL_MAINTENANCE_COMPLETION_NOTICE = 15;


    const CONTRACTOR_DELETION_CONTROL_TASK_RESOLUTION_NOTIFICATION = 20;


    const OPERATION_APPROVAL_NOTIFICATION = 92;
    const OPERATION_REJECTION_NOTIFICATION = 93;


    const TIMESTAMP_TECHNIQUE_USAGE = 110;

    const OBJECT_PARTICIPATES_IN_WORK_PRODUCTION = 112;
    const RESPONSIBLE_ADDED_TO_OBJECT = 113;
    const PROJECT_LEADER_APPOINTED_TO_OBJECT = 114;
    const DOCUMENT_FLOW_ON_OBJECTS_PARTICIPATES_IN_DOCUMENT_FLOW = 115;

    const NEW_EMPLOYEE_ARRIVAL = 200;
    const EMPLOYEE_TERMINATION = 201;
    const INCOMING_CALL_PROCESSING = 204;


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

            case self::WRITE_OFF_OPERATION_REJECTION_NOTIFICATION:
                return WriteOffOperationRejectionNotice::class;

            case self::TECHNICAL_MAINTENANCE_NOTICE:
                return TechnicalMaintenanceNotice::class;

            case self::CONTRACTOR_DELETION_CONTROL_TASK_RESOLUTION_NOTIFICATION:
                return ContractorDeletionControlTaskResolutionNotice::class;

            case self::TECHNICAL_MAINTENANCE_COMPLETION_NOTICE:
                return TechnicalMaintenanceCompletionNotice::class;

            case self::LABOR_CANCEL:
                return LaborCancelNotification::class;
            case self::LABOR_SAFETY:
                return LaborSafetyNotification::class;
            case self::LABOR_SIGNED:
                return LaborSignedNotification::class;


            case self::INCOMING_CALL_PROCESSING:
                return IncomingCallProcessingNotice::class;

            case self::OPERATION_APPROVAL_NOTIFICATION:
                return OperationApprovalNotice::class;
            case self::OPERATION_REJECTION_NOTIFICATION:
                return OperationRejectionNotice::class;

            case self::TIMESTAMP_TECHNIQUE_USAGE:
                return TimestampTechniqueUsageNotice::class;

            case self::OBJECT_PARTICIPATES_IN_WORK_PRODUCTION:
                return ObjectParticipatesInWorkProductionNotice::class;
            case self::RESPONSIBLE_ADDED_TO_OBJECT:
                return ResponsibleAddedToObjectNotice::class;
            case self::PROJECT_LEADER_APPOINTED_TO_OBJECT:
                return ProjectLeaderAppointedToObjectNotice::class;

            case self::DOCUMENT_FLOW_ON_OBJECTS_PARTICIPATES_IN_DOCUMENT_FLOW:
                return DocumentFlowOnObjectsParticipatesInDocumentFlowNotice::class;

            case self::NEW_EMPLOYEE_ARRIVAL:
                return NewEmployeeArrivalNotice::class;
            case self::EMPLOYEE_TERMINATION:
                return EmployeeTerminationNotice::class;

            case self::FUEL_NOT_AWAITING_CONFIRMATION:
            case self::DEFAULT:
            default:
                return DefaultNotification::class;
        }
    }
}
