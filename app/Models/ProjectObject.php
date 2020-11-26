<?php

namespace App\Models;

use App\Models\TechAcc\FuelTank\FuelTank;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;

use App\Models\Building\ObjectResponsibleUser;

class ProjectObject extends Model
{
    use DevExtremeDataSourceLoadable;

    protected $fillable = ['name', 'address', 'cadastral_number', 'short_name'];

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
}
