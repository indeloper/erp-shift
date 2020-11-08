<?php

namespace App\Models\MatAcc;

use App\Models\MatAcc\MaterialAccountingBase;
use App\Models\Task;
use Carbon\CarbonPeriod;
use App\Services\MaterialAccounting\MaterialAccountingService;
use Illuminate\Database\Eloquent\Model;
use App\Models\Manual\ManualMaterial;

use App\Models\MatAcc\MaterialAccountingMaterialAddition;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MaterialAccountingOperationMaterials extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'operation_id',
        'manual_material_id',

        'count',
        'unit',

        'type',

        'updated_material_id',

        'fact_date',
        'used',
    ];

    protected $appends = ['material_id', 'material_unit', 'material_count', 'drawer', 'material_name', 'converted_count'];

    protected $casts = ['used' => 'boolean'];

    public $type_name = [
        1 => 'user from',
        2 => 'user to', // "ИТОГ" for "Списание" and "Поступление"
        3 => 'plan',
        4 => 'result_to', // NOW only for moving, transformation "ИТОГ" for "Перемещение" and "После преобразования"
        5 => 'result_from', // NOW only for moving ,transformation "ИТОГ" for "До преобразования"
        6 => 'plan_to', // NOW only for transformation
        7 => 'plan_from', // NOW only for transformation
        8 => 'part_from',
        9 => 'part_to',
        10 => 'edition_material' // material with updation task
    ];
    /* type map for operation.type / material.type

     *                  plan    fact    itog    part
     * 1 - arrival       3       1       2       9
     * 2 - write_off     3       1       2       8
     * 4 - moving        3      1|2      4      9|8
     * 3 - transfer     6|7     2|1     4|5     9|8

     * */

    public $itog_types = [2, 4, 5];
    public $plan_types = [3, 6, 7];



    public static $main_units = [
        ['id' => 1, 'text' => 'т'],
        ['id' => 2, 'text' => 'шт'],
        ['id' => 3, 'text' => 'м.п'],
        ['id' => 4, 'text' => 'м2'],
        ['id' => 5, 'text' => 'м3'],

    ];

    public $units_name = [
        1 => 'т',
        2 => 'шт',
        3 => 'м.п',
        4 => 'м2',
        5 => 'м3',
    ];

    public static function flipUnit($unit_to_flip)
    {
        $flipped_unit_array = array_filter(self::$main_units, function($unit) use($unit_to_flip) {
            return $unit['id'] == $unit_to_flip;
        });

        if (count($flipped_unit_array) > 0) {
            return $flipped_unit_array[0]['name'];
        }

        $flipped_unit_array = array_filter(self::$main_units, function($unit) use($unit_to_flip) {
            return $unit['text'] == $unit_to_flip;
        });

        return $flipped_unit_array[0]['id'] ?? null;
    }

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getFactDateAttribute($date)
    {
        return $date == null ? \Carbon\Carbon::parse($this->created_at)->format('d.m.Y') : \Carbon\Carbon::parse($date)->format('d.m.Y');
    }

    public function getOriginFactDateAttribute($date)
    {
        return $this->fact_date;
    }

    /**
     * This getter return operation material name
     * with optional 'Б/У' label
     * @return string
     */
    public function getMaterialNameAttribute()
    {
        return $this->manual->name . ($this->used ? ' Б/У' : '');
    }

    public function manual()
    {
        return $this->belongsTo(ManualMaterial::class, 'manual_material_id', 'id')
            ->leftJoin('manual_material_categories', 'manual_material_categories.id','=', 'manual_materials.category_id')
            ->select('manual_materials.*', 'manual_material_categories.id as cat_id', 'manual_material_categories.category_unit')
            ->withTrashed();
    }

    public function getConvertedCountAttribute()
    {
        $params = $this->manual->convert_from($this->unit_for_humans) ?? [];
        $converted = [];
        foreach ($params as $param) {
            $converted[] = [
                'unit' => $param->unit,
                'count' => $this->count * $param->value,
            ];
        }
        return $converted;
    }

    public function materialAddition()
    {
        return $this->hasOne(MaterialAccountingMaterialAddition::class, 'operation_material_id', 'id');
    }

    public function operation()
    {
        return $this->belongsTo(MaterialAccountingOperation::class, 'operation_id', 'id');
    }

    public function siblings()
    {
        return $this->operation->allMaterials()->where('id', '!=', $this->id);
    }

    public function delete_task()
    {
        return $this->hasOne(Task::class, 'target_id', 'id')->where('status', 22);
    }

    public function materialFiles()
    {
        return $this->hasMany(MaterialAccountingMaterialFile::class, 'operation_material_id', 'id');
    }

    public function certificates()
    {
        return $this->materialFiles()->where('type', 3);
    }

    public function getMaterialIdAttribute()
    {
        return $this->manual_material_id;
    }

    public function getMaterialUnitAttribute()
    {
        return $this->unit;
    }

    public function getUnitForHumansAttribute()
    {
        return $this->units_name[$this->unit];
    }

    public function getDrawerAttribute()
    {
        return false;
    }

    public function getMaterialCountAttribute()
    {
        return round($this->count, 3);
    }

    public function updated_material()
    {
        return $this->hasOne(MaterialAccountingOperationMaterials::class, 'updated_material_id', 'id');
    }

    /**
     * Function solve all unsolved tasks
     * for operation material
     * @return bool
     */
    public function solveTasksBeforeDeleting()
    {
        $this->operation->unsolved_tasks()->where('target_id', $this->id)->get()->each(function ($unsolved_task) {
            $unsolved_task->solve_n_notify();
        });

        return true;
    }

    public function createOperationMaterials(MaterialAccountingOperation $operation, array $materials, int $type, string $operation_type = 'inactivity', $description = '')
    {
        $part_materials = [ ];
        foreach ($materials as $material) {
            $mat = ManualMaterial::where('manual_materials.id', $material['material_id'])
                ->withTrashed()
                ->with('parameters')
                ->leftJoin('manual_material_categories', 'manual_material_categories.id', 'manual_materials.category_id')
                ->select('manual_material_categories.id as cat_id', 'manual_material_categories.category_unit', 'manual_materials.*')
                ->first();

            $unit = $this->units_name[$material['material_unit']] ?? 'шт';

            $unit_parameter = $mat->parameters()
                ->where('unit', $unit)
                ->first();

            $count = round($material['material_count'], 3);

            if ($type == 8 or $type == 9) {
                $new_mat = $this::create([
                    'operation_id' => $operation->id,
                    'manual_material_id' => $material['material_id'],
                    'type' => $type,
                    'used' => $material['used'] ?? 0,
                    'fact_date' => $material['material_date']
                ]);


                $part_materials[] = $new_mat->id;
            } else {
                $new_mat = $this::firstOrNew([
                    'operation_id' => $operation->id,
                    'manual_material_id' => $material['material_id'],
                    'type' => $type,
                    'used' => $material['used'] ?? 0,
                ]);
            }

            $new_mat->count += $count;
            $new_mat->unit = $material['material_unit'];

            $new_mat->save();
            if ($type == 8 or $type == 9) {
                MaterialAccountingMaterialAddition::create([
                    'operation_id' => $operation->id,
                    'operation_material_id' => $new_mat->id,
                    'description' => $description,
                    'user_id' => Auth::user()->id
                ]);
            }

            if ($operation_type == 'arrival') {

                $period = CarbonPeriod::create(($material['material_date'] ?? $operation->planned_date_from), Carbon::today());

                foreach ($period as $date) {
                    $base = MaterialAccountingBase::firstOrNew([
                        'object_id' => $operation->object_id_to,
                        'manual_material_id' => $material['material_id'],
                        'date' => $date->format('d.m.Y'),
                        'used' => $material['used'] ?? 0,
                    ]);

                    if (!isset($base->unit)) {
                        $base->unit = $this->units_name[$material['material_unit']];
                    }

                    if ($base->unit == $this->units_name[$material['material_unit']]) {}
                    else {
                        $convertParam = $new_mat->manual
                                ->convert_from($this->units_name[$material['material_unit']])
                                ->where('unit', $base->unit)->first()->value ?? 0;

                        if ($convertParam) {
                            $count = $count * $convertParam;
                        } else {
                            $message = 'Невозможно добавить  ' . $mat->name . ' т.к. нет параметра для перевода в единицу измерения (' . $base->unit . '), в которой он лежит на объекте';

                            return $message;
                        }
                    }
                    $base->count += $count;

                    if ($date->format('d.m.Y') != Carbon::today()->format('d.m.Y')) {
                            $base->transferred_today = 1;
                    }

                    $base->save();
                }
            }
            elseif ($operation_type == 'write_off') {
                $period = CarbonPeriod::create(($material['material_date'] ?? $operation->planned_date_from), Carbon::today());

                foreach ($period as $date) {
                    $base = MaterialAccountingBase::firstOrNew([
                        'object_id' => $operation->object_id_from,
                        'manual_material_id' => $material['material_id'],
                        'date' => $date->format('d.m.Y'),
                        'used' => $material['used'] ?? 0,
                    ]);

                    if (!isset($base->unit)) {
                        $base->unit = $this->units_name[$material['material_unit']];
                    }

                    if ($base->unit == $this->units_name[$material['material_unit']]) {
                    } else {
                        $convertParam = $new_mat->manual
                                ->convert_from($this->units_name[$material['material_unit']])
                                ->where('unit', $base->unit)->first()->value ?? 0;

                        if ($convertParam) {
                            $count = $count * $convertParam;
                        } else {
                            $message = 'Невозможно списать  ' . $mat->name . ' т.к. нет параметра для перевода в единицу измерения (' . $base->unit . '), в которой он лежит на объекте';

                            return $message;
                        }
                    }

                    if (!isset($base->count) or round($count, 3) > round($base->count, 3) or !$base->count) {
                        $message = 'Невозможно списать ' . $mat->name . '. Кол-во материала на объекте ' . $date->format('d.m.Y') . ': ' . ($base->count ?: 0) . ' ' . $mat->category_unit;

                        return $message;
                    }

                    if ($date->format('d.m.Y') != Carbon::today()->format('d.m.Y')) {
                        $base->transferred_today = 1;
                    }

                    $base->count -= $count;
                    if ($base->count <= 0.001) {
                        $base->count = 0;
                    }
                    $base->save();
                }
            }
            elseif ($operation_type == 'moving') {
                // count before change (see on $convertParam)
                $previosCount = $count;

                $period = CarbonPeriod::create(($material['material_date'] ?? $operation->planned_date_from), Carbon::today());

                foreach ($period as $date) {
                    if ($type == 8) {
                        $base = MaterialAccountingBase::where('object_id', $operation->object_id_from)
                            ->where('manual_material_id', $material['material_id'])
                            ->where('date', $date->format('d.m.Y'))
                            ->where('used', $material['used'] ?? 0)
                            ->first();

                        if (!isset($base->unit) && $base != null) {
                            $base->unit = $this->units_name[$material['material_unit']];
                        }

                        if ($base->unit == $this->units_name[$material['material_unit']]) {
                        } else {

                            $convertParam = $new_mat->manual
                                    ->convert_from($this->units_name[$material['material_unit']])
                                    ->where('unit', $base->unit)->first()->value ?? 0;

                            if ($convertParam) {
                                $count = $count * $convertParam;
                            } else {
                                $message = 'Невозможно списать  ' . $mat->name . ($material['used'] ? ' Б/У' : '') . ' т.к. нет параметра для перевода в единицу измерения (' . $base->unit . '), в которой он лежит на объекте';

                                return $message;
                            }
                        }

                        if (!isset($base->count) or round($count, 3) > round($base->count, 3) or !$base->count) {
                            $message = 'Невозможно использовать ' . $mat->name . ($material['used'] ? ' Б/У' : '') . '. Кол-во материала на объекте ' . $date->format('d.m.Y') . ': ' . (isset($base->count) ? $base->count : 0) . ' ' . $base->unit;

                            return $message;
                        }


                        if ($date->format('d.m.Y') != Carbon::today()->format('d.m.Y')) {
                            $base->transferred_today = 1;
                        }

                        $base->count -= $count;
                        if ($base->count <= 0.001) {
                            $base->count = 0;
                        }
                        $base->save();

                    }

                    if ($type == 9) {
                        $base = MaterialAccountingBase::firstOrNew([
                            'object_id' => $operation->object_id_to,
                            'manual_material_id' => $material['material_id'],
                            'date' => $date->format('d.m.Y'),
                            'used' => $material['used'] ?? 0,
                        ]);

                        if (!isset($base->unit)) {
                            $base->unit = $this->units_name[$material['material_unit']];
                        }
                        if ($base->unit == $this->units_name[$material['material_unit']]) {
                        } else {
                            $convertParam = $new_mat->manual
                                    ->convert_from($this->units_name[$material['material_unit']])
                                    ->where('unit', $base->unit)->first()->value ?? 0;

                            if ($convertParam) {
                                $count = $previosCount * $convertParam;
                            } else {
                                $message = 'Невозможно добавить  ' . $mat->name . ($material['used'] ? ' Б/У' : '') . ' т.к. нет параметра для перевода в единицу измерения (' . $base->unit . '), в которой он лежит на объекте';

                                return $message;
                            }
                        }

                        if ($date->format('d.m.Y') != Carbon::today()->format('d.m.Y')) {
                            $base->transferred_today = 1;
                        }

                        $base->count += $count;

                        $base->save();
                    }
                }
            }

            elseif ($operation_type == 'transformation_from') {
                $period = CarbonPeriod::create(($material['material_date'] ?? $operation->planned_date_from), Carbon::today());

                foreach ($period as $date) {
                    $base = MaterialAccountingBase::where('object_id', $operation->object_id_from)
                        ->where('manual_material_id', $material['material_id'])
                        ->where('date', $date->format('d.m.Y'))
                        ->where('used', $material['used'] ?? 0)
                        ->first();

                    if (!isset($base->unit)) {
                        $base->unit = $this->units_name[$material['material_unit']];
                    }

                    if ($base->unit == $this->units_name[$material['material_unit']]) {
                    } else {
                        $convertParam = $new_mat->manual
                                ->convert_from($this->units_name[$material['material_unit']])
                                ->where('unit', $base->unit)->first()->value ?? 0;

                        if ($convertParam) {
                            $count = $count * $convertParam;
                        } else {
                            $message = 'Невозможно списать  ' . $mat->name . ($material['used'] ? ' Б/У' : '') . ' т.к. нет параметра для перевода в единицу измерения (' . $base->unit . '), в которой он лежит на объекте';

                            return $message;
                        }
                    }

                    if (!isset($base->count) or round($count, 3) > round($base->count, 3) or !$base->count) {
                        $message = 'Невозможно использовать ' . $mat->name . ($material['used'] ? ' Б/У' : '') . '. Кол-во материала на объекте на ' . $date->format('d.m.Y') . ': ' . (isset($base->count) ? $base->count : 0) . ' ' . $base->unit;

                        return $message;
                    }

                    if ($date->format('d.m.Y') != Carbon::today()->format('d.m.Y')) {
                        $base->transferred_today = 1;
                    }

                    $base->count -= $count;
                    if ($base->count <= 0.001) {
                        $base->count = 0;
                    }
                    $base->save();
                }
            }
            elseif ($operation_type == 'transformation_to') {
                $period = CarbonPeriod::create(($material['material_date'] ?? $operation->planned_date_from), Carbon::today());

                foreach ($period as $date) {
                    $base = MaterialAccountingBase::firstOrNew([
                        'object_id' => $operation->object_id_to,
                        'manual_material_id' => $material['material_id'],
                        'date' => $date->format('d.m.Y'),
                        'used' => $material['used'] ?? 0,
                    ]);

                    if (!isset($base->unit)) {
                        $base->unit = $this->units_name[$material['material_unit']];
                    }

                    if ($base->unit == $this->units_name[$material['material_unit']]) {
                    } else {
                        $convertParam = $new_mat->manual
                                ->convert_from($this->units_name[$material['material_unit']])
                                ->where('unit', $base->unit)->first()->value ?? 0;

                        if ($convertParam) {
                            $count = $count * $convertParam;
                        } else {
                            $message = 'Невозможно добавить  ' . $mat->name . ($material['used'] ? ' Б/У': '') . ' т.к. нет параметра для перевода в единицу измерения (' . $base->unit . '), в которой он лежит на объекте';

                            return $message;
                        }
                    }

                    if ($date->format('d.m.Y') != Carbon::today()->format('d.m.Y')) {
                        $base->transferred_today = 1;
                    }

                    $base->count += $count;
                    $base->save();
                }
            }
        }
        if ($type == 8 or $type == 9) {
            return ['status' => true, 'operation_material_id' => $new_mat->id ?? 0];
        }

        return true;
    }

    public function deletePart($setting = 'withBase')
    {
        $mat = $this;
        $mat->load('manual');
        $operation = $mat->operation;

        $result_mats = $mat->siblings()->where('manual_material_id', $mat->manual_material_id)->where('type', (in_array($operation->type, [3, 4]) ? ($mat->type == 8 ? 1 : 2) : 1))->get();

        if ($result_mats->count()) {
//                $operation->status = 1;
            $mat->type == 8 ? $operation->actual_date_from = '' : $operation->actual_date_to = '';
            $operation->save();
            $count_to_subtract = $mat->count;
            foreach ($result_mats as $result_mat)
            {
                if ($result_mat->count > $count_to_subtract)
                {
                    $result_mat->count -= $count_to_subtract;
                    $result_mat->save();
                    break;
                } else
                {
                    $count_to_subtract -= $result_mat->count;
                    $result_mat->materialFiles()->delete();
                    $result_mat->delete();
                    if ($count_to_subtract >= 0) {
                        break;
                    }
                }
            }
        }
        if ($mat->type == 8) {
            $diffCount = $mat->count;
            $object_id = $operation->object_id_from;
        } elseif($mat->type == 9) {
            $diffCount = -$mat->count;
            $object_id = $operation->object_id_to;
        } else {
            dd('error!');
        }

        if ($setting == 'withBase') {
            $period = CarbonPeriod::create(($mat->fact_date), Carbon::today());

            foreach ($period as $date) {
                if ($diffCount >= 0) {
                    $base = MaterialAccountingBase::firstOrNew([
                        'object_id' => $object_id,
                        'manual_material_id' => $mat->manual_material_id,
                        'date' => $date->format('d.m.Y'),
                        'used' => $mat->used
                    ]);
                } else {
                    $base = MaterialAccountingBase::where([
                        ['object_id', $object_id],
                        ['manual_material_id', $mat->manual_material_id],
                        ['date', $date->format('d.m.Y')],
                        ['used', $mat->used]
                    ])->first();
                }

                if ($base && !isset($base->unit)) {
                    $base->unit = $mat->manual->category_unit;
                }
                if (!isset($base->unit)) {
                    $message = 'Невозможно списать ' . $mat->name . ($mat->used ? ' Б/У' : '') . '. Кол-во материала на объекте на ' . $date->format('d.m.Y') . ': ' . (isset($base->count) ? round($base->count, 3) : 0) . ' ' . ($base->unit ?? '');

                    return ['status' => 'error', 'message' => $message];
                }
                if ($base->unit == $mat->units_name[$mat->unit]) {
                } else {
                    $convertParam = $mat->manual->convert_from($mat->units_name[$mat->unit])->where('unit', $base->unit)->first()->value ?? 0;

                    if ($convertParam) {

                        $diffCount = $diffCount * $convertParam;
                        $mat->unit = array_flip($mat->units_name)[$base->unit];
                    } else {
                        $message = 'Невозможно списать  ' . $mat->manual->name . ' т.к. нет параметра для перевода в единицу измерения (' . $base->unit . '), в которой он лежит на объекте';

                        return ['status' => 'error', 'message' => $message];
                    }
                }

                if ($diffCount < 0 and (!isset($base->count) or (round($base->count ?? 0, 3) + round($diffCount, 3) < 0) or !($base->count ?? 0))) {
                    $message = 'Невозможно списать ' . $mat->name . ($mat->used ? ' Б/У' : '') . '. Кол-во материала на объекте на ' . $date->format('d.m.Y') . ': ' . (isset($base->count) ? round($base->count, 3) : 0) . ' ' . ($base->unit ?? '');

                    return ['status' => 'error', 'message' => $message];
                }

                $base->count += $diffCount;
                if ($base->date != Carbon::now()->format('d.m.Y')) {
                    $base->transferred_today = 1;
                }
                $base->save();
            }
        }
            $mat->materialFiles()->delete();
            $mat->solveTasksBeforeDeleting();
            $mat->delete();

        return ['status' => 'success', 'message' => 'Материал удален!'];
    }

}
