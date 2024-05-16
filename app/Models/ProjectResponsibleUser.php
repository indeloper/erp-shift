<?php

namespace App\Models;

use App\Models\Vacation\ProjectResponsibleUserRedirectHistory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProjectResponsibleUser extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'user_id', 'role'];

    public static function moveResponsibleUser($roles, $old_user_id, $new_user_id, $vacation_id, $reason = 'Отпуск пользователя')
    {
        DB::beginTransaction();

        $insert = [];
        if ($reason == 'Отпуск пользователя') {
            foreach ($roles as $role) {
                $insert[] = [
                    'vacation_id' => $vacation_id,
                    'role_id' => $role->id,
                    'project_id' => $role->project_id,
                    'old_user_id' => $old_user_id,
                    'new_user_id' => $new_user_id,
                    'role' => $role->role,
                    'reason' => $reason,
                    'created_at' => Carbon::now(),
                ];
                $role->update(['user_id' => $new_user_id]);
            }
        } else {
            foreach ($roles as $role) {
                $insert[] = [
                    'role_id' => $role->id,
                    'project_id' => $role->project_id,
                    'old_user_id' => $old_user_id,
                    'new_user_id' => $new_user_id,
                    'role' => $role->role,
                    'reason' => $reason,
                    'created_at' => Carbon::now(),
                ];
                $role->update(['user_id' => $new_user_id]);
            }
        }

        ProjectResponsibleUserRedirectHistory::insert($insert);

        DB::commit();

        return ['Roles Movin\'', $insert];
    }

    public static function moveResponsibleUserBack($roles, $old_user_id, $new_user_id, $vacation_id)
    {
        DB::beginTransaction();

        $insert = [];
        foreach ($roles as $role) {
            $insert[] = [
                'vacation_id' => $vacation_id,
                'role_id' => $role->id,
                'project_id' => $role->project_id,
                'old_user_id' => $old_user_id,
                'new_user_id' => $new_user_id,
                'role' => $role->role,
                'reason' => 'Выход из отпуска',
                'created_at' => Carbon::now(),
            ];
            $role->update(['user_id' => $new_user_id]);
        }

        ProjectResponsibleUserRedirectHistory::insert($insert);

        DB::commit();

        return 'Roles Movin\' Back';
    }

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
