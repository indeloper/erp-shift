<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
     
    protected $fillable = ['user_id', 'permission_id'];

    public function permissions()
    {
        return $this->hasMany('\App\Models\Permission');
    }
}
