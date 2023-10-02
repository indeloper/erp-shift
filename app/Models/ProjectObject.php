<?php

namespace App\Models;

use App\Traits\Logable;
use App\Models\q3wMaterial\q3wProjectObjectMaterialAccountingType;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Traits\DevExtremeDataSourceLoadable;
use App\Traits\SmartSearchable;
use Illuminate\Database\Eloquent\Model;

use App\Models\Building\ObjectResponsibleUser;
use App\Models\ProjectObjectDocuments\ProjectObjectDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectObject extends Model
{
    use DevExtremeDataSourceLoadable, Logable, SmartSearchable;

    protected $guarded = ['id'];
    // protected $fillable = ['name', 'address', 'cadastral_number', 'short_name', 'material_accounting_type'];

    protected $appends = ['location', 'name_tag'];

    /**
     * Getter for full object location
     * @return string
     */
    public function getLocationAttribute()
    {
        return $this->name .', ' . $this->address;
    }

    /**
     * Getter for object name tag
     * Will return short name or location attribute
     * if short name not setted
     * @return string
     */
    public function getNameTagAttribute()
    {
        return $this->short_name ?? $this->location;
    }

    public function resp_users()
    {
        return $this->hasMany(ObjectResponsibleUser::class, 'object_id', 'id');
    }

    public function material_accounting_type()
    {
        return $this->hasOne(q3wProjectObjectMaterialAccountingType::class, 'id', 'material_accounting_type');
    }

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getLastTenOperations()
    {
        return $this->fuel_tanks->pluck('operations')->flatten()->sortByDesc('id')->take(10);
    }

    public function fuel_tanks()
    {
        return $this->hasMany(FuelTank::class, 'object_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'object_id');
    }

    public function documents()
    {
        return $this->hasMany(ProjectObjectDocument::class, 'project_object_id');
    }

    public function scopeWithResponsibleUserNames($query)
    {
        $query->leftJoin('object_responsible_users', 'project_objects.id', '=', 'object_responsible_users.object_id')
            ->leftJoin('users', 'users.id', '=', 'object_responsible_users.user_id')
            ->leftJoin('object_responsible_user_roles', 'object_responsible_user_roles.id', '=', 'object_responsible_users.object_responsible_user_role_id')
            ->addSelect([
                DB::raw("GROUP_CONCAT(CASE WHEN `object_responsible_user_roles`.`slug` = 'TONGUE_PROJECT_MANAGER' THEN `users`.`user_full_name` ELSE NULL END ORDER BY `users`.`user_full_name` ASC SEPARATOR '<br>' ) AS `tongue_project_manager_full_names`"),
                DB::raw("GROUP_CONCAT(CASE WHEN `object_responsible_user_roles`.`slug` = 'TONGUE_PTO_ENGINEER' THEN `users`.`user_full_name` ELSE NULL END ORDER BY `users`.`user_full_name` ASC SEPARATOR '<br>' ) AS `tongue_pto_engineer_full_names`"),
                DB::raw("GROUP_CONCAT(CASE WHEN `object_responsible_user_roles`.`slug` = 'TONGUE_FOREMAN' THEN `users`.`user_full_name` ELSE NULL END ORDER BY `users`.`user_full_name` ASC SEPARATOR '<br>' ) AS `tongue_foreman_full_names`")
            ])
            ->groupBy(['project_objects.id', 'project_objects.short_name']);
    }

    public function getPermissionsAttribute()
    {
        $permissionsArray = [];
        $permissions = Permission::where("category", 4)->get();

        foreach ($permissions as $permission){
            $permissionsArray[$permission->codename] = Auth::user()->can($permission->codename);
        }

        return $permissionsArray;
    }
}
