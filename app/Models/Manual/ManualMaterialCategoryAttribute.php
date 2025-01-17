<?php

namespace App\Models\Manual;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualMaterialCategoryAttribute extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'is_required', 'unit', 'category_id', 'is_preset', 'from', 'to', 'step', 'value', 'is_display'];

    public function category(): HasOne
    {
        return $this->hasOne(ManualMaterialCategory::class, 'id', 'category_id');
    }
}
