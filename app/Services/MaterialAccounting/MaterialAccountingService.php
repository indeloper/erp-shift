<?php

namespace App\Services\MaterialAccounting;

use App\Http\Requests\Building\MaterialAccounting\SendMovingRequest;
use App\Models\Group;
use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialParameter;
use App\Models\Manual\ManualReference;
use App\Models\MatAcc\MaterialAccountingMaterialFile;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use App\Models\Notification\Notification;
use App\Models\ProjectObject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaterialAccountingService {

    protected $operation;

    public function __construct(MaterialAccountingOperation $operation = null)
    {
        $this->operation = $operation;
    }

    public function send(Request $request): array
    {
        $result = [];

        if ($this->isArrivalOperation()) {
            $partResult = $this->partSaveArrivalOperation($request);

            if ($partResult['status'] === 'success') {
                $result = $this->sendArrivalOperation();
            } else {
                return $partResult;
            }
        }
        elseif ($this->isWriteOffOperation()) {
            $partResult = $this->partSaveWriteOffOperation($request);

            if ($partResult['status'] === 'success') {
                $result = $this->sendWriteOffOperation();
            } else {
                return $partResult;
            }
        }
        elseif ($this->isTransformationOperation()) {
            $partResult = $this->partSaveTransformationOperation($request);

            if ($partResult['status'] === 'success') {
                $result = $this->sendTransformationOperation();
            } else {
                return $partResult;
            }
        }
        elseif ($this->isMovingOperation()) {
            $partResult = $this->partSaveMovingOperation($request);

            if ($partResult['status'] === 'success') {
                $result = $this->sendMovingOperation($request);
            } else {
                return $partResult;
            }
        }

        return $result;
    }

    public function partSend(Request $request)
    {
        $result = [];

        if ($this->isArrivalOperation()) {
            $result = $this->partSaveArrivalOperation($request);
        }
        elseif ($this->isWriteOffOperation()) {
            $result = $this->partSaveWriteOffOperation($request);
        }
        elseif ($this->isTransformationOperation()) {
            $result = $this->partSaveTransformationOperation($request);
        }
        elseif ($this->isMovingOperation()) {
            $result = $this->partSaveMovingOperation($request);
        }

        return $result;
    }

    public function isArrivalOperation(): bool
    {
        return $this->operation->type == 1;
    }

    public function isWriteOffOperation(): bool
    {
        return $this->operation->type == 2;
    }

    public function isTransformationOperation(): bool
    {
        return $this->operation->type == 3;
    }

    public function isMovingOperation(): bool
    {
        return $this->operation->type == 4;
    }

    public function sendArrivalOperation()
    {
        $this->operation->status = 2;
        $this->operation->sender_id = Auth::user()->id;
        $this->operation->saveOrFail();

        $this->operation->generateOperationEndNotifications();

        $materials = $this->operation->materialsPartTo->toArray();
        MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($this->operation, $materials, 1);

        return ['status' => 'success'];
    }

    public function sendWriteOffOperation()
    {
        $this->operation->status = 2;
        $this->operation->sender_id = Auth::user()->id;
        $this->operation->saveOrFail();

        $this->operation->generateOperationEndNotifications();

        $materials = $this->operation->materialsPartFrom->toArray();
        $result = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($this->operation, $materials, 1);

        if ($result !== true) {
            return ['status' => 'error', 'message' => $result];
        }

        return ['status' => 'success'];
    }

    public function sendTransformationOperation()
    {
        $this->operation->status = 2;
        $this->operation->actual_date_from = Carbon::now()->format('d.m.Y');
        $this->operation->actual_date_to= Carbon::now()->format('d.m.Y');

        $this->operation->sender_id = Auth::user()->id;

        $this->operation->saveOrFail();

        $this->operation->generateOperationEndNotifications();

        $result_from = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($this->operation, $this->operation->materialsPartFrom->toArray(), 1);

        if ($result_from !== true) {
            return ['status' => 'error', 'message' => $result_from];
        }

        $result_to = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($this->operation, $this->operation->materialsPartTo->toArray(), 2);

        if ($result_to !== true) {
            return ['status' => 'error', 'message' => $result_to];
        }

        return ['status' => 'success'];
    }

    public function sendMovingOperation($request)
    {
        $bad_materials = collect([]);

        if($request->type == 1) {
            $this->operation->actual_date_from = Carbon::now()->format('d.m.Y');
            $this->operation->sender_id = Auth::user()->id;

            $result = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($this->operation, $this->operation->materialsPartFrom->toArray(), $request->type);

            if ($result !== true) {
                return ['status' => 'error', 'message' => $result];
            }

        } elseif ($request->type == 2) {
            $this->operation->actual_date_to = Carbon::now()->format('d.m.Y');
            $this->operation->recipient_id = Auth::user()->id;

            $result = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($this->operation, $this->operation->materialsPartTo->toArray(), $request->type);

            if ($result !== true) {
                return ['status' => 'error', 'message' => $result];
            }
        }

        if($this->operation->actual_date_from && $this->operation->actual_date_to) {
            $this->operation->status = 2;
            foreach ($this->operation->materials()->whereIn('type', [1, 2])->get()->groupBy('manual_material_id') as $man_mat_id => $material_types) {

                $count_from = $material_types->where('type', 1)->sum('count');
                $count_to = $material_types->where('type', 2)->sum('count');
                if (round($count_from, 4) !== round($count_to, 4)) {
                    $bad_materials->push([
                        'manual_material_id' => $man_mat_id,
                        'material_name' => $material_types->where('type', 1)->first() ? $material_types->where('type', 1)->first()->manual->name : ManualMaterial::find($man_mat_id)->name,
                        'diff' => $count_from - $count_to,
                    ]); //those fields are currently unused
                }
            }

            if ($bad_materials->count()) {
                $user_ids = [$this->operation->author->id, Group::find(6)->getUsers()->first()->id];
                array_push($user_ids, ...$this->operation->responsible_users()->pluck('user_id')->toArray());

                foreach ($user_ids as $user_id) {
                    $notification = new Notification();
                    $notification->save();
                    $notification->additional_info = ' Перейти к операции можно по ссылке: ' . PHP_EOL . $this->operation->general_url;
                    $notification->update([
                        'name' => "По операции перемещения материалов c объекта {$this->operation->object_from->name_tag} на объект {$this->operation->object_to->name_tag};" .
                            " в периоде выполнения: {$this->operation->planned_date_from} - {$this->operation->planned_date_to}" .
                            ', выявлено расхождение в количестве фактически отправленного и фактически полученного материала.',
                        'status' => 6,
                        'user_id' => $user_id,
                        'target_id' => $this->operation->id,
                        'type' => 12
                    ]);
                }
            }
        }

        $this->operation->saveOrFail();

        $this->operation->generateOperationEndNotifications($request->type);

        return ['status' => 'success'];
    }

    public function partSaveArrivalOperation($request)
    {
        DB::beginTransaction();

        $this->operation->actual_date_to = Carbon::now()->format('d.m.Y');
        $this->operation->sender_id = Auth::user()->id;

        $this->operation->saveOrFail();

        $this->operation->generatePartSendNotification();

        $result = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($this->operation, $request->materials, 9, 'arrival', $request->comment);

        if ($result !== true && ($result['status'] ?? null) != true) {
            return ['status' => 'error', 'message' => $result];
        }

        MaterialAccountingMaterialFile::whereIn('id', array_merge((array)$request->files_ids, (array)$request->images_ids))
            ->where('operation_material_id', 0)
            ->update(['operation_material_id' => $result['operation_material_id']]);

        DB::commit();

        return ['status' => 'success', 'operation_material_id' => $result['operation_material_id']];
    }

    public function partSaveWriteOffOperation($request)
    {
        DB::beginTransaction();

        $this->operation->actual_date_from = Carbon::now()->format('d.m.Y');
        $this->operation->sender_id = Auth::user()->id;

        $this->operation->saveOrFail();

        $this->operation->generatePartSendNotification();

        $result = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($this->operation, $request->materials, 8, 'write_off', $request->comment);

        if ($result !== true && ($result['status'] ?? null) != true) {
            return ['status' => 'error', 'message' => $result];
        }

        MaterialAccountingMaterialFile::whereIn('id', array_merge((array)$request->files_ids, (array)$request->images_ids))
            ->where('operation_material_id', 0)
            ->update(['operation_material_id' => $result['operation_material_id']]);

        DB::commit();

        return ['status' => 'success', 'operation_material_id' => $result['operation_material_id']];
    }

    public function partSaveTransformationOperation($request)
    {
        DB::beginTransaction();

        $this->operation->sender_id = Auth::user()->id;

        $this->operation->saveOrFail();

        $this->operation->generatePartSendNotification();

        if (isset($request->type) && $request->type == 8) {
            $result_from = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($this->operation, $request->materials, 8, 'transformation_from', $request->comment);
        } elseif (!isset($request->type)) {
            $result_from = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($this->operation, $request->materials_from, 8, 'transformation_from', $request->comment);
        }

        if (isset($result_from) && $result_from !== true && ($result_from['status'] ?? null) != true) {
            return ['status' => 'error', 'message' => $result_from];
        }

        if (isset($request->type) && $request->type == 9) {
            $result_to = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($this->operation, $request->materials, 9, 'transformation_to', $request->comment);
        } elseif (!isset($request->type)) {
            $result_to = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($this->operation, $request->materials_to, 9, 'transformation_to', $request->comment);
        }

        if (isset($result_to) && $result_to !== true && ($result_to['status']  ?? null) != true) {
            return ['status' => 'error', 'message' => $result_to];
        }

        if (isset($result_to)) {
            MaterialAccountingMaterialFile::whereIn('id', array_merge((array)$request->files_ids, (array)$request->images_ids))
                ->where('operation_material_id', 0)
                ->update(['operation_material_id' => $result_to['operation_material_id']]);
        }

        DB::commit();

        return ['status' => 'success', 'operation_material_id' => $result_to['operation_material_id'] ?? 0];
    }

    public function partSaveMovingOperation($request)
    {
        if (!$request->materials or !count($request->materials)) {
            return ['status' => 'success'];
        }

        $this->validatePartSaveMovingOperation($request);

        $this->operation->saveOrFail();

        // if type == 1 or type == 2 -> send with part save
        if ($request->type == 1 || $request->type == 8) {
            $type = 8;
        } elseif ($request->type == 2 || $request->type == 9) {
            $type = 9;
        }

        $result = MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($this->operation, $request->materials, $type, 'moving', $request->comment);

        $this->operation->generatePartSendNotification($type);

        if ($result !== true && ($result['status'] ?? null) != true) {
            return ['status' => 'error', 'message' => $result];
        }

        MaterialAccountingMaterialFile::whereIn('id', array_merge((array)$request->files_ids, (array)$request->images_ids))
            ->where('operation_material_id', 0)
            ->update(['operation_material_id' => $result['operation_material_id']]);

        return ['status' => 'success', 'operation_material_id' => $result['operation_material_id']];
    }

    public function validatePartSaveMovingOperation($request)
    {
        $validator = new SendMovingRequest();

        $request->validate($validator->rules(), $validator->messages());
    }

    public function createPartAcceptTask($materials)
    {
//        $this->operation->tasks()->create(
//            [
//                'name' => 'Согласование частичного закрытия',
//                'responsible_user_id' => $this->operation->author_id,
//                'status' => 42,
//                'expired_at' => $this->addHours(8),
//            ]
//        );
    }

    public function compareMaterials()
    {
        if ($this->isArrivalOperation()) {
            return $this->compareMaterialsTypes(1, 2);
        }
        elseif ($this->isWriteOffOperation()) {
            return $this->compareMaterialsTypes(1, 2);
        }
        elseif ($this->isTransformationOperation()) {
            $result = $this->compareMaterialsTypes(2, 4);

            if ($result['status'] == 'success') {
                $result = $this->compareMaterialsTypes(1, 4);
            }

            return $result;
        }
        elseif ($this->isMovingOperation()) {
            $result = $this->compareMaterialsTypes(1, 5);

            if ($result['status'] == 'success') {
                $result = $this->compareMaterialsTypes(2, 4);
            }

            return $result;
        }

        return ['status' => 'success', 'message' => 'Операция может быть выполнена.'];
    }

    public function compareMaterialsTypes($acceptType, $factType)
    {
        $materials = $this->operation->materials()
            ->with('manual')
            ->where('type', $factType)
            ->get();

        foreach ($materials as $fact_material) {
            $materialCompare = $fact_material->sameMaterials()->where('type', $acceptType)->first();
            if (!$materialCompare) {
                return [
                    'status' => 'error',
                    'message' => 'Отсутствует материал в факте операции! '
                        . $fact_material->manual->name
                ];
            }

            if ($fact_material->unit == $materialCompare->unit) {
            }
            else {
                $materialCompare->count = ($materialCompare->manual->convert_to($materialCompare->unit)->value ?? 0) * $materialCompare->count;
            }

            if (!$materialCompare || $materialCompare->count != $fact_material->count) {
                return [
                    'status' => 'error',
                    'message' => 'Фактический и итоговый материал отличаются! '
                        . $fact_material->manual->name
                        . ' итог: ' . $fact_material->count . ' ' . $fact_material->units_name[$fact_material->unit]
                        . ', факт: ' . ($materialCompare->count ?? 0) . ' ' . $materialCompare->units_name[$materialCompare->unit]
                ];
            }
        }

        return ['status' => 'success', 'message' => 'Операция может быть выполнена.'];
    }

    public function getSearchValues($search, $for_operations = false)
    {
        $response = [];
//        $mats = ManualMaterial::where('name', 'like', "%$search%")->take(5)->get();
        $objects = ProjectObject::where('name', 'like',  "%{$search}%")
            ->orWhere('address', 'like',  "%{$search}%")
            ->orWhere('short_name', 'like',  "%{$search}%")->take(5)->get();
        $references = ManualReference::where('name', 'like', "%$search%")->take(5)->get();

        $types = (new MaterialAccountingOperation())->type_names;
        $statuses = (new MaterialAccountingOperation())->status_names;

        if ($for_operations) {
            $users = User::where(DB::raw('CONCAT(last_name, " ", first_name, " ", patronymic)'), 'like', "%{$search}%")->take(5)->get();
            if ($search !== '') {
                $types = array_filter($types, function ($type) use ($search) {
                    return !(mb_stripos(($type), ($search)) === false);
                });
                $statuses = array_filter($statuses, function ($status) use ($search) {
                    return !(mb_stripos($status, $search) === false);
                });
            }


            if (count($types)) {
                foreach ($types as $key => $type) {
                    $response[] = [
                        'type_name' => 'Тип операции',
                        'type_id' => 4,
                        'result_id' => $key,
                        'result_name' => $type,
                    ];
                }
            }

            if (count($statuses)) {
                foreach ($statuses as $key => $status) {
                    $response[] = [
                        'type_name' => 'Статус операции',
                        'type_id' => 3,
                        'result_id' => $key,
                        'result_name' => $status,
                    ];
                }
            }

            if ($users->count()) {
                foreach ($users as $user) {
                    $response[] = [
                        'type_name' => 'Пользователи',
                        'type_id' => 2,
                        'result_id' => $user->id,
                        'result_name' => $user->full_name,
                    ];
                }
            }
        }

        if ($objects->count()) {
            foreach ($objects as $object) {
                $response[] = [
                    'type_name' => 'Объекты',
                    'type_id' => 0,
                    'result_id' => $object->id,
                    'result_name' => $object->name_tag,
                ];
            }
        }

        if ($references->count()) {
            $references = $references->filter(function($ref) {return $ref->category;});
            foreach ($references as $reference) {
                $attrs = $reference->category->needAttributes();
                $attr_ids = [];
                foreach ($attrs as $attr) {
                    if ($attr['id'] == 'etalon') continue;
                    $mats_id = ManualMaterial::where('manual_reference_id', $reference->id)->get('id')->pluck('id');
                    $par_q = ManualMaterialParameter::whereIn('mat_id', $mats_id)->where('attr_id', $attr['id'])->get();
                    $par_q = $par_q->filter(function ($par) {
                        return is_numeric($par->value);
                    });
                    $attr_ids[$attr['id']] = [
                        'name' => $attr['name'],
                        'max' => $par_q->max('value'),
                        'min' => $par_q->min('value'),
                    ];
                }
                $response[] = [
                    'type_name' => 'Эталоны',
                    'type_id' => ($for_operations ? 5 : 3),
                    'result_id' => $reference->id,
                    'result_name' => $reference->name,
                    'attr_ids' => $attr_ids,
                ];
            }
        }

//        if ($mats->count()) {
//            foreach ($mats as $mat) {
//                $response[] = [
//                    'type_name' => 'Материалы',
//                    'type_id' => 1,
//                    'result_id' => $mat->id,
//                    'result_name' => $mat->name,
//                ];
//            }
//        }

        return $response;
    }

    public function acceptOperation($operation)
    {
        $operation->checkClosed();
        $operation->status = 3;
        $operation->is_close = 1;
        $operation->recipient_id = $operation->recipient_id != 0 ? $operation->recipient_id : Auth::user()->id;

        $materials = $operation->materialsPartTo()->get()->reduce(function ($result, $mat) {
            if (isset($result[$mat->base_id])) {
                $result[$mat->base_id]['material_count'] += $mat->count;
            } else {
                $result[$mat->base_id] = [
                    'material_id' => $mat->manual_material_id,
                    'base_id' => $mat->base_id,
                    'used' => $mat->used,
                    'material_unit' => $mat->unit,
                    'material_count' => $mat->count,
                ];
            }
            return $result;
        }, []);

        $operation->saveOrFail();
        $operation->generateOperationAcceptNotifications();

        $dummy_mat = (new MaterialAccountingOperationMaterials());
        $itog_type = $dummy_mat->itog_types[$operation->type];

        $result = $dummy_mat->createOperationMaterials($operation, $materials, is_array($itog_type) ? $itog_type[0] : $itog_type, 'inactivity');
        if ($result !== true) {
            return $result;
        }

        if ($operation->type == 3) {
            $materials = $operation->materialsPartFrom()->get()->reduce(function ($result, $mat) {
                if (isset($result[$mat->base_id])) {
                    $result[$mat->base_id]['material_count'] += $mat->count;
                } else {
                    $result[$mat->base_id] = [
                        'material_id' => $mat->manual_material_id,
                        'base_id' => $mat->base_id,
                        'used' => $mat->used,
                        'material_unit' => $mat->unit,
                        'material_count' => $mat->count,
                    ];
                }
                return $result;
            }, []);

            $result = $dummy_mat->createOperationMaterials($operation, $materials, $itog_type[1], 'inactivity');
        }

        return $result;
    }

}
