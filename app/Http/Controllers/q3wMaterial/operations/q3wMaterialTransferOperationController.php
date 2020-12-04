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
                    'b.name as standard_name',
                    'b.material_type',
                    'b.weight as standard_weight',
                    'd.accounting_type',
                    'd.measure_unit',
                    'e.value as measure_unit_value',
                    DB::raw('CASE WHEN `d`.`accounting_type` = 1 THEN `a`.`quantity` END AS `length_quantity`'),
                    DB::raw('CASE WHEN `d`.`accounting_type` = 1 THEN `a`.`amount` ELSE `a`.`quantity` END AS `material_quantity`')])
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

    public function move(Request $request)
    {
        $requestData = json_decode($request->all()["data"], JSON_OBJECT_AS_ARRAY /*| JSON_THROW_ON_ERROR)*/);

        if (isset($requestData['operationId'])) {
            $operation = q3wMaterialOperation::findOrFail($requestData['operationId']);
        }

        (new q3wMaterialSnapshot)->takeSnapshot($operation, ProjectObject::find($operation->source_project_object_id));
        (new q3wMaterialSnapshot)->takeSnapshot($operation, ProjectObject::find($operation->destination_project_object_id));

        foreach ($requestData['materials'] as $inputMaterial) {
            $materialStandard = q3wMaterialStandard::findOrFail($inputMaterial['standard_id']);
            $materialType = q3wMaterialType::findOrFail($materialStandard->material_type);

            $inputMaterialAmount = $materialType->accounting_type == 1 ? $inputMaterial['material_quantity'] : null;
            $inputMaterialQuantity = $materialType->accounting_type == 1 ? $inputMaterial['length_quantity'] : $inputMaterial['material_quantity'];

            // Изменение материалов у отправителя
            if ($materialType->accounting_type == 1) {
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
                if ($materialType->accounting_type == 1) {
                    $materialAmountDelta = $sourceProjectObjectMaterial->amount - $request['material']['quantity'];
                } else {
                    $materialAmountDelta = $sourceProjectObjectMaterial->quantity - $request['material']['quantity'];
                }

                if ($materialAmountDelta < 0) {

                }

                if ($materialType->accounting_type == 1) {
                    $materialAmountDelta = $sourceProjectObjectMaterial->amount - $inputMaterialAmount;
                    if ($materialAmountDelta >= 0) {
                        $sourceProjectObjectMaterial->amount = $materialAmountDelta;
                    } else {
                        abort(400, 'Материала недостаточно на объекте отправления');
                    }
                } else {
                    $materialAmountDelta = $sourceProjectObjectMaterial->quantity - $inputMaterialAmount;
                    if ($materialAmountDelta >= 0) {
                        $sourceProjectObjectMaterial->quantity = $materialAmountDelta;
                    } else {
                        abort(400, 'Материала недостаточно на объекте отправления');
                    }
                }

                $sourceProjectObjectMaterial->save();
            } else {
                abort(400, 'Материала не сузествует на объекте отправителя');
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
                if ($materialType->accounting_type == 1) {
                    $destinationProjectObjectMaterial->amount = $destinationProjectObjectMaterial->amount + $inputMaterialAmount;
                } else {
                    $destinationProjectObjectMaterial->quantity = $destinationProjectObjectMaterial->quantity + $inputMaterialQuantity;
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
        $this->moveOperationToNextStage($operation->id);

        DB::commit();

        return response()->json([
            'result' => 'ok'
        ], 200);
    }

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
        //TODO Нужно разобраться с механизмом искллючений и откатом транзакции
        DB::beginTransaction();
        $requestData = json_decode($request->all()["data"], JSON_OBJECT_AS_ARRAY /*| JSON_THROW_ON_ERROR)*/);

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
            if ($inputMaterial['accounting_type'] == 1) {
                $sourceMaterial = q3wMaterial::where('project_object', $requestData['source_project_object_id'])
                    ->where('standard_id', $inputMaterial['standard_id'])
                    ->where('quantity', $inputMaterial['length_quantity'])
                    ->firstOrFail();

                if ($inputMaterial['material_quantity'] > $sourceMaterial['amount']) {
                    abort(400, 'Bad quantity for standard ' . $inputMaterial['standard_id']);
                }

            }
        }

        $materialOperation = new q3wMaterialOperation([
            'operation_route_id' => 2,
            'operation_route_stage_id' => $operationRouteStage,
            'source_project_object_id' => $requestData['source_project_object_id'],
            'destination_project_object_id' => $requestData['destination_project_object_id'],
            'date_start' => $requestData['date_start'],
            'date_end' => $requestData['date_end'],
            'creator_user_id' => Auth::id(),
            'source_responsible_user_id' => $requestData['source_responsible_user_id'],
            'destination_responsible_user_id' => $requestData['destination_responsible_user_id'],
        ]);

        $materialOperation->save();

        foreach ($requestData['materials'] as $inputMaterial) {
            $materialStandard = q3wMaterialStandard::findOrFail($inputMaterial['standard_id']);
            $materialType = q3wMaterialType::findOrFail($materialStandard->material_type);

            $inputMaterialAmount = $materialType->accounting_type == 1 ? $inputMaterial['material_quantity'] : null;
            $inputMaterialQuantity = $materialType->accounting_type == 1 ? $inputMaterial['length_quantity'] : $inputMaterial['material_quantity'];

            $operationMaterial = new q3wOperationMaterial([
                'material_operation_id' => $materialOperation->id,
                'standard_id' => $materialStandard->id,
                'amount' => $inputMaterialAmount,
                'quantity' => $inputMaterialQuantity
            ]);

            $operationMaterial->save();

            /*if ($materialType -> accounting_type == 1) {
                $material = q3wMaterial::where('project_object', $requestData['project_object_id'])
                    ->where('standard_id', $materialStandard -> id)
                    ->where ('quantity', $inputMaterialQuantity)
                    ->first();
            } else {
                $material = q3wMaterial::where('project_object', $requestData['project_object_id'])
                    ->where('standard_id', $materialStandard -> id)
                    ->first();
            }

            if (isset($material)) {
                if ($materialType -> accounting_type == 1) {
                    $material -> amount = $material -> amount + $inputMaterialAmount;
                } else {
                    $material -> quantity = $material -> quantity + $inputMaterialQuantity;
                }

                $material -> save();
            } else {
                $material = new q3wMaterial([
                   'standard_id' => $materialStandard -> id,
                   'project_object' => $requestData['project_object_id'],
                   'amount' => $inputMaterialAmount,
                   'quantity' => $inputMaterialQuantity
                ]);

                $material -> save();
            }*/
        }

        //(new q3wMaterialSnapshot)->takeSnapshot($materialOperation, ProjectObject::find($requestData['project_object_id']));

        DB::commit();

        $this->moveOperationToNextStage($materialOperation->id);

        return response()->json([
            'result' => 'ok',
        ], 200);
    }

    /**
     * Валидирует одночный эталон материала для операции
     * Материал считается валидным для перемещения если:
     *      1) Существует переданная в запросе заявка (operationId)
     *      2) Существует объект отправления (sourceProjectObjectId)
     *      3) На объекте сущесвует
     *          3.1) Для штучного учета: Эталон + равное количество в единицах измерения (Пример: Шпунт VL 606A 14.5 м.п.)
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
                'b.name as standard_name',
                'b.material_type',
                'b.weight as standard_weight',
                'd.accounting_type',
                'd.measure_unit',
                'e.value as measure_unit_value',
                DB::raw('CASE WHEN `d`.`accounting_type` = 1 THEN `a`.`quantity` END AS `length_quantity`'),
                DB::raw('CASE WHEN `d`.`accounting_type` = 1 THEN `a`.`amount` ELSE `a`.`quantity` END AS `material_quantity`')])
            ->toJSON(JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);

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
     * @param q3wMaterialOperation $q3wMaterialOperation
     * @return Response
     */
    public function update(Request $request, q3wMaterialOperation $q3wMaterialOperation)
    {
        //
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
            default:
                return false;
        }
    }
}
