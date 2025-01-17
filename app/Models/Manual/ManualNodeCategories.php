<?php

namespace App\Models\Manual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualNodeCategories extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'safety_factor'];

    public function nodes(): HasMany
    {
        return $this->hasMany(ManualNodes::class, 'node_category_id', 'id');
    }
}
