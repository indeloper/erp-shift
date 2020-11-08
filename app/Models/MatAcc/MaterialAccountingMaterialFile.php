<?php

namespace App\Models\MatAcc;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialAccountingMaterialFile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'operation_id',
        'operation_material_id',
        'file_name',
        'path',
        'type'
    ];

    public $author_type_info = [
        1 => 'author_id',
        2 => 'sender_id',
        3 => 'recipient_id'
    ];

    public $types = [
        1 => 'doc',
        2 => 'image',
        3 => 'cert',
    ];

    protected $appends = ['name', 'url'];

    // need for file component
    public function getNameAttribute()
    {
        return $this->file_name;
    }

    public function operationMaterial()
    {
        return $this->belongsTo(MaterialAccountingOperationMaterials::class, 'operation_material_id', 'id');
    }

    // need for file component
    public function getUrlAttribute()
    {
        return asset($this->path . '/' . $this->file_name);
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
