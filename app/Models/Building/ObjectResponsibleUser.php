<?php

namespace App\Models\Building;

use App\Models\ProjectObject;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObjectResponsibleUser extends Model
{
    protected $guarded = ['id'];

    public $role_codes = [
        1 => 'Ответственный за мат. учет',
        // 2 => 'Ответственный за транспорт', // coming soon
        // 3 => 'Ответственный за персонал' // coming soon
    ];

    public function object(): BelongsTo
    {
        return $this->belongsTo(ProjectObject::class, 'object_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function isUserResponsibleForObject($userId, $projectObjectId, $role)
    {
        return ObjectResponsibleUser::where('user_id', $userId)
            ->where('object_id', $projectObjectId)
            ->where('object_responsible_user_role_id', (new ObjectResponsibleUserRole)->getRoleIdBySlug($role))
            ->exists();
    }

    public function getResponsibilityUsers($projectObjectId, $role)
    {
        return $this->where('object_id', $projectObjectId)
            ->where('object_responsible_user_role_id', (new ObjectResponsibleUserRole)->getRoleIdBySlug($role))
            ->get();
    }
}
