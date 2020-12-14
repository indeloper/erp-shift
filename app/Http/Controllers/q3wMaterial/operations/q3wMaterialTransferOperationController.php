<?php

namespace App\Http\Controllers\q3wMaterial\operations;

use App\Models\Notification;
use App\Models\ProjectObject;
use App\models\q3wMaterial\operations\q3wMaterialOperation;
use App\Models\q3wMaterial\operations\q3wOperationMaterial;
use App\models\q3wMaterial\q3wMaterial;
use App\models\q3wMaterial\q3wMaterialSnapshot;
use App\models\q3wMaterial\q3wMaterialStandard;
use App\Models\q3wMaterial\q3wMaterialType;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class q3wMaterialTransferOperationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return Application|Factory|View
     */
    public function create(Request $request)
    {
        $transferOperationInitiator = "none";

        if (isset($request->sourceProjectObjectId)) {
            $sourceProjectObjectId = $request->sourceProjectObjectId;
            $transferOperationInitiator = "source";
        } else {
            $sourceProjectObjectId = 0;
        }

        if (isset($request->destinationProjectObjectId)) {
            $destinationProjectObjectId = $request->destinationProjectObjectId;
            $transferOperationInitiator = "destination";
        } else {
            $destinationProjectObjectId = 0;
        }

        if (isset($request->materialsToTransfer)) {
            $predefinedMaterialsArray = explode('+', $request->materialsToTransfer);

            $predefinedMaterials = DB::table('q3w_materials as a')
                ->leftJoin('q3w_material_standards as b', 'a.standard_id', '=', 'b.id')
                ->leftJoin('q3w_material_types as d', 'b.material_type', '=', 'd.id')
                ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
                ->where('a.project_object', '=', $sourceProjectObjectId)
                ->whereIn('a.id', $predefinedMaterialsArray)
                ->get(['a.id',
                    'a.standard_id',
                    'a.amount',
                    'a.quantity',
                    'b.name as standard_name',
                    'b.material_type',
                    'b.weight as standard_weight',
                    'd.accounting_type',
                    'd.measure_unit',
                    'e.value as measure_unit_value'])
                ->toJSON(JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
        } else {
            $predefinedMaterials = json_encode([]);
        }


        return view('materials.operations.transfer.new')->with([
            'sourceProjectObjectId' => $sourceProjectObjectId,
            'destinationProjectObjectId' => $destinationProjectObjectId,
            'transferOperationInitiator' => $transferOperationInitiator,
            'currentUserId' => Auth::id(),
            'predefinedMaterials' => $predefinedMaterials
        ]);
    }

    public function move(q3wMaterialOperation $operation)
    {
        DB::beginTransaction();

        foreach ($operation->materials as $operationMaterial) {
            switch ($operationMaterial->standard->materialType->accounting_type) {
                case 2:
                    $sourceProjectObjectMaterial = q3wMaterial::where('project_object', $operation->source_project_object_id)
                        ->where('standard_id', $operationMaterial->standard->id)
                        ->where('quantity', $operationMaterial->quantity)
                        ->first();
                    $destinationProjectObjectMaterial = q3wMaterial::where('project_object', $operation->destination_project_object_id)
                        ->where('standard_id', $operationMaterial->standard->id)
                        ->where('quantity', $operationMaterial->quantity)
                        ->first();
                    break;
                default:
                    $sourceProjectObjectMaterial = q3wMaterial::where('project_object', $operation->source_project_object_id)
                        ->where('standard_id', $operationMaterial->standard->id)
                        ->first();
                    $destinationProjectObjectMaterial = q3wMaterial::where('project_object', $operation->destination_project_object_id)
                        ->where('standard_id', $operationMaterial->standard->id)
                        ->first();
            }

            if (!isset($sourceProjectObjectMaterial)) {
                abort(400, 'Материала не существует на объекте отправителя');
            }

            switch ($operationMaterial->standard->materialType->accounting_type) {
                case 2:
                    $sourceProjectObjectMaterialDelta = $sourceProjectObjectMaterial->amount - $operationMaterial->amount;
                    break;
                default:
                    $sourceProjectObjectMaterialDelta = $sourceProjectObjectMaterial->quantity - $operationMaterial->amount * $operationMaterial->quantity;
            }

            if ($sourceProjectObjectMaterialDelta < 0) {
                abort(400, 'На объекте отправления недостаточно материала');
            }

            switch ($operationMaterial->standard->materialType->accounting_type) {
                case 2:
                    $sourceProjectObjectMaterial->amount = $sourceProjectObjectMaterial->amount - $operationMaterial->amount;
                    $sourceProjectObjectMaterial->save();

                    if (isset($destinationProjectObjectMaterial)) {
                        $destinationProjectObjectMaterial->amount = $destinationProjectObjectMaterial->amount + $operationMaterial->amount;
                        $destinationProjectObjectMaterial->save();
                    } else {
                        $destinationProjectObjectMaterial = new q3wMaterial([
                            'standard_id' => $operationMaterial->standard->id,
                            'project_object' => $operation->destination_project_object_id,
                            'amount' => $operationMaterial->amount,
                            'quantity' => $operationMaterial->quantity
                        ]);
                        $destinationProjectObjectMaterial->save();
                    }
                    break;
                default:
                    $sourceProjectObjectMaterial->amount = $sourceProjectObjectMaterial->amount - $operationMaterial->amount * $operationMaterial->quantity;
                    $sourceProjectObjectMaterial->save();

                    if (isset($destinationProjectObjectMaterial)) {
                        $destinationProjectObjectMaterial->amount = $destinationProjectObjectMaterial->amount + $operationMaterial->amount * $operationMaterial->quantity;
                        $destinationProjectObjectMaterial->save();
                    } else {
                        $destinationProjectObjectMaterial = new q3wMaterial([
                            'standard_id' => $operationMaterial->standard->id,
                            'project_object' => $operation->destination_project_object_id,
                            'amount' => 1,
                            'quantity' => $operationMaterial->amount * $operationMaterial->quantity
                        ]);
                        $destinationProjectObjectMaterial->save();
                    }
            }
        }

        (new q3wMaterialSnapshot)->takeSnapshot($operation, ProjectObject::find($operation->source_project_object_id));
        (new q3wMaterialSnapshot)->takeSnapshot($operation, ProjectObject::find($operation->destination_project_object_id));

        $this->moveOperationToNextStage($operation->id);

        DB::commit();
    }

    /*public function move(q3wMaterialOperation $operation)
    {
        $requestData = json_decode($request["data"], JSON_OBJECT_AS_ARRAY);

        if (isset($requestData['operationId'])) {
            $operation = q3wMaterialOperation::findOrFail($requestData['operationId']);
        }

        foreach ($requestData['materials'] as $inputMaterial) {
            $materialStandard = q3wMaterialStandard::findOrFail($inputMaterial['standard_id']);

            $materialType = $materialStandard->materialType;

            $inputMaterialAmount = $inputMaterial['amount'];
            $inputMaterialQuantity = $inputMaterial['quantity'];

            // Изменение материалов у отправителя
            if ($materialType->accounting_type == 2) {
                $sourceProjectObjectMaterial = q3wMaterial::where('project_object', $operation->source_project_object_id)
                    ->where('standard_id', $materialStandard->id)
                    ->where('quantity', $inputMaterialQuantity)
                    ->first();
            } else {
                $sourceProjectObjectMaterial = q3wMaterial::where('project_object', $operation->source_project_object_id)
                    ->where('standard_id', $materialStandard->id)
                    ->first();
            }

            if (isset($sourceProjectObjectMaterial)) {
                if ($materialType->accounting_type == 2) {
                    $materialAmountDelta = $sourceProjectObjectMaterial->amount - $inputMaterialAmount;
                    if ($materialAmountDelta >= 0) {
                        $sourceProjectObjectMaterial->amount = $materialAmountDelta;
                    } else {
                        abort(400, 'Материала недостаточно на объекте отправления');
                    }
                } else {
                    $materialQuantityDelta = $sourceProjectObjectMaterial->quantity - $inputMaterialQuantity * $inputMaterialAmount;
                    if ($materialQuantityDelta >= 0) {
                        $sourceProjectObjectMaterial->quantity = $materialQuantityDelta;
                    } else {
                        abort(400, 'Материала недостаточно на объекте отправления');
                    }
                }

                $sourceProjectObjectMaterial->save();
            } else {
                abort(400, 'Материала не существует на объекте отправителя');
            }

            // Изменение материалов у получателя
            if ($materialType->accounting_type == 1) {
                $destinationProjectObjectMaterial = q3wMaterial::where('project_object', $operation->destination_project_object_id)
                    ->where('standard_id', $materialStandard->id)
                    ->where('quantity', $inputMaterialQuantity)
                    ->first();
            } else {
                $destinationProjectObjectMaterial = q3wMaterial::where('project_object', $operation->destination_project_object_id)
                    ->where('standard_id', $materialStandard->id)
                    ->first();
            }

            if (isset($destinationProjectObjectMaterial)) {
                if ($materialType->accounting_type == 2) {
                    $destinationProjectObjectMaterial->amount = $destinationProjectObjectMaterial->amount + $inputMaterialAmount;
                } else {
                    $destinationProjectObjectMaterial->quantity = $destinationProjectObjectMaterial->quantity + $inputMaterialQuantity * $inputMaterialAmount;
                }

                $destinationProjectObjectMaterial->save();
            } else {
                $destinationProjectObjectMaterial = new q3wMaterial([
                    'standard_id' => $materialStandard->id,
                    'project_object' => $operation->destination_project_object_id,
                    'amount' => $inputMaterialAmount,
                    'quantity' => $inputMaterialQuantity
                ]);

                $destinationProjectObjectMaterial->save();
            }
        }


        (new q3wMaterialSnapshot)->takeSnapshot($operation, ProjectObject::find($operation->source_project_object_id));
        (new q3wMaterialSnapshot)->takeSnapshot($operation, ProjectObject::find($operation->destination_project_object_id));

        $this->moveOperationToNextStage($operation->id);

        DB::commit();

        return response()->json([
            'result' => 'ok'
        ], 200);
    }*/

    public function moveOperationToNextStage($operationID)
    {
        $operation = q3wMaterialOperation::findOrFail($operationID);

        if (isset($operation)) {
            switch ($operation->operation_route_stage_id) {
                case 5:
                    $notification = new Notification();
                    $notification->save();
                    $notification->additional_info = ' Ссылка на операцию: ' . route('materials.operations.transfer.view') . '?operationId=' . $operation->id;
                    $notification->update([
                        'name' => 'Новое перемещение',
                        'user_id' => $operation->source_responsible_user_id,
                        //'task_id' => $task->id,
                        //'contractor_id' => $task->contractor_id,
                        'object_id' => $operation->destination_project_object_id,
                        //'object_id' => isset($task->project->object->id) ? $task->project->object->id : null,
                        'created_at' => now(),
                        'type' => 11
                    ]);
                    $operation->operation_route_stage_id = 7;
                    $operation->save();
                    break;
                case 6:
                    $notification = new Notification();
                    $notification->save();
                    $notification->additional_info = ' Ссылка на операцию: ' . route('materials.operations.transfer.view') . '?operationId=' . $operation->id;
                    $notification->update([
                        'name' => 'Новое перемещение',
                        'user_id' => $operation->destination_responsible_user_id,
                        //'task_id' => $task->id,
                        //'contractor_id' => $task->contractor_id,
                        'object_id' => $operation->source_project_object_id,
                        //'object_id' => isset($task->project->object->id) ? $task->project->object->id : null,
                        'created_at' => now(),
                        'type' => 11
                    ]);
                    $operation->operation_route_stage_id = 8;
                    $operation->save();
                    break;
                case 7:
                case 15:
                    $notification = new Notification();
                    $notification->save();
                    $notification->additional_info = ' Ссылка на операцию: ' . route('materials.operations.transfer.view') . '?operationId=' . $operation->id;
                    $notification->update([
                        'name' => 'Перемещение материалов завершено',
                        'user_id' => $operation->source_responsible_user_id,
                        //'task_id' => $task->id,
                        //'contractor_id' => $task->contractor_id,
                        'object_id' => $operation->destination_project_object_id,
                        //'object_id' => isset($task->project->object->id) ? $task->project->object->id : null,
                        'created_at' => now(),
                        'type' => 11
                    ]);
                    $operation->operation_route_stage_id = 9;
                    $operation->save();
                    break;
                case 8:
                case 16:
                    $notification = new Notification();
                    $notification->save();
                    $notification->additional_info = ' Ссылка на операцию: ' . route('materials.operations.transfer.view') . '?operationId=' . $operation->id;
                    $notification->update([
                        'name' => 'Перемещение материалов завершено',
                        'user_id' => $operation->source_responsible_user_id,
                        //'task_id' => $task->id,
                        //'contractor_id' => $task->contractor_id,
                        'object_id' => $operation->destination_project_object_id,
                        //'object_id' => isset($task->project->object->id) ? $task->project->object->id : null,
                        'created_at' => now(),
                        'type' => 11
                    ]);
                    $operation->operation_route_stage_id = 12;
                    $operation->save();
                    break;
            }

        }

        //$this->moveOperationToNextStage($operation->id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        $requestData = json_decode($request["data"], JSON_OBJECT_AS_ARRAY /*| JSON_THROW_ON_ERROR)*/);

        $operationRouteStage = 4;

        if ($requestData['transfer_operation_initiator'] == 'none' || $requestData['transfer_operation_initiator'] == 'source') {
            $operationRouteStage = 6;
        }

        if ($requestData['transfer_operation_initiator'] == 'destination') {
            $operationRouteStage = 5;
        }

        //Нужно проверить, что материал существует
        //Нужно проверить, что остаток будет большим, или равным нулю
        foreach ($requestData['materials'] as $inputMaterial) {
            if ($inputMaterial['accounting_type'] == 2) {
                $sourceMaterial = q3wMaterial::where('project_object', $requestData['source_project_object_id'])
                    ->where('standard_id', $inputMaterial['standard_id'])
                    ->where('quantity', $inputMaterial['quantity'])
                    ->firstOrFail();

                if ($inputMaterial['amount'] > $sourceMaterial['amount']) {
                    abort(400, 'Bad quantity for standard ' . $inputMaterial['standard_id']);
                }

            } else {
                $sourceMaterial = q3wMaterial::where('project_object', $requestData['source_project_object_id'])
                    ->where('standard_id', $inputMaterial['standard_id'])
                    ->firstOrFail();

                if ($inputMaterial['amount'] > $sourceMaterial['quantity']) {
                    abort(400, 'Bad quantity for standard ' . $inputMaterial['standard_id']);
                }
            }
        }

        $materialOperation = new q3wMaterialOperation([
            'operation_route_id' => 2,
            'operation_route_stage_id' => $operationRouteStage,
            'source_project_object_id' => $requestData['source_project_object_id'],
            'destination_project_object_id' => $requestData['destination_project_object_id'],
            'date_start' => isset($requestData['date_start']) ? $requestData['date_start'] : null,
            'date_end' => isset($requestData['date_end']) ? $requestData['date_end'] : null,
            'creator_user_id' => Auth::id(),
            'source_responsible_user_id' => $requestData['source_responsible_user_id'],
            'destination_responsible_user_id' => $requestData['destination_responsible_user_id'],
            'consignment_note_number' => $requestData['consignment_note_number']
        ]);

        $materialOperation->save();

        foreach ($requestData['materials'] as $inputMaterial) {
            $materialStandard = q3wMaterialStandard::findOrFail($inputMaterial['standard_id']);
            $materialType = $materialStandard->materialType;

            $inputMaterialAmount = $inputMaterial['amount'];
            $inputMaterialQuantity = $inputMaterial['quantity'];

            $operationMaterial = new q3wOperationMaterial([
                'material_operation_id' => $materialOperation->id,
                'standard_id' => $materialStandard->id,
                'amount' => $inputMaterialAmount,
                'quantity' => $inputMaterialQuantity,
                'initial_amount' => $inputMaterialAmount,
                'initial_quantity' => $inputMaterialQuantity,
                'edit_states' => json_encode(['addedByInitiator'])
            ]);

            $operationMaterial->save();
        }

        DB::commit();

        $this->moveOperationToNextStage($materialOperation->id);

        return response()->json([
            'result' => 'ok',
        ], 200);
    }

    /**
     * Валидирует одиночный материал для операции
     * Материал считается валидным для перемещения если:
     *      1) Существует переданная в запросе заявка (operationId)
     *      2) Существует объект отправления (sourceProjectObjectId)
     *      3) На объекте сущесвует
     *          3.1) Для шпунтового учета: Эталон + равное количество в единицах измерения (Пример: Шпунт VL 606A 14.5 м.п.)
     *          3.2) Для учета по единицам измерения: Эталон
     *      4) Количество материала на объекте отправления больше или равно, чем количество в заявке
     *      5) Отстатки не будут конфликтовать по количеству с ранее созданными заявками (//TODO: Реализовать проверку на конфликт заявок)
     * @param Request $request
     *      Должен содержать следующие поля:
     *      operationId
     *      sourceProjectObjectId
     *      material:[
     *          standardID
     *          lengthQuantity
     *          quantity
     *      ]
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateSingleMaterial(Request $request)
    {
        $errors = [];

        $operation = q3wMaterialOperation::find($request['operationId']);
        if (!isset($operation)) {
            $errors['operationNotFound'] = 'Операция не найдена';
        }

        $projectObject = ProjectObject::find($request['sourceProjectObjectId']);
        if (!isset($projectObject)) {
            $errors['sourceProjectObjectNotFound'] = 'Объект отправления не найден';
        }

        $materialStandard = q3wMaterialStandard::find($request['material']['standardID']);

        $accountingType = $materialStandard->materialType->accounting_type;

        if ($accountingType == 1) {
            $sourceProjectObjectMaterial = (new q3wMaterial)
                ->where('project_object', $projectObject->id)
                ->where('standard_id', $request['material']['standardID'])
                ->where('quantity', $request['material']['lengthQuantity'])
                ->get()
                ->first();
        } else {
            $sourceProjectObjectMaterial = (new q3wMaterial)
                ->where('project_object', $projectObject->id)
                ->where('standard_id', $request['material']['standardID'])
                ->get()
                ->first();
        }

        if (!isset($sourceProjectObjectMaterial)) {
            $errors['materialNotFound'] = 'Этого материала не существует на объекте отправления';
        } else {
            if ($accountingType == 1) {
                $materialAmountDelta = $sourceProjectObjectMaterial->amount - $request['material']['quantity'];
            } else {
                $materialAmountDelta = $sourceProjectObjectMaterial->quantity - $request['material']['quantity'];
            }

            if ($materialAmountDelta < 0) {
                $errors['negativeMaterialQuantity'] = 'Материала недостаточно на объекте отправления';
            }
        }

        if (count($errors) == 0) {
            return response()->json([
                'result' => 'ok'
            ], 200, [], JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json([
                'result' => 'error',
                'errors' => $errors
            ], 400, [], JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param q3wMaterialOperation $q3wMaterialOperation
     * @return Application|Factory|Response|View
     */
    public function show(Request $request)
    {

        $operation = q3wMaterialOperation::findOrFail($request->operationId);

        $materials = DB::table('q3w_operation_materials as a')
            ->leftJoin('q3w_material_standards as b', 'a.standard_id', '=', 'b.id')
            ->leftJoin('q3w_material_types as d', 'b.material_type', '=', 'd.id')
            ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
            ->where('a.material_operation_id', '=', $operation->id)
            ->get(['a.id',
                'a.standard_id',
                'a.amount',
                'a.initial_amount',
                'a.quantity',
                'a.edit_states',
                'a.initial_quantity',
                'b.name as standard_name',
                'b.material_type',
                'b.weight as standard_weight',
                'd.accounting_type',
                'd.measure_unit',
                'e.value as measure_unit_value'])
            ->toArray();

        foreach ($materials as $material) {
            $material->edit_states = json_decode($material->edit_states);
        }

        $materials = json_encode($materials, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);

        $operationData = $operation->toJSON(JSON_OBJECT_AS_ARRAY);

        return view('materials.operations.transfer.view')->with([
            'operationData' => $operationData,
            'operationMaterials' => $materials,
            'currentUserId' => Auth::id(),
            'allowEditing' => $this->allowEditing($operation)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param q3wMaterialOperation $q3wMaterialOperation
     * @return Response
     */
    public function edit(q3wMaterialOperation $q3wMaterialOperation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function update(Request $request)
    {
        $requestData = json_decode($request["data"]);

        if (isset($requestData->operationId)) {
            $operation = q3wMaterialOperation::findOrFail($requestData->operationId);
        }

        $moveToConflict = false;
        DB::beginTransaction();
        foreach ($requestData->materials as $inputMaterial) {
            $materialStandard = q3wMaterialStandard::findOrFail($inputMaterial->standard_id);

            if (in_array("addedByRecipient", $inputMaterial->edit_states) ||
                in_array("deletedByRecipient", $inputMaterial->edit_states)) {
                $moveToConflict = true;
            }

            $operationMaterial = q3wOperationMaterial::find($inputMaterial->id);
            if (!isset($operationMaterial)) {
                $moveToConflict = true;

                $operationMaterial = new q3wOperationMaterial([
                        'material_operation_id' => $operation->id,
                        'standard_id' => $materialStandard->id,
                        'amount' => $inputMaterial->amount,
                        'quantity' => $inputMaterial->quantity,
                        'edit_states' => json_encode($inputMaterial->edit_states)
                    ]
                );
                $operationMaterial->save();
            } else {
                $operationMaterial->amount = $inputMaterial->amount;
                $operationMaterial->quantity = $inputMaterial->quantity;
                $operationMaterial->edit_states = json_encode($inputMaterial->edit_states);
                $operationMaterial->save();
            }

            if (!$moveToConflict) {
                if ($operationMaterial->initial_amount != $operationMaterial->amount ||
                    $operationMaterial->initial_quantity != $operationMaterial->quantity) {
                    $moveToConflict = true;
                }
            }
        }

        DB::commit();

        switch ($operation->operation_route_stage_id) {
            case 7:
            case 8:
                if ($moveToConflict) {
                    $this->createConflict($operation);
                } else {
                    $this->move($operation);
                }
                break;
            case 15:
                if (Auth::id() == ProjectObject::findOrFail($operation->source_project_object_id)->resp_users->first()->user_id) {
                    $this->move($operation);
                }
                break;
            case 16:
                if (Auth::id() == ProjectObject::findOrFail($operation->destination_project_object_id)->resp_users->first()->user_id) {
                    $this->move($operation);
                }
                break;
        }

        if ($moveToConflict) {
            $this->createConflict($operation);
        } else {
            $this->move($operation);
        }
    }

    public function createConflict($operation)
    {

        switch ($operation->operation_route_stage_id) {
            case 7:
                $projectObject = ProjectObject::findOrFail($operation->source_project_object_id);
                $responsibleUser = $projectObject->resp_users->first();
                $notification = new Notification();
                $notification->save();
                $notification->additional_info = ' Ссылка на операцию: ' . route('materials.operations.transfer.view') . '?operationId=' . $operation->id;
                $notification->update([
                    'name' => 'Конфликт в операции',
                    'user_id' => $responsibleUser->user_id,
                    //'task_id' => $task->id,
                    //'contractor_id' => $task->contractor_id,
                    'object_id' => $operation->source_project_object_id,
                    //'object_id' => isset($task->project->object->id) ? $task->project->object->id : null,
                    'created_at' => now(),
                    'type' => 11
                ]);
                $operation->operation_route_stage_id = 15;
                $operation->save();
                break;
            case 8:
                $projectObject = ProjectObject::findOrFail($operation->destination_project_object_id);
                $responsibleUser = $projectObject->resp_users->first();
                $notification = new Notification();
                $notification->save();
                $notification->additional_info = ' Ссылка на операцию: ' . route('materials.operations.transfer.view') . '?operationId=' . $operation->id;
                $notification->update([
                    'name' => 'Конфликт в операции',
                    'user_id' => $responsibleUser->user_id,
                    //'task_id' => $task->id,
                    //'contractor_id' => $task->contractor_id,
                    'object_id' => $operation->destination_project_object_id,
                    //'object_id' => isset($task->project->object->id) ? $task->project->object->id : null,
                    'created_at' => now(),
                    'type' => 11
                ]);
                $operation->operation_route_stage_id = 16;
                $operation->save();
                break;
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param q3wMaterialOperation $q3wMaterialOperation
     * @return Response
     */
    public function destroy(q3wMaterialOperation $operation)
    {

    }

    public function allowEditing(q3wMaterialOperation $operation)
    {
        switch ($operation->operation_route_stage_id) {
            case 7:
                return Auth::id() == $operation->source_responsible_user_id;
            case 8:
                return Auth::id() == $operation->destination_responsible_user_id;
            case 15:
                return Auth::id() == ProjectObject::findOrFail($operation->source_project_object_id)->resp_users->first()->user_id;
            case 16:
                return Auth::id() == ProjectObject::findOrFail($operation->destination_project_object_id)->resp_users->first()->user_id;
            default:
                return false;
        }
    }

    //TODO - Сейчас, если указать много объектов с 1 стандартом и количеством, превышающее на объекте отправления - валидация это пропустит. Исправить :)
    public function validateNewMaterialList(Request $request)
    {
        $errors = [];

        $requestData = json_decode($request['data']);

        $projectObject = ProjectObject::find($requestData->source_project_object_id);
        if (!isset($projectObject)) {
            $errors['sourceProjectObjectNotFound'] = 'Объект отправления не найден';
        }

        foreach ($requestData->materials as $material) {
            if (!isset($material->amount) || $material->amount == null || $material->amount == 0 || $material->amount == '') {
                $errors['amountIsNull'][] = array('id' => $material->id, 'message' => 'Количество в штуках не указано');
            }

            if (!isset($material->quantity) || $material->quantity == null || $material->quantity == 0 || $material->quantity == '') {
                $errors['quantityIsNull'][] = array('id' => $material->id, 'message' => 'Количество не указано');
            }

            $materialStandard = q3wMaterialStandard::find($material->standard_id);

            $accountingType = $materialStandard->materialType->accounting_type;

            if ($accountingType == 2) {
                $sourceProjectObjectMaterial = (new q3wMaterial)
                    ->where('project_object', $projectObject->id)
                    ->where('standard_id', $material->standard_id)
                    ->where('quantity', $material->quantity)
                    ->get()
                    ->first();
            } else {
                $sourceProjectObjectMaterial = (new q3wMaterial)
                    ->where('project_object', $projectObject->id)
                    ->where('standard_id', $material->standard_id)
                    ->get()
                    ->first();
            }

            if (!isset($sourceProjectObjectMaterial)) {
                $errors['materialNotFound'][] = array('id' => $material->id, 'message' => 'Этого материала не существует на объекте отправления');
            } else {
                if ($accountingType == 2) {
                    $materialAmountDelta = $sourceProjectObjectMaterial->amount - $material->amount;
                } else {
                    $materialAmountDelta = $sourceProjectObjectMaterial->quantity - $material->quantity * $material->amount;
                }

                if ($materialAmountDelta < 0) {
                    $errors['negativeMaterialQuantity'] = array('id' => $material->id, 'message' => 'Материала недостаточно на объекте отправления');
                }
            }
        }

        if (count($errors) == 0) {
            return response()->json([
                'result' => 'ok'
            ], 200, [], JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json([
                'result' => 'error',
                'errors' => $errors
            ], 400, [], JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
        }
    }
}
