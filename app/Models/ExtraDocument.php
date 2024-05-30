<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtraDocument extends Model
{
    protected $fillable = ['project_document_id', 'user_id', 'file_name', 'project_id', 'version', 'created_at'];

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }
}
