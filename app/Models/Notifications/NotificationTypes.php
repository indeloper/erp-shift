<?php

namespace App\Models\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationTypes extends Model
{
    use SoftDeletes;

    const NOTIFICATION_GROUPS = [
        1 => 'Notifications for Standard Tasks or for all tasks',
        2 => 'Notifications related to Material Accounting and tasks',
        3 => 'Notifications related to Tech Support and tasks',
        4 => 'Notifications related to Contractors and tasks',
        5 => 'Notifications related to Work Volumes tasks',
        6 => 'Notifications related to Commercial Offers and tasks',
        7 => 'Notifications related to Contracts and tasks',
        8 => 'Notifications related to Projects',
        9 => 'Notifications related to Users',
        10 => 'Notifications related to Tech Accounting and tasks',
        11 => 'Notifications related to Human Resources Accounting',
    ];

    const WDIM_FOR_EVERYONE = [
        0 => 'Not everyone can receive these notifications',
        1 => 'Everyone can receive these notifications',
    ];

    protected $fillable = [
        'group',
        'name',
        'for_everyone',
    ];

    public function alwaysAllowedNotifications()
    {
        return $this->where('for_everyone', 1)->get();
    }
}
