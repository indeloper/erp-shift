<?php

namespace App\Models\Manual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualMaterialCategoryRelationToWork extends Model
{
    use SoftDeletes;

    protected $fillable = ['manual_material_category_id', 'work_id'];
}
