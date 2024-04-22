<?php

declare(strict_types=1);

namespace App\Domain\Enum;

use App\Notifications\Claim\AppointmentOfWorkSupervisorSheetPilingTaskCreationNotice;
use App\Notifications\Claim\PileDrivingCalculationTaskCreationNotice;
use App\Notifications\Claim\SheetPilingCalculationTaskCreationNotice;
use App\Notifications\Claim\SheetPilingWorkExecutionControlTaskCreationNotice;
use App\Notifications\Claim\WorkRequestProcessingTaskCreationNotice;
use App\Notifications\Claim\WorkVolumeClaimProcessingNotice;
use App\Notifications\CommercialOffer\AppointmentOfResponsibleForOfferSheetPilingTaskNotice;
use App\Notifications\CommercialOffer\ApprovalOfOfferSheetPilingTaskNotice;
use App\Notifications\CommercialOffer\CommercialOfferApprovedNotice;
use App\Notifications\CommercialOffer\CustomerApprovalOfJointOfferTaskNotice;
use App\Notifications\CommercialOffer\CustomerApprovalOfOfferPileDrivingTaskNotice;
use App\Notifications\CommercialOffer\CustomerApprovalOfOfferSheetPilingTaskNotice;
use App\Notifications\CommercialOffer\OfferCreationPilingDirectionTaskNotice;
use App\Notifications\CommercialOffer\OfferCreationSheetPilingTaskNotice;
use App\Notifications\CommercialOffer\OfferProcessingNotice;
use App\Notifications\CommercialOffer\PileDrivingOfferApprovalTaskCreationNotice;
use App\Notifications\Contract\CertificateAvailabilityControlTaskCreatedNotice;
use App\Notifications\Contract\CertificateAvailabilityControlTaskNotice;
use App\Notifications\Contract\ContractApprovalControlTaskCreationNotice;
use App\Notifications\Contract\ContractApprovalTaskCreationNotice;
use App\Notifications\Contract\ContractDeletionControlTaskCreationNotice;
use App\Notifications\Contract\ContractDeletionRequestResolutionNotice;
use App\Notifications\Contract\ContractFormationTaskCreationNotice;
use App\Notifications\Contract\ContractSignatureControlTaskCreationNotice;
use App\Notifications\Contract\ContractSignatureControlTaskRecreationNotice;
use App\Notifications\Contract\OperationsWithoutCertificatesNotice;
use App\Notifications\Contractor\ContractorContactInformationRequiredNotice;
use App\Notifications\Contractor\ContractorDeletionControlTaskNotice;
use App\Notifications\Contractor\ContractorDeletionControlTaskResolutionNotice;
use App\Notifications\Contractor\UserCreatedContractorWithoutContactsNotice;
use App\Notifications\DefaultNotification;
use App\Notifications\DocumentFlow\DocumentFlowOnObjectsNewStatusNotice;
use App\Notifications\DocumentFlow\DocumentFlowOnObjectsNotice;
use App\Notifications\DocumentFlow\DocumentFlowOnObjectsParticipatesInDocumentFlowNotice;
use App\Notifications\Employee\EmployeeBirthdayNextWeekNotice;
use App\Notifications\Employee\EmployeeBirthdayTodayNotice;
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
use App\Notifications\Material\MaterialDifferenceNotice;
use App\Notifications\Object\ObjectParticipatesInWorkProductionNotice;
use App\Notifications\Object\ProjectLeaderAppointedToObjectNotice;
use App\Notifications\Object\ResponsibleAddedToObjectNotice;
use App\Notifications\Object\ResponsibleSelectedForProjectDirectionProjectLeaderNotice;
use App\Notifications\OnlyTelegramNotification;
use App\Notifications\Operation\ContractControlInOperationsTaskNotice;
use App\Notifications\Operation\OperationApprovalNotice;
use App\Notifications\Operation\OperationCancelledNotice;
use App\Notifications\Operation\OperationCompletionNotice;
use App\Notifications\Operation\OperationConfirmedNotice;
use App\Notifications\Operation\OperationControlTaskNotice;
use App\Notifications\Operation\OperationCreationApprovalRequestNotice;
use App\Notifications\Operation\OperationCreationRequestUpdatedNotice;
use App\Notifications\Operation\OperationDraftApprovalNotice;
use App\Notifications\Operation\OperationDraftDeclinedNotice;
use App\Notifications\Operation\OperationRejectionNotice;
use App\Notifications\Operation\OperationStatusConflictNotice;
use App\Notifications\Operation\PartialOperationClosureNotice;
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
use App\Notifications\Task\StandardTaskCreationNotice;
use App\Notifications\Task\SubstituteUserReturnFromLeaveTaskTransferNotice;
use App\Notifications\Task\TaskClosureNotice;
use App\Notifications\Task\TaskCompletionDeadlineApproachingNotice;
use App\Notifications\Task\TaskCompletionDeadlineNotice;
use App\Notifications\Task\TaskPostponedAndClosedNotice;
use App\Notifications\Task\TaskTransferNotificationToNewResponsibleNotice;
use App\Notifications\Task\UserOverdueTaskNotice;
use App\Notifications\Task\WriteOffControlTaskCreatedNotice;
use App\Notifications\Technic\RequestProcessedByLogisticianNotice;
use App\Notifications\Technic\RequestProcessingRequiredNotice;
use App\Notifications\Technic\TechnicalDeviceFaultReportCreatedNotice;
use App\Notifications\Technic\TechnicalFaultControlTaskNotice;
use App\Notifications\Technic\TechnicalFaultReportAssigneeNotice;
use App\Notifications\Technic\TechnicalFaultReportAssignmentTaskCreationNotice;
use App\Notifications\Technic\TechnicalFaultReportCompletionControlTaskNotice;
use App\Notifications\Technic\TechnicalFaultReportConfirmedNotice;
use App\Notifications\Technic\TechnicalFaultReportDeletedNotice;
use App\Notifications\Technic\TechnicalFaultReportDeletedOrRejectedNotice;
use App\Notifications\Technic\TechnicalFaultReportNewCommentNotice;
use App\Notifications\Technic\TechnicalFaultReportRejectionNotice;
use App\Notifications\Technic\TechnicalFaultReportRepairPeriodChangeNotice;
use App\Notifications\Technic\TechnicalFaultReportRepairPeriodEndingNotice;
use App\Notifications\Technic\TechnicalFaultReportWorkCompletionNotice;
use App\Notifications\Technic\TechnicAvailableNotice;
use App\Notifications\Technic\TechnicDispatchConfirmationNotice;
use App\Notifications\Technic\TechnicExtentionApprovedNotice;
use App\Notifications\Technic\TechnicReceiptConfirmationNotice;
use App\Notifications\Technic\TechnicRequestApprovalNotice;
use App\Notifications\Technic\TechnicUsageExtensionRequestApprovalNotice;
use App\Notifications\Technic\TechnicUsageExtensionRequestRejectionNotice;
use App\Notifications\Technic\TechnicUsageStartTaskNotice;
use App\Notifications\TechnicalMaintence\TechnicalMaintenanceCompletionNotice;
use App\Notifications\TechnicalMaintence\TechnicalMaintenanceNotice;
use App\Notifications\TimestampTechniqueUsageNotice;

final class NotificationType
{
    const DEFAULT = 0;
    const TASK_COMPLETION_DEADLINE_APPROACHING_NOTIFICATION = 1;
    const TASK_COMPLETION_DEADLINE_NOTIFICATION = 2;
    const TASK_CLOSURE_NOTIFICATION = 3;
    const INCOMING_CALL_PROCESSING = 4;
    const USER_OVERDUE_TASK_NOTIFICATION = 5;
    const TASK_TRANSFER_NOTIFICATION_TO_NEW_RESPONSIBLE = 6;
    const TASK_POSTPONED_AND_CLOSED_NOTIFICATION = 7;
    const WRITE_OFF_CONTROL_TASK_CREATED_NOTIFICATION = 8;
    const PARTIAL_CLOSURE_OPERATION_EDIT_REQUEST_NOTIFICATION = 9;
    const PARTIAL_CLOSURE_OPERATION_DELETION_REQUEST_NOTIFICATION = 10;
    const RESPONSIBLE_APPOINTMENT_IN_OPERATION_NOTIFICATION = 11;
    const MATERIAL_DIFFERENCE_NOTIFICATION = 12;
    const WRITE_OFF_OPERATION_REJECTION_NOTIFICATION = 13;
    const TECHNICAL_MAINTENANCE_NOTICE = 14;
    const TECHNICAL_MAINTENANCE_COMPLETION_NOTICE = 15;
    const ADDITIONAL_WORKS_APPROVAL_TASK_NOTIFICATION = 16;
    const CONTRACTOR_DELETION_CONTROL_TASK_NOTIFICATION = 17;
    const USER_CREATED_CONTRACTOR_WITHOUT_CONTACTS_NOTIFICATION = 18;
    const CONTRACTOR_CONTACT_INFORMATION_REQUIRED_NOTIFICATION = 19;
    const CONTRACTOR_DELETION_CONTROL_TASK_RESOLUTION_NOTIFICATION = 20;
    const SHEET_PILING_CALCULATION_TASK_CREATION_NOTIFICATION = 21;
    const PILE_DRIVING_CALCULATION_TASK_CREATION_NOTIFICATION = 22;
    const WORK_REQUEST_PROCESSING_TASK_CREATION_NOTIFICATION = 23;
    const APPOINTMENT_OF_WORK_SUPERVISOR_SHEET_PILING_TASK_CREATION_NOTIFICATION = 24;
    const SHEET_PILING_WORK_EXECUTION_CONTROL_TASK_CREATION_NOTIFICATION = 25;
    const WORK_VOLUME_CLAIM_PROCESSING_NOTIFICATION = 27;
    const OFFER_CREATION_SHEET_PILING_TASK_NOTIFICATION = 28;
    const OFFER_CREATION_PILING_DIRECTION_TASK_NOTIFICATION = 29;
    const APPOINTMENT_OF_RESPONSIBLE_FOR_OFFER_SHEET_PILING_TASK_NOTIFICATION = 30;
    const APPROVAL_OF_OFFER_SHEET_PILING_TASK_NOTIFICATION = 31;
    const PILE_DRIVING_OFFER_APPROVAL_TASK_CREATION_NOTIFICATION = 32;
    const CUSTOMER_APPROVAL_OF_OFFER_SHEET_PILING_TASK_NOTIFICATION = 33;
    const CUSTOMER_APPROVAL_OF_OFFER_PILE_DRIVING_TASK_NOTIFICATION = 34;
    const CUSTOMER_APPROVAL_OF_JOINT_OFFER_TASK_NOTIFICATION = 35;
    const OFFER_PROCESSING_NOTIFICATION = 37;
    const CONTRACT_CREATION_TASK_NOTIFICATION = 38;
    const CONTRACT_FORMATION_TASK_CREATION_NOTIFICATION = 39;
    const CONTRACT_APPROVAL_TASK_CREATION_NOTIFICATION = 40;
    const CONTRACT_SIGNATURE_CONTROL_TASK_CREATION_NOTIFICATION = 41;
    const CONTRACT_SIGNATURE_CONTROL_TASK_RECREATION_NOTIFICATION = 42;
    const CONTRACT_DELETION_CONTROL_TASK_CREATION_NOTIFICATION = 43;
    const CONTRACT_DELETION_REQUEST_RESOLUTION_NOTIFICATION = 44;
    const NEW_PROJECT_CREATION_NOTIFICATION = 45;
    const USER_LEAVE_SUBSTITUTION_NOTIFICATION = 46;
    const NEW_TASKS_FROM_USER_ON_LEAVE_NOTIFICATION = 47;
    const SUBSTITUTE_USER_RETURN_FROM_LEAVE_TASK_TRANSFER_NOTIFICATION = 48;
    const NEW_TASKS_FROM_DELETED_USER_NOTIFICATION = 49;
    const OFFER_CHANGE_CONTROL_TASK_NOTIFICATION = 50;
    const CONTRACT_APPROVAL_CONTROL_TASK_CREATION_NOTIFICATION = 51;
    const STANDARD_TASK_CREATION_NOTIFICATION = 52;
    const SUPPORT_TICKET_APPROXIMATE_DUE_DATE_CHANGE_NOTIFICATION = 53;
    const SUPPORT_TICKET_STATUS_CHANGE_NOTIFICATION = 54;
    const OPERATION_CANCELLED_NOTIFICATION = 55;
    const OPERATION_CREATION_APPROVAL_REQUEST_NOTIFICATION = 56;
    const OPERATION_DRAFT_APPROVAL_NOTIFICATION = 57;
    const OPERATION_DRAFT_DECLINED_NOTIFICATION = 58;
    const PARTIAL_OPERATION_CLOSURE_NOTIFICATION = 59;
    const OPERATION_COMPLETION_NOTIFICATION = 60;
    const OPERATION_CONFIRMED_NOTIFICATION = 61;
    const OPERATION_STATUS_CONFLICT_NOTIFICATION = 62;
    const PROJECT_LEADER_APPOINTMENT_TASK_NOTIFICATION = 63;
    const OPERATION_CREATION_REQUEST_UPDATED_NOTIFICATION = 64;
    const TECHNICAL_DEVICE_FAULT_REPORT_CREATED_NOTIFICATION = 65;
    const TECHNICAL_FAULT_REPORT_ASSIGNMENT_TASK_CREATION_NOTIFICATION = 66;
    const TECHNICAL_FAULT_REPORT_ASSIGNEE_NOTIFICATION = 67;
    const TECHNIC_REQUEST_APPROVAL_NOTIFICATION = 68;
    const TECHNIC_USAGE_START_TASK_NOTIFICATION = 69;
    const REQUEST_PROCESSING_REQUIRED_NOTIFICATION = 70;
    const TECHNIC_DISPATCH_CONFIRMATION_NOTIFICATION = 71;
    const TECHNIC_RECEIPT_CONFIRMATION_NOTIFICATION = 72;
    const TECHNICAL_FAULT_REPORT_REJECTION_NOTIFICATION = 73;
    const TECHNICAL_FAULT_REPORT_CONFIRMED_NOTIFICATION = 74;
    const TECHNICAL_FAULT_CONTROL_TASK_NOTIFICATION = 75;
    const TECHNICAL_FAULT_REPORT_NEW_COMMENT_NOTIFICATION = 76;
    const TECHNICAL_FAULT_REPORT_REPAIR_PERIOD_CHANGE_NOTIFICATION = 77;
    const TECHNICAL_FAULT_REPORT_REPAIR_PERIOD_ENDING_NOTIFICATION = 78;
    const TECHNICAL_FAULT_REPORT_COMPLETION_CONTROL_TASK_NOTIFICATION = 79;
    const TECHNICAL_FAULT_REPORT_WORK_COMPLETION_NOTIFICATION = 80;
    const TECHNICAL_FAULT_REPORT_DELETED_NOTIFICATION = 81;
    const TECHNIC_USAGE_EXTENSION_REQUEST_APPROVAL_NOTIFICATION = 82;
    const TECHNIC_USAGE_EXTENSION_REQUEST_REJECTION_NOTIFICATION = 83;
    const REQUEST_PROCESSED_BY_LOGISTICIAN_NOTIFICATION = 84;
    const TECHNICAL_FAULT_REPORT_DELETED_OR_REJECTED_NOTIFICATION = 85;
    const TECHNIC_AVAILABLE_NOTIFICATION = 86;
    const TECHNIC_EXTENTION_APPROVED_NOTIFICATION = 87;
    const EMPLOYEE_BIRTHDAY_NEXT_WEEK_NOTIFICATION = 88;
    const EMPLOYEE_BIRTHDAY_TODAY_NOTIFICATION = 89;
    const OPERATION_APPROVAL_NOTIFICATION = 92;
    const OPERATION_REJECTION_NOTIFICATION = 93;
    const CONTRACTOR_CHANGES_VERIFICATION_TASK_NOTIFICATION = 94;
    const OPERATION_CONTROL_TASK_NOTIFICATION = 95;


    const CERTIFICATE_AVAILABILITY_CONTROL_TASK_NOTIFICATION = 104;
    const CERTIFICATE_AVAILABILITY_CONTROL_TASK_CREATED_NOTIFICATION = 105;
    const OPERATIONS_WITHOUT_CERTIFICATES_NOTIFICATION = 106;
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
    const COMMERCIAL_OFFER_APPROVED_NOTIFICATION = 123;
    const LABOR_SAFETY = 124;
    const LABOR_SIGNED = 125;
    const NEW_EMPLOYEE_ARRIVAL = 126;

    const ONLY_TELEGRAM = 201;
    const FUEL_NEW_TANK_RESPONSIBLE = 202;
    const FUEL_NOT_AWAITING_CONFIRMATION = 203;
    const FUEL_CONFIRM_TANK_MOVING_PREVIOUS_RESPONSIBLE = 204;
    const FUEL_TANK_MOVING_CONFIRMATION_OFFICE_RESPONSIBLES = 205;
    const FUEL_NOTIFY_OFFICE_RESPONSIBLES_ABOUT_TANK_MOVING_CONFIRMATION_DELAYED = 206;
    const FUEL_TANKS_LEVEL_CHECK = 207;
    const LABOR_CANCEL = 208;

    public static function determinateNotificationClassByType(int $type): string
    {
        switch ($type) {
            case self::ONLY_TELEGRAM:
                return OnlyTelegramNotification::class;

            case self::TASK_COMPLETION_DEADLINE_APPROACHING_NOTIFICATION:
                return TaskCompletionDeadlineApproachingNotice::class;
            case self::TASK_COMPLETION_DEADLINE_NOTIFICATION:
                return TaskCompletionDeadlineNotice::class;
            case self::TASK_CLOSURE_NOTIFICATION:
                return TaskClosureNotice::class;
            case self::INCOMING_CALL_PROCESSING:
                return IncomingCallProcessingNotice::class;
            case self::USER_OVERDUE_TASK_NOTIFICATION:
                return UserOverdueTaskNotice::class;
            case self::TASK_TRANSFER_NOTIFICATION_TO_NEW_RESPONSIBLE:
                return TaskTransferNotificationToNewResponsibleNotice::class;
            case self::TASK_POSTPONED_AND_CLOSED_NOTIFICATION:
                return TaskPostponedAndClosedNotice::class;
            case self::WRITE_OFF_CONTROL_TASK_CREATED_NOTIFICATION:
                return WriteOffControlTaskCreatedNotice::class;
            case self::PARTIAL_CLOSURE_OPERATION_EDIT_REQUEST_NOTIFICATION:
                return PartialClosureOperationEditRequestNotice::class;
            case self::PARTIAL_CLOSURE_OPERATION_DELETION_REQUEST_NOTIFICATION:
                return PartialClosureOperationDeletionRequestNotice::class;
            case self::RESPONSIBLE_APPOINTMENT_IN_OPERATION_NOTIFICATION:
                return ResponsibleAppointmentInOperationNotice::class;
            case self::MATERIAL_DIFFERENCE_NOTIFICATION:
                return MaterialDifferenceNotice::class;
            case self::WRITE_OFF_OPERATION_REJECTION_NOTIFICATION:
                return WriteOffOperationRejectionNotice::class;
            case self::CONTRACTOR_CHANGES_VERIFICATION_TASK_NOTIFICATION:
                return ContractorChangesVerificationTaskNotice::class;
            case self::OPERATION_CONTROL_TASK_NOTIFICATION:
                return OperationControlTaskNotice::class;

            case self::CERTIFICATE_AVAILABILITY_CONTROL_TASK_NOTIFICATION:
                return CertificateAvailabilityControlTaskNotice::class;
            case self::CERTIFICATE_AVAILABILITY_CONTROL_TASK_CREATED_NOTIFICATION:
                return CertificateAvailabilityControlTaskCreatedNotice::class;
            case self::OPERATIONS_WITHOUT_CERTIFICATES_NOTIFICATION:
                return OperationsWithoutCertificatesNotice::class;
            case self::TECHNICAL_MAINTENANCE_NOTICE:
                return TechnicalMaintenanceNotice::class;
            case self::USER_CREATED_CONTRACTOR_WITHOUT_CONTACTS_NOTIFICATION:
                return UserCreatedContractorWithoutContactsNotice::class;
            case self::CONTRACTOR_CONTACT_INFORMATION_REQUIRED_NOTIFICATION:
                return ContractorContactInformationRequiredNotice::class;
            case self::CONTRACTOR_DELETION_CONTROL_TASK_RESOLUTION_NOTIFICATION:
                return ContractorDeletionControlTaskResolutionNotice::class;
            case self::SHEET_PILING_CALCULATION_TASK_CREATION_NOTIFICATION:
                return SheetPilingCalculationTaskCreationNotice::class;
            case self::PILE_DRIVING_CALCULATION_TASK_CREATION_NOTIFICATION:
                return PileDrivingCalculationTaskCreationNotice::class;
            case self::WORK_REQUEST_PROCESSING_TASK_CREATION_NOTIFICATION:
                return WorkRequestProcessingTaskCreationNotice::class;
            case self::APPOINTMENT_OF_WORK_SUPERVISOR_SHEET_PILING_TASK_CREATION_NOTIFICATION:
                return AppointmentOfWorkSupervisorSheetPilingTaskCreationNotice::class;
            case self::SHEET_PILING_WORK_EXECUTION_CONTROL_TASK_CREATION_NOTIFICATION:
                return SheetPilingWorkExecutionControlTaskCreationNotice::class;
            case self::TECHNICAL_MAINTENANCE_COMPLETION_NOTICE:
                return TechnicalMaintenanceCompletionNotice::class;
            case self::ADDITIONAL_WORKS_APPROVAL_TASK_NOTIFICATION:
                return AdditionalWorksApprovalTaskNotice::class;
            case self::CONTRACTOR_DELETION_CONTROL_TASK_NOTIFICATION:
                return ContractorDeletionControlTaskNotice::class;
            case self::WORK_VOLUME_CLAIM_PROCESSING_NOTIFICATION:
                return WorkVolumeClaimProcessingNotice::class;
            case self::OFFER_CREATION_SHEET_PILING_TASK_NOTIFICATION:
                return OfferCreationSheetPilingTaskNotice::class;
            case self::OFFER_CREATION_PILING_DIRECTION_TASK_NOTIFICATION:
                return OfferCreationPilingDirectionTaskNotice::class;
            case self::APPOINTMENT_OF_RESPONSIBLE_FOR_OFFER_SHEET_PILING_TASK_NOTIFICATION:
                return AppointmentOfResponsibleForOfferSheetPilingTaskNotice::class;
            case self::APPROVAL_OF_OFFER_SHEET_PILING_TASK_NOTIFICATION:
                return ApprovalOfOfferSheetPilingTaskNotice::class;
            case self::PILE_DRIVING_OFFER_APPROVAL_TASK_CREATION_NOTIFICATION:
                return PileDrivingOfferApprovalTaskCreationNotice::class;
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
            case self::CONTRACT_FORMATION_TASK_CREATION_NOTIFICATION:
                return ContractFormationTaskCreationNotice::class;
            case self::CONTRACT_APPROVAL_TASK_CREATION_NOTIFICATION:
                return ContractApprovalTaskCreationNotice::class;
            case self::CONTRACT_SIGNATURE_CONTROL_TASK_CREATION_NOTIFICATION:
                return ContractSignatureControlTaskCreationNotice::class;
            case self::CONTRACT_SIGNATURE_CONTROL_TASK_RECREATION_NOTIFICATION:
                return ContractSignatureControlTaskRecreationNotice::class;
            case self::CONTRACT_DELETION_CONTROL_TASK_CREATION_NOTIFICATION:
                return ContractDeletionControlTaskCreationNotice::class;
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
            case self::CONTRACT_APPROVAL_CONTROL_TASK_CREATION_NOTIFICATION:
                return ContractApprovalControlTaskCreationNotice::class;
            case self::STANDARD_TASK_CREATION_NOTIFICATION:
                return StandardTaskCreationNotice::class;
            case self::SUPPORT_TICKET_APPROXIMATE_DUE_DATE_CHANGE_NOTIFICATION:
                return SupportTicketApproximateDueDateChangeNotice::class;
            case self::SUPPORT_TICKET_STATUS_CHANGE_NOTIFICATION:
                return SupportTicketStatusChangeNotice::class;
            case self::OPERATION_CANCELLED_NOTIFICATION:
                return OperationCancelledNotice::class;
            case self::OPERATION_CREATION_APPROVAL_REQUEST_NOTIFICATION:
                return OperationCreationApprovalRequestNotice::class;
            case self::OPERATION_DRAFT_APPROVAL_NOTIFICATION:
                return OperationDraftApprovalNotice::class;
            case self::OPERATION_DRAFT_DECLINED_NOTIFICATION:
                return OperationDraftDeclinedNotice::class;
            case self::PARTIAL_OPERATION_CLOSURE_NOTIFICATION:
                return PartialOperationClosureNotice::class;
            case self::OPERATION_COMPLETION_NOTIFICATION:
                return OperationCompletionNotice::class;
            case self::OPERATION_CONFIRMED_NOTIFICATION:
                return OperationConfirmedNotice::class;
            case self::OPERATION_STATUS_CONFLICT_NOTIFICATION:
                return OperationStatusConflictNotice::class;
            case self::PROJECT_LEADER_APPOINTMENT_TASK_NOTIFICATION:
                return ProjectLeaderAppointmentTaskNotice::class;
            case self::OPERATION_CREATION_REQUEST_UPDATED_NOTIFICATION:
                return OperationCreationRequestUpdatedNotice::class;
            case self::TECHNICAL_DEVICE_FAULT_REPORT_CREATED_NOTIFICATION:
                return TechnicalDeviceFaultReportCreatedNotice::class;
            case self::TECHNICAL_FAULT_REPORT_ASSIGNMENT_TASK_CREATION_NOTIFICATION:
                return TechnicalFaultReportAssignmentTaskCreationNotice::class;
            case self::TECHNICAL_FAULT_REPORT_ASSIGNEE_NOTIFICATION:
                return TechnicalFaultReportAssigneeNotice::class;
            case self::TECHNIC_REQUEST_APPROVAL_NOTIFICATION:
                return TechnicRequestApprovalNotice::class;
            case self::TECHNIC_USAGE_START_TASK_NOTIFICATION:
                return TechnicUsageStartTaskNotice::class;
            case self::REQUEST_PROCESSING_REQUIRED_NOTIFICATION:
                return RequestProcessingRequiredNotice::class;
            case self::TECHNIC_DISPATCH_CONFIRMATION_NOTIFICATION;
                return TechnicDispatchConfirmationNotice::class;
            case self::TECHNIC_RECEIPT_CONFIRMATION_NOTIFICATION:
                return TechnicReceiptConfirmationNotice::class;
            case self::TECHNICAL_FAULT_REPORT_REJECTION_NOTIFICATION:
                return TechnicalFaultReportRejectionNotice::class;
            case self::TECHNICAL_FAULT_REPORT_CONFIRMED_NOTIFICATION:
                return TechnicalFaultReportConfirmedNotice::class;
            case self::TECHNICAL_FAULT_CONTROL_TASK_NOTIFICATION:
                return TechnicalFaultControlTaskNotice::class;
            case self::TECHNICAL_FAULT_REPORT_NEW_COMMENT_NOTIFICATION:
                return TechnicalFaultReportNewCommentNotice::class;
            case self::TECHNICAL_FAULT_REPORT_REPAIR_PERIOD_CHANGE_NOTIFICATION:
                return TechnicalFaultReportRepairPeriodChangeNotice::class;
            case self::TECHNICAL_FAULT_REPORT_REPAIR_PERIOD_ENDING_NOTIFICATION:
                return TechnicalFaultReportRepairPeriodEndingNotice::class;
            case self::TECHNICAL_FAULT_REPORT_COMPLETION_CONTROL_TASK_NOTIFICATION:
                return TechnicalFaultReportCompletionControlTaskNotice::class;
            case self::TECHNICAL_FAULT_REPORT_WORK_COMPLETION_NOTIFICATION:
                return TechnicalFaultReportWorkCompletionNotice::class;
            case self::TECHNICAL_FAULT_REPORT_DELETED_NOTIFICATION:
                return TechnicalFaultReportDeletedNotice::class;
            case self::TECHNIC_USAGE_EXTENSION_REQUEST_APPROVAL_NOTIFICATION:
                return TechnicUsageExtensionRequestApprovalNotice::class;
            case self::TECHNIC_USAGE_EXTENSION_REQUEST_REJECTION_NOTIFICATION:
                return TechnicUsageExtensionRequestRejectionNotice::class;
            case self::REQUEST_PROCESSED_BY_LOGISTICIAN_NOTIFICATION:
                return RequestProcessedByLogisticianNotice::class;
            case self::TECHNICAL_FAULT_REPORT_DELETED_OR_REJECTED_NOTIFICATION:
                return TechnicalFaultReportDeletedOrRejectedNotice::class;
            case self::TECHNIC_AVAILABLE_NOTIFICATION:
                return TechnicAvailableNotice::class;
            case self::TECHNIC_EXTENTION_APPROVED_NOTIFICATION:
                return TechnicExtentionApprovedNotice::class;
            case self::EMPLOYEE_BIRTHDAY_NEXT_WEEK_NOTIFICATION:
                return EmployeeBirthdayNextWeekNotice::class;
            case self::EMPLOYEE_BIRTHDAY_TODAY_NOTIFICATION:
                return EmployeeBirthdayTodayNotice::class;
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
            case self::EMPLOYEE_TERMINATION:
                return EmployeeTerminationNotice::class;
            case self::COMMERCIAL_OFFER_APPROVED_NOTIFICATION:
                return CommercialOfferApprovedNotice::class;
            case self::LABOR_SAFETY:
                return LaborSafetyNotification::class;
            case self::LABOR_SIGNED:
                return LaborSignedNotification::class;
            case self::NEW_EMPLOYEE_ARRIVAL:
                return NewEmployeeArrivalNotice::class;

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

            case self::FUEL_NOT_AWAITING_CONFIRMATION:
            case self::DEFAULT:
            default:
                return DefaultNotification::class;
        }
    }
}
