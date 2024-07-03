<?php

namespace App\Models\MatAcc;

use Illuminate\Database\Eloquent\Model;

class MaterialAccountingOperationFile extends Model
{
    protected $fillable = [
        'type',
        'operation_id',
        'file_name',
        'path',
        'user_id',
        'author_type',
    ];

    public $author_type_info = [
        1 => 'author_id',
        2 => 'sender_id',
        3 => 'recipient_id',
    ];

    protected $appends = ['name', 'url'];

    // need for file component
    public function getNameAttribute()
    {
        return $this->file_name;
    }

    // need for file component
    public function getUrlAttribute()
    {
        return asset($this->path.'/'.$this->file_name);
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
