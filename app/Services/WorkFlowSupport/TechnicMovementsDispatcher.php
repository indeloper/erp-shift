<?php

namespace App\Services\WorkFlowSupport;

use App\Jobs\WorkFlowSupport\Technic\TechnicMovementDayBeforeReminder;
use App\Jobs\WorkFlowSupport\Technic\TechnicMovementsSetStatus;
use App\Models\Building\ObjectResponsibleUser;
use App\Models\Building\ObjectResponsibleUserRole;
use App\Models\Permission;
use App\Models\TechAcc\TechnicCategory;
use App\Models\TechAcc\TechnicMovementStatus;
use App\Models\User;
use App\Notifications\Technic\TechnicMovementNotifications;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TechnicMovementsDispatcher
{
    protected $data;

    protected $entity;

    protected $isTechnicOversized;

    public function __construct($data, $entity = null)
    {
        $this->data = $data;
        $this->entity = $entity;
        $this->isTechnicOversized = $this->isTechnicOversized();
        $this->handle();
    }

    public function handle()
    {
        $data = $this->data;
        $statusSlug = TechnicMovementStatus::find($data['technic_movement_status_id'])->slug ?? null;

        if (empty(self::fabricData[$statusSlug])) {
            return;
        }

        $method = self::fabricData[$statusSlug];

        $this->$method();
    }

    public function created()
    {
        $notificationRecipientsIds = User::where('id', '<>', Auth::id())
            ->whereIn('id',
                ObjectResponsibleUser::where('object_id', $this->entity->object_id)
                    ->orWhere('object_id', $this->entity->previous_object_id)
                    ->whereIn('object_responsible_user_role_id',
                        ObjectResponsibleUserRole::whereIn('slug', ['TONGUE_FOREMAN', 'TONGUE_PROJECT_MANAGER'])->pluck('id')->toArray()
                    )->pluck('user_id')->toArray()
            )

            ->when($this->isTechnicOversized, function ($query) {
                $responsiblesIds = Permission::UsersIdsByCodename('technics_processing_movement_oversized_equipment');
                if (! empty($responsiblesIds)) {
                    $query->orWhereIn('id', $responsiblesIds);
                }

                $subscribersIds = Permission::UsersIdsByCodename('technics_movement_receive_oversized_order_notification');
                if (! empty($subscribersIds)) {
                    $query->orWhereIn('id', $subscribersIds);
                }
            })

            ->when(! $this->isTechnicOversized, function ($query) {
                $responsiblesIds = Permission::UsersIdsByCodename('technics_processing_movement_standart_sized_equipment');
                if (! empty($responsiblesIds)) {
                    $query->orWhereIn('id', $responsiblesIds);
                }

                $subscribersIds = Permission::UsersIdsByCodename('technics_movement_receive_standard_size_notification');
                if (! empty($subscribersIds)) {
                    $query->orWhereIn('id', $subscribersIds);
                }
            })

            ->pluck('id')->toArray();

        (new TechnicMovementNotifications)->notifyAboutTechnicMovementCreated($this->data, $this->entity, $notificationRecipientsIds);
    }

    public function planned()
    {
        if (empty($this->data['movement_start_datetime'])) {
            return;
        }

        $this->setNewMovementStatus('inProgress', $this->data['movement_start_datetime']);
        $this->setNotificationForDayBeforeMovement();

        $notificationRecipientsIds = User::where('id', '<>', Auth::id())
            ->whereIn('id',
                ObjectResponsibleUser::where('object_id', $this->entity->object_id)
                    ->orWhere('object_id', $this->entity->previous_object_id)
                    ->whereIn('object_responsible_user_role_id',
                        ObjectResponsibleUserRole::whereIn('slug', ['TONGUE_FOREMAN', 'TONGUE_PROJECT_MANAGER'])->pluck('id')->toArray()
                    )->pluck('user_id')->toArray()
            )
            ->orWhereIn('id', [$this->entity->author_id])

            ->when($this->isTechnicOversized, function ($query) {
                $subscribersIds = Permission::UsersIdsByCodename('technics_movement_receive_oversized_order_notification');
                if (! empty($subscribersIds)) {
                    $query->orWhereIn('id', $subscribersIds);
                }
            })

            ->when(! $this->isTechnicOversized, function ($query) {
                $subscribersIds = Permission::UsersIdsByCodename('technics_movement_receive_standard_size_notification');
                if (! empty($subscribersIds)) {
                    $query->orWhereIn('id', $subscribersIds);
                }
            })

            ->pluck('id')->toArray();

        (new TechnicMovementNotifications)->notifyAboutTechnicMovementPlanned($this->data, $this->entity, $notificationRecipientsIds);
    }

    public function completed()
    {
        $notificationRecipientsIds = User::where('id', '<>', Auth::id())
            ->whereIn('id',
                ObjectResponsibleUser::where('object_id', $this->entity->object_id)
                    ->orWhere('object_id', $this->entity->previous_object_id)
                    ->whereIn('object_responsible_user_role_id',
                        ObjectResponsibleUserRole::whereIn('slug', ['TONGUE_FOREMAN', 'TONGUE_PROJECT_MANAGER'])->pluck('id')->toArray()
                    )->pluck('user_id')->toArray()
            )
            ->orWhereIn('id', [$this->entity->author_id])

            ->when($this->isTechnicOversized, function ($query) {
                $subscribersIds = Permission::UsersIdsByCodename('technics_movement_receive_oversized_order_notification');
                if (! empty($subscribersIds)) {
                    $query->orWhereIn('id', $subscribersIds);
                }
            })

            ->when(! $this->isTechnicOversized, function ($query) {
                $subscribersIds = Permission::UsersIdsByCodename('technics_movement_receive_standard_size_notification');
                if (! empty($subscribersIds)) {
                    $query->orWhereIn('id', $subscribersIds);
                }
            })

            ->pluck('id')->toArray();

        (new TechnicMovementNotifications)->notifyAboutTechnicMovementCompleted($this->data, $this->entity, $notificationRecipientsIds);
    }

    public function cancelled()
    {
        $notificationRecipientsIds = User::where('id', '<>', Auth::id())
            ->whereIn('id',
                ObjectResponsibleUser::where('object_id', $this->entity->object_id)
                    ->orWhere('object_id', $this->entity->previous_object_id)
                    ->whereIn('object_responsible_user_role_id',
                        ObjectResponsibleUserRole::whereIn('slug', ['TONGUE_FOREMAN', 'TONGUE_PROJECT_MANAGER'])->pluck('id')->toArray()
                    )->pluck('user_id')->toArray()
            )
            ->orWhereIn('id', [$this->entity->author_id])

            ->when($this->isTechnicOversized, function ($query) {
                $subscribersIds = Permission::UsersIdsByCodename('technics_movement_receive_oversized_order_notification');
                if (! empty($subscribersIds)) {
                    $query->orWhereIn('id', $subscribersIds);
                }
            })

            ->when(! $this->isTechnicOversized, function ($query) {
                $subscribersIds = Permission::UsersIdsByCodename('technics_movement_receive_standard_size_notification');
                if (! empty($subscribersIds)) {
                    $query->orWhereIn('id', $subscribersIds);
                }
            })
            ->when($this->entity->responsible_id != Auth::id(), function ($query) {
                $query->orWhereIn('id', [$this->entity->responsible_id]);
            })

            ->pluck('id')->toArray();

        (new TechnicMovementNotifications)->notifyAboutTechnicMovementCancelled($this->data, $this->entity, $notificationRecipientsIds);
    }

    public function setNotificationForDayBeforeMovement()
    {
        $notificationRecipientsIds = User::where('id', '<>', Auth::id())
            ->orWhereIn('id', [$this->entity->author_id])
            ->when($this->entity->responsible_id != Auth::id(), function ($query) {
                $query->orWhereIn('id', [$this->entity->responsible_id]);
            })
            ->pluck('id')->toArray();

        $dateBeforeMovementDateStr = Carbon::create($this->data['movement_start_datetime'])->subDay()->format('Y-m-d');
        $dateTimeBeforeMovementDateStr = $dateBeforeMovementDateStr.' 10:10';
        $dateTimeBeforeMovementDateCarbon = Carbon::parse($dateTimeBeforeMovementDateStr);
        $delayMinutes = now()->diffInMinutes($dateTimeBeforeMovementDateCarbon);

        $data = [
            'updateData' => $this->data,
            'entity' => $this->entity,
            'notificationRecipientsIds' => $notificationRecipientsIds,
        ];

        // dispatch(new TechnicMovementDayBeforeReminder($data))
        //     ->delay(now()->addMinutes($delayMinutes));

    }

    public function isTechnicOversized()
    {
        if (TechnicCategory::find($this->entity->technic_category_id)->name === 'Гусеничные краны') {
            return true;
        }

        return false;
    }

    public function setNewMovementStatus($status, $delayedTo = null)
    {
        $delayMinutes = now()->diffInMinutes($delayedTo);

        dispatch(
            new TechnicMovementsSetStatus(
                $this->entity->id,
                TechnicMovementStatus::where('slug', 'inProgress')->first()->id,
                $this->data['movement_start_datetime']
            )
        )
            ->delay(now()->addMinutes($delayMinutes));
    }

    const fabricData = [
        'created' => 'created',
        'transportationPlanned' => 'planned',
        'completed' => 'completed',
        'cancelled' => 'cancelled',
    ];
}
