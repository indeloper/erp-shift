<?php

namespace App\Models\MatAcc;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class MaterialAccountingMaterialAddition extends Model
{
    protected $fillable = [
        'operation_id',
        'operation_material_id',
        'user_id',
        'description'
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
