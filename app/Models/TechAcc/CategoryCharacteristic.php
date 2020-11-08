<?php

namespace App\Models\TechAcc;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryCharacteristic extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'is_hidden', 'unit', 'required'];
    protected $casts = [
        'is_hidden' => 'boolean',
        'required' => 'boolean',
    ];

    public function technic_categories()
    {
        return $this->belongsToMany(TechnicCategory::class, 'technic_category_category_characteristic');
    }
}
