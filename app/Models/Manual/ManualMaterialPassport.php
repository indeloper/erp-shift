<?php

namespace App\Models\Manual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualMaterialPassport extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'material_id', 'user_id', 'file_name'];
}
