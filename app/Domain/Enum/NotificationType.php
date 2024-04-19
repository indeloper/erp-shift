<?php

declare(strict_types=1);

namespace App\Domain\Enum;

use App\Notifications\Claim\WorkVolumeClaimProcessingNotice;
use App\Notifications\CommercialOffer\AppointmentOfResponsibleForOfferSheetPilingTaskNotice;
use App\Notifications\CommercialOffer\ApprovalOfOfferSheetPilingTaskNotice;
use App\Notifications\CommercialOffer\CustomerApprovalOfJointOfferTaskNotice;
use App\Notifications\CommercialOffer\CustomerApprovalOfOfferPileDrivingTaskNotice;
use App\Notifications\CommercialOffer\CustomerApprovalOfOfferSheetPilingTaskNotice;
use App\Notifications\CommercialOffer\OfferCreationPilingDirectionTaskNotice;
use App\Notifications\CommercialOffer\OfferCreationSheetPilingTaskNotice;
use App\Notifications\CommercialOffer\OfferProcessingNotice;
use App\Notifications\Contract\ContractDeletionRequestResolutionNotice;
use App\Notifications\Contractor\ContractorDeletionControlTaskNotice;
use App\Notifications\Contractor\ContractorDeletionControlTaskResolutionNotice;
use App\Notifications\DefaultNotification;
use App\Notifications\DocumentFlow\DocumentFlowOnObjectsNewStatusNotice;
use App\Notifications\DocumentFlow\DocumentFlowOnObjectsNotice;
use App\Notifications\DocumentFlow\DocumentFlowOnObjectsParticipatesInDocumentFlowNotice;
use App\Notifications\Employee\EmployeeTerminationNotice;
use App\Notifications\Employee\NewEmployeeArrivalNotice;
use App\Notifications\Employee\UserLeaveSubstitutionNotice;
use App\Notifications\Equipment\ChiefMechanicMissingForEquipmentDefectTrackingNotice;
use App\Notifications\Equipment\EquipmentMovementNotice;
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
use App\Notifications\Object\ResponsibleSelectedForProjectDirectionProjectLeaderNotice;
use App\Notifications\OnlyTelegramNotification;
use App\Notifications\Operation\ContractControlInOperationsTaskNotice;
use App\Notifications\Operation\OperationApprovalNotice;
use App\Notifications\Operation\OperationControlTaskNotice;
use App\Notifications\Operation\OperationCreationApprovalRequestNotice;
use App\Notifications\Operation\OperationRejectionNotice;
use App\Notifications\Operation\ResponsibleAppointmentInOperationNotice;
use App\Notifications\Operation\WriteOffOperationRejectionNotice;
use App\Notifications\Project\NewProjectCreationNotice;
use App\Notifications\Support\SupportTicketApproximateDueDateChangeNotice;
use App\Notifications\Support\SupportTicketStatusChangeNotice;
use App\Notifications\Task\AdditionalWorksApprovalTaskNotice;
use App\Notifications\Task\ContractCreationTaskNotice;
use App\Notifications\Task\ContractorChangesVerificationTaskNotice;
use App\Notifications\Task\DelayedTaskAddedAgainNotice;
use App\Notifications\Task\NewTasksFromDeletedUserNotice;
use App\Notifications\Task\NewTasksFromUserOnLeaveNotice;
use App\Notifications\Task\OfferChangeControlTaskNotice;
use App\Notifications\Task\PartialClosureOperationDeletionRequestNotice;
use App\Notifications\Task\PartialClosureOperationEditRequestNotice;
use App\Notifications\Task\ProjectLeaderAppointmentTaskNotice;
use App\Notifications\Task\SubstituteUserReturnFromLeaveTaskTransferNotice;
use App\Notifications\Task\TaskClosureNotice;
use App\Notifications\Task\TaskCompletionDeadlineApproachingNotice;
use App\Notifications\Task\TaskCompletionDeadlineNotice;
use App\Notifications\Task\TaskPostponedAndClosedNotice;
use App\Notifications\Task\TaskTransferNotificationToNewResponsibleNotice;
use App\Notifications\Task\UserOverdueTaskNotice;
use App\Notifications\Technic\TechnicDispatchConfirmationNotice;
use App\Notifications\Technic\TechnicReceiptConfirmationNotice;
use App\Notifications\Technic\TechnicUsageExtensionRequestApprovalNotice;
use App\Notifications\Technic\TechnicUsageExtensionRequestRejectionNotice;
use App\Notifications\Technic\TechnicUsageStartTaskNotice;
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


    const RESPONSIBLE_APPOINTMENT_IN_OPERATION_NOTIFICATION = 11;

    const WRITE_OFF_OPERATION_REJECTION_NOTIFICATION = 13;
    const TECHNICAL_MAINTENANCE_NOTICE = 14;
    const TECHNICAL_MAINTENANCE_COMPLETION_NOTICE = 15;
    const ADDITIONAL_WORKS_APPROVAL_TASK_NOTIFICATION = 16;
    const CONTRACTOR_DELETION_CONTROL_TASK_NOTIFICATION = 17;

    const CONTRACTOR_DELETION_CONTROL_TASK_RESOLUTION_NOTIFICATION = 20;

    const WORK_VOLUME_CLAIM_PROCESSING_NOTIFICATION = 27;
    const OFFER_CREATION_SHEET_PILING_TASK_NOTIFICATION = 28;
    const OFFER_CREATION_PILING_DIRECTION_TASK = 29;
    const APPOINTMENT_OF_RESPONSIBLE_FOR_OFFER_SHEET_PILING_TASK_NOTIFICATION = 30;
    const APPROVAL_OF_OFFER_SHEET_PILING_TASK_NOTIFICATION = 31;
    const CUSTOMER_APPROVAL_OF_OFFER_SHEET_PILING_TASK_NOTIFICATION = 33;
    const CUSTOMER_APPROVAL_OF_OFFER_PILE_DRIVING_TASK_NOTIFICATION = 34;
    const CUSTOMER_APPROVAL_OF_JOINT_OFFER_TASK_NOTIFICATION = 35;

    const OFFER_PROCESSING_NOTIFICATION = 37;
    const CONTRACT_CREATION_TASK_NOTIFICATION = 38;

    const CONTRACT_DELETION_REQUEST_RESOLUTION_NOTIFICATION = 44;
    const NEW_PROJECT_CREATION_NOTIFICATION = 45;
    const USER_LEAVE_SUBSTITUTION_NOTIFICATION = 46;
    const NEW_TASKS_FROM_USER_ON_LEAVE_NOTIFICATION = 47;
    const SUBSTITUTE_USER_RETURN_FROM_LEAVE_TASK_TRANSFER_NOTIFICATION = 48;
    const NEW_TASKS_FROM_DELETED_USER_NOTIFICATION = 49;

    const OFFER_CHANGE_CONTROL_TASK_NOTIFICATION = 50;
    const SUPPORT_TICKET_APPROXIMATE_DUE_DATE_CHANGE_NOTIFICATION = 53;
    const SUPPORT_TICKET_STATUS_CHANGE_NOTIFICATION = 54;
    const OPERATION_CREATION_APPROVAL_REQUEST_NOTIFICATION = 56;

    const PROJECT_LEADER_APPOINTMENT_TASK_NOTIFICATION = 63;
    const TECHNIC_USAGE_START_TASK_NOTIFICATION = 69;
    const TECHNIC_DISPATCH_CONFIRMATION_NOTIFICATION = 71;
    const TECHNIC_RECEIPT_CONFIRMATION_NOTIFICATION = 72;
    const TECHNIC_USAGE_EXTENSION_REQUEST_APPROVAL_NOTIFICATION = 82;
    const TECHNIC_USAGE_EXTENSION_REQUEST_REJECTION_NOTIFICATION = 83;

    const OPERATION_APPROVAL_NOTIFICATION = 92;
    const OPERATION_REJECTION_NOTIFICATION = 93;
    const CONTRACTOR_CHANGES_VERIFICATION_TASK_NOTIFICATION = 94;
    const OPERATION_CONTROL_TASK_NOTIFICATION = 95;


    const CONTRACT_CONTROL_IN_OPERATIONS_TASK_NOTIFICATION = 109;
    const TIMESTAMP_TECHNIQUE_USAGE = 110;

    const OBJECT_PARTICIPATES_IN_WORK_PRODUCTION = 112;
    const RESPONSIBLE_ADDED_TO_OBJECT = 113;
    const PROJECT_LEADER_APPOINTED_TO_OBJECT = 114;
    const DOCUMENT_FLOW_ON_OBJECTS_PARTICIPATES_IN_DOCUMENT_FLOW = 115;
    const DOCUMENT_FLOW_ON_OBJECTS_NEW_STATUS = 116;
    const RESPONSIBLE_SELECTED_FOR_PROJECT_DIRECTION_PROJECT_LEADER = 117;
    const CHIEF_MECHANIC_MISSING_FOR_EQUIPMENT_DEFECT_TRACKING = 118;
    const EQUIPMENT_MOVEMENT_NOTIFICATION = 119;
    const DOCUMENT_FLOW_ON_OBJECTS_NOTIFICATION = 120;
    const DELAYED_TASK_ADDED_AGAIN_NOTIFICATION = 121;
    const EMPLOYEE_TERMINATION = 122;


    const NEW_EMPLOYEE_ARRIVAL = 200;
    const TASK_COMPLETION_DEADLINE_APPROACHING_NOTIFICATION = 201;
    const TASK_COMPLETION_DEADLINE_NOTIFICATION = 202;
    const TASK_CLOSURE_NOTIFICATION = 203;
    const INCOMING_CALL_PROCESSING = 204;
    const USER_OVERDUE_TASK_NOTIFICATION = 205;
    const TASK_TRANSFER_NOTIFICATION_TO_NEW_RESPONSIBLE = 206;
    const TASK_POSTPONED_AND_CLOSED_NOTIFICATION = 207;
    const PARTIAL_CLOSURE_OPERATION_EDIT_REQUEST_NOTIFICATION = 209;
    const PARTIAL_CLOSURE_OPERATION_DELETION_REQUEST_NOTIFICATION = 210;

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
            case self::RESPONSIBLE_APPOINTMENT_IN_OPERATION_NOTIFICATION:
                return ResponsibleAppointmentInOperationNotice::class;
            case self::WRITE_OFF_OPERATION_REJECTION_NOTIFICATION:
                return WriteOffOperationRejectionNotice::class;
            case self::CONTRACTOR_CHANGES_VERIFICATION_TASK_NOTIFICATION:
                return ContractorChangesVerificationTaskNotice::class;
            case self::OPERATION_CONTROL_TASK_NOTIFICATION:
                return OperationControlTaskNotice::class;

            case self::TECHNICAL_MAINTENANCE_NOTICE:
                return TechnicalMaintenanceNotice::class;

            case self::CONTRACTOR_DELETION_CONTROL_TASK_RESOLUTION_NOTIFICATION:
                return ContractorDeletionControlTaskResolutionNotice::class;
            case self::TECHNICAL_MAINTENANCE_COMPLETION_NOTICE:
                return TechnicalMaintenanceCompletionNotice::class;
            case self::ADDITIONAL_WORKS_APPROVAL_TASK_NOTIFICATION:
                return AdditionalWorksApprovalTaskNotice::class;
            case self::CONTRACTOR_DELETION_CONTROL_TASK_NOTIFICATION:
                return ContractorDeletionControlTaskNotice::class;

            case self::LABOR_CANCEL:
                return LaborCancelNotification::class;
            case self::LABOR_SAFETY:
                return LaborSafetyNotification::class;
            case self::LABOR_SIGNED:
                return LaborSignedNotification::class;


            case self::INCOMING_CALL_PROCESSING:
                return IncomingCallProcessingNotice::class;

            case self::WORK_VOLUME_CLAIM_PROCESSING_NOTIFICATION:
                return WorkVolumeClaimProcessingNotice::class;
            case self::OFFER_CREATION_SHEET_PILING_TASK_NOTIFICATION:
                return OfferCreationSheetPilingTaskNotice::class;
            case self::OFFER_CREATION_PILING_DIRECTION_TASK:
                return OfferCreationPilingDirectionTaskNotice::class;
            case self::APPOINTMENT_OF_RESPONSIBLE_FOR_OFFER_SHEET_PILING_TASK_NOTIFICATION:
                return AppointmentOfResponsibleForOfferSheetPilingTaskNotice::class;
            case self::APPROVAL_OF_OFFER_SHEET_PILING_TASK_NOTIFICATION:
                return ApprovalOfOfferSheetPilingTaskNotice::class;
            case self::CUSTOMER_APPROVAL_OF_OFFER_SHEET_PILING_TASK_NOTIFICATION:
                return CustomerApprovalOfOfferSheetPilingTaskNotice::class;
            case self::CUSTOMER_APPROVAL_OF_OFFER_PILE_DRIVING_TASK_NOTIFICATION:
                return CustomerApprovalOfOfferPileDrivingTaskNotice::class;
            case self::CUSTOMER_APPROVAL_OF_JOINT_OFFER_TASK_NOTIFICATION:
                return CustomerApprovalOfJointOfferTaskNotice::class;

            case self::OFFER_PROCESSING_NOTIFICATION:
                return OfferProcessingNotice::class;
            case self::CONTRACT_CREATION_TASK_NOTIFICATION:
                return ContractCreationTaskNotice::class;

            case self::CONTRACT_DELETION_REQUEST_RESOLUTION_NOTIFICATION:
                return ContractDeletionRequestResolutionNotice::class;
            case self::NEW_PROJECT_CREATION_NOTIFICATION:
                return NewProjectCreationNotice::class;
            case self::USER_LEAVE_SUBSTITUTION_NOTIFICATION:
                return UserLeaveSubstitutionNotice::class;
            case self::NEW_TASKS_FROM_USER_ON_LEAVE_NOTIFICATION:
                return NewTasksFromUserOnLeaveNotice::class;
            case self::SUBSTITUTE_USER_RETURN_FROM_LEAVE_TASK_TRANSFER_NOTIFICATION:
                return SubstituteUserReturnFromLeaveTaskTransferNotice::class;
            case self::NEW_TASKS_FROM_DELETED_USER_NOTIFICATION:
                return NewTasksFromDeletedUserNotice::class;

            case self::OFFER_CHANGE_CONTROL_TASK_NOTIFICATION:
                return OfferChangeControlTaskNotice::class;

            case self::SUPPORT_TICKET_APPROXIMATE_DUE_DATE_CHANGE_NOTIFICATION:
                return SupportTicketApproximateDueDateChangeNotice::class;
            case self::SUPPORT_TICKET_STATUS_CHANGE_NOTIFICATION:
                return SupportTicketStatusChangeNotice::class;
            case self::OPERATION_CREATION_APPROVAL_REQUEST_NOTIFICATION:
                return OperationCreationApprovalRequestNotice::class;

            case self::PROJECT_LEADER_APPOINTMENT_TASK_NOTIFICATION:
                return ProjectLeaderAppointmentTaskNotice::class;

            case self::TECHNIC_USAGE_START_TASK_NOTIFICATION:
                return TechnicUsageStartTaskNotice::class;
            case self::TECHNIC_DISPATCH_CONFIRMATION_NOTIFICATION;
                return TechnicDispatchConfirmationNotice::class;
            case self::TECHNIC_RECEIPT_CONFIRMATION_NOTIFICATION:
                return TechnicReceiptConfirmationNotice::class;

            case self::TECHNIC_USAGE_EXTENSION_REQUEST_APPROVAL_NOTIFICATION:
                return TechnicUsageExtensionRequestApprovalNotice::class;
            case self::TECHNIC_USAGE_EXTENSION_REQUEST_REJECTION_NOTIFICATION:
                return TechnicUsageExtensionRequestRejectionNotice::class;

            case self::OPERATION_APPROVAL_NOTIFICATION:
                return OperationApprovalNotice::class;
            case self::OPERATION_REJECTION_NOTIFICATION:
                return OperationRejectionNotice::class;

            case self::CONTRACT_CONTROL_IN_OPERATIONS_TASK_NOTIFICATION:
                return ContractControlInOperationsTaskNotice::class;
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
            case self::DOCUMENT_FLOW_ON_OBJECTS_NEW_STATUS:
                return DocumentFlowOnObjectsNewStatusNotice::class;
            case self::RESPONSIBLE_SELECTED_FOR_PROJECT_DIRECTION_PROJECT_LEADER:
                return ResponsibleSelectedForProjectDirectionProjectLeaderNotice::class;
            case self::CHIEF_MECHANIC_MISSING_FOR_EQUIPMENT_DEFECT_TRACKING:
                return ChiefMechanicMissingForEquipmentDefectTrackingNotice::class;
            case self::EQUIPMENT_MOVEMENT_NOTIFICATION:
                return EquipmentMovementNotice::class;
            case self::DOCUMENT_FLOW_ON_OBJECTS_NOTIFICATION:
                return DocumentFlowOnObjectsNotice::class;
            case self::DELAYED_TASK_ADDED_AGAIN_NOTIFICATION:
                return DelayedTaskAddedAgainNotice::class;
            case self::TASK_COMPLETION_DEADLINE_APPROACHING_NOTIFICATION:
                return TaskCompletionDeadlineApproachingNotice::class;
            case self::TASK_COMPLETION_DEADLINE_NOTIFICATION:
                return TaskCompletionDeadlineNotice::class;
            case self::USER_OVERDUE_TASK_NOTIFICATION:
                return UserOverdueTaskNotice::class;

            case self::NEW_EMPLOYEE_ARRIVAL:
                return NewEmployeeArrivalNotice::class;
            case self::EMPLOYEE_TERMINATION:
                return EmployeeTerminationNotice::class;

            case self::TASK_CLOSURE_NOTIFICATION:
                return TaskClosureNotice::class;

            case self::TASK_TRANSFER_NOTIFICATION_TO_NEW_RESPONSIBLE:
                return TaskTransferNotificationToNewResponsibleNotice::class;
            case self::TASK_POSTPONED_AND_CLOSED_NOTIFICATION:
                return TaskPostponedAndClosedNotice::class;
            case self::PARTIAL_CLOSURE_OPERATION_EDIT_REQUEST_NOTIFICATION:
                return PartialClosureOperationEditRequestNotice::class;
            case self::PARTIAL_CLOSURE_OPERATION_DELETION_REQUEST_NOTIFICATION:
                return PartialClosureOperationDeletionRequestNotice::class;

            case self::FUEL_NOT_AWAITING_CONFIRMATION:
            case self::DEFAULT:
            default:
                return DefaultNotification::class;
        }
    }
}
