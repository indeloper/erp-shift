<?php

namespace App\Models\TechAcc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\DefaultSortable;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TechnicCategory extends Model
{
    use HasFactory;

    use DefaultSortable, DevExtremeDataSourceLoadable, SoftDeletes;

    protected $guarded = ['id'];

    public $defaultSortOrder = [
        'name' => 'asc',
    ];

    // protected $fillable = ['name', 'description', 'characteristic_id'];

    // public function __construct($attributes = [])
    // {
    //     parent::__construct($attributes);

    //     static::deleted(function($category) {
    //         $category->technics->each(function ($tech) {$tech->delete();});
    //     });
    // }

    // public function addCharacteristic($characteristic)
    // {
    //     $this->category_characteristics()->attach($characteristic);
    // }

    // public function category_characteristics()
    // {
    //     return $this->belongsToMany(CategoryCharacteristic::class, 'technic_category_category_characteristic');
    // }

    // public function technics()
    // {
    //     return $this->hasMany(OurTechnic::class);
    // }

    // public function free_technics()
    // {
    //     return $this->technics()->free();
    // }

    // public function trashed_technics()
    // {
    //     return $this->hasMany(OurTechnic::class)->onlyTrashed();
    // }
}
