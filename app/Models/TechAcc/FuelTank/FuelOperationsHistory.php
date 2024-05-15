<?php

namespace App\Models\TechAcc\FuelTank;

use App\Models\Contractors\Contractor;
use App\Models\ProjectObject;
use App\Models\TechAcc\OurTechnic;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelOperationsHistory extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'fuel_operation_id', 'changed_fields'];

    protected $appends = ['changed_fields_parsed'];

    protected $with = ['user'];

    protected $casts = ['changed_fields' => 'array'];

    public function fuelTankOperation()
    {
        $this->belongsTo(FuelTankOperation::class, 'fuel_operation_id');
    }

    public function getChangedFieldsParsedAttribute()
    {
        $raw_data = $this->changed_fields;

        $fields_to_cast = [
            'our_technic_id' => [
                'class' => OurTechnic::class,
                'name_method' => 'name',
            ],
            'fuel_tank_id' => [
                'class' => FuelTank::class,
                'name_method' => 'name',
            ],
            'object_id' => [
                'class' => ProjectObject::class,
                'name_method' => 'name',
            ],
            'contractor_id' => [
                'class' => Contractor::class,
                'name_method' => 'short_name',
            ],
            'author_id' => [
                'class' => User::class,
                'name_method' => 'full_name',
            ],
        ];

        $casted_data = [];

        foreach ($raw_data as $old_new => $values) {
            foreach ($values as $field => $value) {
                if (array_key_exists($field, $fields_to_cast)) {
                    $name_method = $fields_to_cast[$field]['name_method'];
                    $casted_data[$old_new][$field] = $fields_to_cast[$field]['class']::find($value)->$name_method;
                } else {
                    if ($field == 'owner_id') {
                        $casted_data[$old_new][$field] = OurTechnic::$owners[$value];
                    } elseif ($field == 'operation_date') {
                        $casted_data[$old_new][$field] = Carbon::parse($value)->isoFormat('D.MM.YYYY');
                    } elseif ($field != 'result_value') {
                        $casted_data[$old_new][$field] = $value;
                    }
                }
            }
        }

        return $casted_data;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
