<?php

namespace App\Models\MatAcc;

use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Comment;
use App\Models\Manual\ManualMaterial;
use App\Models\Task;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class MaterialAccountingOperationMaterials extends Model
{
    use HasFactory;
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
        'base_id',
    ];

    protected $appends = ['material_id', 'material_unit', 'material_count', 'drawer', 'material_name', 'comment_name', 'converted_count'];

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
        10 => 'edition_material', // material with updation task
    ];
    /* type map for operation.type / material.type

     *                  plan    fact    itog    part
     * 1 - arrival       3       1       2       9
     * 2 - write_off     3       1       2       8
     * 4 - moving        3      1|2      4      9|8
     * 3 - transfer     6|7     2|1     4|5     9|8

     * */

    public $itog_types = [
        1 => 2, //8
        2 => 2, //8
        4 => 4, //9
        3 => [4, 5]];

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
        $flipped_unit_array = array_values(array_filter(self::$main_units, function ($unit) use ($unit_to_flip) {
            return $unit['id'] == $unit_to_flip;
        }));

        if (count($flipped_unit_array) > 0) {
            return $flipped_unit_array[0]['text'];
        }

        $flipped_unit_array = array_values(array_filter(self::$main_units, function ($unit) use ($unit_to_flip) {
            return $unit['text'] == $unit_to_flip;
        }));

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
     */
    public function getMaterialNameAttribute(): string
    {
        return $this->manual()->first()->name.($this->used ? ' Б/У' : '');
    }

    public function getCommentNameAttribute()
    {
        if ($this->base()->exists()) {
            $comments = $this->base->getCommentsInline();

            return $this->material_name." ($comments)";
        } else {
            return $this->material_name;
        }
    }

    public function base(): BelongsTo
    {
        return $this->belongsTo(MaterialAccountingBase::class, 'base_id', 'id');
    }

    public function comments(): HasManyThrough
    {
        return $this->hasManyThrough(Comment::class, MaterialAccountingBase::class, 'id', 'commentable_id', 'base_id', 'id')
            ->where('commentable_type', MaterialAccountingBase::class);
    }

    public function manual(): BelongsTo
    {
        return $this->belongsTo(ManualMaterial::class, 'manual_material_id', 'id')
            ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
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

    public function materialAddition(): HasOne
    {
        return $this->hasOne(MaterialAccountingMaterialAddition::class, 'operation_material_id', 'id');
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(MaterialAccountingOperation::class, 'operation_id', 'id');
    }

    public function siblings()
    {
        return $this->operation->allMaterials()->where('id', '!=', $this->id);
    }

    public function sameMaterials()
    {
        $comments = $this->comments()->get();
        $sib_q = $this->siblings()->where('manual_material_id', $this->manual_material_id)->where('used', $this->used);
        if ($comments->count()) {
            foreach ($comments as $comment) {
                $sib_q->whereHas('comments', function ($com_q) use ($comment) {
                    $com_q->where('comment', $comment->comment);
                });
            }
            $sib_q->has('comments', $comments->count());
        } else {
            $sib_q->whereDoesntHave('comments');
        }

        return $sib_q;
    }

    public function delete_task(): HasOne
    {
        return $this->hasOne(Task::class, 'target_id', 'id')->where('status', 22);
    }

    public function materialFiles(): HasMany
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

    public function updated_material(): HasOne
    {
        return $this->hasOne(MaterialAccountingOperationMaterials::class, 'updated_material_id', 'id');
    }

    /**
     * Function solve all unsolved tasks
     * for operation material
     */
    public function solveTasksBeforeDeleting(): bool
    {
        $this->operation->unsolved_tasks()->where('target_id', $this->id)->get()->each(function ($unsolved_task) {
            $unsolved_task->solve_n_notify();
        });

        return true;
    }

    public function createOperationMaterials(MaterialAccountingOperation $operation, array $materials, int $type, string $operation_type = 'inactivity', $description = '')
    {
        $part_materials = [];
        foreach ($materials as $material) {
            $mat = ManualMaterial::where('manual_materials.id', $material['material_id'])
                ->withTrashed()
                ->with('parameters')
                ->leftJoin('manual_material_categories', 'manual_material_categories.id', 'manual_materials.category_id')
                ->select('manual_material_categories.id as cat_id', 'manual_material_categories.category_unit', 'manual_materials.*')
                ->first();
            $period = CarbonPeriod::create(($material['material_date'] ?? $operation->planned_date_from), Carbon::today());

            $count = round($material['material_count'], 3);

            $base_to = null;
            //we use object_to because we cannot take new materials from base. Only put them to base
            if (in_array($type, array_merge($this->plan_types, [9]))) {
                foreach ($period as $date) {
                    $base_to = $this->findOrCreateBaseTo($material, $operation, $date, $type);
                }
            }

            if ($this->isPartSend($type)) {
                $new_mat = $this::create([
                    'operation_id' => $operation->id,
                    'base_id' => ($type == 9 and $base_to != null) ? $base_to->id : $material['base_id'],
                    'manual_material_id' => $material['material_id'],
                    'type' => $type,
                    'used' => $material['used'] ?? 0,
                    'fact_date' => $material['material_date'],
                ]);
                MaterialAccountingMaterialAddition::create([
                    'operation_id' => $operation->id,
                    'operation_material_id' => $new_mat->id,
                    'description' => $description,
                    'user_id' => Auth::user()->id,
                ]);
                $part_materials[] = $new_mat->id;
                if ($new_mat->sameMaterials()->whereIn('type', $this->plan_types)->doesntExist()) {
                    $new_plan = $new_mat->replicate();
                    $new_plan->count = 0;
                    $new_plan->type = ($operation->type == 3) ? ($new_mat->type == 8 ? 7 : 6) : 3;
                    $new_plan->unit = $material['material_unit'];
                    $new_plan->save();
                }
            } else {
                $new_mat = $this::firstOrNew([
                    'operation_id' => $operation->id,
                    'manual_material_id' => $material['material_id'],
                    'base_id' => ($type != 7 and $base_to != null) ? $base_to->id : $material['base_id'],
                    'type' => $type,
                    'used' => $material['used'] ?? 0,
                ]);
            }

            $new_mat->count += $count;
            $new_mat->unit = $material['material_unit'];

            $new_mat->save();

            if (in_array($operation_type, ['moving', 'write_off', 'arrival', 'transformation_from', 'transformation_to'])) {
                foreach ($period as $date) {
                    if ($type == 8) {
                        $base = $this->findOrCreateBaseTo($material, $operation, $date, $type);
                        if ($base == null) {
                            return 'Не удалось найти '.$mat->name.($material['used'] ? ' Б/У' : '').'. Пожалуйста, проверьте поля в форме.';
                        }
                        if (! isset($base->unit)) {
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
                                $message = 'Невозможно списать  '.$mat->name.($material['used'] ? ' Б/У' : '').' т.к. нет параметра для перевода в единицу измерения ('.$base->unit.'), в которой он лежит на объекте';

                                return $message;
                            }
                        }

                        if (! isset($base->count) or round($count, 3) > round($base->count, 3) or ! $base->count) {
                            $message = 'Невозможно списать '.$mat->name.($material['used'] ? ' Б/У' : '').'. Кол-во материала на объекте '.$date->format('d.m.Y').': '.(isset($base->count) ? $base->count : 0).' '.$base->unit;

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

                        if (isset($material['comments'])) {
                            $this->updateBaseComments($base, $material);
                        }

                    }

                    if ($type == 9) {
                        $base = $this->findOrCreateBaseTo($material, $operation, $date, $type);

                        if ($base->count <= 0.0009) { //that means base is recently created on unused
                            $base->unit = $this->units_name[$material['material_unit']];
                        }
                        if ($base->unit != $this->units_name[$material['material_unit']]) { //it means we have to convert
                            $convertParam = $new_mat->manual
                                ->convert_from($this->units_name[$material['material_unit']])
                                ->where('unit', $base->unit)->first()->value ?? 0;

                            if ($convertParam) {
                                $count = $count * $convertParam;
                            } else {
                                $message = 'Невозможно добавить  '.$mat->name.($material['used'] ? ' Б/У' : '').' т.к. нет параметра для перевода в единицу измерения ('.$base->unit.'), в которой он лежит на объекте';

                                return $message;
                            }
                        }

                        if ($date->format('d.m.Y') != Carbon::today()->format('d.m.Y')) {
                            $base->transferred_today = 1;
                        }

                        $base->count += $count;

                        $base->save();

                        if (isset($material['comments'])) {
                            $this->updateBaseComments($base, $material);
                        }
                    }
                }
            }
        }
        if ($this->isPartSend($type)) {
            return ['status' => true, 'operation_material_id' => $new_mat->id ?? 0];
        }

        return true;
    }

    public function deletePart($setting = 'withBase')
    {
        $mat = $this;
        $mat->load('manual');
        $operation = $mat->operation;

        $result_mats = $mat->sameMaterials()->where('type', (in_array($operation->type, [3, 4]) ? ($mat->type == 8 ? 1 : 2) : 1))->get();

        if ($result_mats->count()) {
            //                $operation->status = 1;
            $mat->type == 8 ? $operation->actual_date_from = '' : $operation->actual_date_to = '';
            $operation->save();
            $count_to_subtract = $mat->count;
            foreach ($result_mats as $result_mat) {
                if ($result_mat->count > $count_to_subtract) {
                    $result_mat->count -= $count_to_subtract;
                    $result_mat->save();
                    break;
                } else {
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
        } elseif ($mat->type == 9) {
            $diffCount = -$mat->count;
            $object_id = $operation->object_id_to;
        } else {
            dd('error!');
        }

        if ($setting == 'withBase') {
            $period = CarbonPeriod::create(($mat->fact_date), Carbon::today());

            foreach ($period as $date) {
                if ($diffCount >= 0) {
                    $base = MaterialAccountingBase::where('id', $mat->base_id)
                        ->with('historyBases')
                        ->where('date', $date->format('d.m.Y'))
                        ->first();
                    if (! $base) {
                        $base = MaterialAccountingBase::firstOrNew([
                            'object_id' => $object_id,
                            'manual_material_id' => $mat->manual_material_id,
                            'date' => $date->format('d.m.Y'),
                            'used' => $mat->used,
                        ]);
                    }
                } else {
                    $base = MaterialAccountingBase::where('id', $mat->base_id)
                        ->with('historyBases')
                        ->where('date', $date->format('d.m.Y'))
                        ->first();
                    if (! $base) {
                        $base = MaterialAccountingBase::firstOrNew([
                            'object_id' => $object_id,
                            'manual_material_id' => $mat->manual_material_id,
                            'date' => $date->format('d.m.Y'),
                            'used' => $mat->used,
                        ]);
                    }
                }

                if ($base && ! isset($base->unit)) {
                    $base->unit = $mat->manual->category_unit;
                }
                if (! isset($base->unit)) {
                    $message = 'Невозможно списать '.$mat->name.($mat->used ? ' Б/У' : '').'. Кол-во материала на объекте на '.$date->format('d.m.Y').': '.(isset($base->count) ? round($base->count, 3) : 0).' '.($base->unit ?? '');

                    return ['status' => 'error', 'message' => $message];
                }
                if ($base->unit == $mat->units_name[$mat->unit]) {
                } else {
                    $convertParam = $mat->manual->convert_from($mat->units_name[$mat->unit])->where('unit', $base->unit)->first()->value ?? 0;

                    if ($convertParam) {

                        $diffCount = $diffCount * $convertParam;
                        $mat->unit = array_flip($mat->units_name)[$base->unit];
                    } else {
                        $message = 'Невозможно списать  '.$mat->manual->name.' т.к. нет параметра для перевода в единицу измерения ('.$base->unit.'), в которой он лежит на объекте';

                        return ['status' => 'error', 'message' => $message];
                    }
                }

                if ($diffCount < 0 and (! isset($base->count) or (round($base->count ?? 0, 3) + round($diffCount, 3) < 0) or ! ($base->count ?? 0))) {
                    $message = 'Невозможно списать '.$mat->name.($mat->used ? ' Б/У' : '').'. Кол-во материала на объекте на '.$date->format('d.m.Y').': '.(isset($base->count) ? round($base->count, 3) : 0).' '.($base->unit ?? '');

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
        if ($mat->sameMaterials()->where('count', '>', 0)->doesntExist()) {
            $mat->sameMaterials()->get()->each->delete();
        }
        $mat->delete();

        return ['status' => 'success', 'message' => 'Материал удален!'];
    }

    private function updateBaseComments($base, $material): void
    {
        $base->comments()->delete();
        $base->refresh();
        foreach ($material['comments'] as $comment) {
            $base->comments()->create([
                'comment' => isset($comment['comment']) ? $comment['comment'] : $comment,
                'author_id' => auth()->id() ?? 0,
            ]);
        }
    }

    private function isPartSend(int $type): bool
    {
        return $type == 8 or $type == 9;
    }

    /**
     * @return \App\Models\MatAcc\MaterialAccountingBase|\Illuminate\Database\Eloquent\Builder|Model|object
     */
    private function findOrCreateBaseTo($material, MaterialAccountingOperation $operation, \Carbon\CarbonInterface $date, $type)
    {
        $comments = isset($material['comments']) ? $material['comments'] : [];

        if (isset($material['base_id'])) {
            if ($material['base_id'] != 'undefined' and $material['base_id'] == true and ! count($comments) and $type != 9) {
                $comments = MaterialAccountingBase::find($material['base_id'])->comments()->get();
            }
        }
        //trying to find existing base for mat
        //we have to find or create base for every day of the period
        $main_fields = [
            'object_id' => $type == 8 ? ($operation->object_id_from ?: $operation->object_id_to) : ($operation->object_id_to ?: $operation->object_id_from),
            'manual_material_id' => $material['material_id'],
            'date' => $date->format('d.m.Y'),
            'used' => $material['used'] ?? 0,
        ];
        $ex_base = MaterialAccountingBase::query()->where($main_fields);

        if (count($comments) > 0) {
            //we are looking for the same comments.
            foreach ($comments as $comment) {
                $ex_base->whereHas('comments', function ($comm_q) use ($comment) {
                    $comm_q->where('comment', $comment['comment']);
                });
            }
            $ex_base->has('comments', count($comments));
        } else {
            // or absence of comments
            $ex_base->whereDoesntHave('comments');
        }

        // we create new base if there is no one and it's not write off. Do nothing otherwise
        if ($ex_base->doesntExist() and $type != 8) {
            $new_base = new MaterialAccountingBase($main_fields);
            $new_base->count = 0;
            $new_base->save();
            foreach ($comments as $comment) {
                $new_base->comments()->create(['comment' => $comment['comment']]);
            }
        } else {
            $new_base = $ex_base->first();
        }

        return $new_base;
    }
}
