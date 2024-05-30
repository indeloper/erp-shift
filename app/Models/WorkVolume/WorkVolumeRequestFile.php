<?php

namespace App\Models\WorkVolume;

use Illuminate\Database\Eloquent\Model;

class WorkVolumeRequestFile extends Model
{
    protected $fillable = ['request_id', 'tongue_pile', 'file_name'];
}
