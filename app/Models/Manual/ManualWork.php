<?php

namespace App\Models\Manual;

use App\Traits\Reviewable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualWork extends Model
{
    use Reviewable, SoftDeletes;

    protected $fillable = ['work_group_id', 'name', 'description', 'price_per_unit', 'unit', 'unit_per_days', 'nds', 'show_material', 'is_copied'];

    public $work_group = [
        '1' => 'Шпунтовые работы',
        '2' => 'Устройство свайного поля',
        '3' => 'Земляные работы',
        '4' => 'Монтаж системы крепления',
        '5' => 'Дополнительные работы',
    ];
    //there are hardcoded work_group names at:
    //commercial_offer/offer.blade.php
    //commercial_offer/edit.blade.php
    //commercial_offer/card.blade.php

    public $pile_groups = [2, 3, 5];

    public $tongue_groups = [1, 3, 4, 5];

    // WDIM == What Does It Mean
    public $WDIM_is_copied = [
        0 => false, // original work
        1 => true,   // copy
    ];

    public function material_relations()
    {
        return $this->hasMany(ManualRelationMaterialWork::class, 'manual_work_id', 'id')
            ->leftJoin('manual_materials', 'manual_materials.id', '=', 'manual_relation_material_works.manual_material_id')
            ->select('manual_relation_material_works.*', 'manual_materials.name', 'manual_materials.bue_cost', 'manual_materials.description', 'manual_materials.use_cost')
            ->withTrashed();
    }

    public function materialRelationsClear()
    {
        return $this->hasMany(ManualRelationMaterialWork::class, 'manual_work_id', 'id')->withTrashed();
    }

    public function related_categories()
    {
        return $this->belongsToMany(ManualMaterialCategory::class, 'manual_material_category_relation_to_works', 'work_id', 'manual_material_category_id');
    }

    public function related_materials()
    {
        return $this->belongsToMany(ManualMaterial::class, 'manual_relation_material_works', 'manual_work_id', 'manual_material_id');
    }

    public function childs()
    {
        return $this->hasMany(ManualCopiedWorks::class, 'parent_work_id', 'id');
    }

    public function parent()
    {
        return $this->hasOne(ManualCopiedWorks::class, 'child_work_id', 'id');
    }

    public function normal_parent()
    {
        return $this->hasOneThrough(ManualWork::class, ManualCopiedWorks::class, 'child_work_id', 'id', 'id', 'parent_work_id');
    }

    public function is_parent()
    {
        return ManualCopiedWorks::where('parent_work_id', $this->id)->count();
    }

    public function delete_childs()
    {
        ManualWork::whereIn('id', $this->childs->pluck('child_work_id')->toArray())->delete();
        $this->childs()->delete();

        return true;
    }

    public function worksWithCount()
    {
        return $this->where('unit', 'шт')->pluck('id')->toArray();
    }
}
