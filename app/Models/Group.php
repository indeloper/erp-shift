<?php

namespace App\Models;

use App\Models\Notifications\NotificationsForGroups;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['name', 'department_id'];

    const FOREMEN = [14, 23, 31];

    const PROJECT_MANAGERS = [8, 13, 19, 27, 58];

    const LOGIST = [15, 16, 17];

    const MECHANICS = [46, 47];

    const PTO = [52, 53, 62];

    public function permissions()
    {
        return Permission::where('group_permissions.group_id', $this->id)
            ->leftJoin('group_permissions', 'group_permissions.permission_id', '=', 'permissions.id')
            ->get();
    }

    public function getUsers()
    {
        $all_users = $this->users;
        $working_users = collect([]);

        foreach ($all_users as $user) {
            if ($user->in_vacation) {
                $working_users->push($user->replacing_users->first());
            } else {
                $working_users->push($user);
            }
        }

        return $working_users;
    }

    public function users()
    {
        return $this->hasMany(User::class)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->where('id', '!=', 1);
    }

    public function group_permissions()
    {
        return $this->belongsToMany(Permission::class, 'group_permissions', 'group_id', 'permission_id');
    }

    public function relatedNotifications()
    {
        return $this->hasMany(NotificationsForGroups::class, 'group_id', 'id');
    }
}
