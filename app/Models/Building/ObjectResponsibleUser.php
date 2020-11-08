<?php

namespace App\Models\Building;

use Illuminate\Database\Eloquent\Model;

use App\Models\ProjectObject;
use App\Models\User;

class ObjectResponsibleUser extends Model
{
    protected $fillable = [
        'object_id',
        'user_id',
        'role',
    ];

    public $role_codes = [
        1 => 'Ответственный за мат. учет',
        2 => 'Ответственный за транспорт', // coming soon
        3 => 'Ответственный за персонал' // coming soon
    ];

    public function object()
    {
        return $this->belongsTo(ProjectObject::class, 'object_id', 'id');
    }

    public function user()
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
}
