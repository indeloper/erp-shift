<?php

namespace App\Models\Vacation;

use App\Models\ProjectResponsibleUser;
use Illuminate\Database\Eloquent\Model;

class ProjectResponsibleUserRedirectHistory extends Model
{
    protected $fillable = ['vacation_id', 'project_id', 'old_user_id', 'new_user_id', 'role', 'reason'];

    public static function roles($role_ids)
    {
        $roles = ProjectResponsibleUser::whereIn('id', $role_ids)->get();

        return $roles;
    }

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }
}
