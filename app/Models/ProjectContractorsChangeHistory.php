<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectContractorsChangeHistory extends Model
{
    protected $fillable = [
        'project_id',
        'old_contractor_id',
        'new_contractor_id',
        'user_id',
    ];

    public $types = [
        'has only old_contractor_id' => 'delete relation',
        'has only new_contractor_id' => 'add relation',
        'has new_contractor_id and old_contractor_id' => 'change',
    ];

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
