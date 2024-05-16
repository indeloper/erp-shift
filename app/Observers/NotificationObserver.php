<?php

namespace App\Observers;

use App\Models\Notification\Notification;
use App\Models\User;

class NotificationObserver
{
    /**
     * Handle the vacations history "saved" event.
     */
    public function saved(Notification $notification): void
    {
        if ($this->dontHaveName($notification) or $this->isUpdate($notification)) {
            return;
        }

        //        event(new NotificationCreated(($notification->name . (is_array($notification->additional_info) ? '' : $notification->additional_info)), $notification->user_id, $notification->type, $notification->id));
    }

    public function saving(Notification $notification)
    {
        if ($this->dontHaveName($notification)) {
            return;
        }

        $user = User::find($notification->user_id);
        $type = $notification->type ?? 0;
        $notification->is_showing = $user ? ($user->checkIfNotifyDisabled($type) ? 0 : 1) : 0;
    }

    /**
     * Method return true if notification don't have name
     */
    public function dontHaveName(Notification $notification): bool
    {
        return ! $notification->name;
    }

    /**
     * Method return true if we update notification (see or delete)
     */
    public function isUpdate(Notification $notification): bool
    {
        return boolval($notification->is_seen == 1 or $notification->is_deleted == 1);
    }
}
