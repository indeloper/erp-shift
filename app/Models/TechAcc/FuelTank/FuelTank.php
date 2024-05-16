<?php

namespace App\Models\TechAcc\FuelTank;

use App\Models\Company\Company;
use App\Models\ProjectObject;
use App\Models\User;
use App\Traits\DefaultSortable;
use App\Traits\Defectable;
use App\Traits\DevExtremeDataSourceLoadable;
use App\Traits\Logable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelTank extends Model
{
    use DefaultSortable, Defectable, DevExtremeDataSourceLoadable, Logable, SoftDeletes;
    use HasFactory;

    protected $guarded = ['id'];

    public $defaultSortOrder = [
        'tank_number' => 'asc',
    ];

    // protected $with = [
    //     'object',
    //     'defectsLight'
    // ];

    // protected $appends = [
    //     'name',
    // ];

    // public function __construct(array $attributes = [])
    // {
    //     parent::__construct($attributes);

    //     static::addGlobalScope('id_latest', function (Builder $builder) {
    //         $builder->latest('id');
    //     });
    // }

    public function object()
    {
        return $this->belongsTo(ProjectObject::class);
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getNameAttribute()
    {
        return "Топливная емкость $this->tank_number";
    }

    public function operations()
    {
        return $this->hasMany(FuelTankOperation::class);
    }

    public function trashed_operations()
    {
        return $this->hasMany(FuelTankOperation::class)->onlyTrashed();
    }

    public function loadAllMissingRelations()
    {
        return $this->loadMissing(['operations.author', 'object']);
    }

    // public function scopeFilter($query, $request)
    // {
    //     if (isset($request['fuel_level_from'])) {
    //         $query->where('fuel_level', '>=', $request['fuel_level_from']);
    //     }
    //     if (isset($request['fuel_level_to'])) {
    //         $query->where('fuel_level', '<=', $request['fuel_level_to']);
    //     }
    //     if (isset($request['object_id'])) {
    //         $query->whereIn('object_id', (array) $request['object_id']);
    //     }
    //     if (isset($request['tank_number'])) {
    //         $query->where('tank_number', 'like', '%' . $request['tank_number'] . '%');
    //     }
    //     if (isset($request['search'])) {
    //         $query->where(function ($query) use ($request) {
    //             $query->where('tank_number', $request['search']);
    //             $query->orWhereHas('object', function($query) use ($request) {
    //                 $query->where('address', 'like', '%' . $request['search'] . '%');
    //             });
    //         });
    //     }

    //     return $query;
    // }

    // public function close()
    // {
    //     $dispatcher = FuelTankOperation::getEventDispatcher();

    //     FuelTankOperation::unsetEventDispatcher();

    //     $this->operations()->delete();

    //     FuelTankOperation::setEventDispatcher($dispatcher);

    //     $this->defects()->delete();

    //     $this->delete();
    // }

}
