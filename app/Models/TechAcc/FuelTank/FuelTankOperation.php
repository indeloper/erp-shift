<?php

namespace App\Models\TechAcc\FuelTank;

use App\Models\Contractors\Contractor;
use App\Models\ProjectObject;
use App\Models\TechAcc\OurTechnic;
use App\Models\User;
use App\Traits\Documentable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelTankOperation extends Model
{
    use Documentable;
    use SoftDeletes;

    protected $fillable = [
        'fuel_tank_id',
        'author_id',
        'object_id',
        'our_technic_id',
        'contractor_id',
        'value',
        'type',
        'description',
        'operation_date',
        'owner_id',
        'result_value',
    ];

    public $types = [
        1 => 'Завоз',
        2 => 'Расход',
        3 => 'Ручное изменение',
    ];

    public $types_json = [
        ['id' => 1, 'name' => 'Завоз'],
        ['id' => 2, 'name' => 'Расход'],
        ['id' => 3, 'name' => 'Ручное изменение'],
    ];

    protected $with = [
        'fuel_tank',
        'author',
        'our_technic',
        'contractor',
        'object',
    ];

    protected $appends = [
        'type_name',
        'fuel_tank_number',
        'owner',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        static::addGlobalScope('operation_date_order', function (Builder $builder) {
            $builder->latest('operation_date');
        });
    }

    public function author()
    {
        return $this->belongsTo(User::class);
    }

    public function our_technic()
    {
        return $this->belongsTo(OurTechnic::class)->withTrashed();
    }

    public function object()
    {
        return $this->belongsTo(ProjectObject::class);
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function fuel_tank()
    {
        return $this->belongsTo(FuelTank::class);
    }

    public function history()
    {
        return $this->hasMany(FuelOperationsHistory::class, 'fuel_operation_id');
    }

    public function getFutureHistoryAttribute()
    {
        return FuelTankOperation::where('operation_date', '>', $this->operation_date)->where('fuel_tank_id', $this->fuel_tank_id)->where('id', '!=', $this->id)->oldest('operation_date')->get();
    }

    public function getOldFuelLevelAttribute()
    {
        $fuel_level = null;
        $older_oper = FuelTankOperation::where('operation_date', '<', $this->operation_date)->where('fuel_tank_id', $this->fuel_tank_id)->where('id', '!=', $this->id)->latest('operation_date')->first();

        if ($older_oper) {
            $fuel_level = $older_oper->result_value;
        } else {
            $younger_oper = FuelTankOperation::where('operation_date', '>', $this->operation_date)->where('fuel_tank_id', $this->fuel_tank_id)->where('id', '!=', $this->id)->oldest('operation_date')->first();
            if ($younger_oper) {
                $fuel_level = $younger_oper->result_value - $younger_oper->value;
            }
        }

        return $fuel_level;
    }

    public function getOwnerAttribute()
    {
        return OurTechnic::$owners[$this->owner_id ?? 1];
    }

    public function getTypeNameAttribute()
    {
        return $this->types[$this->type];
    }

    public function getFormattedOperationDateAttribute()
    {
        return Carbon::parse($this->operation_date)->isoFormat('DD.MM.YYYY');
    }

    public function getFuelTankNumberAttribute()
    {
        if ($this->fuel_tank()->count()) {
            return $this->fuel_tank->tank_number;
        }

        return 0;
    }

    public function getValueDiffAttribute()
    {
        if ($this->type == 2) {
            return -$this->value;
        } else {
            return $this->value;
        }
    }

    public function loadMissingRelations()
    {
        return $this->loadMissing(['contractor', 'fuel_tank', 'our_technic']);
    }

    public function scopeFilter($query, $request)
    {
        if (isset($request['operation_date_from'])) {
            $query->whereDate('operation_date', '>=', Carbon::parse($request['operation_date_from']));
        }
        if (isset($request['operation_date_to'])) {
            $query->whereDate('operation_date', '<=', Carbon::parse($request['operation_date_to'])->endOfDay());
        }
        if (isset($request['date_updated_from'])) {
            $query->where('updated_at', '>=', $request['date_updated_from']);
        }
        if (isset($request['date_updated_to'])) {
            $query->where('updated_at', '<=', Carbon::parse($request['date_updated_to'])->endOfDay());
        }
        if (isset($request['object_id'])) {
            $query->whereIn('object_id', (array) $request['object_id']);
        }
        if (isset($request['fuel_tank_id'])) {
            $query->whereIn('fuel_tank_id', (array) $request['fuel_tank_id']);
        }
        if (isset($request['tank_number'])) {
            $query->whereHas('fuel_tank', function ($q) use ($request) {
                $q->whereIn('tank_number', (array) $request['tank_number']);
            });
        }
        if (isset($request['type'])) {
            $query->whereIn('type', (array) $request['type']);
        }
        if (isset($request['value_from'])) {
            $query->where('value', '>=', $request['value_from']);
        }
        if (isset($request['value_to'])) {
            $query->where('value', '<=', $request['value_to']);
        }
        if (isset($request['our_technic'])) {
            $query->whereIn('our_technic_id', (array) $request['our_technic']);
        }
        if (isset($request['contractor'])) {
            $query->whereIn('contractor_id', (array) $request['contractor']);
        }
        if (isset($request['author'])) {
            $query->whereIn('author_id', (array) $request['author']);
        }

        return $query;
    }
}
